<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventRanking;
use App\Models\ExamResult;
use App\Models\Participant;
use App\Models\ScoringConfig;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\{Fill, Alignment, Border};

/**
 * RankingService
 *
 * Alur:
 * 1. Baca Excel hasil ujian
 * 2. Untuk setiap no_register:
 *    - Cek apakah terdaftar di tabel participants (event yang sama)
 *    - Cek apakah memiliki catatan hadir di tabel attendances
 * 3. Hitung skor total berdasarkan ScoringConfig
 * 4. Urutkan: skor DESC, waktu_akhir ASC (tiebreaker)
 * 5. Assign rank (dense rank — skor sama = rank sama)
 * 6. Simpan ke event_rankings
 */
class RankingService
{
    // ── REQUIRED EXCEL COLUMNS ──────────────────────────────────
    private const COL_MAP = [
        'noreg'       => ['no_register', 'noreg', 'no reg', 'nomor registrasi'],
        'kode_paket'  => ['kode_paket', 'kode paket', 'paket'],
        'kelompok'    => ['nama_kelompok_ujian', 'nama kelompok ujian', 'kelompok', 'mata ujian', 'mapel'],
        'benar'       => ['benar', 'jawaban benar', 'correct'],
        'salah'       => ['salah', 'jawaban salah', 'wrong', 'incorrect'],
        'kosong'      => ['kosong', 'tidak dijawab', 'empty', 'blank'],
        'waktu_awal'  => ['waktu_awal', 'waktu awal', 'start_time', 'mulai'],
        'waktu_akhir' => ['waktu_akhir', 'waktu akhir', 'end_time', 'selesai', 'finish'],
    ];

    // ════════════════════════════════════════════════════════════
    //  IMPORT + PROCESS
    // ════════════════════════════════════════════════════════════
    public function import(Event $event, UploadedFile $file): array
    {
        // ── Baca file ──
        try {
            $spreadsheet = IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows  = $sheet->toArray(null, true, true, false);
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'Gagal membaca file: ' . $e->getMessage()];
        }

        if (count($rows) < 2) {
            return ['success' => false, 'message' => 'File kosong atau hanya berisi header.'];
        }

        // ── Detect kolom ──
        $header = array_map(fn($h) => strtolower(trim((string)$h)), $rows[0]);
        $cols   = $this->resolveColumns($header);

        if (!isset($cols['noreg']) || !isset($cols['benar'])) {
            return ['success' => false, 'message' => 'Kolom no_register dan benar wajib ada. Periksa format file.'];
        }

        // ── Ambil scoring config ──
        $config = ScoringConfig::firstOrCreate(
            ['event_id' => $event->id],
            ['point_benar' => 2, 'point_salah' => 1, 'point_kosong' => 0, 'tiebreak_by_time' => true]
        );

        // ── Ambil semua peserta event (hanya yang hadir) ──
        $presentNoregs = Participant::where('event_id', $event->id)
            ->whereHas('attendance')
            ->pluck('id', 'noreg')   // noreg => participant_id
            ->toArray();

        // ── Ambil semua peserta event (terdaftar, tidak harus hadir) ──
        $registeredNoregs = Participant::where('event_id', $event->id)
            ->pluck('id', 'noreg')
            ->toArray();

