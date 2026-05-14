@extends('layouts.admin')
@section('title', 'Manajemen Kota')
@section('page-title', 'Manajemen Kota')

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

        /* toast */
        #liveToast {
            min-width: 280px;
        }
    </style>
@endpush
{{--
@section('topbar-actions')
    <button class="btn btn-sm" onclick="openCreateModal()"
        style="background:linear-gradient(135deg,#7b1fa2,#9c27b0);color:#fff;border-radius:10px;font-weight:700;padding:7px 16px;border:none;">
        <i class="bi bi-plus-circle me-1"></i> Tambah Kota
    </button>
@endsection --}}

@section('content')

    {{-- Flash messages --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert"
            style="border-radius:12px;font-size:13.5px;">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert"
            style="border-radius:12px;font-size:13.5px;">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Stat pills --}}
    <div class="d-flex flex-wrap gap-2 mb-4">
        <div class="stat-pill">
            <span style="width:8px;height:8px;border-radius:50%;background:#7b1fa2;display:inline-block;"></span>
            Total: <strong>{{ $cities->total() }}</strong> kota
        </div>
        <div class="stat-pill">
            <i class="bi bi-building text-primary" style="font-size:12px;"></i>
            Total Unit: <strong>{{ $cities->getCollection()->sum('units_count') }}</strong>
        </div>
    </div>

    <div class="card dt-card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <div>
                <h6 class="mb-0 fw-bold" style="color:#7b1fa2;"><i class="bi bi-geo-alt me-2"></i>Daftar Kota</h6>
                <small class="text-muted">Kelola data kota</small>
            </div>
            <button class="btn btn-sm" onclick="openCreateModal()"
                style="background:linear-gradient(135deg,#7b1fa2,#9c27b0);color:#fff;border-radius:10px;font-weight:700;border:none;">
                <i class="bi bi-plus me-1"></i> Kota Baru
            </button>
        </div>
        <div class="card-body p-3">
            <div class="table-responsive">
                <table id="citiesTable" class="table table-hover w-100">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Kota</th>
                            <th>Provinsi</th>
                            <th class="text-center">Jumlah Unit</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($cities as $i => $city)
                            <tr>
                                <td class="text-muted" style="font-size:12px;">{{ $i + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="unit-avatar">
                                            {{ strtoupper(substr($city->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold" style="font-size:13.5px;">{{ $city->name }}</div>
                                        </div>
                                    </div>
                                </td>
                                {{-- province adalah kolom string, bukan relasi --}}
                                <td style="font-size:13px;">{{ $city->province ?: '-' }}</td>
                                <td class="text-center">
                                    <span class="fw-bold" style="font-size:15px;color:#7b1fa2;">
                                        {{ $city->units_count ?? 0 }}
                                    </span>
                                    <span class="text-muted" style="font-size:11px;"> unit</span>
                                </td>
                                <td class="text-center" style="white-space:nowrap;">
                                    <button class="btn-action" style="background:#f3e5f5;color:#7b1fa2;" title="Edit"
                                        onclick="openEditModal({{ $city->id }})">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn-action" style="background:#ffebee;color:#c62828;" title="Hapus"
                                        onclick="openDeleteModal({{ $city->id }}, '{{ addslashes($city->name) }}', {{ $city->units_count ?? 0 }})">
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

    {{-- Data JSON untuk JS --}}
    <script id="citiesData" type="application/json">
        {!! json_encode($cities->getCollection()->map(fn($c) => [
            'id'         => $c->id,
            'name'       => $c->name,
            'province'   => $c->province,
            'units_count'=> $c->units_count ?? 0,
            'update_url' => route('admin.cities.update', $c),
            'delete_url' => route('admin.cities.destroy', $c),
        ])) !!}
    </script>

    {{-- ════ MODAL CREATE ════ --}}
    <div class="modal fade" id="modalCreate" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header modal-header-custom py-3">
                    <h5 class="modal-title fw-bold"><i class="bi bi-geo-alt me-2"></i>Tambah Kota Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Nama Kota <span class="text-danger">*</span></label>
                            <input type="text" id="c_name" class="form-control" placeholder="Contoh: Banda Aceh"
                                maxlength="100">
                            <div class="invalid-feedback" id="c_name_error"></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Provinsi</label>
                            <input type="text" id="c_province" class="form-control" placeholder="Contoh: Aceh"
                                maxlength="100">
                            <div class="invalid-feedback" id="c_province_error"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 px-4 pb-4 gap-2">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal"
                        style="border-radius:10px;">Batal</button>
                    <button type="button" class="btn px-4 fw-bold" id="btnCreate" onclick="submitCreate()"
                        style="background:linear-gradient(135deg,#7b1fa2,#9c27b0);color:#fff;border-radius:10px;border:none;">
                        <i class="bi bi-check-circle me-1"></i> Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ════ MODAL EDIT ════ --}}
    <div class="modal fade" id="modalEdit" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header py-3"
                    style="background:linear-gradient(135deg,#1565c0,#1976d2);color:#fff;border-radius:14px 14px 0 0;">
                    <h5 class="modal-title fw-bold"><i class="bi bi-geo-alt me-2"></i>Edit Kota</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter:invert(1);"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" id="e_id">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Nama Kota <span class="text-danger">*</span></label>
                            <input type="text" id="e_name" class="form-control" maxlength="100">
                            <div class="invalid-feedback" id="e_name_error"></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Provinsi</label>
                            <input type="text" id="e_province" class="form-control" maxlength="100">
                            <div class="invalid-feedback" id="e_province_error"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 px-4 pb-4 gap-2">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal"
                        style="border-radius:10px;">Batal</button>
                    <button type="button" class="btn px-4 fw-bold" id="btnEdit" onclick="submitEdit()"
                        style="background:linear-gradient(135deg,#1565c0,#1976d2);color:#fff;border-radius:10px;border:none;">
                        <i class="bi bi-check-circle me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ════ MODAL DELETE ════ --}}
    <div class="modal fade" id="modalDelete" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
            <div class="modal-content">
                <div class="modal-body text-center p-5">
                    <div
                        style="width:68px;height:68px;background:#ffebee;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:30px;">
                        🗑️
                    </div>
                    <h5 class="fw-bold mb-2">Hapus Kota?</h5>
                    <p class="text-muted mb-1" style="font-size:14px;">Kota berikut akan dihapus:</p>
                    <p class="fw-bold" id="deleteCityName" style="color:#7b1fa2;font-size:15px;"></p>
                    <div id="deleteWarning" class="alert alert-warning py-2 px-3"
                        style="font-size:12px;border-radius:10px;display:none;">
                        ⚠️ Kota ini masih memiliki unit terkait. Hapus semua unit di kota ini terlebih dahulu.
                    </div>
                    <p class="text-muted" style="font-size:12px;">Tindakan ini tidak bisa dibatalkan.</p>
                </div>
                <div class="d-flex gap-2 px-4 pb-4">
                    <button class="btn btn-light flex-grow-1" data-bs-dismiss="modal"
                        style="border-radius:10px;font-weight:600;">Batal</button>
                    <button type="button" id="btnDeleteCity" class="btn btn-danger flex-grow-1 fw-bold"
                        onclick="submitDelete()" style="border-radius:10px;">
                        <i class="bi bi-trash me-1"></i> Ya, Hapus
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    <script>
        // ── DataTable ──────────────────────────────────────────────
        $(document).ready(function() {
            $('#citiesTable').DataTable({
                responsive: true,
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_–_END_ dari _TOTAL_ kota",
                    infoEmpty: "Tidak ada data",
                    emptyTable: "Belum ada kota.",
                    paginate: {
                        previous: "‹",
                        next: "›"
                    },
                },
                columnDefs: [{
                    orderable: false,
                    targets: [4]
                }],
                order: [
                    [1, 'asc']
                ],
                pageLength: 10,
            });
        });

        // ── Data map ───────────────────────────────────────────────
        const CITIES = {};
        JSON.parse(document.getElementById('citiesData').textContent)
            .forEach(c => CITIES[c.id] = c);

        const CSRF = document.querySelector('meta[name="csrf-token"]').content;

        // ── Helpers ────────────────────────────────────────────────
        function setLoading(btnId, loading) {
            const btn = document.getElementById(btnId);
            btn.disabled = loading;
            btn.innerHTML = loading ?
                '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...' :
                (btnId === 'btnCreate' ?
                    '<i class="bi bi-check-circle me-1"></i> Simpan' :
                    '<i class="bi bi-check-circle me-1"></i> Simpan Perubahan');
        }

        function clearErrors(prefix) {
            ['name', 'province'].forEach(f => {
                const el = document.getElementById(`${prefix}_${f}`);
                const err = document.getElementById(`${prefix}_${f}_error`);
                if (el) el.classList.remove('is-invalid');
                if (err) err.textContent = '';
            });
        }

        function showErrors(prefix, errors) {
            Object.entries(errors).forEach(([field, messages]) => {
                const el = document.getElementById(`${prefix}_${field}`);
                const err = document.getElementById(`${prefix}_${field}_error`);
                if (el) el.classList.add('is-invalid');
                if (err) err.textContent = messages[0];
            });
        }

        function showToast(message, type = 'success') {
            // pakai alert Bootstrap sederhana — inject ke atas konten
            const wrap = document.querySelector('.card.dt-card');
            const div = document.createElement('div');
            div.className = `alert alert-${type} alert-dismissible fade show`;
            div.style.cssText = 'border-radius:12px;font-size:13.5px;';
            div.innerHTML = `<i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
            wrap.parentNode.insertBefore(div, wrap);
            setTimeout(() => div.remove(), 5000);
        }

        // ── Reload baris tabel tanpa full reload ───────────────────
        function reloadPage() {
            location.reload();
        }

        // ── CREATE ─────────────────────────────────────────────────
        function openCreateModal() {
            clearErrors('c');
            document.getElementById('c_name').value = '';
            document.getElementById('c_province').value = '';
            new bootstrap.Modal(document.getElementById('modalCreate')).show();
            setTimeout(() => document.getElementById('c_name').focus(), 400);
        }

        async function submitCreate() {
            clearErrors('c');
            const name = document.getElementById('c_name').value.trim();
            const province = document.getElementById('c_province').value.trim();

            if (!name) {
                document.getElementById('c_name').classList.add('is-invalid');
                document.getElementById('c_name_error').textContent = 'Nama kota wajib diisi.';
                return;
            }

            setLoading('btnCreate', true);
            try {
                const res = await fetch('{{ route('admin.cities.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        name,
                        province
                    }),
                });

                const data = await res.json();

                if (res.status === 422) {
                    showErrors('c', data.errors);
                    return;
                }
                if (!res.ok) throw new Error(data.message ?? 'Terjadi kesalahan.');

                bootstrap.Modal.getInstance(document.getElementById('modalCreate')).hide();
                showToast(data.message ?? `Kota "${name}" berhasil ditambahkan.`);
                reloadPage();

            } catch (err) {
                showToast(err.message, 'danger');
            } finally {
                setLoading('btnCreate', false);
            }
        }

        // ── EDIT ───────────────────────────────────────────────────
        function openEditModal(id) {
            const c = CITIES[id];
            if (!c) return;
            clearErrors('e');
            document.getElementById('e_id').value = c.id;
            document.getElementById('e_name').value = c.name;
            document.getElementById('e_province').value = c.province ?? '';
            new bootstrap.Modal(document.getElementById('modalEdit')).show();
            setTimeout(() => document.getElementById('e_name').focus(), 400);
        }

        async function submitEdit() {
            clearErrors('e');
            const id = document.getElementById('e_id').value;
            const name = document.getElementById('e_name').value.trim();
            const province = document.getElementById('e_province').value.trim();
            const c = CITIES[id];

            if (!name) {
                document.getElementById('e_name').classList.add('is-invalid');
                document.getElementById('e_name_error').textContent = 'Nama kota wajib diisi.';
                return;
            }

            setLoading('btnEdit', true);
            try {
                const res = await fetch(c.update_url, {
                    method: 'POST', // Laravel _method spoofing via JSON tidak bisa,
                    headers: { // jadi kita kirim _method di body
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        _method: 'PUT',
                        name,
                        province
                    }),
                });

                const data = await res.json();

                if (res.status === 422) {
                    showErrors('e', data.errors);
                    return;
                }
                if (!res.ok) throw new Error(data.message ?? 'Terjadi kesalahan.');

                bootstrap.Modal.getInstance(document.getElementById('modalEdit')).hide();
                showToast(data.message ?? 'Kota berhasil diperbarui.');
                reloadPage();

            } catch (err) {
                showToast(err.message, 'danger');
            } finally {
                setLoading('btnEdit', false);
            }
        }

        // ── DELETE ─────────────────────────────────────────────────
        let _deleteId = null;

        function openDeleteModal(id, name, unitsCount) {
            _deleteId = id;
            document.getElementById('deleteCityName').textContent = name;
            const warn = document.getElementById('deleteWarning');
            const btn = document.getElementById('btnDeleteCity');
            if (unitsCount > 0) {
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

        async function submitDelete() {
            const c = CITIES[_deleteId];
            if (!c) return;

            const btn = document.getElementById('btnDeleteCity');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menghapus...';

            try {
                const res = await fetch(c.delete_url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        _method: 'DELETE'
                    }),
                });

                const data = await res.json();

                if (!res.ok) throw new Error(data.message ?? 'Gagal menghapus.');

                bootstrap.Modal.getInstance(document.getElementById('modalDelete')).hide();
                showToast(data.message ?? `Kota berhasil dihapus.`);
                reloadPage();

            } catch (err) {
                showToast(err.message, 'danger');
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-trash me-1"></i> Ya, Hapus';
            }
        }
    </script>
@endpush
