<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventRanking;
use App\Models\ExamResult;
use App\Models\ScoringConfig;
use App\Services\RankingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RankingController extends Controller
{
    public function __construct(private RankingService $rankingService) {}

    /** Halaman utama ranking event */
    public function index(Event $event)
    {
        $this->authorizeEvent($event);

        $config = ScoringConfig::firstOrCreate(
            ['event_id' => $event->id],
            ['point_benar' => 2, 'point_salah' => -1, 'point_kosong' => 0, 'tiebreak_by_time' => true]
        );

        $rankings = EventRanking::with('participant')
            ->where('event_id', $event->id)
            ->where('status', 'valid')
            ->orderBy('rank')
            ->get();

        $invalidNoregs = EventRanking::with('participant')
            ->where('event_id', $event->id)
            ->where('status', 'invalid_noreg')
            ->get();

        $absentees = EventRanking::with('participant')
            ->where('event_id', $event->id)
            ->where('status', 'absent')
            ->get();

        // Detail per kelompok soal per peserta
        $examDetails = ExamResult::where('event_id', $event->id)
            ->orderBy('noreg')
            ->orderBy('nama_kelompok')
            ->get()
            ->groupBy('noreg');

        $stats = [
            'total_valid'   => $rankings->count(),
            'top3'          => $rankings->where('rank', '<=', 3),
            'avg_score'     => $rankings->count() ? round($rankings->avg('total_score'), 2) : 0,
            'max_score'     => $rankings->max('total_score') ?? 0,
            'invalid'       => $invalidNoregs->count(),
            'absent'        => $absentees->count(),
            'has_data'      => $rankings->count() > 0,
        ];

        // ── Bangun $rankingsByClass ─────────────────────────────────────
        // Kelompokkan peserta valid berdasarkan kelas peserta.
        // Peserta tanpa data kelas dilewati (tidak masuk podium per kelas).
        $rankingsByClass = $this->buildRankingsByClass($rankings, $config);

        return view('admin.rankings.index', compact(
            'event',
            'config',
            'rankings',
            'invalidNoregs',
            'absentees',
            'examDetails',
            'stats',
            'rankingsByClass',   // ← variabel yang ditunggu blade
        ));
    }

    /** Simpan konfigurasi poin */
    public function saveConfig(Request $request, Event $event)
    {
        $this->authorizeEvent($event);

        $validated = $request->validate([
            'point_benar'      => 'required|numeric|min:0|max:100',
            'point_salah'      => 'required|numeric|min:-100|max:100',
            'point_kosong'     => 'required|numeric|max:100',
            'tiebreak_by_time' => 'boolean',
            'scoring_note'     => 'nullable|string|max:255',
        ]);
        $validated['tiebreak_by_time'] = $request->boolean('tiebreak_by_time', true);

        ScoringConfig::updateOrCreate(
            ['event_id' => $event->id],
            $validated
        );

        // Jika ada data ranking, re-kalkulasi
        if (EventRanking::where('event_id', $event->id)->exists()) {
            $this->recalculate($event);
            return back()->with('success', 'Konfigurasi disimpan dan ranking di-recalculate.');
        }

        return back()->with('success', 'Konfigurasi poin berhasil disimpan.');
    }

    /** Upload + proses file Excel hasil ujian */
    public function upload(Request $request, Event $event)
    {
        $this->authorizeEvent($event);

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:20480',
        ], ['file.required' => 'Pilih file Excel hasil ujian.']);

        $result = $this->rankingService->import($event, $request->file('file'));

        if (!$result['success']) {
            return back()->with('error', $result['message']);
        }

        return back()->with('success', $result['message']);
    }

    /** Download template Excel */
    public function downloadTemplate(Event $event)
    {
        $this->authorizeEvent($event);
        $path = $this->rankingService->generateTemplate($event);
        $name = 'template_hasil_' . $event->slug . '.xlsx';
        return response()->download($path, $name)->deleteFileAfterSend(true);
    }

    /** Export ranking ke Excel */
    public function export(Event $event)
    {
        $this->authorizeEvent($event);
        $path = $this->rankingService->exportRanking($event);
        $name = 'ranking_' . $event->slug . '_' . now()->format('Ymd') . '.xlsx';
        return response()->download($path, $name)->deleteFileAfterSend(true);
    }

    /** Hapus semua data hasil & ranking event ini */
    public function reset(Event $event)
    {
        $this->authorizeEvent($event);
        ExamResult::where('event_id', $event->id)->delete();
        EventRanking::where('event_id', $event->id)->delete();
        return back()->with('success', 'Data hasil ujian dan ranking berhasil dihapus.');
    }

    /** AJAX: data ranking untuk DataTable */
    public function rankingData(Event $event)
    {
        $this->authorizeEvent($event);
        $data = EventRanking::with('participant')
            ->where('event_id', $event->id)
            ->where('status', 'valid')
            ->orderBy('rank')
            ->get()
            ->map(fn($r) => [
                'rank'        => $r->rank,
                'medal'       => match ($r->rank) {
                    1 => '🥇',
                    2 => '🥈',
                    3 => '🥉',
                    default => ''
                },
                'noreg'       => $r->noreg,
                'nama'        => $r->participant?->name ?? '-',
                'kelas'       => $r->participant?->class ?? '-',
                'sekolah'     => $r->participant?->school ?? '-',
                'ruang'       => $r->participant?->room ?? '-',
                'total_score' => $r->total_score,
                'benar'       => $r->total_benar,
                'salah'       => $r->total_salah,
                'kosong'      => $r->total_kosong,
                'waktu_akhir' => $r->waktu_akhir?->format('d/m/Y H:i:s') ?? '-',
            ]);
        return response()->json($data);
    }

    // ── PRIVATE ─────────────────────────────────────────────────

    private function authorizeEvent(Event $event): void
    {
        if (!$event->canBeManagedBy(Auth::user())) {
            abort(403, 'Anda tidak memiliki akses ke event ini.');
        }
    }

    /**
     * Bangun array $rankingsByClass dari koleksi $rankings yang sudah di-load
     * beserta relasi participant-nya.
     *
     * Struktur output:
     * [
     *   'VII'  => [
     *     'total'     => 30,
     *     'avg_score' => 85.4,
     *     'max_score' => 120,
     *     'top9'      => [ ['class_rank'=>1, 'name'=>'...', 'noreg'=>'...', 'total_score'=>120], ... ],
     *     'rankings'  => [ ['class_rank'=>1, 'overall_rank'=>3, 'noreg'=>'...', ...], ... ],
     *   ],
     *   ...
     * ]
     */
    private function buildRankingsByClass($rankings, ScoringConfig $config): array
    {
        // Hanya peserta yang memiliki data kelas
        $withClass = $rankings->filter(fn($r) => !empty($r->participant?->class));

        if ($withClass->isEmpty()) {
            return [];
        }

        // Kelompokkan berdasarkan kelas
        $grouped = $withClass->groupBy(fn($r) => $r->participant->class);

        $result = [];

        foreach ($grouped as $className => $classCollection) {
            // Urutkan: skor tertinggi dulu, lalu waktu_akhir terkecil (jika tiebreak aktif)
            $sorted = $classCollection
                ->sortByDesc('total_score')
                ->when(
                    $config->tiebreak_by_time,
                    fn($col) => $col->sortBy([
                        ['total_score', 'desc'],
                        ['waktu_akhir', 'asc'],
                    ])
                )
                ->values();

            // Assign class_rank (dense rank — skor+waktu sama = rank sama)
            $classRank = 0;
            $prevScore = null;
            $prevWaktu = null;
            $rankingsArr = [];

            foreach ($sorted as $r) {
                $sameScore = $r->total_score === $prevScore;
                // Bandingkan waktu sebagai string agar tidak masalah dengan tipe Carbon
                $sameWaktu = $config->tiebreak_by_time
                    ? (string) $r->waktu_akhir === (string) $prevWaktu
                    : true;

                if (!$sameScore || !$sameWaktu) {
                    $classRank++;
                }

                $rankingsArr[] = [
                    'class_rank'   => $classRank,
                    'overall_rank' => $r->rank,
                    'noreg'        => $r->noreg,
                    'name'         => $r->participant?->name  ?? '-',
                    'class'        => $r->participant?->class ?? '-',
                    'school'       => $r->participant?->school ?? '-',
                    'total_score'  => $r->total_score,
                    'total_benar'  => $r->total_benar,
                    'total_salah'  => $r->total_salah,
                    'total_kosong' => $r->total_kosong,
                    'waktu_akhir'  => $r->waktu_akhir?->format('H:i:s') ?? '-',
                ];

                $prevScore = $r->total_score;
                $prevWaktu = $r->waktu_akhir;
            }

            $scores = $sorted->pluck('total_score');

            $result[$className] = [
                'total'     => $sorted->count(),
                'avg_score' => $scores->count() ? round($scores->avg(), 2) : 0,
                'max_score' => $scores->max() ?? 0,
                // top9 = 9 entri pertama; blade hanya pakai class_rank 1-3 untuk podium
                'top9'      => array_slice($rankingsArr, 0, 9),
                'rankings'  => $rankingsArr,
            ];
        }

        // Urutkan kartu kelas secara natural (VII, VIII, IX / X, XI, XII / dsb.)
        uksort($result, 'strnatcmp');

        return $result;
    }

    private function recalculate(Event $event): void
    {
        $config   = ScoringConfig::where('event_id', $event->id)->first();
        $rankings = EventRanking::where('event_id', $event->id)->where('status', 'valid')->get();

        // Recalculate scores dari exam_results
        foreach ($rankings as $r) {
            $rows   = ExamResult::where('event_id', $event->id)->where('noreg', $r->noreg)->get();
            $tScore = 0;
            foreach ($rows as $row) {
                $rs = $config->calcScore($row->benar, $row->salah, $row->kosong);
                $row->update(['row_score' => $rs]);
                $tScore += $rs;
            }
            $maxWaktu = ExamResult::where('event_id', $event->id)->where('noreg', $r->noreg)
                ->whereNotNull('waktu_akhir')->max('waktu_akhir');
            $r->update([
                'total_score' => round($tScore, 2),
                'waktu_akhir' => $maxWaktu,
            ]);
        }

        // Re-sort & assign rank (dense rank)
        $sorted = EventRanking::where('event_id', $event->id)->where('status', 'valid')
            ->orderByDesc('total_score')
            ->when($config->tiebreak_by_time, fn($q) => $q->orderBy('waktu_akhir'))
            ->get();

        $rank      = 0;
        $prevScore = null;
        $prevWaktu = null;

        foreach ($sorted as $r) {
            // Gunakan == (bukan ===) agar Carbon dibandingkan sebagai value, bukan referensi
            $same = $r->total_score === $prevScore
                && (!$config->tiebreak_by_time || $r->waktu_akhir == $prevWaktu);
            if (!$same) {
                $rank++;
            }
            $r->update(['rank' => $rank]);
            $prevScore = $r->total_score;
            $prevWaktu = $r->waktu_akhir;
        }
    }
}
