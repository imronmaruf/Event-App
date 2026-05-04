<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Participant;
use App\Services\AttendanceCodeService;
use App\Services\ImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ParticipantController extends Controller
{
    public function __construct(
        private ImportService $importService,
        private AttendanceCodeService $codeService,
    ) {}

    public function index(Event $event)
    {
        $this->authorizeEvent($event);

        $search = request('search');
        $filter = request('filter', 'all'); // all, hadir, belum

        $query = Participant::where('event_id', $event->id)
            ->with('attendance');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('noreg', 'like', "%{$search}%")
                    ->orWhere('school', 'like', "%{$search}%")
                    ->orWhere('class', 'like', "%{$search}%")
                    ->orWhere('attendance_code', 'like', "%{$search}%");
            });
        }

        if ($filter === 'hadir') {
            $query->whereHas('attendance');
        } elseif ($filter === 'belum') {
            $query->whereDoesntHave('attendance');
        }

        $participants = $query->orderBy('name')->paginate(30);

        $stats = [
            'total'  => Participant::where('event_id', $event->id)->count(),
            'hadir'  => $event->attendances()->count(),
            'belum'  => max(0, Participant::where('event_id', $event->id)->count() - $event->attendances()->count()),
        ];

        return view('admin.participants.index', compact('event', 'participants', 'stats', 'search', 'filter'));
    }

    public function create(Event $event)
    {
        $this->authorizeEvent($event);
        return view('admin.participants.create', compact('event'));
    }

    public function store(Request $request, Event $event)
    {
        $this->authorizeEvent($event);

        $validated = $request->validate([
            'noreg'      => 'required|string|max:50',
            'name'       => 'required|string|max:150',
            'class'      => 'nullable|string|max:30',
            'school'     => 'nullable|string|max:150',
            'room'       => 'nullable|string|max:30',
            'supervisor' => 'nullable|string|max:100',
        ]);

        // Cek duplikat NOREG
        $exists = Participant::where('event_id', $event->id)->where('noreg', $validated['noreg'])->exists();
        if ($exists) {
            return back()->withErrors(['noreg' => 'NOREG ini sudah terdaftar di event ini.'])->withInput();
        }

        $validated['event_id'] = $event->id;
        $p = Participant::create($validated);

        // Auto-generate kode absensi jika sudah ada konfigurasi digit
        if ($event->digit_count) {
            $code = $this->codeService->generateCode($p->noreg, $event->digit_count, $event->digit_position);
            // Cek konflik
            $conflict = Participant::where('event_id', $event->id)
                ->where('attendance_code', $code)
                ->where('id', '!=', $p->id)
                ->exists();
            if (!$conflict) {
                $p->update(['attendance_code' => $code]);
            }
        }

        return redirect()->route('admin.participants.index', $event)
            ->with('success', "Peserta \"{$p->name}\" berhasil ditambahkan.");
    }

    public function edit(Event $event, Participant $participant)
    {
        $this->authorizeEvent($event);
        abort_if($participant->event_id !== $event->id, 404);
        return view('admin.participants.edit', compact('event', 'participant'));
    }

    public function update(Request $request, Event $event, Participant $participant)
    {
        $this->authorizeEvent($event);
        abort_if($participant->event_id !== $event->id, 404);

        $validated = $request->validate([
            'noreg'      => 'required|string|max:50',
            'name'       => 'required|string|max:150',
            'class'      => 'nullable|string|max:30',
            'school'     => 'nullable|string|max:150',
            'room'       => 'nullable|string|max:30',
            'supervisor' => 'nullable|string|max:100',
        ]);

        // Cek duplikat NOREG (exclude diri sendiri)
        $exists = Participant::where('event_id', $event->id)
            ->where('noreg', $validated['noreg'])
            ->where('id', '!=', $participant->id)
            ->exists();
        if ($exists) {
            return back()->withErrors(['noreg' => 'NOREG ini sudah terdaftar di event ini.'])->withInput();
        }

        $participant->update($validated);

        return redirect()->route('admin.participants.index', $event)
            ->with('success', "Data peserta berhasil diperbarui.");
    }

    public function destroy(Event $event, Participant $participant)
    {
        $this->authorizeEvent($event);
        abort_if($participant->event_id !== $event->id, 404);
        $name = $participant->name;
        $participant->delete();
        return back()->with('success', "Peserta \"{$name}\" berhasil dihapus.");
    }

    /** Halaman import Excel */
    public function importForm(Event $event)
    {
        $this->authorizeEvent($event);
        return view('admin.participants.import', compact('event'));
    }

    /** Proses import Excel */
    public function importProcess(Request $request, Event $event)
    {
        $this->authorizeEvent($event);

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $result = $this->importService->import($event, $request->file('file'));

        if (!$result['success']) {
            return back()->with('error', $result['message']);
        }

        // Setelah import, auto-generate kode jika mode auto atau digit sudah ditentukan
        $codeResult = null;
        if ($result['inserted'] > 0) {
            $codeResult = $this->codeService->generateCodesForEvent($event);
        }

        $msg = $result['message'];
        if ($codeResult) {
            $msg .= ' | ' . ($codeResult['success'] ? $codeResult['message'] : '⚠️ ' . $codeResult['message']);
        }

        return redirect()->route('admin.participants.index', $event)
            ->with('success', $msg);
    }

    /** Download template Excel */
    public function downloadTemplate()
    {
        $path = $this->importService->generateTemplate();
        return response()->download($path, 'template_peserta.xlsx')->deleteFileAfterSend(true);
    }

    // ── PRIVATE ─────────────────────────────────────────────────

    private function authorizeEvent(Event $event): void
    {
        if (!$event->canBeManagedBy(Auth::user())) {
            abort(403, 'Anda tidak memiliki akses ke event ini.');
        }
    }
}