        DB::beginTransaction();
        try {
            // Hapus data lama event ini
            ExamResult::where('event_id', $event->id)->delete();
            EventRanking::where('event_id', $event->id)->delete();

            $dataRows  = array_slice($rows, 1);
            $rawBuckets = []; // noreg => [ rows... ]
            $skipped   = 0;
            $invalid   = [];
            $absent    = [];

            foreach ($dataRows as $row) {
                $noreg = trim((string)($row[$cols['noreg']] ?? ''));
                if (!$noreg) continue;

                $benar  = (int)($row[$cols['benar']]  ?? 0);
                $salah  = (int)($row[$cols['salah']]  ?? 0);
                $kosong = (int)($row[$cols['kosong']] ?? 0);
                $ka     = isset($cols['waktu_akhir']) ? trim((string)($row[$cols['waktu_akhir']] ?? '')) : null;
                $kaw    = isset($cols['waktu_awal'])  ? trim((string)($row[$cols['waktu_awal']]  ?? '')) : null;
                $kode   = isset($cols['kode_paket']) ? trim((string)($row[$cols['kode_paket']] ?? '')) : null;
                $kelp   = isset($cols['kelompok'])   ? trim((string)($row[$cols['kelompok']]   ?? '')) : null;

                $rowScore      = $config->calcScore($benar, $salah, $kosong);
                $participantId = $presentNoregs[$noreg] ?? null;

                // Tentukan status
                $status = 'valid';
                if (!isset($registeredNoregs[$noreg])) {
                    $status = 'invalid_noreg';
                } elseif (!isset($presentNoregs[$noreg])) {
                    $status = 'absent';
                }

                // Simpan raw row
                ExamResult::create([
                    'event_id'       => $event->id,
                    'participant_id' => $presentNoregs[$noreg] ?? ($registeredNoregs[$noreg] ?? null),
                    'noreg'          => $noreg,
                    'kode_paket'     => $kode,
                    'nama_kelompok'  => $kelp,
                    'benar'          => $benar,
                    'salah'          => $salah,
                    'kosong'         => $kosong,
                    'waktu_awal'     => $kaw  ? $this->parseDateTime($kaw)  : null,
                    'waktu_akhir'    => $ka   ? $this->parseDateTime($ka)   : null,
                    'row_score'      => $rowScore,
                ]);

                // Kelompokkan per noreg untuk aggregasi
                if (!isset($rawBuckets[$noreg])) {
                    $rawBuckets[$noreg] = [
                        'status' => $status,
                        'participant_id' => $presentNoregs[$noreg] ?? ($registeredNoregs[$noreg] ?? null),
                        'rows' => [],
                    ];
                }
                $rawBuckets[$noreg]['rows'][] = [
                    'benar'       => $benar,
                    'salah'       => $salah,
                    'kosong'      => $kosong,
                    'row_score'   => $rowScore,
                    'waktu_akhir' => $ka,
                ];
            }

            // ── Aggregasi per noreg ──
            $rankings = [];
            foreach ($rawBuckets as $noreg => $bucket) {
                $rows_   = $bucket['rows'];
                $tBenar  = array_sum(array_column($rows_, 'benar'));
                $tSalah  = array_sum(array_column($rows_, 'salah'));
                $tKosong = array_sum(array_column($rows_, 'kosong'));
                $tScore  = round(array_sum(array_column($rows_, 'row_score')), 2);

                // Waktu akhir = timestamp terbesar dari semua kelompok (paling akhir selesai)
                $times = array_filter(array_column($rows_, 'waktu_akhir'));
                $maxWaktu = $times ? max($times) : null;

                $rankings[] = [
                    'event_id'       => $event->id,
                    'participant_id' => $bucket['participant_id'],
                    'noreg'          => $noreg,
                    'total_score'    => $tScore,
                    'total_benar'    => $tBenar,
                    'total_salah'    => $tSalah,
                    'total_kosong'   => $tKosong,
                    'waktu_akhir'    => $maxWaktu ? $this->parseDateTime($maxWaktu) : null,
                    'status'         => $bucket['status'],
                    'rank'           => null,
                ];
            }

            // ── Sort & assign rank (hanya untuk status=valid) ──
            $valid   = array_filter($rankings, fn($r) => $r['status'] === 'valid');
            $invalid = array_filter($rankings, fn($r) => $r['status'] !== 'valid');

            usort($valid, function ($a, $b) use ($config) {
                if ($a['total_score'] !== $b['total_score']) {
                    return $b['total_score'] <=> $a['total_score']; // skor tinggi = rank rendah
                }
                if ($config->tiebreak_by_time && $a['waktu_akhir'] && $b['waktu_akhir']) {
                    return strcmp($a['waktu_akhir'], $b['waktu_akhir']); // lebih awal = lebih baik
                }
                return 0;
            });

            // Dense rank
            $rank = 0;
            $prevScore = null;
            $prevWaktu = null;
            foreach ($valid as &$r) {
                $sameScore = ($r['total_score'] === $prevScore);
                $sameWaktu = ($config->tiebreak_by_time ? $r['waktu_akhir'] === $prevWaktu : true);
                if (!$sameScore || !$sameWaktu) $rank++;
                $r['rank']  = $rank;
                $prevScore  = $r['total_score'];
                $prevWaktu  = $r['waktu_akhir'];
            }
            unset($r);

            // Simpan semua ranking
            foreach (array_merge(array_values($valid), array_values($invalid)) as $r) {
                EventRanking::create($r);
            }

            DB::commit();

            $validCount   = count($valid);
            $invalidCount = count(array_filter($invalid, fn($r) => $r['status'] === 'invalid_noreg'));
            $absentCount  = count(array_filter($invalid, fn($r) => $r['status'] === 'absent'));

            return [
                'success'       => true,
                'total_rows'    => count($dataRows),
                'valid'         => $validCount,
                'invalid_noreg' => $invalidCount,
                'absent'        => $absentCount,
                'message'       => "Import berhasil! {$validCount} peserta valid diranking. " .
                    ($invalidCount ? "{$invalidCount} NOREG tidak terdaftar. " : '') .
                    ($absentCount  ? "{$absentCount} peserta tidak hadir diabaikan." : ''),
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    // ════════════════════════════════════════════════════════════
    //  GENERATE TEMPLATE
    // ════════════════════════════════════════════════════════════
    public function generateTemplate(Event $event): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Hasil Ujian');

        // ── Header ──
        $headers = [
            'no_register',
            'kode_paket',
            'nama_kelompok_ujian',
            'benar',
            'salah',
            'kosong',
            'waktu_awal',
            'waktu_akhir',
        ];
        $sheet->fromArray([$headers], null, 'A1');

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'C62828']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
        ];
        $sheet->getStyle('A1:H1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(24);

        // ── Data contoh dari peserta event ini ──
        $participants = Participant::where('event_id', $event->id)
            ->limit(5)
            ->get();

        $row = 2;
        foreach ($participants as $p) {
            $sheet->fromArray([[
                $p->noreg,
                'TO-XXXXX',
                'MATEMATIKA',
                0,      // benar
                0,      // salah
                0,      // kosong
                now()->format('Y-m-d H:i:s'),
                now()->format('Y-m-d H:i:s'),
            ]], null, 'A' . $row);
            $row++;
        }

        // ── Instruksi di bawah ──
        $instrRow = $row + 1;
        $sheet->setCellValue('A' . $instrRow, '📋 PETUNJUK PENGISIAN:');
        $sheet->getStyle('A' . $instrRow)->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('C62828'));

