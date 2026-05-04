<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Participant;
use App\Models\Attendance;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->isSuperAdmin()) {
            $stats = [
                'total_events'       => Event::count(),
                'active_events'      => Event::where('is_active', true)->count(),
                'total_participants' => Participant::count(),
                'total_attended'     => Attendance::count(),
                'total_units'        => Unit::where('is_active', true)->count(),
                'total_admins'       => User::where('role', 'admin')->count(),
            ];
            $recentEvents = Event::with(['unit', 'city'])
                ->orderBy('created_at', 'desc')
                ->limit(8)
                ->get();
            $activeEvents = Event::with(['unit', 'city'])
                ->where('is_active', true)
                ->get();
        } else {
            $unitId = $user->unit_id;
            $stats = [
                'total_events'       => Event::where('unit_id', $unitId)->count(),
                'active_events'      => Event::where('unit_id', $unitId)->where('is_active', true)->count(),
                'total_participants' => Participant::whereHas('event', fn($q) => $q->where('unit_id', $unitId))->count(),
                'total_attended'     => Attendance::whereHas('event', fn($q) => $q->where('unit_id', $unitId))->count(),
            ];
            $recentEvents = Event::with(['unit', 'city'])
                ->where('unit_id', $unitId)
                ->orderBy('created_at', 'desc')
                ->limit(8)
                ->get();
            $activeEvents = Event::with(['unit', 'city'])
                ->where('unit_id', $unitId)
                ->where('is_active', true)
                ->get();
        }

        return view('admin.dashboard.index', compact('stats', 'recentEvents', 'activeEvents'));
    }
}
