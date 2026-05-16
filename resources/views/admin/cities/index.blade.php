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

        .modal-content {
            border-radius: 16px;
            border: none;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .2);
        }

        .modal-header-purple {
            background: linear-gradient(135deg, #7b1fa2, #9c27b0);
            color: #fff;
            border-radius: 14px 14px 0 0;
        }

        .modal-header-purple .btn-close {
            filter: invert(1);
        }

        .form-label {
            font-size: 13px;
            font-weight: 600;
            color: #444;
            margin-bottom: 5px;
        }

        .form-control {
            border-radius: 10px;
            border: 1.5px solid #e0e0e0;
            font-size: 13.5px;
            padding: 9px 12px;
        }

        .form-control:focus {
            border-color: #7b1fa2;
            box-shadow: 0 0 0 3px rgba(123, 31, 162, .12);
        }

        .form-control.is-invalid {
            border-color: #c62828;
        }

        .invalid-feedback {
            font-size: 12px;
            color: #c62828;
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

        .city-avatar {
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

        /* Toast notification */
        .toast-container-custom {
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
        }

        .toast-item {
            background: #fff;
            border-radius: 12px;
            padding: 12px 16px;
            margin-bottom: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, .15);
            border-left: 4px solid;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13.5px;
            animation: slideIn .3s ease;
        }

        .toast-item.success {
            border-color: #2e7d32;
        }

        .toast-item.danger {
            border-color: #c62828;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(20px)
            }

            to {
                opacity: 1;
                transform: translateX(0)
            }
        }
    </style>
@endpush

@section('topbar-actions')
    <button onclick="openCreateModal()" class="btn btn-sm fw-bold"
        style="background:linear-gradient(135deg,#7b1fa2,#9c27b0);color:#fff;border-radius:10px;border:none;padding:7px 16px;">
        <i class="bi bi-plus-circle me-1"></i> Tambah Kota
    </button>
@endsection

@section('content')

    {{-- Toast container --}}
    <div class="toast-container-custom" id="toast-container"></div>

    {{-- Stat --}}
    <div class="d-flex flex-wrap gap-2 mb-4">
        <div class="stat-pill">
            <span style="width:8px;height:8px;border-radius:50%;background:#7b1fa2;display:inline-block;"></span>
            Total: <strong>{{ $cities->total() }}</strong> kota
        </div>
    </div>

    {{-- Table card --}}
    <div class="card dt-card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <div>
                <h6 class="mb-0 fw-bold" style="color:#7b1fa2;"><i class="bi bi-geo-alt me-2"></i>Daftar Kota</h6>
                <small class="text-muted">Kelola kota penyelenggara event</small>
            </div>
            <button onclick="openCreateModal()" class="btn btn-sm fw-bold"
                style="background:linear-gradient(135deg,#7b1fa2,#9c27b0);color:#fff;border-radius:10px;border:none;">
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
                            <th>Unit</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($cities as $i => $city)
                            <tr>
                                <td class="text-muted" style="font-size:12px;">{{ $i + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="city-avatar">{{ strtoupper(substr($city->name, 0, 1)) }}</div>
                                        <span class="fw-bold" style="font-size:13.5px;">{{ $city->name }}</span>
                                    </div>
                                </td>
                                <td class="text-muted" style="font-size:13px;">{{ $city->province ?? '-' }}</td>
                                <td>
                                    <span class="fw-bold"
                                        style="font-size:15px;color:#7b1fa2;">{{ $city->units_count }}</span>
                                    <span class="text-muted" style="font-size:11px;"> unit</span>
                                </td>
                                <td class="text-center" style="white-space:nowrap;">
                                    <button class="btn-action" style="background:#f3e5f5;color:#7b1fa2;" title="Edit"
                                        onclick="openEditModal({{ $city->id }})">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn-action" style="background:#ffebee;color:#c62828;" title="Hapus"
                                        onclick="openDeleteModal({{ $city->id }}, '{{ addslashes($city->name) }}', {{ $city->units_count }})">
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

    {{-- ════ MODAL CREATE ════ --}}
    <div class="modal fade" id="modalCreate" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered" style="max-width:460px;">
            <div class="modal-content">
                <div class="modal-header modal-header-purple py-3">
                    <h5 class="modal-title fw-bold"><i class="bi bi-geo-alt me-2"></i>Tambah Kota Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label">Nama Kota <span class="text-danger">*</span></label>
                        <input type="text" id="c_name" class="form-control" placeholder="Contoh: Banda Aceh"
                            autofocus>
                        <div class="invalid-feedback" id="c_name_error"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Provinsi</label>
                        <input type="text" id="c_province" class="form-control" placeholder="Contoh: Aceh">
                        <div class="invalid-feedback" id="c_province_error"></div>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-0 gap-2">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal"
                        style="border-radius:10px;">Batal</button>
                    <button type="button" id="btnCreate" class="btn px-4 fw-bold"
                        style="background:linear-gradient(135deg,#7b1fa2,#9c27b0);color:#fff;border-radius:10px;border:none;"
                        onclick="submitCreate()">
                        <i class="bi bi-check-circle me-1"></i> Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ════ MODAL EDIT ════ --}}
    <div class="modal fade" id="modalEdit" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered" style="max-width:460px;">
            <div class="modal-content">
                <div class="modal-header py-3"
                    style="background:linear-gradient(135deg,#1565c0,#1976d2);color:#fff;border-radius:14px 14px 0 0;">
                    <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i>Edit Kota</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter:invert(1);"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" id="e_id">
                    <div class="mb-3">
                        <label class="form-label">Nama Kota <span class="text-danger">*</span></label>
                        <input type="text" id="e_name" class="form-control" placeholder="Nama kota">
                        <div class="invalid-feedback" id="e_name_error"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Provinsi</label>
                        <input type="text" id="e_province" class="form-control" placeholder="Nama provinsi">
                        <div class="invalid-feedback" id="e_province_error"></div>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-0 gap-2">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal"
                        style="border-radius:10px;">Batal</button>
                    <button type="button" id="btnEdit" class="btn px-4 fw-bold"
                        style="background:linear-gradient(135deg,#1565c0,#1976d2);color:#fff;border-radius:10px;border:none;"
                        onclick="submitEdit()">
                        <i class="bi bi-check-circle me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ════ MODAL DELETE ════ --}}
    <div class="modal fade" id="modalDelete" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
            <div class="modal-content" style="border-radius:16px;">
                <div class="modal-body text-center p-5">
                    <div
                        style="width:68px;height:68px;background:#ffebee;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:30px;">
                        🗺️</div>
                    <h5 class="fw-bold mb-2">Hapus Kota?</h5>
                    <p class="text-muted mb-1" style="font-size:14px;">Kota berikut akan dihapus:</p>
                    <p class="fw-bold" id="deleteCityName" style="color:#7b1fa2;font-size:15px;"></p>
                    <div id="deleteWarning" class="alert alert-warning py-2 px-3 text-start"
                        style="font-size:12px;border-radius:10px;display:none;">
                        ⚠️ Kota ini masih memiliki unit terkait. Hapus atau pindahkan unit terlebih dahulu.
                    </div>
                </div>
                <div class="d-flex gap-2 px-4 pb-4">
                    <button class="btn btn-light flex-grow-1" data-bs-dismiss="modal"
                        style="border-radius:10px;font-weight:600;">Batal</button>
                    <button id="btnDeleteCity" class="btn btn-danger flex-grow-1 fw-bold" style="border-radius:10px;"
                        onclick="submitDelete()">
                        <i class="bi bi-trash me-1"></i> Ya, Hapus
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Data embed --}}
    <script id="citiesData" type="application/json">{!! json_encode($cities->getCollection()->map(fn($c) => [
    'id'         => $c->id,
    'name'       => $c->name,
    'province'   => $c->province,
    'units_count'=> $c->units_count,
    // Gunakan route() untuk URL — tapi kita akan override di JS jika ada mismatch
    'update_url' => route('admin.cities.update-post', $c),
    'delete_url' => route('admin.cities.destroy', $c),
])) !!}</script>

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    <script>
        // ════════════════════════════════════════════════════════════
        //  INIT
        // ════════════════════════════════════════════════════════════
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

        // ── Data map ──────────────────────────────────────────────────
        const CITIES = {};
        JSON.parse(document.getElementById('citiesData').textContent)
            .forEach(c => CITIES[c.id] = c);

        // ────────────────────────────────────────────────────────────
        //  CSRF — ambil dari meta tag (paling reliable di semua kondisi)
        // ────────────────────────────────────────────────────────────
        function getCsrf() {
            return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
        }

        // ════════════════════════════════════════════════════════════
        //  FETCH HELPER — mengatasi semua masalah di server:
        //  1. Memakai URL relatif (tidak ada http/https mismatch)
        //  2. Selalu sertakan CSRF
        //  3. Tangani response bukan JSON (redirect, HTML error page)
        //  4. Timeout 15 detik agar tidak hang selamanya
        // ════════════════════════════════════════════════════════════
        async function apiCall(url, method, body = null) {
            // Konversi URL absolut → relatif agar tidak ada mismatch HTTP/HTTPS
            // Contoh: "https://eventapp.imronmf.web.id/admin/cities/1/update"
            //       → "/admin/cities/1/update"
            const relativeUrl = url.replace(/^https?:\/\/[^\/]+/, '');

            const controller = new AbortController();
            const timeout = setTimeout(() => controller.abort(), 15000); // 15 detik timeout

            try {
                const res = await fetch(relativeUrl, {
                    method: method,
                    signal: controller.signal,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCsrf(),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest', // penting: agar Laravel kenali sebagai AJAX
                    },
                    ...(body ? {
                        body: JSON.stringify(body)
                    } : {}),
                });

                clearTimeout(timeout);

                // Cek apakah response adalah JSON
                const contentType = res.headers.get('content-type') ?? '';
                if (!contentType.includes('application/json')) {
                    // Server mengirim HTML (mungkin halaman login, 500, atau redirect)
                    const text = await res.text();
                    console.error('Non-JSON response:', res.status, text.substring(0, 200));

                    if (res.status === 419) {
                        throw new Error('Sesi expired. Silakan refresh halaman dan coba lagi.');
                    }
                    if (res.status === 403) {
                        throw new Error('Akses ditolak. Anda tidak memiliki izin untuk aksi ini.');
                    }
                    if (res.status === 404) {
                        throw new Error('URL tidak ditemukan (404). Cek konfigurasi route.');
                    }
                    throw new Error(`Server error ${res.status}. Refresh halaman dan coba lagi.`);
                }

                const data = await res.json();

                if (!res.ok) {
                    // Validasi error (422) atau error lain dengan pesan JSON
                    throw {
                        isValidation: res.status === 422,
                        data,
                        status: res.status
                    };
                }

                return data;

            } catch (err) {
                clearTimeout(timeout);

                if (err.name === 'AbortError') {
                    throw new Error('Request timeout. Periksa koneksi internet Anda.');
                }
                if (err.message === 'Failed to fetch') {
                    throw new Error('Gagal terhubung ke server. Periksa koneksi jaringan Anda.');
                }
                throw err;
            }
        }

        // ════════════════════════════════════════════════════════════
        //  TOAST NOTIFICATION
        // ════════════════════════════════════════════════════════════
        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const icon = type === 'success' ? '✅' : '❌';
            const div = document.createElement('div');
            div.className = `toast-item ${type}`;
            div.innerHTML =
                `<span style="font-size:18px;">${icon}</span><span style="flex:1;">${message}</span>
        <button onclick="this.parentElement.remove()" style="background:none;border:none;font-size:16px;color:#999;cursor:pointer;">✕</button>`;
            container.appendChild(div);
            setTimeout(() => div.remove(), 5000);
        }

        // ════════════════════════════════════════════════════════════
        //  HELPERS UI
        // ════════════════════════════════════════════════════════════
        function clearErrors(prefix) {
            ['name', 'province'].forEach(f => {
                const el = document.getElementById(`${prefix}_${f}`);
                const err = document.getElementById(`${prefix}_${f}_error`);
                if (el) {
                    el.classList.remove('is-invalid');
                }
                if (err) {
                    err.textContent = '';
                }
            });
        }

        function showErrors(prefix, errors) {
            Object.entries(errors).forEach(([field, messages]) => {
                const el = document.getElementById(`${prefix}_${field}`);
                const err = document.getElementById(`${prefix}_${field}_error`);
                if (el) el.classList.add('is-invalid');
                if (err) err.textContent = Array.isArray(messages) ? messages[0] : messages;
            });
        }

        function setBtnLoading(btnId, loading, defaultText) {
            const btn = document.getElementById(btnId);
            if (!btn) return;
            btn.disabled = loading;
            btn.innerHTML = loading ?
                '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...' :
                defaultText;
        }

        // ════════════════════════════════════════════════════════════
        //  CREATE
        // ════════════════════════════════════════════════════════════
        function openCreateModal() {
            clearErrors('c');
            document.getElementById('c_name').value = '';
            document.getElementById('c_province').value = '';
            new bootstrap.Modal(document.getElementById('modalCreate')).show();
            setTimeout(() => document.getElementById('c_name').focus(), 400);
        }

        // Enter key di form create
        document.getElementById('c_name')?.addEventListener('keydown', e => {
            if (e.key === 'Enter') submitCreate();
        });
        document.getElementById('c_province')?.addEventListener('keydown', e => {
            if (e.key === 'Enter') submitCreate();
        });

        async function submitCreate() {
            clearErrors('c');
            const name = document.getElementById('c_name').value.trim();
            const province = document.getElementById('c_province').value.trim();

            if (!name) {
                document.getElementById('c_name').classList.add('is-invalid');
                document.getElementById('c_name_error').textContent = 'Nama kota wajib diisi.';
                return;
            }

            const defaultText = '<i class="bi bi-check-circle me-1"></i> Simpan';
            setBtnLoading('btnCreate', true, defaultText);

            try {
                // Gunakan URL relatif langsung — tidak ada mismatch
                const data = await apiCall('/admin/cities', 'POST', {
                    name,
                    province
                });

                bootstrap.Modal.getInstance(document.getElementById('modalCreate'))?.hide();
                showToast(data.message ?? `Kota "${name}" berhasil ditambahkan.`);

                // Reload setelah modal tutup
                setTimeout(() => location.reload(), 600);

            } catch (err) {
                if (err.isValidation) {
                    showErrors('c', err.data.errors ?? {});
                } else {
                    showToast(err.message ?? 'Terjadi kesalahan.', 'danger');
                }
            } finally {
                setBtnLoading('btnCreate', false, defaultText);
            }
        }

        // ════════════════════════════════════════════════════════════
        //  EDIT
        // ════════════════════════════════════════════════════════════
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

        document.getElementById('e_name')?.addEventListener('keydown', e => {
            if (e.key === 'Enter') submitEdit();
        });
        document.getElementById('e_province')?.addEventListener('keydown', e => {
            if (e.key === 'Enter') submitEdit();
        });

        async function submitEdit() {
            clearErrors('e');
            const id = document.getElementById('e_id').value;
            const name = document.getElementById('e_name').value.trim();
            const province = document.getElementById('e_province').value.trim();

            if (!name) {
                document.getElementById('e_name').classList.add('is-invalid');
                document.getElementById('e_name_error').textContent = 'Nama kota wajib diisi.';
                return;
            }

            const defaultText = '<i class="bi bi-check-circle me-1"></i> Simpan Perubahan';
            setBtnLoading('btnEdit', true, defaultText);

            try {
                // Pakai alias route POST khusus — lebih reliable di berbagai konfigurasi server
                // daripada PUT yang kadang diblok Nginx
                const data = await apiCall(`/admin/cities/${id}/update`, 'POST', {
                    name,
                    province
                });

                bootstrap.Modal.getInstance(document.getElementById('modalEdit'))?.hide();
                showToast(data.message ?? 'Kota berhasil diperbarui.');
                setTimeout(() => location.reload(), 600);

            } catch (err) {
                if (err.isValidation) {
                    showErrors('e', err.data.errors ?? {});
                } else {
                    showToast(err.message ?? 'Terjadi kesalahan.', 'danger');
                }
            } finally {
                setBtnLoading('btnEdit', false, defaultText);
            }
        }

        // ════════════════════════════════════════════════════════════
        //  DELETE
        // ════════════════════════════════════════════════════════════
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
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menghapus...';

            try {
                // Gunakan URL relatif — fix utama untuk masalah di server
                const data = await apiCall(`/admin/cities/${_deleteId}`, 'DELETE');

                bootstrap.Modal.getInstance(document.getElementById('modalDelete'))?.hide();
                showToast(data.message ?? 'Kota berhasil dihapus.');
                setTimeout(() => location.reload(), 600);

            } catch (err) {
                if (err.isValidation) {
                    showToast(err.data.message ?? 'Tidak bisa menghapus.', 'danger');
                } else {
                    showToast(err.message ?? 'Terjadi kesalahan saat menghapus.', 'danger');
                }
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-trash me-1"></i> Ya, Hapus';
            }
        }
    </script>
@endpush