        $instruksi = [
            'no_register : Nomor registrasi peserta (harus terdaftar dan hadir di event ini)',
            'kode_paket  : Kode paket soal (opsional)',
            'nama_kelompok_ujian : Nama mata ujian / kelompok soal (boleh kosong)',
            'benar  : Jumlah jawaban benar (angka)',
            'salah  : Jumlah jawaban salah (angka)',
            'kosong : Jumlah soal tidak dijawab (angka)',
            'waktu_awal  : Waktu mulai ujian (format: YYYY-MM-DD HH:MM:SS)',
            'waktu_akhir : Waktu selesai ujian (format: YYYY-MM-DD HH:MM:SS) — dipakai sebagai tiebreaker',
            '',
            'CATATAN: Satu peserta bisa memiliki lebih dari 1 baris (untuk beberapa mata ujian).',
            'Skor total = jumlah semua baris milik peserta tersebut.',
        ];
        foreach ($instruksi as $i => $ins) {
            $sheet->setCellValue('A' . ($instrRow + $i + 1), $ins);
            $sheet->getStyle('A' . ($instrRow + $i + 1))->getFont()->setSize(10)->setItalic(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('555555'));
        }

        // ── Column widths ──
        $widths = ['A' => 18, 'B' => 14, 'C' => 24, 'D' => 8, 'E' => 8, 'F' => 8, 'G' => 22, 'H' => 22];
        foreach ($widths as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }

