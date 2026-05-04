@extends('layouts.admin')
@section('title', 'Manajemen Admin')
@section('page-title', 'Manajemen Admin')

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
            white-space: nowrap;
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
            border-color: #00838f;
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 131, 143, .1);
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
            background: linear-gradient(135deg, #00838f, #00acc1) !important;
            border-color: transparent !important;
            color: #fff !important;
            border-radius: 8px;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #e0f7fa !important;
            border-color: transparent !important;
            color: #00838f !important;
            border-radius: 8px;
        }

        .role-superadmin {
            background: linear-gradient(135deg, #f4b846, #e64a19);
            color: #fff;
            font-size: 11px;
            font-weight: 800;
            padding: 4px 12px;
            border-radius: 99px;
            letter-spacing: .3px;
        }

        .role-admin {
            background: #e3f2fd;
            color: #1565c0;
            font-size: 11px;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 99px;
        }

        .status-aktif {
            background: #e8f5e9;
            color: #2e7d32;
            font-size: 11px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 99px;
        }

        .status-nonaktif {
            background: #ffebee;
            color: #c62828;
            font-size: 11px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 99px;
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

        .btn-action:disabled {
            opacity: .4;
            cursor: not-allowed;
            transform: none;
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
            border-color: #00838f;
            box-shadow: 0 0 0 3px rgba(0, 131, 143, .12);
        }

        .form-switch .form-check-input {
            width: 42px;
            height: 22px;
        }

        .form-switch .form-check-input:checked {
            background-color: #00838f;
            border-color: #00838f;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 16px;
            color: #fff;
            flex-shrink: 0;
        }

        .avatar-superadmin {
            background: linear-gradient(135deg, #f4b846, #e64a19);
        }

        .avatar-admin {
            background: linear-gradient(135deg, #00838f, #00acc1);
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

        .pwd-strength {
            height: 4px;
            border-radius: 99px;
            margin-top: 6px;
            transition: all .3s;
        }

        .pwd-wrap {
            position: relative;
        }

        .pwd-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #888;
            cursor: pointer;
            font-size: 16px;
        }

        .info-box {
            background: #e0f7fa;
            border: 1.5px solid #b2ebf2;
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 12.5px;
            color: #00695c;
        }
    </style>
@endpush

@section('topbar-actions')
    <button class="btn btn-sm" onclick="openCreateModal()"
        style="background:linear-gradient(135deg,#00838f,#00acc1);color:#fff;border-radius:10px;font-weight:700;padding:7px 16px;border:none;">
        <i class="bi bi-person-plus me-1"></i> Tambah Admin
    </button>
@endsection

@section('content')

    <div class="d-flex flex-wrap gap-2 mb-4">
        <div class="stat-pill"><span
                style="width:8px;height:8px;border-radius:50%;background:#f4b846;display:inline-block;"></span> Superadmin:
            <strong>{{ $users->getCollection()->where('role', 'superadmin')->count() }}</strong></div>
        <div class="stat-pill"><span
                style="width:8px;height:8px;border-radius:50%;background:#00838f;display:inline-block;"></span> Admin Unit:
            <strong>{{ $users->getCollection()->where('role', 'admin')->count() }}</strong></div>
        <div class="stat-pill"><span
                style="width:8px;height:8px;border-radius:50%;background:#2e7d32;display:inline-block;"></span> Aktif:
            <strong>{{ $users->getCollection()->where('is_active', true)->count() }}</strong></div>
        <div class="stat-pill"><span
                style="width:8px;height:8px;border-radius:50%;background:#c62828;display:inline-block;"></span> Nonaktif:
            <strong>{{ $users->getCollection()->where('is_active', false)->count() }}</strong></div>
    </div>

    <div class="card dt-card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <div>
                <h6 class="mb-0 fw-bold" style="color:#00838f;"><i class="bi bi-people me-2"></i>Daftar Admin</h6>
                <small class="text-muted">Kelola akun admin dan superadmin</small>
            </div>
            <button class="btn btn-sm" onclick="openCreateModal()"
                style="background:linear-gradient(135deg,#00838f,#00acc1);color:#fff;border-radius:10px;font-weight:700;border:none;">
                <i class="bi bi-person-plus me-1"></i> Admin Baru
            </button>
        </div>
        <div class="card-body p-3">
            <div class="table-responsive">
                <table id="usersTable" class="table table-hover w-100">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama / Email</th>
                            <th>Role</th>
                            <th>Unit</th>
                            <th>Dibuat</th>
                            <th>Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $i => $user)
                            <tr>
                                <td class="text-muted" style="font-size:12px;">{{ $i + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div
                                            class="user-avatar {{ $user->role === 'superadmin' ? 'avatar-superadmin' : 'avatar-admin' }}">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold" style="font-size:13.5px;">
                                                {{ $user->name }}
                                                @if ($user->id === auth()->id())
                                                    <span
                                                        style="font-size:10px;background:#e8f5e9;color:#2e7d32;padding:2px 8px;border-radius:99px;font-weight:700;margin-left:4px;">Anda</span>
                                                @endif
                                            </div>
                                            <div class="text-muted" style="font-size:12px;">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if ($user->role === 'superadmin')
                                        <span class="role-superadmin">👑 Superadmin</span>
                                    @else
                                        <span class="role-admin">🔑 Admin</span>
                                    @endif
                                </td>
                                <td style="font-size:13px;">
                                    @if ($user->unit)
                                        <span style="font-weight:600;">{{ $user->unit->name }}</span>
                                    @else
                                        <span class="text-muted" style="font-size:12px;">— Semua Unit</span>
                                    @endif
                                </td>
                                <td style="font-size:12px;color:#888;">{{ $user->created_at->format('d/m/Y') }}</td>
                                <td>
                                    @if ($user->is_active)
                                        <span class="status-aktif"><i class="bi bi-circle-fill me-1"
                                                style="font-size:7px;"></i>Aktif</span>
                                    @else
                                        <span class="status-nonaktif">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="text-center" style="white-space:nowrap;">
                                    <button class="btn-action" style="background:#e0f7fa;color:#00838f;" title="Edit"
                                        onclick="openEditModal({{ $user->id }})"><i class="bi bi-pencil"></i></button>

                                    <button class="btn-action" style="background:#ffebee;color:#c62828;" title="Hapus"
                                        onclick="openDeleteModal({{ $user->id }}, '{{ addslashes($user->name) }}')"
                                        @if ($user->id === auth()->id()) disabled title="Tidak bisa hapus akun sendiri" @endif>
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
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header py-3"
                    style="background:linear-gradient(135deg,#00838f,#00acc1);color:#fff;border-radius:14px 14px 0 0;">
                    <h5 class="modal-title fw-bold"><i class="bi bi-person-plus me-2"></i>Tambah Admin Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter:invert(1);"></button>
                </div>
                <form action="{{ route('admin.users.store') }}" method="POST" id="formCreate">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="info-box mb-3">
                            <i class="bi bi-info-circle me-1"></i>
                            <strong>Superadmin</strong> dapat mengelola semua unit dan event. <strong>Admin</strong> hanya
                            bisa mengelola event di unit yang ditugaskan.
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" placeholder="Nama lengkap admin"
                                    required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" placeholder="email@domain.com"
                                    required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Password <span class="text-danger">*</span></label>
                                <div class="pwd-wrap">
                                    <input type="password" name="password" id="c_password" class="form-control"
                                        placeholder="Min. 8 karakter" required minlength="8"
                                        oninput="checkPwdStrength('c')">
                                    <button type="button" class="pwd-toggle"
                                        onclick="togglePwd('c_password','c_pwd_eye')"><i class="bi bi-eye"
                                            id="c_pwd_eye"></i></button>
                                </div>
                                <div class="pwd-strength" id="c_pwd_strength" style="background:#e0e0e0;"></div>
                                <div class="form-text" id="c_pwd_hint">Minimal 8 karakter</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                                <div class="pwd-wrap">
                                    <input type="password" name="password_confirmation" id="c_password_conf"
                                        class="form-control" placeholder="Ulangi password" required>
                                    <button type="button" class="pwd-toggle"
                                        onclick="togglePwd('c_password_conf','c_pwd_eye2')"><i class="bi bi-eye"
                                            id="c_pwd_eye2"></i></button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Role <span class="text-danger">*</span></label>
                                <select name="role" id="c_role" class="form-select" required
                                    onchange="toggleUnitField('c')">
                                    <option value="admin">🔑 Admin Unit</option>
                                    <option value="superadmin">👑 Superadmin</option>
                                </select>
                            </div>
                            <div class="col-md-6" id="c_unit_wrap">
                                <label class="form-label">Unit yang Dikelola <span class="text-danger">*</span></label>
                                <select name="unit_id" id="c_unit_id" class="form-select" required>
                                    <option value="">-- Pilih Unit --</option>
                                    @foreach (\App\Models\Unit::where('is_active', true)->orderBy('name')->get() as $unit)
                                        <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="c_is_active"
                                        value="1" checked style="width:42px;height:22px;">
                                    <label class="form-check-label fw-semibold" for="c_is_active"
                                        style="font-size:13px;">Akun Aktif</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0 px-4 pb-4 gap-2">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal"
                            style="border-radius:10px;">Batal</button>
                        <button type="submit" class="btn px-4 fw-bold"
                            style="background:linear-gradient(135deg,#00838f,#00acc1);color:#fff;border-radius:10px;border:none;">
                            <i class="bi bi-person-check me-1"></i> Buat Akun
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
                    style="background:linear-gradient(135deg,#e65100,#f57c00);color:#fff;border-radius:14px 14px 0 0;">
                    <h5 class="modal-title fw-bold"><i class="bi bi-person-gear me-2"></i>Edit Admin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter:invert(1);"></button>
                </div>
                <form id="formEdit" method="POST">
                    @csrf @method('PUT')
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="e_name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" id="e_email" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Password Baru <span class="text-muted fw-normal">(kosongkan jika
                                        tidak diganti)</span></label>
                                <div class="pwd-wrap">
                                    <input type="password" name="password" id="e_password" class="form-control"
                                        placeholder="Isi jika ingin ganti password" minlength="8"
                                        oninput="checkPwdStrength('e')">
                                    <button type="button" class="pwd-toggle"
                                        onclick="togglePwd('e_password','e_pwd_eye')"><i class="bi bi-eye"
                                            id="e_pwd_eye"></i></button>
                                </div>
                                <div class="pwd-strength" id="e_pwd_strength" style="background:#e0e0e0;"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Konfirmasi Password Baru</label>
                                <div class="pwd-wrap">
                                    <input type="password" name="password_confirmation" id="e_password_conf"
                                        class="form-control" placeholder="Ulangi password baru">
                                    <button type="button" class="pwd-toggle"
                                        onclick="togglePwd('e_password_conf','e_pwd_eye2')"><i class="bi bi-eye"
                                            id="e_pwd_eye2"></i></button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Role <span class="text-danger">*</span></label>
                                <select name="role" id="e_role" class="form-select" required
                                    onchange="toggleUnitField('e')">
                                    <option value="admin">🔑 Admin Unit</option>
                                    <option value="superadmin">👑 Superadmin</option>
                                </select>
                            </div>
                            <div class="col-md-6" id="e_unit_wrap">
                                <label class="form-label">Unit yang Dikelola</label>
                                <select name="unit_id" id="e_unit_id" class="form-select">
                                    <option value="">-- Pilih Unit --</option>
                                    @foreach (\App\Models\Unit::where('is_active', true)->orderBy('name')->get() as $unit)
                                        <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="e_is_active"
                                        value="1" style="width:42px;height:22px;">
                                    <label class="form-check-label fw-semibold" for="e_is_active"
                                        style="font-size:13px;">Akun Aktif</label>
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

    {{-- ════ MODAL DELETE ════ --}}
    <div class="modal fade" id="modalDelete" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
            <div class="modal-content" style="border-radius:16px;border:none;">
                <div class="modal-body text-center p-5">
                    <div
                        style="width:68px;height:68px;background:#ffebee;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:30px;">
                        👤</div>
                    <h5 class="fw-bold mb-2">Hapus Admin?</h5>
                    <p class="text-muted mb-1" style="font-size:14px;">Akun berikut akan dihapus permanen:</p>
                    <p class="fw-bold" id="deleteUserName" style="color:#00838f;font-size:15px;"></p>
                    <p class="text-muted" style="font-size:12px;">Admin yang dihapus tidak bisa login lagi. Event yang
                        dibuatnya tetap ada.</p>
                </div>
                <div class="d-flex gap-2 px-4 pb-4">
                    <button class="btn btn-light flex-grow-1" data-bs-dismiss="modal"
                        style="border-radius:10px;font-weight:600;">Batal</button>
                    <form id="formDelete" method="POST" class="flex-grow-1">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100 fw-bold" style="border-radius:10px;">
                            <i class="bi bi-person-x me-1"></i> Ya, Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script id="usersData" type="application/json">{!! json_encode($users->getCollection()->map(fn($u) => [
    'id'        => $u->id,
    'name'      => $u->name,
    'email'     => $u->email,
    'role'      => $u->role,
    'unit_id'   => $u->unit_id,
    'is_active' => $u->is_active,
    'update_url'=> route('admin.users.update', $u),
    'delete_url'=> route('admin.users.destroy', $u),
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
            $('#usersTable').DataTable({
                responsive: true,
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_–_END_ dari _TOTAL_ admin",
                    infoEmpty: "Tidak ada data",
                    emptyTable: "Belum ada admin.",
                    paginate: {
                        previous: "‹",
                        next: "›"
                    },
                },
                columnDefs: [{
                    orderable: false,
                    targets: [6]
                }],
                order: [
                    [2, 'asc'],
                    [1, 'asc']
                ],
                pageLength: 10,
            });
        });

        const USERS = {};
        JSON.parse(document.getElementById('usersData').textContent).forEach(u => USERS[u.id] = u);
        const ME = {{ auth()->id() }};

        // ── Toggle unit field berdasar role ─────────────────────────
        function toggleUnitField(prefix) {
            const role = document.getElementById(prefix + '_role').value;
            const wrap = document.getElementById(prefix + '_unit_wrap');
            const sel = document.getElementById(prefix + '_unit_id');
            if (role === 'superadmin') {
                wrap.style.display = 'none';
                if (sel) sel.required = false;
            } else {
                wrap.style.display = 'block';
                if (sel) sel.required = true;
            }
        }

        // ── Password visibility toggle ───────────────────────────────
        function togglePwd(inputId, iconId) {
            const inp = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (inp.type === 'password') {
                inp.type = 'text';
                icon.className = 'bi bi-eye-slash';
            } else {
                inp.type = 'password';
                icon.className = 'bi bi-eye';
            }
        }

        // ── Password strength indicator ──────────────────────────────
        function checkPwdStrength(prefix) {
            const val = document.getElementById(prefix + '_password').value;
            const bar = document.getElementById(prefix + '_pwd_strength');
            const hint = document.getElementById(prefix + '_pwd_hint');
            let strength = 0;
            if (val.length >= 8) strength++;
            if (/[A-Z]/.test(val)) strength++;
            if (/[0-9]/.test(val)) strength++;
            if (/[^A-Za-z0-9]/.test(val)) strength++;

            const colors = ['#e0e0e0', '#f44336', '#ff9800', '#ffc107', '#4caf50'];
            const labels = ['', 'Sangat Lemah', 'Lemah', 'Sedang', 'Kuat'];
            const widths = ['0%', '25%', '50%', '75%', '100%'];

            bar.style.background = colors[strength];
            bar.style.width = widths[strength];
            if (hint) hint.textContent = val.length === 0 ? 'Minimal 8 karakter' : labels[strength];
        }

        // ── Modal: Create ────────────────────────────────────────────
        function openCreateModal() {
            document.getElementById('formCreate').reset();
            ['c_pwd_strength'].forEach(id => {
                const el = document.getElementById(id);
                if (el) {
                    el.style.background = '#e0e0e0';
                    el.style.width = '0%';
                }
            });
            toggleUnitField('c');
            new bootstrap.Modal(document.getElementById('modalCreate')).show();
        }

        // ── Modal: Edit ──────────────────────────────────────────────
        function openEditModal(id) {
            const u = USERS[id];
            if (!u) return;
            document.getElementById('formEdit').action = u.update_url;
            document.getElementById('e_name').value = u.name;
            document.getElementById('e_email').value = u.email;
            document.getElementById('e_password').value = '';
            document.getElementById('e_password_conf').value = '';
            document.getElementById('e_role').value = u.role;
            document.getElementById('e_unit_id').value = u.unit_id ?? '';
            document.getElementById('e_is_active').checked = u.is_active;
            document.getElementById('e_pwd_strength').style.background = '#e0e0e0';
            document.getElementById('e_pwd_strength').style.width = '0%';
            toggleUnitField('e');
            new bootstrap.Modal(document.getElementById('modalEdit')).show();
        }

        // ── Modal: Delete ────────────────────────────────────────────
        function openDeleteModal(id, name) {
            if (id === ME) return;
            const u = USERS[id];
            document.getElementById('deleteUserName').textContent = name;
            document.getElementById('formDelete').action = u.delete_url;
            new bootstrap.Modal(document.getElementById('modalDelete')).show();
        }
    </script>
@endpush
