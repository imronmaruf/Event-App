@extends('layouts.admin')
@section('title', $event->name)
@section('page-title', 'Detail Event')

@push('styles')
    <style>
        .info-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 16px rgba(0, 0, 0, .07);
            border: none;
            overflow: hidden;
        }

        .info-card .card-header {
            padding: 16px 20px;
            border-bottom: 1px solid #f5f5f5;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .info-card .card-body {
            padding: 20px;
        }

        .stat-big {
            border-radius: 16px;
            padding: 22px 20px;
            color: #fff;
            position: relative;
            overflow: hidden;
        }

        .stat-big::after {
            content: '';
            position: absolute;
            bottom: -20px;
            right: -20px;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .1);
        }

        .stat-big .n {
            font-size: 38px;
            font-weight: 900;
            line-height: 1;
        }

        .stat-big .l {
            font-size: 12px;
            font-weight: 600;
            opacity: .85;
            margin-top: 4px;
            text-transform: uppercase;
            letter-spacing: .6px;
        }

        .badge-aktif {
            background: #e8f5e9;
            color: #2e7d32;
            font-weight: 700;
            padding: 5px 14px;
            border-radius: 99px;
            font-size: 12px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .badge-nonaktif {
            background: #fff3e0;
            color: #e65100;
            font-weight: 700;
            padding: 5px 14px;
            border-radius: 99px;
            font-size: 12px;
        }

        .prog-bar {
            background: #f0f0f0;
            border-radius: 99px;
            height: 12px;
            overflow: hidden;
        }

        .prog-fill {
            height: 100%;
            border-radius: 99px;
            background: linear-gradient(90deg, #c62828, #e64a19, #f4b846);
            transition: width 1s ease;
        }

        .info-row {
            display: flex;
            padding: 10px 0;
            border-bottom: 1px solid #f5f5f5;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-row .lbl {
            font-size: 12px;
            color: #888;
            font-weight: 600;
            width: 140px;
            flex-shrink: 0;
            padding-top: 1px;
        }

        .info-row .val {
            font-size: 13.5px;
            color: #1a1a1a;
            font-weight: 500;
        }

        .qr-box {
            border: 2px dashed #e0e0e0;
            border-radius: 16px;
            padding: 20px;
            text-align: center;
            background: #fafafa;
        }

        .qr-box img {
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, .1);
        }

        .link-box {
            background: #f0f9ff;
            border: 1.5px solid #b3e5fc;
            border-radius: 12px;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .link-box code {
            flex: 1;
            font-size: 12px;
            color: #0277bd;
            word-break: break-all;
            background: none;
            padding: 0;
        }

        .quick-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 16px 12px;
            background: #fff;
            border: 1.5px solid #f0f0f0;
            border-radius: 14px;
            text-decoration: none;
            color: #444;
            font-size: 12px;
            font-weight: 700;
            transition: all .2s;
            text-align: center;
        }

        .quick-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, .1);
            color: #c62828;
            border-color: #c62828;
        }

        .quick-btn .icon {
            font-size: 26px;
        }

        .digit-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #e3f2fd;
            color: #1565c0;
            font-weight: 700;
            padding: 6px 14px;
            border-radius: 99px;
            font-size: 13px;
        }

        .modal-content {
            border-radius: 16px;
            border: none;
        }

        .form-control,
        .form-select {
            border-radius: 10px;
            border: 1.5px solid #e0e0e0;
            font-size: 13.5px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #c62828;
            box-shadow: 0 0 0 3px rgba(198, 40, 40, .12);
        }

        .form-label {
            font-size: 13px;
            font-weight: 600;
            color: #444;
        }
    </style>
@endpush

@section('topbar-actions')
    <a href="{{ route('admin.events.index') }}" class="btn btn-sm btn-light" style="border-radius:10px;">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
    <a href="{{ route('admin.participants.index', $event) }}" class="btn btn-sm btn-primary"
        style="border-radius:10px;background:#1565c0;border:none;">
        <i class="bi bi-people me-1"></i> Data Peserta
    </a>
    <button onclick="document.getElementById('modalEdit').querySelector('form').submit()" class="btn btn-sm"
        style="border-radius:10px;background:linear-gradient(135deg,#c62828,#e64a19);color:#fff;border:none;"
        onclick="openEditModal()">
        <i class="bi bi-pencil me-1"></i> Edit
    </button>
@endsection

