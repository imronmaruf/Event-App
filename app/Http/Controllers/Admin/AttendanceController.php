<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceController extends Controller
{
    public function index(Event $event)
    {
        $this->authorizeEvent($event);

        $search = request('search');

        $query = Attendance::with('participant')
            ->where('event_id', $event->id)
            ->orderByDesc('attended_at');

        if ($search) {
            $query->whereHas('participant', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('noreg', 'like', "%{$search}%")
                    ->orWhere('school', 'like', "%{$search}%");
            });
        }

        $attendances = $query->paginate(30);
        $stats = [
            'total'  => $event->participants()->count(),
            'hadir'  => $event->attendances()->count(),
        ];
        $stats['belum']  = max(0, $stats['total'] - $stats['hadir']);
        $stats['persen'] = $stats['total'] > 0 ? round(($stats['hadir'] / $stats['total']) * 100, 1) : 0;

        return view('admin.attendances.index', compact('event', 'attendances', 'stats', 'search'));
    }

    /** Data per ruang/pengawas */
    public function byRoom(Event $event)
    {
        $this->authorizeEvent($event);

        $rooms = DB::table('participants')
            ->where('participants.event_id', $event->id)
            ->select(
                'participants.room',
                'participants.supervisor',
                DB::raw('COUNT(participants.id) as total'),
                DB::raw('COUNT(attendances.id) as hadir')
            )
            ->leftJoin('attendances', function ($join) use ($event) {
                $join->on('attendances.participant_id', '=', 'participants.id')
                    ->where('attendances.event_id', '=', $event->id);
            })
            ->groupBy('participants.room', 'participants.supervisor')
            ->orderBy('participants.room')
            ->get()
            ->map(function ($r) {
                $r->persen = $r->total > 0 ? round(($r->hadir / $r->total) * 100) : 0;
                return $r;
            });

        return view('admin.attendances.by_room', compact('event', 'rooms'));
    }

    /**
     * [SUPERADMIN] Tandai SEMUA peserta event sebagai hadir sekarang.
     * Peserta yang sudah hadir tidak akan diduplikasi.
     * Digunakan untuk keperluan testing input nilai / perangkingan.
     */
    public function markAllPresent(Request $request, Event $event)
    {
        $this->authorizeSuperAdmin();
        $this->authorizeEvent($event);

        $request->validate([
            'confirm' => 'required|in:HADIR',
        ], ['confirm.in' => 'Ketik "HADIR" untuk konfirmasi.']);

        $now = now();

        // Ambil semua participant_id yang BELUM punya record attendance di event ini
        $alreadyPresent = Attendance::where('event_id', $event->id)
            ->pluck('participant_id')
            ->toArray();

        $participants = $event->participants()
            ->whereNotIn('id', $alreadyPresent)
            ->pluck('id');

        $inserts = $participants->map(fn($pid) => [
            'event_id'       => $event->id,
            'participant_id' => $pid,
            'attended_at'    => $now,
            'created_at'     => $now,
            'updated_at'     => $now,
        ])->toArray();

        if (!empty($inserts)) {
            // Insert secara batch agar efisien untuk ratusan/ribuan peserta
            foreach (array_chunk($inserts, 500) as $chunk) {
                Attendance::insert($chunk);
            }
        }

        $count = count($inserts);

        return redirect()->route('admin.participants.index', $event)
            ->with('success', $count > 0
                ? "{$count} peserta berhasil ditandai hadir."
                : 'Semua peserta sudah tercatat hadir sebelumnya.');
    }

    /**
     * [SUPERADMIN] Reset / hapus SEMUA data absensi untuk event ini.
     */
    public function reset(Request $request, Event $event)
    {
        $this->authorizeSuperAdmin();
        $this->authorizeEvent($event);

        $request->validate([
            'confirm' => 'required|in:RESET',
        ], ['confirm.in' => 'Ketik "RESET" untuk konfirmasi penghapusan data.']);

        Attendance::where('event_id', $event->id)->delete();

        return redirect()->route('admin.participants.index', $event)
            ->with('success', 'Semua data absensi berhasil direset.');
    }

    /** Hapus satu record absensi (undo check-in) */
    public function destroy(Event $event, Attendance $attendance)
    {
        $this->authorizeEvent($event);
        abort_if($attendance->event_id !== $event->id, 404);
        $attendance->delete();
        return back()->with('success', 'Absensi peserta berhasil dibatalkan.');
    }

    /** Export ke Excel */
    public function export(Event $event)
    {
        $this->authorizeEvent($event);
        $filename = 'absensi_' . $event->slug . '_' . now()->format('Ymd_His') . '.xlsx';
        return response()->streamDownload(function () use ($event) {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Absensi');

            $headers = ['No', 'NOREG', 'Kode', 'Nama', 'Kelas', 'Sekolah', 'Ruang', 'Waktu Hadir'];
            $sheet->fromArray([$headers], null, 'A1');

            $attendances = Attendance::with('participant')
                ->where('event_id', $event->id)
                ->orderByDesc('attended_at')
                ->get();

            $rows = $attendances->map(function ($a, $i) {
                $p = $a->participant;
                return [
                    $i + 1,
                    $p?->noreg,
                    $p?->attendance_code,
                    $p?->name,
                    $p?->class,
                    $p?->school,
                    $p?->room,
                    $a->attended_at?->format('d/m/Y H:i:s'),
                ];
            })->toArray();

            $sheet->fromArray($rows, null, 'A2');

            $headerStyle = ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'color' => ['rgb' => 'C62828']]];
            $sheet->getStyle('A1:H1')->applyFromArray($headerStyle);
            foreach (range('A', 'H') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save('php://output');
        }, $filename, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    // ── PRIVATE ─────────────────────────────────────────────────

    private function authorizeEvent(Event $event): void
    {
        if (!$event->canBeManagedBy(Auth::user())) {
            abort(403, 'Anda tidak memiliki akses ke event ini.');
        }
    }

    private function authorizeSuperAdmin(): void
    {
        if (Auth::user()->role !== 'superadmin') {
            abort(403, 'Fitur ini hanya untuk Superadmin.');
        }
    }
}
