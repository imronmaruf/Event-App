<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Event;
use App\Services\AttendanceCodeService;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function __construct(private AttendanceCodeService $codeService) {}

    /** Halaman utama absensi publik — diakses via /absensi/{slug} */
    public function show(string $slug)
    {
        $event = Event::where('slug', $slug)
            ->with('unit')
            ->firstOrFail();

        // Statistik realtime
        $stats = [
            'total'  => $event->participants()->count(),
            'hadir'  => $event->attendances()->count(),
        ];
        $stats['belum']  = max(0, $stats['total'] - $stats['hadir']);
        $stats['persen'] = $stats['total'] > 0 ? round(($stats['hadir'] / $stats['total']) * 100, 1) : 0;

        return view('attendance.show', compact('event', 'stats'));
    }

    /** AJAX: Cari peserta berdasarkan kode absensi yang diinput */
    public function find(Request $request, string $slug)
    {
        $event = Event::where('slug', $slug)->firstOrFail();
        $code  = trim($request->input('code', ''));

        if (!$event->is_active) {
            return response()->json([
                'status'  => 'EVENT_INACTIVE',
                'message' => 'Event ini belum diaktifkan atau sudah ditutup. Hubungi panitia.',
            ]);
        }

        $result = $this->codeService->findParticipantByCode($event, $code);

        // Format response untuk frontend
        if ($result['status'] === 'FOUND') {
            $p = $result['peserta'];
            return response()->json([
                'status'      => 'FOUND',
                'sudahHadir'  => $result['sudahHadir'],
                'waktuHadir'  => $result['waktuHadir'],
                'peserta'     => [
                    'id'         => $p->id,
                    'noreg'      => $p->noreg,
                    'code'       => $p->attendance_code,
                    'nama'       => $p->name,
                    'kelas'      => $p->class,
                    'sekolah'    => $p->school,
                    'ruang'      => $p->room,
                    'pengawas'   => $p->supervisor,
                ],
            ]);
        }

        return response()->json([
            'status'  => $result['status'],
            'message' => $result['message'],
        ]);
    }

    /** AJAX: Konfirmasi absensi (tandai hadir) */
    public function markAttendance(Request $request, string $slug)
    {
        $event       = Event::where('slug', $slug)->firstOrFail();
        $participantId = (int) $request->input('participant_id');

        if (!$event->is_active) {
            return response()->json(['status' => 'ERROR', 'message' => 'Event tidak aktif.']);
        }

        $participant = $event->participants()->find($participantId);
        if (!$participant) {
            return response()->json(['status' => 'ERROR', 'message' => 'Peserta tidak ditemukan.']);
        }

        // Cek sudah hadir
        $alreadyAttended = Attendance::where('event_id', $event->id)
            ->where('participant_id', $participant->id)
            ->exists();

        if ($alreadyAttended) {
            return response()->json([
                'status'  => 'ALREADY',
                'message' => "{$participant->name} sudah tercatat hadir sebelumnya.",
            ]);
        }

        // Catat kehadiran (dengan pessimistic lock untuk mencegah race condition)
        try {
            \DB::transaction(function () use ($event, $participant, $request) {
                // Lock row untuk mencegah double-submit
                $check = Attendance::where('event_id', $event->id)
                    ->where('participant_id', $participant->id)
                    ->lockForUpdate()
                    ->first();

                if ($check) return; // sudah ada, skip

                Attendance::create([
                    'event_id'       => $event->id,
                    'participant_id' => $participant->id,
                    'attended_at'    => now(),
                    'recorded_by'    => $request->input('recorded_by'),
                    'ip_address'     => $request->ip(),
                ]);
            });
        } catch (\Throwable $e) {
            return response()->json(['status' => 'ERROR', 'message' => 'Gagal menyimpan: ' . $e->getMessage()]);
        }

        return response()->json([
            'status'  => 'SUCCESS',
            'message' => 'Kehadiran berhasil dicatat!',
            'peserta' => [
                'nama'    => $participant->name,
                'noreg'   => $participant->noreg,
                'kelas'   => $participant->class,
                'sekolah' => $participant->school,
                'ruang'   => $participant->room,
            ],
            'waktu'   => now()->timezone('Asia/Jakarta')->format('d/m/Y H:i:s'),
            'stats'   => [
                'hadir' => Attendance::where('event_id', $event->id)->count(),
                'total' => $event->participants()->count(),
            ],
        ]);
    }

    /** AJAX: Daftar peserta untuk tab Peserta (realtime) */
    public function participants(string $slug)
    {
        $event = Event::where('slug', $slug)->firstOrFail();

        $list = $event->participants()
            ->with('attendance')
            ->get()
            ->map(function ($p) {
                return [
                    'noreg'   => $p->noreg,
                    'code'    => $p->attendance_code,
                    'nama'    => $p->name,
                    'kelas'   => $p->class,
                    'sekolah' => $p->school,
                    'ruang'   => $p->room,
                    'hadir'   => (bool)$p->attendance,
                    'waktu'   => $p->attendance?->attended_at?->timezone('Asia/Jakarta')->format('H:i:s'),
                    'tgl'     => $p->attendance?->attended_at?->timezone('Asia/Jakarta')->format('d/m/Y'),
                ];
            })
            ->sortByDesc('hadir')
            ->values();

        return response()->json($list);
    }

    /** AJAX: Riwayat kehadiran untuk tab Riwayat */
    public function history(string $slug)
    {
        $event = Event::where('slug', $slug)->firstOrFail();

        $list = Attendance::with('participant')
            ->where('event_id', $event->id)
            ->orderByDesc('attended_at')
            ->get()
            ->map(function ($a) {
                $p = $a->participant;
                return [
                    'noreg'   => $p?->noreg,
                    'code'    => $p?->attendance_code,
                    'nama'    => $p?->name,
                    'kelas'   => $p?->class,
                    'sekolah' => $p?->school,
                    'ruang'   => $p?->room,
                    'waktu'   => $a->attended_at->timezone('Asia/Jakarta')->format('H:i:s'),
                    'tgl'     => $a->attended_at->timezone('Asia/Jakarta')->format('d/m/Y'),
                ];
            });

        return response()->json($list);
    }

    /** AJAX: Data per ruang untuk tab Pengawas */
    public function rooms(string $slug)
    {
        $event = Event::where('slug', $slug)->firstOrFail();

        $rooms = \DB::table('participants')
            ->where('participants.event_id', $event->id)
            ->select(
                'participants.room',
                'participants.supervisor',
                \DB::raw('COUNT(participants.id) as total'),
                \DB::raw('COUNT(attendances.id) as hadir')
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

        return response()->json($rooms);
    }
}
