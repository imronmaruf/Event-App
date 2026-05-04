@extends('layouts.admin')
@section('title', 'Data Absensi — ' . $event->name)
@section('page-title', 'Data Absensi')

@push('styles')
    <style>
        .tbl-wrap {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .06);
            overflow: hidden;
        }

        table th {
            font-size: 11.5px;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: #999;
            font-weight: 700;
            border-bottom: 1.5px solid #f5f5f5 !important;
            padding: 12px 16px !important;
            white-space: nowrap;
        }

        table td {
            font-size: 13.5px;
            padding: 11px 16px !important;
            vertical-align: middle;
            border-bottom: 1px solid #f9f9f9 !important;
        }

        .stat-strip {
            background: #fff;
            border-radius: 14px;
            padding: 16px 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .05);
            margin-bottom: 16px;
        }

        .stat-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 16px;
            border-radius: 99px;
            font-size: 13px;
            font-weight: 700;
        }

        .prog-bar {
            background: #f0f0f0;
            border-radius: 99px;
            height: 8px;
            overflow: hidden;
        }

        .prog-fill {
            height: 100%;
            border-radius: 99px;
            background: linear-gradient(90deg, #c62828, #f4b846);
        }

        .code-chip {
            background: #e3f2fd;
            color: #1565c0;
            font-family: monospace;
            font-weight: 700;
            font-size: 13px;
            padding: 3px 10px;
            border-radius: 8px;
            letter-spacing: 1px;
        }

        .time-chip {
            background: #e8f5e9;
            color: #2e7d32;
            font-size: 12px;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 8px;
        }
    </style>
@endpush

@section('topbar-actions')
    <a href="{{ route('admin.events.show', $event) }}" class="btn btn-sm btn-light" style="border-radius:10px;">
        <i class="bi bi-arrow-left me-1"></i> Detail Event
    </a>
    <a href="{{ route('admin.attendances.by-room', $event) }}" class="btn btn-sm fw-600"
        style="border-radius:10px;background:#f3e5f5;color:#7b1fa2;border:none;">
        <i class="bi bi-door-open me-1"></i> Per Ruang
    </a>
    <a href="{{ route('admin.attendances.export', $event) }}" class="btn btn-sm fw-600"
        style="border-radius:10px;background:#e8f5e9;color:#2e7d32;border:none;">
        <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
    </a>
@endsection

@section('content')
    {{-- Stats Strip --}}
    <div class="stat-strip">
        <div class="d-flex flex-wrap align-items-center gap-3">
            <div class="flex-grow-1">
                <div class="fw-bold" style="font-size:15px;">{{ $event->name }}</div>
                <div style="font-size:12px;color:#aaa;">{{ $event->unit->name }}</div>
            </div>
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <span class="stat-chip" style="background:#e3f2fd;color:#1565c0;">
                    <i class="bi bi-people"></i> {{ $stats['total'] }}
                </span>
                <span class="stat-chip" style="background:#e8f5e9;color:#2e7d32;">
                    <i class="bi bi-check2-circle"></i> {{ $stats['hadir'] }} Hadir
                </span>
                <span class="stat-chip" style="background:#ffebee;color:#c62828;">
                    <i class="bi bi-hourglass"></i> {{ $stats['belum'] }} Belum
                </span>
                <span class="fw-bold" style="font-size:18px;color:#c62828;">{{ $stats['persen'] }}%</span>
            </div>
        </div>
        <div class="prog-bar mt-2">
            <div class="prog-fill" style="width:{{ $stats['persen'] }}%"></div>
        </div>
    </div>

    {{-- Filter --}}
    <div
        style="background:#fff;border-radius:14px;padding:14px 20px;box-shadow:0 2px 10px rgba(0,0,0,.05);margin-bottom:16px;">
        <form method="GET" class="d-flex align-items-center gap-2 flex-wrap">
            <input type="text" name="search" class="form-control form-control-sm flex-grow-1"
                placeholder="Cari nama, NOREG, sekolah..." value="{{ $search }}"
                style="border-radius:10px;border:1.5px solid #e0e0e0;min-width:180px;">
            <button type="submit" class="btn btn-sm btn-primary" style="border-radius:10px;">
                <i class="bi bi-search"></i>
            </button>
            @if ($search)
                <a href="{{ route('admin.attendances.index', $event) }}" class="btn btn-sm btn-light"
                    style="border-radius:10px;"><i class="bi bi-x"></i></a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="tbl-wrap">
        <div
            style="padding:16px 20px;border-bottom:1px solid #f5f5f5;display:flex;align-items:center;justify-content:space-between;">
            <span class="fw-bold" style="font-size:14px;">
                Riwayat Kehadiran
                <span style="color:#aaa;font-weight:500;font-size:13px;">({{ $attendances->total() }} data)</span>
            </span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>NOREG</th>
                        <th>Kode</th>
                        <th>Nama Peserta</th>
                        <th>Kelas</th>
                        <th>Sekolah</th>
                        <th>Ruang</th>
                        <th>Waktu Hadir</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendances as $i => $a)
                        @php $p = $a->participant; @endphp
                        <tr>
                            <td style="color:#aaa;font-size:12px;">{{ $attendances->firstItem() + $i }}</td>
                            <td style="font-family:monospace;font-size:12px;color:#555;">{{ $p?->noreg ?? '-' }}</td>
                            <td>
                                @if ($p?->attendance_code)
                                    <span class="code-chip">{{ $p->attendance_code }}</span>
                                @else
                                    <span style="color:#ccc;">—</span>
                                @endif
                            </td>
                            <td class="fw-600">{{ $p?->name ?? '-' }}</td>
                            <td style="color:#666;">{{ $p?->class ?? '-' }}</td>
                            <td style="color:#666;font-size:13px;">{{ $p?->school ?? '-' }}</td>
                            <td style="color:#666;">{{ $p?->room ?? '-' }}</td>
                            <td>
                                <span class="time-chip">
                                    {{ $a->attended_at->timezone('Asia/Jakarta')->format('H:i:s') }}
                                </span>
                                <div style="font-size:11px;color:#aaa;margin-top:2px;">
                                    {{ $a->attended_at->timezone('Asia/Jakarta')->format('d/m/Y') }}
                                </div>
                            </td>
                            <td>
                                <form action="{{ route('admin.attendances.destroy', [$event, $a]) }}" method="POST"
                                    onsubmit="return confirm('Batalkan kehadiran {{ $p?->name }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm"
                                        style="border-radius:8px;background:#ffebee;border:none;color:#c62828;padding:4px 8px;"
                                        title="Batalkan">
                                        <i class="bi bi-x-circle" style="font-size:13px;"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5" style="color:#bbb;">
                                <i class="bi bi-clipboard-x" style="font-size:36px;display:block;margin-bottom:8px;"></i>
                                Belum ada peserta yang diabsen.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($attendances->hasPages())
            <div class="px-4 py-3 border-top">
                {{ $attendances->withQueryString()->links() }}
            </div>
        @endif
    </div>
@endsection
