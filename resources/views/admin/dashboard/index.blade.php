@extends('layouts.admin')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
    {{-- Stat Cards --}}
    <div class="mb-4">
        <h5 class="fw-bold mb-1" style="color:#1a1a1a;">
            Selamat datang, {{ auth()->user()->name }} 👋
        </h5>
        <div style="font-size:13px;color:#888;">
            {{ auth()->user()->isSuperAdmin() ? 'Super Admin — Akses penuh ke semua data.' : 'Admin Unit: ' . (auth()->user()->unit?->name ?? '-') }}
            · {{ now()->translatedFormat('l, d F Y') }}
        </div>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="num text-danger">{{ $stats['total_events'] }}</div>
                        <div class="lbl">Total Event</div>
                    </div>
                    <div class="icon" style="background:#ffebee;color:#c62828;"><i class="bi bi-calendar-event"></i></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="num text-success">{{ $stats['active_events'] }}</div>
                        <div class="lbl">Event Aktif</div>
                    </div>
                    <div class="icon" style="background:#e8f5e9;color:#2e7d32;"><i class="bi bi-lightning-charge"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="num" style="color:#1565c0;">{{ $stats['total_participants'] }}</div>
                        <div class="lbl">Total Peserta</div>
                    </div>
                    <div class="icon" style="background:#e3f2fd;color:#1565c0;"><i class="bi bi-people"></i></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="num" style="color:#f57c00;">{{ $stats['total_attended'] }}</div>
                        <div class="lbl">Total Hadir</div>
                    </div>
                    <div class="icon" style="background:#fff3e0;color:#f57c00;"><i class="bi bi-check2-square"></i></div>
                </div>
            </div>
        </div>
        @if (auth()->user()->isSuperAdmin())
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="num" style="color:#7b1fa2;">{{ $stats['total_units'] }}</div>
                            <div class="lbl">Unit Aktif</div>
                        </div>
                        <div class="icon" style="background:#f3e5f5;color:#7b1fa2;"><i class="bi bi-building"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="num" style="color:#00838f;">{{ $stats['total_admins'] }}</div>
                            <div class="lbl">Admin Terdaftar</div>
                        </div>
                        <div class="icon" style="background:#e0f7fa;color:#00838f;"><i class="bi bi-person-badge"></i>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="row g-4">
        {{-- Event Aktif --}}
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm" style="border-radius:16px;">
                <div class="card-header bg-white border-0 pt-4 pb-2 px-4" style="border-radius:16px 16px 0 0;">
                    <div class="d-flex align-items-center justify-content-between">
                        <h6 class="mb-0 fw-bold" style="color:#c62828;">
                            <i class="bi bi-lightning-charge me-2"></i>Event Aktif Sekarang
                        </h6>
                        <span class="badge" style="background:#e8f5e9;color:#2e7d32;">{{ $activeEvents->count() }}
                            aktif</span>
                    </div>
                </div>
                <div class="card-body px-4 pb-4">
                    @forelse($activeEvents as $event)
                        <div class="d-flex align-items-center gap-3 p-3 mb-2 rounded-3"
                            style="background:#fff8f8;border:1px solid #ffcdd2;">
                            <div class="flex-grow-1 min-w-0">
                                <div class="fw-bold text-truncate" style="font-size:14px;">{{ $event->name }}</div>
                                <div class="text-muted" style="font-size:12px;">
                                    <i class="bi bi-building me-1"></i>{{ $event->unit->name }}
                                    @if ($event->city)
                                        · {{ $event->city->name }}
                                    @endif
                                </div>
                                {{-- Progress bar --}}
                                @php
                                    $pct = $event->attendancePercentage();
                                    $attended = $event->totalAttended();
                                    $total = $event->totalParticipants();
                                @endphp
                                <div class="d-flex align-items-center gap-2 mt-2">
                                    <div class="flex-grow-1"
                                        style="background:#e0e0e0;border-radius:99px;height:6px;overflow:hidden;">
                                        <div
                                            style="width:{{ $pct }}%;height:100%;background:linear-gradient(90deg,#c62828,#e64a19);border-radius:99px;transition:width 0.5s;">
                                        </div>
                                    </div>
                                    <span
                                        style="font-size:11px;font-weight:700;color:#c62828;">{{ $attended }}/{{ $total }}</span>
                                </div>
                            </div>
                            <div class="d-flex flex-column gap-1">
                                <a href="{{ route('admin.events.show', $event) }}" class="btn btn-sm btn-outline-danger"
                                    style="font-size:11px;">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ $event->publicAttendanceUrl() }}" target="_blank"
                                    class="btn btn-sm btn-outline-success" style="font-size:11px;"
                                    title="Buka halaman absensi">
                                    <i class="bi bi-qr-code"></i>
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-calendar-x fs-2 d-block mb-2 opacity-25"></i>
                            <span style="font-size:13px;">Tidak ada event aktif saat ini.</span>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Recent Events --}}
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm" style="border-radius:16px;">
                <div class="card-header bg-white border-0 pt-4 pb-2 px-4" style="border-radius:16px 16px 0 0;">
                    <div class="d-flex align-items-center justify-content-between">
                        <h6 class="mb-0 fw-bold" style="color:#1565c0;"><i class="bi bi-clock-history me-2"></i>Event
                            Terbaru</h6>
                        <a href="{{ route('admin.events.index') }}" class="btn btn-sm btn-outline-primary"
                            style="font-size:12px;">Lihat Semua</a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" style="font-size:13px;">
                            <thead style="background:#f8f9fa;">
                                <tr>
                                    <th class="ps-4">Nama Event</th>
                                    <th>Unit</th>
                                    <th>Status</th>
                                    <th>Peserta</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentEvents as $event)
                                    <tr>
                                        <td class="ps-4 fw-semibold">{{ Str::limit($event->name, 32) }}</td>
                                        <td class="text-muted">{{ $event->unit->name }}</td>
                                        <td>
                                            @if ($event->is_active)
                                                <span class="badge badge-active px-2 py-1"><i
                                                        class="bi bi-circle-fill me-1"
                                                        style="font-size:7px;"></i>Aktif</span>
                                            @else
                                                <span class="badge badge-inactive px-2 py-1">Nonaktif</span>
                                            @endif
                                        </td>
                                        <td>{{ $event->participants_count ?? '-' }}</td>
                                        <td>
                                            <a href="{{ route('admin.events.show', $event) }}"
                                                class="btn btn-sm btn-light" style="font-size:12px;">
                                                <i class="bi bi-arrow-right"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">Belum ada event.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
