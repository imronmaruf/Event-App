@extends('layouts.admin')
@section('title', 'Manajemen Event')
@section('page-title', 'Manajemen Event')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <link rel="stylesheet" href="{{ asset('css/admin/event/style.css') }}">
@endpush

@section('topbar-actions')
    <button class="btn btn-sm btn-danger" onclick="openCreateModal()"
        style="border-radius:10px;font-weight:700;padding:7px 16px;">
        <i class="bi bi-plus-circle me-1"></i> Tambah Event
    </button>
@endsection

@section('content')

    {{-- ── STAT PILLS ── --}}
    <div class="d-flex flex-wrap gap-2 mb-4">
        <div class="stat-pill"><span class="dot" style="background:#c62828;"></span> Total:
            <strong>{{ $events->total() }}</strong> event
        </div>
        <div class="stat-pill"><span class="dot" style="background:#2e7d32;"></span> Aktif:
            <strong>{{ $events->getCollection()->where('is_active', true)->count() }}</strong>
        </div>
        <div class="stat-pill"><span class="dot" style="background:#e65100;"></span> Nonaktif:
            <strong>{{ $events->getCollection()->where('is_active', false)->count() }}</strong>
        </div>
    </div>

    {{-- ── TABLE CARD ── --}}
    <div class="card dt-card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <div>
                <h6 class="mb-0 fw-bold" style="color:#c62828;"><i class="bi bi-calendar-event me-2"></i>Daftar Event</h6>
                <small class="text-muted">Kelola semua event perlombaan</small>
            </div>
            <button class="btn btn-sm" onclick="openCreateModal()"
                style="background:linear-gradient(135deg,#c62828,#e64a19);color:#fff;border-radius:10px;font-weight:700;border:none;">
                <i class="bi bi-plus me-1"></i> Event Baru
            </button>
        </div>
        <div class="card-body p-3">
            <div class="table-responsive">
                <table id="eventsTable" class="table table-hover w-100">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama Event</th>
                            <th>Unit</th>
                            <th>Kota</th>
                            <th>Tanggal</th>
                            <th>Peserta</th>
                            <th>Absensi</th>
                            <th>Kode</th>
                            <th>Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($events as $i => $event)
                            <tr>
                                <td class="text-muted" style="font-size:12px;">{{ $i + 1 }}</td>
                                <td>
                                    <div class="fw-bold"
                                        style="font-size:13.5px;max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"
                                        title="{{ $event->name }}">
                                        {{ $event->name }}
                                    </div>
                                    @if ($event->venue)
                                        <div class="text-muted" style="font-size:11px;"><i
                                                class="bi bi-geo-alt me-1"></i>{{ $event->venue }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span style="font-size:13px;font-weight:600;">{{ $event->unit->name }}</span>
                                </td>
                                <td class="text-muted" style="font-size:13px;">{{ $event->city->name ?? '-' }}</td>
                                <td style="font-size:12px;white-space:nowrap;">
                                    {{ $event->event_date ? $event->event_date->format('d/m/Y') : '-' }}
                                </td>
                                <td>
                                    @php
                                        $total = $event->participants_count ?? 0;
                                        $hadir = $event->attendances_count ?? 0;
                                    @endphp
                                    <div style="font-size:13px;font-weight:700;">{{ $total }}</div>
                                    <div style="font-size:10px;color:#aaa;">peserta</div>
                                </td>
                                <td>
                                    <div style="font-size:13px;font-weight:700;color:#2e7d32;">{{ $hadir }}<span
                                            style="font-size:11px;color:#aaa;font-weight:400;">/{{ $total }}</span>
                                    </div>
                                    <div class="prog-mini">
                                        <div class="prog-mini-fill"
                                            style="width:{{ $total > 0 ? round(($hadir / $total) * 100) : 0 }}%"></div>
                                    </div>
                                </td>
                                <td>
                                    @if ($event->digit_count)
                                        <span class="badge-digit"><i class="bi bi-123 me-1"></i>{{ $event->digit_count }}
                                            digit</span>
                                    @else
                                        <span style="font-size:11px;color:#aaa;">Belum diset</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($event->is_active)
                                        <span class="badge-aktif"><i class="bi bi-lightning-charge me-1"></i>Aktif</span>
                                    @elseif($event->is_archived)
                                        <span class="badge-arsip">Arsip</span>
                                    @else
                                        <span class="badge-nonaktif">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="text-center" style="white-space:nowrap;">
                                    {{-- Detail --}}
                                    <a href="{{ route('admin.events.show', $event) }}" class="btn-action"
                                        style="background:#e3f2fd;color:#1565c0;" title="Detail"><i
                                            class="bi bi-eye"></i></a>

                                    {{-- Edit --}}
                                    <button class="btn-action" style="background:#fff8e1;color:#f57c00;" title="Edit"
                                        onclick="openEditModal({{ $event->id }})"><i class="bi bi-pencil"></i></button>

                                    {{-- Toggle Aktif --}}
                                    <form action="{{ route('admin.events.toggle', $event) }}" method="POST"
                                        class="d-inline">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="btn-action"
                                            style="background:{{ $event->is_active ? '#fce4ec' : '#e8f5e9' }};color:{{ $event->is_active ? '#c62828' : '#2e7d32' }};"
                                            title="{{ $event->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                                            onclick="return confirm('{{ $event->is_active ? 'Nonaktifkan' : 'Aktifkan' }} absensi event ini?')">
                                            <i class="bi bi-{{ $event->is_active ? 'pause-circle' : 'play-circle' }}"></i>
                                        </button>
                                    </form>

                                    {{-- Link Absensi --}}
                                    <a href="{{ $event->publicAttendanceUrl() }}" target="_blank" class="btn-action"
                                        style="background:#e8f5e9;color:#2e7d32;" title="Buka halaman absensi publik">
                                        <i class="bi bi-qr-code"></i>
                                    </a>

                                    {{-- Hapus --}}
                                    <button class="btn-action" style="background:#ffebee;color:#c62828;" title="Hapus"
                                        onclick="openDeleteModal({{ $event->id }}, '{{ addslashes($event->name) }}')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ════════════════ MODAL: CREATE EVENT ═══════════════ --}}
    <div class="modal fade" id="modalCreate" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header modal-header-custom py-3">
                    <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle me-2"></i>Tambah Event Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formCreate" action="{{ route('admin.events.store') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            {{-- Nama Event --}}
                            <div class="col-12">
                                <label class="form-label">Nama Event <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control"
                                    placeholder="Contoh: Math & Science Competition 2025" required>
                            </div>

                            {{-- Unit --}}
                            <div class="col-md-6">
                                <label class="form-label">Unit Kegiatan <span class="text-danger">*</span></label>
                                <select name="unit_id" class="form-select" required>
                                    <option value="">-- Pilih Unit --</option>
                                    @foreach (\App\Models\Unit::where('is_active', true)->orderBy('name')->get() as $unit)
                                        <option value="{{ $unit->id }}"
                                            {{ auth()->user()->isAdmin() && auth()->user()->unit_id == $unit->id ? 'selected' : '' }}>
                                            {{ $unit->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Kota --}}
                            <div class="col-md-6">
                                <label class="form-label">Kota Kegiatan</label>
                                <select name="city_id" class="form-select">
                                    <option value="">-- Pilih Kota --</option>
                                    @foreach (\App\Models\City::orderBy('name')->get() as $city)
                                        <option value="{{ $city->id }}">{{ $city->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Venue --}}
                            <div class="col-md-6">
                                <label class="form-label">Tempat / Venue</label>
                                <input type="text" name="venue" class="form-control"
                                    placeholder="Nama gedung atau lokasi">
                            </div>

                            {{-- Tanggal --}}
                            <div class="col-md-3">
                                <label class="form-label">Tanggal</label>
                                <input type="date" name="event_date" class="form-control">
                            </div>

                            {{-- Jam --}}
                            <div class="col-md-3">
                                <label class="form-label">Jam Mulai</label>
                                <input type="time" name="event_time" class="form-control">
                            </div>

                            {{-- Deskripsi --}}
                            <div class="col-12">
                                <label class="form-label">Deskripsi</label>
                                <textarea name="description" class="form-control" rows="2" placeholder="Keterangan tambahan (opsional)"></textarea>
                            </div>

                            {{-- ── Konfigurasi Kode Absensi ── --}}
                            <div class="col-12">
                                <div class="p-3 rounded-3" style="background:#fff8f8;border:1.5px solid #ffcdd2;">
                                    <div class="fw-bold mb-3" style="font-size:13px;color:#c62828;"><i
                                            class="bi bi-key me-1"></i> Konfigurasi Kode Absensi</div>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label">Mode Digit</label>
                                            <select name="digit_mode" class="form-select" id="c_digit_mode"
                                                onchange="toggleDigitCount('c')">
                                                <option value="auto">🤖 Otomatis (Auto-detect)</option>
                                                <option value="manual">✏️ Manual (Tentukan sendiri)</option>
                                            </select>
                                            <div class="form-text">Auto: sistem hitung sendiri setelah import peserta.
                                            </div>
                                        </div>
                                        <div class="col-md-4" id="c_digit_count_wrap" style="display:none;">
                                            <label class="form-label">Jumlah Digit</label>
                                            <input type="number" name="digit_count" class="form-control"
                                                id="c_digit_count" min="1" max="20"
                                                placeholder="Contoh: 4">
                                            <div class="form-text">Berapa digit unik dari NOREG.</div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Posisi Ambil Digit</label>
                                            <select name="digit_position" class="form-select">
                                                <option value="suffix">📍 Akhir (Suffix) — Direkomendasikan</option>
                                                <option value="prefix">📍 Awal (Prefix)</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0 px-4 pb-4 gap-2">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal"
                            style="border-radius:10px;">Batal</button>
                        <button type="submit" class="btn px-4 fw-bold"
                            style="background:linear-gradient(135deg,#c62828,#e64a19);color:#fff;border-radius:10px;border:none;">
                            <i class="bi bi-check-circle me-1"></i> Simpan Event
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ════════════════ MODAL: EDIT EVENT ═══════════════ --}}
    <div class="modal fade" id="modalEdit" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header py-3"
                    style="background:linear-gradient(135deg,#e65100,#f57c00);color:#fff;border-radius:14px 14px 0 0;">
                    <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i>Edit Event</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter:invert(1);"></button>
                </div>
                <form id="formEdit" method="POST">
                    @csrf @method('PUT')
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Nama Event <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="e_name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Unit Kegiatan <span class="text-danger">*</span></label>
                                <select name="unit_id" id="e_unit_id" class="form-select" required>
                                    <option value="">-- Pilih Unit --</option>
                                    @foreach (\App\Models\Unit::where('is_active', true)->orderBy('name')->get() as $unit)
                                        <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Kota Kegiatan</label>
                                <select name="city_id" id="e_city_id" class="form-select">
                                    <option value="">-- Pilih Kota --</option>
                                    @foreach (\App\Models\City::orderBy('name')->get() as $city)
                                        <option value="{{ $city->id }}">{{ $city->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Venue</label>
                                <input type="text" name="venue" id="e_venue" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Tanggal</label>
                                <input type="date" name="event_date" id="e_event_date" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Jam Mulai</label>
                                <input type="time" name="event_time" id="e_event_time" class="form-control">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Deskripsi</label>
                                <textarea name="description" id="e_description" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="col-12">
                                <div class="p-3 rounded-3" style="background:#fff8f0;border:1.5px solid #ffe0b2;">
                                    <div class="fw-bold mb-3" style="font-size:13px;color:#e65100;"><i
                                            class="bi bi-key me-1"></i> Konfigurasi Kode Absensi</div>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label">Mode Digit</label>
                                            <select name="digit_mode" id="e_digit_mode" class="form-select"
                                                onchange="toggleDigitCount('e')">
                                                <option value="auto">🤖 Otomatis</option>
                                                <option value="manual">✏️ Manual</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4" id="e_digit_count_wrap" style="display:none;">
                                            <label class="form-label">Jumlah Digit</label>
                                            <input type="number" name="digit_count" id="e_digit_count"
                                                class="form-control" min="1" max="20">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Posisi Digit</label>
                                            <select name="digit_position" id="e_digit_position" class="form-select">
                                                <option value="suffix">📍 Akhir (Suffix)</option>
                                                <option value="prefix">📍 Awal (Prefix)</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0 px-4 pb-4 gap-2">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal"
                            style="border-radius:10px;">Batal</button>
                        <button type="submit" class="btn px-4 fw-bold"
                            style="background:linear-gradient(135deg,#e65100,#f57c00);color:#fff;border-radius:10px;border:none;">
                            <i class="bi bi-check-circle me-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ════════════════ MODAL: DELETE CONFIRM ═══════════════ --}}
    <div class="modal fade" id="modalDelete" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
            <div class="modal-content" style="border-radius:16px;border:none;">
                <div class="modal-body text-center p-5">
                    <div
                        style="width:68px;height:68px;background:#ffebee;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:30px;">
                        🗑️</div>
                    <h5 class="fw-bold mb-2">Hapus Event?</h5>
                    <p class="text-muted mb-1" style="font-size:14px;">Event berikut akan dihapus permanen:</p>
                    <p class="fw-bold" id="deleteEventName" style="color:#c62828;font-size:15px;"></p>
                    <p class="text-muted" style="font-size:12px;">Semua data peserta dan absensi dalam event ini juga ikut
                        terhapus. Tindakan ini tidak bisa dibatalkan.</p>
                </div>
                <div class="d-flex gap-2 px-4 pb-4">
                    <button class="btn btn-light flex-grow-1" data-bs-dismiss="modal"
                        style="border-radius:10px;font-weight:600;">Batal</button>
                    <form id="formDelete" method="POST" class="flex-grow-1">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100 fw-bold" style="border-radius:10px;">
                            <i class="bi bi-trash me-1"></i> Ya, Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Data events untuk JS --}}
    <script id="eventsData" type="application/json">{!! json_encode($events->getCollection()->map(fn($e) => [
    'id'             => $e->id,
    'name'           => $e->name,
    'unit_id'        => $e->unit_id,
    'city_id'        => $e->city_id,
    'venue'          => $e->venue,
    'event_date'     => $e->event_date?->format('Y-m-d'),
    'event_time'     => $e->event_time,
    'description'    => $e->description,
    'digit_mode'     => $e->digit_mode,
    'digit_count'    => $e->digit_count,
    'digit_position' => $e->digit_position,
    'update_url'     => route('admin.events.update', $e),
    'delete_url'     => route('admin.events.destroy', $e),
])) !!}</script>

