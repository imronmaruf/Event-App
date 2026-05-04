@extends('layouts.admin')
@section('title', 'Manajemen Unit')
@section('page-title', 'Manajemen Unit')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <style>
        .dt-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 16px rgba(0, 0, 0, .07);
            border: none;
        }

        .dt-card .card-header {
            background: #fff;
            border-bottom: 1px solid #f0f0f0;
            border-radius: 16px 16px 0 0;
            padding: 18px 20px;
        }

        table.dataTable thead th {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: #555;
            border-bottom: 2px solid #f0f0f0 !important;
        }

        table.dataTable tbody td {
            font-size: 13.5px;
            vertical-align: middle;
            padding: 10px 14px;
        }

        table.dataTable tbody tr:hover {
            background: #fafafa;
        }

        .dataTables_wrapper .dataTables_filter input {
            border-radius: 8px;
            border: 1.5px solid #e0e0e0;
            padding: 6px 12px;
            font-size: 13px;
        }

        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: #7b1fa2;
            outline: none;
            box-shadow: 0 0 0 3px rgba(123, 31, 162, .1);
        }

        .dataTables_wrapper .dataTables_length select {
            border-radius: 8px;
            border: 1.5px solid #e0e0e0;
            padding: 5px 10px;
        }

        .dataTables_wrapper .dataTables_info {
            font-size: 12px;
            color: #888;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: linear-gradient(135deg, #7b1fa2, #9c27b0) !important;
            border-color: transparent !important;
            color: #fff !important;
            border-radius: 8px;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #f3e5f5 !important;
            border-color: transparent !important;
            color: #7b1fa2 !important;
            border-radius: 8px;
        }

        .badge-aktif {
            background: #e8f5e9;
            color: #2e7d32;
            font-weight: 700;
            font-size: 11px;
            padding: 4px 10px;
            border-radius: 99px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .badge-nonaktif {
            background: #ffebee;
            color: #c62828;
            font-weight: 700;
            font-size: 11px;
            padding: 4px 10px;
            border-radius: 99px;
        }

        .dot-active {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #2e7d32;
            display: inline-block;
        }

        .dot-inactive {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #c62828;
            display: inline-block;
        }

        .btn-action {
            width: 30px;
            height: 30px;
            padding: 0;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            border: none;
            transition: all .18s;
            cursor: pointer;
        }

        .btn-action:hover {
            transform: translateY(-1px);
        }

        .modal-header-custom {
            background: linear-gradient(135deg, #7b1fa2, #9c27b0);
            color: #fff;
            border-radius: 14px 14px 0 0;
        }

        .modal-header-custom .btn-close {
            filter: invert(1);
        }

        .modal-content {
            border-radius: 16px;
            border: none;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .2);
        }

        .form-label {
            font-size: 13px;
            font-weight: 600;
            color: #444;
            margin-bottom: 5px;
        }

        .form-control,
        .form-select {
            border-radius: 10px;
            border: 1.5px solid #e0e0e0;
            font-size: 13.5px;
            padding: 9px 12px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #7b1fa2;
            box-shadow: 0 0 0 3px rgba(123, 31, 162, .12);
        }

        .stat-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #f8f9fa;
            border: 1px solid #eee;
            border-radius: 99px;
            padding: 5px 14px;
            font-size: 12px;
            font-weight: 600;
            color: #555;
        }

        .unit-avatar {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: linear-gradient(135deg, #7b1fa2, #9c27b0);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 15px;
            flex-shrink: 0;
        }
    </style>
@endpush

@section('topbar-actions')
    <button class="btn btn-sm" onclick="openCreateModal()"
        style="background:linear-gradient(135deg,#7b1fa2,#9c27b0);color:#fff;border-radius:10px;font-weight:700;padding:7px 16px;border:none;">
        <i class="bi bi-plus-circle me-1"></i> Tambah Unit
    </button>
@endsection

@section('content')

    {{-- Stat pills --}}
    <div class="d-flex flex-wrap gap-2 mb-4">
        <div class="stat-pill"><span
                style="width:8px;height:8px;border-radius:50%;background:#7b1fa2;display:inline-block;"></span> Total:
            <strong>{{ $units->total() }}</strong> unit</div>
        <div class="stat-pill"><span class="dot-active"></span> Aktif:
            <strong>{{ $units->getCollection()->where('is_active', true)->count() }}</strong></div>
        <div class="stat-pill"><span class="dot-inactive"></span> Nonaktif:
            <strong>{{ $units->getCollection()->where('is_active', false)->count() }}</strong></div>
    </div>

    <div class="card dt-card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <div>
                <h6 class="mb-0 fw-bold" style="color:#7b1fa2;"><i class="bi bi-building me-2"></i>Daftar Unit Kegiatan</h6>
                <small class="text-muted">Kelola unit penyelenggara event</small>
            </div>
            <button class="btn btn-sm" onclick="openCreateModal()"
                style="background:linear-gradient(135deg,#7b1fa2,#9c27b0);color:#fff;border-radius:10px;font-weight:700;border:none;">
                <i class="bi bi-plus me-1"></i> Unit Baru
            </button>
        </div>
        <div class="card-body p-3">
            <div class="table-responsive">
                <table id="unitsTable" class="table table-hover w-100">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Unit</th>
                            <th>Slug</th>
                            <th>Kota</th>
                            <th>Kontak</th>
                            <th>Event</th>
                            <th>Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($units as $i => $unit)
                            <tr>
                                <td class="text-muted" style="font-size:12px;">{{ $i + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="unit-avatar">{{ strtoupper(substr($unit->name, 0, 1)) }}</div>
                                        <div>
                                            <div class="fw-bold" style="font-size:13.5px;">{{ $unit->name }}</div>
                                            @if ($unit->description)
                                                <div class="text-muted"
                                                    style="font-size:11px;max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                                    {{ $unit->description }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td><code
                                        style="font-size:12px;background:#f3e5f5;color:#7b1fa2;padding:2px 8px;border-radius:6px;">{{ $unit->slug }}</code>
                                </td>
                                <td style="font-size:13px;">{{ $unit->city->name ?? '-' }}</td>
                                <td>
                                    @if ($unit->contact_person)
                                        <div style="font-size:13px;font-weight:600;">{{ $unit->contact_person }}</div>
                                    @endif
                                    @if ($unit->contact_phone)
                                        <div style="font-size:11px;color:#888;"><i
                                                class="bi bi-phone me-1"></i>{{ $unit->contact_phone }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="fw-bold"
                                        style="font-size:15px;color:#7b1fa2;">{{ $unit->events_count }}</span>
                                    <span class="text-muted" style="font-size:11px;"> event</span>
                                </td>
                                <td>
                                    @if ($unit->is_active)
                                        <span class="badge-aktif"><span class="dot-active"></span> Aktif</span>
                                    @else
                                        <span class="badge-nonaktif">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="text-center" style="white-space:nowrap;">
                                    <button class="btn-action" style="background:#f3e5f5;color:#7b1fa2;" title="Edit"
                                        onclick="openEditModal({{ $unit->id }})"><i class="bi bi-pencil"></i></button>

                                    <button class="btn-action" style="background:#ffebee;color:#c62828;" title="Hapus"
                                        onclick="openDeleteModal({{ $unit->id }}, '{{ addslashes($unit->name) }}', {{ $unit->events_count }})">
                                        <i class="bi bi-trash"></i></button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ════ MODAL CREATE ════ --}}
    <div class="modal fade" id="modalCreate" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header modal-header-custom py-3">
                    <h5 class="modal-title fw-bold"><i class="bi bi-building-add me-2"></i>Tambah Unit Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.units.store') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Nama Unit <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control"
                                    placeholder="Contoh: Unit Banda Aceh" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Slug <span class="text-muted fw-normal">(opsional, digenerate
                                        otomatis)</span></label>
                                <input type="text" name="slug" class="form-control" placeholder="unit-banda-aceh">
                                <div class="form-text">Digunakan sebagai bagian dari URL event. Gunakan huruf kecil dan
                                    tanda hubung.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Kota</label>
                                <select name="city_id" class="form-select">
                                    <option value="">-- Pilih Kota --</option>
                                    @foreach (\App\Models\City::orderBy('name')->get() as $city)
                                        <option value="{{ $city->id }}">{{ $city->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nama Kontak PIC</label>
                                <input type="text" name="contact_person" class="form-control"
                                    placeholder="Nama penanggung jawab">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">No. Telepon</label>
                                <input type="text" name="contact_phone" class="form-control"
                                    placeholder="08xxxxxxxxxx">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Deskripsi</label>
                                <textarea name="description" class="form-control" rows="2" placeholder="Keterangan unit (opsional)"></textarea>
                            </div>
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="c_is_active"
                                        value="1" checked style="width:42px;height:22px;">
                                    <label class="form-check-label fw-semibold" for="c_is_active"
                                        style="font-size:13px;">Unit Aktif</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0 px-4 pb-4 gap-2">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal"
                            style="border-radius:10px;">Batal</button>
                        <button type="submit" class="btn px-4 fw-bold"
                            style="background:linear-gradient(135deg,#7b1fa2,#9c27b0);color:#fff;border-radius:10px;border:none;">
                            <i class="bi bi-check-circle me-1"></i> Simpan Unit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ════ MODAL EDIT ════ --}}
    <div class="modal fade" id="modalEdit" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header py-3"
                    style="background:linear-gradient(135deg,#1565c0,#1976d2);color:#fff;border-radius:14px 14px 0 0;">
                    <h5 class="modal-title fw-bold"><i class="bi bi-building me-2"></i>Edit Unit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter:invert(1);"></button>
                </div>
                <form id="formEdit" method="POST">
                    @csrf @method('PUT')
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Nama Unit <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="e_name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Slug</label>
                                <input type="text" name="slug" id="e_slug" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Kota</label>
                                <select name="city_id" id="e_city_id" class="form-select">
                                    <option value="">-- Pilih Kota --</option>
                                    @foreach (\App\Models\City::orderBy('name')->get() as $city)
                                        <option value="{{ $city->id }}">{{ $city->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nama Kontak PIC</label>
                                <input type="text" name="contact_person" id="e_contact_person" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">No. Telepon</label>
                                <input type="text" name="contact_phone" id="e_contact_phone" class="form-control">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Deskripsi</label>
                                <textarea name="description" id="e_description" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="e_is_active"
                                        value="1" style="width:42px;height:22px;">
                                    <label class="form-check-label fw-semibold" for="e_is_active"
                                        style="font-size:13px;">Unit Aktif</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0 px-4 pb-4 gap-2">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal"
                            style="border-radius:10px;">Batal</button>
                        <button type="submit" class="btn px-4 fw-bold"
                            style="background:linear-gradient(135deg,#1565c0,#1976d2);color:#fff;border-radius:10px;border:none;">
                            <i class="bi bi-check-circle me-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ════ MODAL DELETE ════ --}}
    <div class="modal fade" id="modalDelete" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
            <div class="modal-content" style="border-radius:16px;border:none;">
                <div class="modal-body text-center p-5">
                    <div
                        style="width:68px;height:68px;background:#ffebee;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:30px;">
                        🏢</div>
                    <h5 class="fw-bold mb-2">Hapus Unit?</h5>
                    <p class="text-muted mb-1" style="font-size:14px;">Unit berikut akan dihapus:</p>
                    <p class="fw-bold" id="deleteUnitName" style="color:#7b1fa2;font-size:15px;"></p>
                    <div id="deleteWarning" class="alert alert-warning py-2 px-3"
                        style="font-size:12px;border-radius:10px;display:none;">
                        ⚠️ Unit ini memiliki event yang terkait. Hapus semua event di unit ini terlebih dahulu.
                    </div>
                    <p class="text-muted" style="font-size:12px;" id="deleteNote">Tindakan ini tidak bisa dibatalkan.</p>
                </div>
                <div class="d-flex gap-2 px-4 pb-4" id="deleteActions">
                    <button class="btn btn-light flex-grow-1" data-bs-dismiss="modal"
                        style="border-radius:10px;font-weight:600;">Batal</button>
                    <form id="formDelete" method="POST" class="flex-grow-1">
                        @csrf @method('DELETE')
                        <button type="submit" id="btnDeleteUnit" class="btn btn-danger w-100 fw-bold"
                            style="border-radius:10px;">
                            <i class="bi bi-trash me-1"></i> Ya, Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script id="unitsData" type="application/json">{!! json_encode($units->getCollection()->map(fn($u) => [
    'id'             => $u->id,
    'name'           => $u->name,
    'slug'           => $u->slug,
    'city_id'        => $u->city_id,
    'description'    => $u->description,
    'contact_person' => $u->contact_person,
    'contact_phone'  => $u->contact_phone,
    'is_active'      => $u->is_active,
    'events_count'   => $u->events_count,
    'update_url'     => route('admin.units.update', $u),
    'delete_url'     => route('admin.units.destroy', $u),
])) !!}</script>

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#unitsTable').DataTable({
                responsive: true,
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_–_END_ dari _TOTAL_ unit",
                    infoEmpty: "Tidak ada data",
                    emptyTable: "Belum ada unit.",
                    paginate: {
                        previous: "‹",
                        next: "›"
                    },
                },
                columnDefs: [{
                    orderable: false,
                    targets: [7]
                }],
                order: [
                    [1, 'asc']
                ],
                pageLength: 10,
            });
        });

        const UNITS = {};
        JSON.parse(document.getElementById('unitsData').textContent).forEach(u => UNITS[u.id] = u);

        function openCreateModal() {
            new bootstrap.Modal(document.getElementById('modalCreate')).show();
        }

        function openEditModal(id) {
            const u = UNITS[id];
            if (!u) return;
            document.getElementById('formEdit').action = u.update_url;
            document.getElementById('e_name').value = u.name;
            document.getElementById('e_slug').value = u.slug ?? '';
            document.getElementById('e_city_id').value = u.city_id ?? '';
            document.getElementById('e_contact_person').value = u.contact_person ?? '';
            document.getElementById('e_contact_phone').value = u.contact_phone ?? '';
            document.getElementById('e_description').value = u.description ?? '';
            document.getElementById('e_is_active').checked = u.is_active;
            new bootstrap.Modal(document.getElementById('modalEdit')).show();
        }

        function openDeleteModal(id, name, eventCount) {
            const u = UNITS[id];
            document.getElementById('deleteUnitName').textContent = name;
            document.getElementById('formDelete').action = u.delete_url;
            const warn = document.getElementById('deleteWarning');
            const btn = document.getElementById('btnDeleteUnit');
            if (eventCount > 0) {
                warn.style.display = 'block';
                btn.disabled = true;
                btn.style.opacity = '.5';
            } else {
                warn.style.display = 'none';
                btn.disabled = false;
                btn.style.opacity = '1';
            }
            new bootstrap.Modal(document.getElementById('modalDelete')).show();
        }
    </script>
@endpush
