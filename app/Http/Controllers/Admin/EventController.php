<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Event;
use App\Models\Unit;
use App\Services\AttendanceCodeService;
use App\Services\QRCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class EventController extends Controller
{
    public function __construct(
        private AttendanceCodeService $codeService,
        private QRCodeService $qrService,
    ) {}

    public function index()
    {
        $user  = Auth::user();
        $query = Event::with(['unit', 'city', 'creator'])->withCount(['participants', 'attendances']);

        if (!$user->isSuperAdmin()) {
            $query->where('unit_id', $user->unit_id);
        }

        $events = $query->orderByDesc('created_at')->paginate(15);
        return view('admin.events.index', compact('events'));
    }

    public function create()
    {
        $user  = Auth::user();
        $units = $user->isSuperAdmin() ? Unit::where('is_active', true)->get() : collect([$user->unit]);
        $cities = City::orderBy('name')->get();
        return view('admin.events.create', compact('units', 'cities'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name'           => 'required|string|max:200',
            'unit_id'        => 'required|exists:units,id',
            'city_id'        => 'nullable|exists:cities,id',
            'description'    => 'nullable|string',
            'venue'          => 'nullable|string|max:200',
            'event_date'     => 'nullable|date',
            'event_time'     => 'nullable',
            'digit_mode'     => 'required|in:auto,manual',
            'digit_count'    => 'nullable|required_if:digit_mode,manual|integer|min:1|max:20',
            'digit_position' => 'required|in:suffix,prefix',
        ]);

        // Admin hanya boleh membuat event untuk unitnya sendiri
        if (!$user->isSuperAdmin() && (int)$validated['unit_id'] !== (int)$user->unit_id) {
            abort(403, 'Anda hanya dapat membuat event untuk unit Anda sendiri.');
        }

        $validated['created_by'] = $user->id;
        if ($validated['digit_mode'] === 'auto') {
            $validated['digit_count'] = null; // akan dihitung nanti saat peserta diimport
        }

        $event = Event::create($validated);

        return redirect()
            ->route('admin.events.show', $event)
            ->with('success', "Event \"{$event->name}\" berhasil dibuat.");
    }

    public function show(Event $event)
    {
        $this->authorizeEvent($event);
        $event->loadCount(['participants', 'attendances']);
        $qrUrl = $this->qrService->generateEventQRUrl($event);
        $stats = [
            'total'   => $event->participants_count,
            'hadir'   => $event->attendances_count,
            'belum'   => max(0, $event->participants_count - $event->attendances_count),
            'persen'  => $event->participants_count > 0
                ? round(($event->attendances_count / $event->participants_count) * 100, 1)
                : 0,
        ];
        return view('admin.events.show', compact('event', 'qrUrl', 'stats'));
    }

    public function edit(Event $event)
    {
        $this->authorizeEvent($event);
        $user   = Auth::user();
        $units  = $user->isSuperAdmin() ? Unit::where('is_active', true)->get() : collect([$user->unit]);
        $cities = City::orderBy('name')->get();
        return view('admin.events.edit', compact('event', 'units', 'cities'));
    }

    public function update(Request $request, Event $event)
    {
        $this->authorizeEvent($event);
        $user = Auth::user();

        $validated = $request->validate([
            'name'           => 'required|string|max:200',
            'unit_id'        => 'required|exists:units,id',
            'city_id'        => 'nullable|exists:cities,id',
            'description'    => 'nullable|string',
            'venue'          => 'nullable|string|max:200',
            'event_date'     => 'nullable|date',
            'event_time'     => 'nullable',
            'digit_mode'     => 'required|in:auto,manual',
            'digit_count'    => 'nullable|required_if:digit_mode,manual|integer|min:1|max:20',
            'digit_position' => 'required|in:suffix,prefix',
        ]);

        if (!$user->isSuperAdmin() && (int)$validated['unit_id'] !== (int)$user->unit_id) {
            abort(403);
        }

        if ($validated['digit_mode'] === 'auto') {
            $validated['digit_count'] = null;
        }

        $event->update($validated);

        return redirect()
            ->route('admin.events.show', $event)
            ->with('success', "Event berhasil diperbarui.");
    }

    public function destroy(Event $event)
    {
        $this->authorizeEvent($event);
        $name = $event->name;
        $event->delete();
        return redirect()->route('admin.events.index')
            ->with('success', "Event \"{$name}\" berhasil dihapus.");
    }

    /** Toggle aktif/nonaktif absensi event */
    public function toggleActive(Event $event)
    {
        $this->authorizeEvent($event);
        $event->update(['is_active' => !$event->is_active]);
        $status = $event->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Absensi event \"{$event->name}\" berhasil {$status}.");
    }

    /** Regenerate kode absensi semua peserta (setelah import atau ganti konfigurasi digit) */
    public function regenerateCodes(Event $event)
    {
        $this->authorizeEvent($event);

        if ($event->participants()->count() === 0) {
            return back()->with('error', 'Belum ada peserta. Import data peserta terlebih dahulu.');
        }

        $result = $this->codeService->generateCodesForEvent($event);

        if (!$result['success']) {
            return back()->with('error', $result['message']);
        }

        return back()->with('success', $result['message']);
    }

    /** Update konfigurasi digit saja (tanpa regenerate, atau dengan regenerate) */
    public function updateDigitSettings(Request $request, Event $event)
    {
        $this->authorizeEvent($event);

        $validated = $request->validate([
            'digit_mode'     => 'required|in:auto,manual',
            'digit_count'    => 'nullable|required_if:digit_mode,manual|integer|min:1|max:20',
            'digit_position' => 'required|in:suffix,prefix',
        ]);

        if ($validated['digit_mode'] === 'auto') $validated['digit_count'] = null;

        $event->update($validated);

        if ($request->boolean('auto_regenerate')) {
            $result = $this->codeService->generateCodesForEvent($event);
            if (!$result['success']) return back()->with('error', $result['message']);
            return back()->with('success', $result['message']);
        }

        return back()->with('success', 'Konfigurasi digit berhasil disimpan.');
    }

    /** Auto-detect digit terbaik (AJAX) */
    public function detectDigits(Event $event)
    {
        $this->authorizeEvent($event);
        $position = request('position', 'suffix');
        $detected = $this->codeService->detectMinimumDigits($event, $position);
        return response()->json([
            'detected'  => $detected,
            'message'   => $detected
                ? "Sistem mendeteksi {$detected} digit sudah cukup untuk membedakan semua peserta."
                : 'Tidak dapat mendeteksi digit unik. Periksa data NOREG.',
        ]);
    }

    // ── PRIVATE ─────────────────────────────────────────────────

    private function authorizeEvent(Event $event): void
    {
        if (!$event->canBeManagedBy(Auth::user())) {
            abort(403, 'Anda tidak memiliki akses ke event ini.');
        }
    }
}