@endsection

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>

    <script>
        // ── DataTable ────────────────────────────────────────────────
        $(document).ready(function() {
            $('#eventsTable').DataTable({
                responsive: true,
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_–_END_ dari _TOTAL_ event",
                    infoEmpty: "Tidak ada data",
                    paginate: {
                        previous: "‹",
                        next: "›"
                    },
                    emptyTable: "Belum ada event.",
                    zeroRecords: "Tidak ditemukan.",
                },
                columnDefs: [{
                    orderable: false,
                    targets: [9]
                }],
                order: [
                    [0, 'asc']
                ],
                pageLength: 10,
            });
        });

        // ── Event data map ───────────────────────────────────────────
        const EVENTS = {};
        JSON.parse(document.getElementById('eventsData').textContent).forEach(e => EVENTS[e.id] = e);

        // ── Helpers ──────────────────────────────────────────────────
        function toggleDigitCount(prefix) {
            const mode = document.getElementById(prefix + '_digit_mode').value;
            const wrap = document.getElementById(prefix + '_digit_count_wrap');
            const inp = document.getElementById(prefix + '_digit_count');
            if (mode === 'manual') {
                wrap.style.display = 'block';
                inp.required = true;
            } else {
                wrap.style.display = 'none';
                inp.required = false;
            }
        }

        function openCreateModal() {
            document.getElementById('formCreate').reset();
            document.getElementById('c_digit_count_wrap').style.display = 'none';
            new bootstrap.Modal(document.getElementById('modalCreate')).show();
        }

        function openEditModal(id) {
            const e = EVENTS[id];
            if (!e) return;

            document.getElementById('formEdit').action = e.update_url;
            document.getElementById('e_name').value = e.name;
            document.getElementById('e_unit_id').value = e.unit_id;
            document.getElementById('e_city_id').value = e.city_id ?? '';
            document.getElementById('e_venue').value = e.venue ?? '';
            document.getElementById('e_event_date').value = e.event_date ?? '';
            document.getElementById('e_event_time').value = e.event_time ?? '';
            document.getElementById('e_description').value = e.description ?? '';
            document.getElementById('e_digit_mode').value = e.digit_mode ?? 'auto';
            document.getElementById('e_digit_count').value = e.digit_count ?? '';
            document.getElementById('e_digit_position').value = e.digit_position ?? 'suffix';
            toggleDigitCount('e');

            new bootstrap.Modal(document.getElementById('modalEdit')).show();
        }

        function openDeleteModal(id, name) {
            const e = EVENTS[id];
            document.getElementById('deleteEventName').textContent = name;
            document.getElementById('formDelete').action = e.delete_url;
            new bootstrap.Modal(document.getElementById('modalDelete')).show();
        }
    </script>
@endpush
