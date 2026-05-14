@extends('layouts.admin')
@section('title', 'Data Absensi — ' . $event->name)
@section('page-title', 'Data Absensi')

@push('styles')
    {{-- DataTables --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <style>
        /* ── Wrapper ── */
        .tbl-wrap {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .06);
            overflow: hidden;
        }

        /* ── Table ── */
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

        /* ── DataTables overrides ── */
        div.dataTables_wrapper div.dataTables_filter input {
            border-radius: 10px;
            border: 1.5px solid #e0e0e0;
            padding: 5px 12px;
            font-size: 13px;
        }

        div.dataTables_wrapper div.dataTables_length select {
            border-radius: 10px;
            border: 1.5px solid #e0e0e0;
            padding: 4px 8px;
            font-size: 13px;
        }

        div.dataTables_wrapper div.dataTables_info,
        div.dataTables_wrapper div.dataTables_paginate {
            font-size: 13px;
            padding-top: 12px;
        }

        div.dataTables_wrapper div.dataTables_paginate ul.pagination {
            margin-bottom: 0;
        }

        .page-link {
            border-radius: 8px !important;
        }

        div.dataTables_wrapper div.dataTables_filter,
        div.dataTables_wrapper div.dataTables_length {
            padding: 14px 20px 0;
        }

        /* ── Stat strip ── */
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

        /* ── Chips ── */
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

        /* ── Modal konfirmasi ── */
        .modal-confirm-header {
            padding: 20px 24px;
        }

        .modal-confirm-icon {
            width: 44px;
            height: 44px;
            background: rgba(255, 255, 255, .2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .alert-info-custom {
            background: #e8f5e9;
            border-radius: 12px;
            padding: 12px 16px;
            margin-bottom: 16px;
            border-left: 3px solid #2e7d32;
            font-size: 13px;
            color: #2e7d32;
        }

        .alert-warn-custom {
            background: #fff8e1;
            border-radius: 12px;
            padding: 12px 16px;
            margin-bottom: 16px;
            border-left: 3px solid #f9a825;
            font-size: 13px;
            color: #f57f17;
        }

        .alert-danger-custom {
            background: #ffebee;
            border-radius: 12px;
            padding: 12px 16px;
            margin-bottom: 16px;
            border-left: 3px solid #c62828;
            font-size: 13px;
            color: #c62828;
        }

        .confirm-input {
            border-radius: 10px !important;
            border: 1.5px solid #e0e0e0 !important;
            font-family: monospace !important;
            font-weight: 700 !important;
            letter-spacing: 2px !important;
        }
    </style>
@endpush

{{-- ═══════════════════════════════════════════════════════════
     TOPBAR — hanya tombol, TANPA modal di sini
═══════════════════════════════════════════════════════════ --}}
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
    @if (Auth::user()->role === 'superadmin')
        <button type="button" class="btn btn-sm fw-600"
            style="border-radius:10px;background:#e8f5e9;color:#2e7d32;border:none;" data-bs-toggle="modal"
            data-bs-target="#modalMarkAllPresent">
            <i class="bi bi-check2-all me-1"></i> Tandai Semua Hadir
        </button>
        <button type="button" class="btn btn-sm fw-600"
            style="border-radius:10px;background:#ffebee;color:#c62828;border:none;" data-bs-toggle="modal"
            data-bs-target="#modalResetAttendance">
            <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Absensi
        </button>
    @endif
@endsection

{{-- ═══════════════════════════════════════════════════════════
     CONTENT
═══════════════════════════════════════════════════════════ --}}
@section('content')

    {{-- ── Stats Strip ── --}}
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

    {{-- ── Tabel DataTables ── --}}
    <div class="tbl-wrap">
        <div
            style="padding:16px 20px;border-bottom:1px solid #f5f5f5;
                    display:flex;align-items:center;justify-content:space-between;">
            <span class="fw-bold" style="font-size:14px;">
                Riwayat Kehadiran
                <span style="color:#aaa;font-weight:500;font-size:13px;">({{ $attendances->count() }} data)</span>
            </span>
        </div>

        <div class="table-responsive p-2">
            <table id="tblAttendance" class="table table-hover mb-0 w-100">
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
                        <th class="no-sort"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendances as $i => $a)
                        @php $p = $a->participant; @endphp
                        <tr>
                            <td style="color:#aaa;font-size:12px;">{{ $i + 1 }}</td>
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
                            {{-- Simpan sort key sebagai data-order iso datetime --}}
                            <td data-order="{{ $a->attended_at->timestamp }}">
                                <span class="time-chip">
                                    {{ $a->attended_at->timezone('Asia/Jakarta')->format('H:i:s') }}
                                </span>
                                <div style="font-size:11px;color:#aaa;margin-top:2px;">
                                    {{ $a->attended_at->timezone('Asia/Jakarta')->format('d/m/Y') }}
                                </div>
                            </td>
                            <td>
                                <form action="{{ route('admin.attendances.destroy', [$event, $a]) }}" method="POST"
                                    onsubmit="return confirm('Batalkan kehadiran {{ addslashes($p?->name) }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm"
                                        style="border-radius:8px;background:#ffebee;border:none;color:#c62828;padding:4px 8px;"
                                        title="Batalkan kehadiran">
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
                        {{-- <tr>
                            <td colspan="9" class="text-center py-5" style="color:#bbb;">
                                <i class="bi bi-clipboard-x" style="font-size:36px;display:block;margin-bottom:8px;"></i>
                                Belum ada peserta yang diabsen.
                            </td>
                        </tr> --}}
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>


    {{-- ═══════════════════════════════════════════════════════
         MODAL: Tandai Semua Hadir  ← WAJIB di dalam @section('content')
    ════════════════════════════════════════════════════════ --}}
    @if (Auth::user()->role === 'superadmin')
        <div class="modal fade" id="modalMarkAllPresent" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
                <div class="modal-content" style="border-radius:18px;border:none;overflow:hidden;">

                    <div class="modal-confirm-header" style="background:linear-gradient(135deg,#2e7d32,#66bb6a);">
                        <div class="d-flex align-items-center gap-3">
                            <div class="modal-confirm-icon">
                                <i class="bi bi-check2-all" style="font-size:22px;color:#fff;"></i>
                            </div>
                            <div>
                                <div class="fw-bold" style="font-size:15px;color:#fff;">Tandai Semua Hadir</div>
                                <div style="font-size:12px;color:rgba(255,255,255,.75);">Superadmin · Mode Testing</div>
                            </div>
                        </div>
                    </div>

                    <div style="padding:20px 24px;">
                        <div class="alert-info-custom">
                            <i class="bi bi-info-circle me-1"></i>
                            Semua peserta yang <strong>belum hadir</strong> akan otomatis dicatat hadir
                            dengan waktu sekarang. Peserta yang sudah hadir tidak terpengaruh.
                        </div>
                        <div class="alert-warn-custom">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            Fitur ini hanya untuk <strong>keperluan testing</strong> (input nilai & perangkingan).
                            Jangan gunakan di event resmi yang sedang berlangsung.
                        </div>

                        <form action="{{ route('admin.attendances.mark-all-present', $event) }}" method="POST"
                            id="formMarkAllPresent">
                            @csrf
                            <label class="form-label fw-600" style="font-size:13px;">
                                Ketik
                                <span
                                    style="font-family:monospace;background:#e8f5e9;color:#2e7d32;
                                             padding:1px 8px;border-radius:6px;font-weight:700;">HADIR</span>
                                untuk konfirmasi:
                            </label>
                            <input type="text" name="confirm" id="confirmMarkAll" class="form-control confirm-input"
                                placeholder="HADIR" autocomplete="off">
                        </form>
                    </div>

                    <div style="padding:0 24px 20px;display:flex;gap:8px;justify-content:flex-end;">
                        <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal"
                            style="border-radius:10px;">Batal</button>
                        <button type="submit" form="formMarkAllPresent" id="btnMarkAll" class="btn btn-sm fw-600"
                            disabled
                            style="border-radius:10px;background:#2e7d32;color:#fff;border:none;opacity:.5;transition:opacity .2s;">
                            <i class="bi bi-check2-all me-1"></i> Ya, Tandai Semua Hadir
                        </button>
                    </div>

                </div>
            </div>
        </div>


        {{-- ═══════════════════════════════════════════════════════
             MODAL: Reset Semua Absensi
        ════════════════════════════════════════════════════════ --}}
        <div class="modal fade" id="modalResetAttendance" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
                <div class="modal-content" style="border-radius:18px;border:none;overflow:hidden;">

                    <div class="modal-confirm-header" style="background:linear-gradient(135deg,#c62828,#ef5350);">
                        <div class="d-flex align-items-center gap-3">
                            <div class="modal-confirm-icon">
                                <i class="bi bi-arrow-counterclockwise" style="font-size:22px;color:#fff;"></i>
                            </div>
                            <div>
                                <div class="fw-bold" style="font-size:15px;color:#fff;">Reset Semua Absensi</div>
                                <div style="font-size:12px;color:rgba(255,255,255,.75);">Superadmin · Tindakan Permanen
                                </div>
                            </div>
                        </div>
                    </div>

                    <div style="padding:20px 24px;">
                        <div class="alert-danger-custom">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                            <strong>Peringatan!</strong> Seluruh data kehadiran peserta untuk event ini
                            akan <strong>dihapus permanen</strong> dan tidak dapat dikembalikan.
                        </div>

                        <form action="{{ route('admin.attendances.reset', $event) }}" method="POST"
                            id="formResetAttendance">
                            @csrf
                            @method('DELETE')
                            <label class="form-label fw-600" style="font-size:13px;">
                                Ketik
                                <span
                                    style="font-family:monospace;background:#ffebee;color:#c62828;
                                             padding:1px 8px;border-radius:6px;font-weight:700;">RESET</span>
                                untuk konfirmasi:
                            </label>
                            <input type="text" name="confirm" id="confirmReset" class="form-control confirm-input"
                                placeholder="RESET" autocomplete="off">
                        </form>
                    </div>

                    <div style="padding:0 24px 20px;display:flex;gap:8px;justify-content:flex-end;">
                        <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal"
                            style="border-radius:10px;">Batal</button>
                        <button type="submit" form="formResetAttendance" id="btnReset" class="btn btn-sm fw-600"
                            disabled
                            style="border-radius:10px;background:#c62828;color:#fff;border:none;opacity:.5;transition:opacity .2s;">
                            <i class="bi bi-trash me-1"></i> Ya, Reset Semua Absensi
                        </button>
                    </div>

                </div>
            </div>
        </div>
    @endif {{-- end superadmin modals --}}

@endsection


{{-- ═══════════════════════════════════════════════════════════
     SCRIPTS  ← @push di luar @section, bukan di dalamnya
════════════════════════════════════════════════════════════ --}}
@push('scripts')
    {{-- DataTables JS --}}
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

    <script>
        $(function() {

            // ── Init DataTables ───────────────────────────────────────
            $('#tblAttendance').DataTable({
                responsive: true,
                pageLength: 25,
                order: [
                    [7, 'desc']
                ], // default sort: Waktu Hadir terbaru
                language: {
                    search: 'Cari:',
                    searchPlaceholder: 'Nama, NOREG, Sekolah...',
                    lengthMenu: 'Tampilkan _MENU_ data',
                    info: 'Menampilkan _START_–_END_ dari _TOTAL_ data',
                    infoEmpty: 'Tidak ada data',
                    infoFiltered: '(difilter dari _MAX_ total data)',
                    zeroRecords: 'Data tidak ditemukan',
                    emptyTable: 'Belum ada peserta yang diabsen',
                    paginate: {
                        first: '«',
                        last: '»',
                        previous: '‹',
                        next: '›',
                    },
                },
                columnDefs: [{
                        orderable: false,
                        targets: [0, 8]
                    }, // kolom # dan aksi tidak sortable
                    {
                        searchable: false,
                        targets: [0, 8]
                    },
                ],
                dom: "<'row'<'col-sm-6'l><'col-sm-6'f>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col-sm-5'i><'col-sm-7'p>>",
                drawCallback: function() {
                    // Renumber kolom # sesuai halaman DataTables
                    var api = this.api();
                    var start = api.page.info().start;
                    api.column(0, {
                        page: 'current'
                    }).nodes().each(function(cell, i) {
                        cell.innerHTML =
                            '<span style="color:#aaa;font-size:12px;">' + (start + i + 1) +
                            '</span>';
                    });
                },
            });

            // ── Validasi konfirmasi modal "Tandai Semua Hadir" ────────
            @if (Auth::user()->role === 'superadmin')
                var $confirmMarkAll = $('#confirmMarkAll');
                var $btnMarkAll = $('#btnMarkAll');
                var $confirmReset = $('#confirmReset');
                var $btnReset = $('#btnReset');

                // ── Validasi "HADIR"
                $confirmMarkAll.on('input', function() {
                    var value = $(this).val().trim().toUpperCase();
                    var ok = value === 'HADIR';

                    $btnMarkAll.prop('disabled', !ok);
                    $btnMarkAll.css('opacity', ok ? '1' : '.5');
                });

                // ── Validasi "RESET"
                $confirmReset.on('input', function() {
                    var value = $(this).val().trim().toUpperCase();
                    var ok = value === 'RESET';

                    $btnReset.prop('disabled', !ok);
                    $btnReset.css('opacity', ok ? '1' : '.5');
                });

                // ── Reset saat modal ditutup
                $('#modalMarkAllPresent').on('hidden.bs.modal', function() {
                    $confirmMarkAll.val('');
                    $btnMarkAll.prop('disabled', true).css('opacity', '.5');
                });

                $('#modalResetAttendance').on('hidden.bs.modal', function() {
                    $confirmReset.val('');
                    $btnReset.prop('disabled', true).css('opacity', '.5');
                });
            @endif

            // @if (Auth::user()->role === 'superadmin')
            //     var $confirmMarkAll = $('#confirmMarkAll');
            //     var $btnMarkAll = $('#btnMarkAll');
            //     var $confirmReset = $('#confirmReset');
            //     var $btnReset = $('#btnReset');

            //     $confirmMarkAll.on('input', function() {
            //         var ok = this.value.trim() === 'HADIR';
            //         $btnMarkAll.prop('disabled', !ok).css('opacity', ok ? '1' : '.5');
            //     });

            //     $confirmReset.on('input', function() {
            //         var ok = this.value.trim() === 'RESET';
            //         $btnReset.prop('disabled', !ok).css('opacity', ok ? '1' : '.5');
            //     });

            //     // Bersihkan input saat modal ditutup
            //     $('#modalMarkAllPresent').on('hidden.bs.modal', function() {
            //         $confirmMarkAll.val('');
            //         $btnMarkAll.prop('disabled', true).css('opacity', '.5');
            //     });
            //     $('#modalResetAttendance').on('hidden.bs.modal', function() {
            //         $confirmReset.val('');
            //         $btnReset.prop('disabled', true).css('opacity', '.5');
            //     });
            // @endif

        });
    </script>
@endpush