@section('content')
    <div class="row g-4">

        {{-- ══ KOLOM KIRI ══ --}}
        <div class="col-12 col-xl-8">

            {{-- Header Event --}}
            <div class="info-card mb-4">
                <div class="card-body" style="padding:24px;">
                    <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                @if ($event->is_active)
                                    <span class="badge-aktif"><span
                                            style="width:7px;height:7px;border-radius:50%;background:#2e7d32;display:inline-block;"></span>
                                        Absensi Aktif</span>
                                @else
                                    <span class="badge-nonaktif">Absensi Nonaktif</span>
                                @endif
                                @if ($event->is_archived)
                                    <span
                                        style="background:#eceff1;color:#546e7a;font-weight:700;padding:5px 14px;border-radius:99px;font-size:12px;">Diarsipkan</span>
                                @endif
                            </div>
                            <h4 class="fw-bold mb-1" style="color:#1a1a1a;">{{ $event->name }}</h4>
                            <div class="text-muted" style="font-size:13px;">
                                <i class="bi bi-building me-1"></i>{{ $event->unit->name }}
                                @if ($event->city)
                                    <span class="mx-1">·</span><i class="bi bi-geo-alt me-1"></i>{{ $event->city->name }}
                                @endif
                                @if ($event->venue)
                                    <span class="mx-1">·</span><i class="bi bi-map me-1"></i>{{ $event->venue }}
                                @endif
                            </div>
                            @if ($event->event_date)
                                <div class="mt-1 text-muted" style="font-size:13px;">
                                    <i class="bi bi-calendar3 me-1"></i>{{ $event->event_date->translatedFormat('d F Y') }}
                                    @if ($event->event_time)
                                        · <i class="bi bi-clock me-1"></i>{{ $event->event_time }}
                                    @endif
                                </div>
                            @endif
                            @if ($event->description)
                                <p class="mt-2 mb-0" style="font-size:13px;color:#666;">{{ $event->description }}</p>
                            @endif
                        </div>
                        {{-- Toggle button --}}
                        <form action="{{ route('admin.events.toggle', $event) }}" method="POST">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn fw-bold px-4"
                                style="border-radius:12px;border:2px solid {{ $event->is_active ? '#c62828' : '#2e7d32' }};color:{{ $event->is_active ? '#c62828' : '#2e7d32' }};background:{{ $event->is_active ? '#ffebee' : '#e8f5e9' }};">
                                <i class="bi bi-{{ $event->is_active ? 'pause-circle' : 'play-circle' }} me-1"></i>
                                {{ $event->is_active ? 'Nonaktifkan' : 'Aktifkan' }} Absensi
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Statistik Besar --}}
            <div class="row g-3 mb-4">
                <div class="col-4">
                    <div class="stat-big" style="background:linear-gradient(135deg,#1565c0,#1976d2);">
                        <div class="n">{{ $stats['total'] }}</div>
                        <div class="l">Total Peserta</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="stat-big" style="background:linear-gradient(135deg,#2e7d32,#388e3c);">
                        <div class="n">{{ $stats['hadir'] }}</div>
                        <div class="l">Sudah Hadir</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="stat-big" style="background:linear-gradient(135deg,#c62828,#e64a19);">
                        <div class="n">{{ $stats['belum'] }}</div>
                        <div class="l">Belum Hadir</div>
                    </div>
                </div>
            </div>

            {{-- Progress Bar --}}
            <div class="info-card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-bold" style="font-size:14px;">Progress Kehadiran</span>
                        <span class="fw-bold" style="font-size:22px;color:#c62828;">{{ $stats['persen'] }}%</span>
                    </div>
                    <div class="prog-bar">
                        <div class="prog-fill" style="width:{{ $stats['persen'] }}%"></div>
                    </div>
                    <div class="d-flex justify-content-between mt-2">
                        <span style="font-size:11px;color:#888;">{{ $stats['hadir'] }} hadir dari {{ $stats['total'] }}
                            peserta</span>
                        <span style="font-size:11px;color:#e65100;">{{ $stats['belum'] }} belum hadir</span>
                    </div>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="info-card mb-4">
                <div class="card-header">
                    <span class="fw-bold" style="font-size:14px;color:#444;"><i class="bi bi-lightning me-1"
                            style="color:#f4b846;"></i>Menu Cepat</span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6 col-md-3">
                            <a href="{{ route('admin.participants.index', $event) }}" class="quick-btn">
                                <span class="icon">👥</span> Data Peserta
                                <small class="text-muted fw-normal">{{ $stats['total'] }} peserta</small>
                            </a>
                        </div>
                        <div class="col-6 col-md-3">
                            <a href="{{ route('admin.participants.import', $event) }}" class="quick-btn">
                                <span class="icon">📥</span> Import Excel
                                <small class="text-muted fw-normal">Upload peserta</small>
                            </a>
                        </div>
                        <div class="col-6 col-md-3">
                            <a href="{{ route('admin.attendances.index', $event) }}" class="quick-btn">
                                <span class="icon">✅</span> Data Absensi
                                <small class="text-muted fw-normal">{{ $stats['hadir'] }} hadir</small>
                            </a>
                        </div>
                        <div class="col-6 col-md-3">
                            <a href="{{ route('admin.attendances.by-room', $event) }}" class="quick-btn">
                                <span class="icon">🚪</span> Per Ruang
                                <small class="text-muted fw-normal">Rekap ruang</small>
                            </a>
                        </div>
                        <div class="col-6 col-md-3">
                            <a href="{{ $event->publicAttendanceUrl() }}" target="_blank" class="quick-btn">
                                <span class="icon">📱</span> Buka Absensi
                                <small class="text-muted fw-normal">Halaman publik</small>
                            </a>
                        </div>
                        <div class="col-6 col-md-3">
                            <a href="{{ route('admin.attendances.export', $event) }}" class="quick-btn">
                                <span class="icon">📊</span> Export Excel
                                <small class="text-muted fw-normal">Download data</small>
                            </a>
                        </div>
                        <div class="col-6 col-md-3">
                            <form action="{{ route('admin.events.regenerate-codes', $event) }}" method="POST">
                                @csrf
                                <button type="submit" class="quick-btn w-100"
                                    onclick="return confirm('Regenerate kode absensi semua peserta?')"
                                    style="cursor:pointer;border:1.5px solid #f0f0f0;">
                                    <span class="icon">🔑</span> Regenerate Kode
                                    <small class="text-muted fw-normal">Kode absensi</small>
                                </button>
                            </form>
                        </div>
                        <div class="col-6 col-md-3">
                            <button class="quick-btn w-100" onclick="openResetModal()"
                                style="cursor:pointer;border:1.5px solid #ffcdd2;color:#c62828;">
                                <span class="icon">🗑️</span> Reset Absensi
                                <small style="color:#c62828;font-weight:400;">Hapus semua data</small>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Konfigurasi Digit --}}
            <div class="info-card">
                <div class="card-header">
                    <span class="fw-bold" style="font-size:14px;color:#1565c0;"><i class="bi bi-key me-1"></i>Konfigurasi
                        Kode Absensi</span>
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse"
                        data-bs-target="#digitConfig" style="border-radius:8px;font-size:12px;">
                        <i class="bi bi-gear me-1"></i>Ubah Pengaturan
                    </button>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-3 align-items-center mb-3">
                        @if ($event->digit_count)
                            <div class="digit-badge"><i class="bi bi-123 me-1"></i>{{ $event->digit_count }} digit
                                {{ $event->digit_mode === 'auto' ? '(otomatis)' : '(manual)' }}</div>
                            <div class="digit-badge" style="background:#f3e5f5;color:#7b1fa2;"><i
                                    class="bi bi-arrow-{{ $event->digit_position === 'suffix' ? 'right' : 'left' }}-circle me-1"></i>Dari
                                {{ $event->digit_position === 'suffix' ? 'akhir (suffix)' : 'awal (prefix)' }}</div>
                        @else
                            <span class="text-muted" style="font-size:13px;"><i
                                    class="bi bi-exclamation-circle me-1 text-warning"></i>Kode belum digenerate. Import
                                peserta dulu lalu klik Regenerate Kode.</span>
                        @endif
                    </div>

                    <div class="collapse" id="digitConfig">
                        <hr>
                        <form action="{{ route('admin.events.digit-settings', $event) }}" method="POST">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Mode</label>
                                    <select name="digit_mode" id="ds_mode" class="form-select"
                                        onchange="toggleDigitCount()">
                                        <option value="auto" {{ $event->digit_mode === 'auto' ? 'selected' : '' }}>🤖
                                            Otomatis</option>
                                        <option value="manual" {{ $event->digit_mode === 'manual' ? 'selected' : '' }}>✏️
                                            Manual</option>
                                    </select>
                                </div>
                                <div class="col-md-3" id="ds_count_wrap"
                                    style="{{ $event->digit_mode === 'manual' ? '' : 'display:none;' }}">
                                    <label class="form-label">Jumlah Digit</label>
                                    <input type="number" name="digit_count" class="form-control"
                                        value="{{ $event->digit_count }}" min="1" max="20">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Posisi Ambil</label>
                                    <select name="digit_position" class="form-select">
                                        <option value="suffix"
                                            {{ $event->digit_position === 'suffix' ? 'selected' : '' }}>📍 Akhir (Suffix)
                                        </option>
                                        <option value="prefix"
                                            {{ $event->digit_position === 'prefix' ? 'selected' : '' }}>📍 Awal (Prefix)
                                        </option>
                                    </select>
                                </div>
                                <div class="col-12 d-flex align-items-center gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="auto_regenerate"
                                            id="auto_regen" value="1" checked>
                                        <label class="form-check-label" for="auto_regen"
                                            style="font-size:13px;">Regenerate kode otomatis setelah disimpan</label>
                                    </div>
                                    <button type="submit" class="btn btn-primary fw-bold ms-auto"
                                        style="border-radius:10px;background:#1565c0;border:none;">
                                        <i class="bi bi-save me-1"></i> Simpan & Terapkan
                                    </button>
                                </div>
                            </div>
                        </form>
                        {{-- Tombol detect otomatis --}}
                        <div class="mt-3 p-3 rounded-3" style="background:#f0f4f8;">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="fw-bold" style="font-size:13px;">🤖 Auto-Detect Digit Minimum</div>
                                    <div class="text-muted" style="font-size:12px;">Sistem akan menghitung digit terkecil
                                        yang membuat semua kode unik</div>
                                </div>
                                <button class="btn btn-sm fw-bold" onclick="detectDigits()"
                                    style="background:#e3f2fd;color:#1565c0;border:none;border-radius:10px;white-space:nowrap;">
                                    <i class="bi bi-cpu me-1"></i> Deteksi Sekarang
                                </button>
                            </div>
                            <div id="detectResult" class="mt-2" style="display:none;font-size:13px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ══ KOLOM KANAN ══ --}}
        <div class="col-12 col-xl-4">

            {{-- QR Code --}}
            <div class="info-card mb-4">
                <div class="card-header">
                    <span class="fw-bold" style="font-size:14px;"><i class="bi bi-qr-code me-1"
                            style="color:#c62828;"></i>QR Code Absensi</span>
                </div>
                <div class="card-body">
                    <div class="qr-box mb-3">
                        <img src="{{ $qrUrl }}" alt="QR Absensi {{ $event->name }}" width="200"
                            height="200" loading="lazy">
                        <div class="mt-2 text-muted" style="font-size:11px;">Scan untuk membuka halaman absensi</div>
                    </div>
                    <div class="link-box mb-3">
                        <i class="bi bi-link-45deg" style="color:#0277bd;font-size:18px;flex-shrink:0;"></i>
                        <code id="attendance-url">{{ $event->publicAttendanceUrl() }}</code>
                        <button onclick="copyLink()" class="btn btn-sm"
                            style="background:#e3f2fd;color:#0277bd;border:none;border-radius:8px;flex-shrink:0;"
                            title="Salin link">
                            <i class="bi bi-clipboard" id="copy-icon"></i>
                        </button>
                    </div>
                    <a href="{{ $event->publicAttendanceUrl() }}" target="_blank" class="btn w-100 fw-bold"
                        style="background:linear-gradient(135deg,#2e7d32,#388e3c);color:#fff;border-radius:12px;border:none;">
                        <i class="bi bi-box-arrow-up-right me-1"></i> Buka Halaman Absensi
                    </a>
                </div>
            </div>

            {{-- Info Event --}}
            <div class="info-card mb-4">
                <div class="card-header">
                    <span class="fw-bold" style="font-size:14px;"><i class="bi bi-info-circle me-1"
                            style="color:#888;"></i>Informasi Event</span>
                </div>
                <div class="card-body" style="padding:16px 20px;">
                    <div class="info-row">
                        <span class="lbl">Unit</span>
                        <span class="val">{{ $event->unit->name }}</span>
                    </div>
                    <div class="info-row">
                        <span class="lbl">Kota</span>
                        <span class="val">{{ $event->city->name ?? '-' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="lbl">Venue</span>
                        <span class="val">{{ $event->venue ?? '-' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="lbl">Tanggal</span>
                        <span class="val">{{ $event->event_date?->format('d/m/Y') ?? '-' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="lbl">Jam</span>
                        <span class="val">{{ $event->event_time ?? '-' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="lbl">Dibuat oleh</span>
                        <span class="val">{{ $event->creator->name }}</span>
                    </div>
                    <div class="info-row">
                        <span class="lbl">Dibuat pada</span>
                        <span class="val">{{ $event->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="info-row">
                        <span class="lbl">Slug URL</span>
                        <span class="val"><code
                                style="font-size:12px;background:#f5f5f5;padding:2px 8px;border-radius:6px;">{{ $event->slug }}</code></span>
                    </div>
                </div>
            </div>

            {{-- Danger zone --}}
            <div class="info-card" style="border:1.5px solid #ffcdd2;">
                <div class="card-header" style="background:#fff8f8;">
                    <span class="fw-bold" style="font-size:14px;color:#c62828;"><i
                            class="bi bi-exclamation-triangle me-1"></i>Zona Berbahaya</span>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button onclick="openResetModal()" class="btn btn-outline-danger fw-bold"
                            style="border-radius:10px;">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Semua Absensi
                        </button>
                        <form action="{{ route('admin.events.destroy', $event) }}" method="POST"
                            onsubmit="return confirm('Hapus event ini beserta semua data peserta dan absensi? Tidak bisa dibatalkan!')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100 fw-bold" style="border-radius:10px;">
                                <i class="bi bi-trash me-1"></i> Hapus Event Ini
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ════ MODAL RESET ABSENSI ════ --}}
    <div class="modal fade" id="modalReset" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
            <div class="modal-content" style="border-radius:16px;border:none;">
                <div class="modal-body text-center p-5">
                    <div
                        style="width:68px;height:68px;background:#ffebee;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:30px;">
                        ⚠️</div>
                    <h5 class="fw-bold mb-2">Reset Data Absensi?</h5>
                    <p class="text-muted mb-3" style="font-size:13.5px;">Semua <strong>{{ $stats['hadir'] }} catatan
                            kehadiran</strong> akan dihapus permanen. Data peserta tetap aman.</p>
                    <form action="{{ route('admin.attendances.reset', $event) }}" method="POST">
                        @csrf
                        <div class="mb-3 text-start">
                            <label class="form-label fw-bold" style="font-size:13px;">Ketik <code>RESET</code> untuk
                                konfirmasi:</label>
                            <input type="text" name="confirm" class="form-control" placeholder="RESET" required
                                style="border-radius:10px;text-align:center;font-weight:700;letter-spacing:2px;">
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-light flex-grow-1" data-bs-dismiss="modal"
                                style="border-radius:10px;font-weight:600;">Batal</button>
                            <button type="submit" class="btn btn-danger flex-grow-1 fw-bold"
                                style="border-radius:10px;">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function openResetModal() {
            new bootstrap.Modal(document.getElementById('modalReset')).show();
        }

        function toggleDigitCount() {
            const mode = document.getElementById('ds_mode').value;
            document.getElementById('ds_count_wrap').style.display = mode === 'manual' ? 'block' : 'none';
        }

        async function detectDigits() {
            const btn = event.target.closest('button');
            const result = document.getElementById('detectResult');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Mendeteksi...';
            result.style.display = 'none';
            try {
                const pos = document.querySelector('[name="digit_position"]').value;
                const res = await fetch('{{ route('admin.events.detect-digits', $event) }}?position=' + pos);
                const data = await res.json();
                result.style.display = 'block';
                result.innerHTML = data.detected ?
                    `<div class="alert alert-success py-2 mb-0" style="border-radius:10px;font-size:13px;">✅ ${data.message}</div>` :
                    `<div class="alert alert-warning py-2 mb-0" style="border-radius:10px;font-size:13px;">⚠️ ${data.message}</div>`;
                if (data.detected) {
                    document.querySelector('[name="digit_count"]').value = data.detected;
                    document.getElementById('ds_mode').value = 'manual';
                    toggleDigitCount();
                }
            } catch (e) {
                result.style.display = 'block';
                result.innerHTML =
                    '<div class="alert alert-danger py-2 mb-0" style="border-radius:10px;font-size:13px;">❌ Gagal menghubungi server.</div>';
            }
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-cpu me-1"></i> Deteksi Sekarang';
        }

        function copyLink() {
            const url = document.getElementById('attendance-url').textContent;
            const icon = document.getElementById('copy-icon');
            navigator.clipboard.writeText(url).then(() => {
                icon.className = 'bi bi-clipboard-check';
                setTimeout(() => icon.className = 'bi bi-clipboard', 2000);
            });
        }
    </script>
@endpush