        // ── Info event ──
        $infoSheet = $spreadsheet->createSheet();
        $infoSheet->setTitle('Info Event');
        $infoSheet->setCellValue('A1', 'Event');
        $infoSheet->setCellValue('B1', $event->name);
        $infoSheet->setCellValue('A2', 'Unit');
        $infoSheet->setCellValue('B2', $event->unit->name);
        $infoSheet->setCellValue('A3', 'Tanggal');
        $infoSheet->setCellValue('B3', $event->event_date?->format('d/m/Y') ?? '-');
        $infoSheet->setCellValue('A4', 'Total Peserta');
        $infoSheet->setCellValue('B4', $event->participants()->count());
        $infoSheet->setCellValue('A5', 'Template dibuat');
        $infoSheet->setCellValue('B5', now()->format('d/m/Y H:i'));

        $tmpPath = sys_get_temp_dir() . '/template_hasil_' . $event->id . '_' . time() . '.xlsx';
        $writer  = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($tmpPath);
        return $tmpPath;
    }

    // ════════════════════════════════════════════════════════════
    //  EXPORT RANKING
    // ════════════════════════════════════════════════════════════
    public function exportRanking(Event $event): string
    {
        $rankings = EventRanking::with('participant')
            ->where('event_id', $event->id)
            ->where('status', 'valid')
            ->orderBy('rank')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Ranking');

        $headers = ['Rank', 'Medal', 'NOREG', 'Nama', 'Kelas', 'Sekolah', 'Skor Total', 'Benar', 'Salah', 'Kosong', 'Waktu Akhir'];
        $sheet->fromArray([$headers], null, 'A1');
        $sheet->getStyle('A1:K1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'C62828']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $row = 2;
        foreach ($rankings as $r) {
            $medal = match ($r->rank) {
                1 => '🥇',
                2 => '🥈',
                3 => '🥉',
                default => ''
            };
            $p = $r->participant;
            $sheet->fromArray([[
                $r->rank,
                $medal,
                $r->noreg,
                $p?->name ?? '-',
                $p?->class ?? '-',
                $p?->school ?? '-',
                $r->total_score,
                $r->total_benar,
                $r->total_salah,
                $r->total_kosong,
                $r->waktu_akhir?->format('d/m/Y H:i:s') ?? '-',
            ]], null, 'A' . $row);

            // Highlight top 3
            if ($r->rank <= 3) {
                $bg = match ($r->rank) {
                    1 => 'FFF9C4',
                    2 => 'F5F5F5',
                    3 => 'FBE9E7',
                    default => 'FFFFFF'
                };
                $sheet->getStyle("A{$row}:K{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($bg);
                $sheet->getStyle("A{$row}:K{$row}")->getFont()->setBold(true);
            }
            $row++;
        }

        foreach (['A' => 6, 'B' => 6, 'C' => 16, 'D' => 22, 'E' => 10, 'F' => 22, 'G' => 12, 'H' => 8, 'I' => 8, 'J' => 8, 'K' => 20] as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }

        $tmpPath = sys_get_temp_dir() . '/ranking_' . $event->slug . '_' . time() . '.xlsx';
        IOFactory::createWriter($spreadsheet, 'Xlsx')->save($tmpPath);
        return $tmpPath;
    }

    // ── PRIVATE HELPERS ─────────────────────────────────────────

    private function resolveColumns(array $header): array
    {
        $result = [];
        foreach (self::COL_MAP as $field => $aliases) {
            foreach ($header as $idx => $cell) {
                if (in_array($cell, $aliases, true)) {
                    $result[$field] = $idx;
                    break;
                }
            }
        }
        return $result;
    }

    private function parseDateTime(string $dt): ?string
    {
        try {
            return \Carbon\Carbon::parse($dt)->format('Y-m-d H:i:s');
        } catch (\Throwable) {
            return null;
        }
    }
}
