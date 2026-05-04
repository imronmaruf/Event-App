@extends('layouts.admin')
@section('title', 'Data Peserta — ' . $event->name)
@section('page-title', 'Data Peserta')

@push('styles')
    <style>
        .tbl-wrap {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .06);
            overflow: hidden;
        }

        .tbl-head {
            padding: 16px 20px;
            border-bottom: 1px solid #f5f5f5;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
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
            color: #1a1a1a;
        }

        .badge-hadir {
            background: #e8f5e9;
            color: #2e7d32;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 99px;
        }

        .badge-belum {
            background: #fff3e0;
            color: #e65100;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 99px;
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

        .filter-bar {
            background: #fff;
            border-radius: 14px;
            padding: 14px 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .05);
            margin-bottom: 16px;
        }

        .stat-pill {
            padding: 8px 16px;
            border-radius: 99px;
            font-size: 12px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .dataTables_wrapper .dataTables_filter input {
            border-radius: 10px;
            border: 1.5px solid #ddd;
            padding: 6px 10px;
            margin-left: 8px;
        }

        .dataTables_wrapper .dataTables_length select {
            border-radius: 8px;
            padding: 4px 8px;
        }
    </style>

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
@endpush

@section('topbar-actions')
    <a href="{{ route('admin.events.show', $event) }}" class="btn btn-sm btn-light" style="border-radius:10px;">
        <i class="bi bi-arrow-left me-1"></i> Detail Event
    </a>
    <a href="{{ route('admin.participants.import', $event) }}" class="btn btn-sm fw-600"
        style="border-radius:10px;background:#e8f5e9;color:#2e7d32;border:none;">
        <i class="bi bi-file-earmark-arrow-up me-1"></i> Import Excel
    </a>
    <a href="{{ route('admin.participants.create', $event) }}" class="btn btn-sm fw-bold"
        style="border-radius:10px;background:linear-gradient(135deg,#c62828,#e64a19);color:#fff;border:none;">
        <i class="bi bi-plus-lg me-1"></i> Tambah
    </a>
@endsection

@section('content')
    <div class="p-3 rounded-3 mb-3 d-flex flex-wrap align-items-center gap-3"
        style="background:#fff;box-shadow:0 2px 10px rgba(0,0,0,.05);">
        <div class="flex-grow-1">
            <div class="fw-bold" style="font-size:15px;">{{ $event->name }}</div>
            <div style="font-size:12px;color:#aaa;">
                {{ $event->unit->name }}
                @if ($event->city)
                    · {{ $event->city->name }}
                @endif
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <span class="stat-pill" style="background:#e3f2fd;color:#1565c0;">
                <i class="bi bi-people"></i> {{ $stats['total'] }} Total
            </span>
            <span class="stat-pill" style="background:#e8f5e9;color:#2e7d32;">
                <i class="bi bi-check2"></i> {{ $stats['hadir'] }} Hadir
            </span>
            <span class="stat-pill" style="background:#fff3e0;color:#e65100;">
                <i class="bi bi-clock"></i> {{ $stats['belum'] }} Belum
            </span>
        </div>
    </div>

    <div class="tbl-wrap">
        <div class="tbl-head">
            <span class="fw-bold" style="font-size:14px;">
                Daftar Peserta
            </span>

            <a href="{{ route('admin.participants.template', $event) }}" class="btn btn-sm"
                style="border-radius:10px;font-size:12px;background:#f0f4f8;border:1.5px solid #e0e0e0;color:#555;">
                <i class="bi bi-download me-1"></i> Template Excel
            </a>
        </div>

        <div class="table-responsive p-3">
            <table id="participantsTable" class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>NOREG</th>
                        <th>Kode</th>
                        <th>Nama Peserta</th>
                        <th>Kelas</th>
                        <th>Sekolah</th>
                        <th>Ruang</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($participants as $i => $p)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $p->noreg }}</td>
                            <td>
                                @if ($p->attendance_code)
                                    <span class="code-chip">{{ $p->attendance_code }}</span>
                                @endif
                            </td>
                            <td>{{ $p->name }}</td>
                            <td>{{ $p->class ?? '-' }}</td>
                            <td>{{ $p->school ?? '-' }}</td>
                            <td>{{ $p->room ?? '-' }}</td>
                            <td>
                                @if ($p->attendance)
                                    <span class="badge-hadir">✓ Hadir</span>
                                @else
                                    <span class="badge-belum">Belum</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('admin.participants.edit', [$event, $p]) }}"
                                        class="btn btn-sm btn-light">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('admin.participants.destroy', [$event, $p]) }}" method="POST"
                                        onsubmit="return confirm('Hapus peserta {{ $p->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#participantsTable').DataTable({
                pageLength: 10,
                responsive: true,
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                    zeroRecords: "Data tidak ditemukan",
                    paginate: {
                        next: "›",
                        previous: "‹"
                    }
                }
            });
        });
    </script>
@endpush
