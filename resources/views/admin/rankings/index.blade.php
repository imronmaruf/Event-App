@extends('layouts.admin')
@section('title', 'Hasil & Ranking — ' . $event->name)
@section('page-title', 'Hasil Perlombaan')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <style>
        /* ── Design tokens ─────────────────────────────────────────── */
        :root {
            --red: #c62828;
            --red-lt: #ffebee;
            --red-mid: #e53935;
            --blue: #1565c0;
            --blue-lt: #e3f2fd;
            --green: #2e7d32;
            --green-lt: #e8f5e9;
            --orange: #e65100;
            --gold: #f9a825;
            --gold-lt: #fff8e1;
            --silver: #78909c;
            --bronze: #a1887f;
            --surface: #ffffff;
            --bg: #f4f6fa;
            --border: #e8eaf0;
            --text: #1a1a2e;
            --muted: #6b7280;
            --radius: 14px;
            --radius-sm: 9px;
            --shadow: 0 2px 12px rgba(0, 0, 0, .07);
            --shadow-md: 0 4px 24px rgba(0, 0, 0, .10);
        }

        /* ── Page layout ────────────────────────────────────────────── */
        body {
            background: var(--bg);
        }

        .page-card {
            background: var(--surface);
            border-radius: var(--radius);
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        /* ── Stat pills ─────────────────────────────────────────────── */
        .stat-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 99px;
            padding: 4px 12px;
            font-size: 12px;
            white-space: nowrap;
        }

        .stat-pill .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        /* ── Card header ─────────────────────────────────────────────── */
        .card-hd {
            padding: 14px 18px;
            border-bottom: 1px solid var(--border);
            background: #fafbfc;
        }

        .card-hd-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13.5px;
            font-weight: 700;
        }

        .card-hd-icon {
            width: 34px;
            height: 34px;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
        }

        .card-body {
            padding: 16px 18px;
        }

        /* ── Config poin ─────────────────────────────────────────────── */
        .config-box {
            background: var(--bg);
            border-radius: var(--radius-sm);
            padding: 14px;
        }

        .point-input-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .point-label {
            font-size: 12px;
            font-weight: 600;
            width: 70px;
            flex-shrink: 0;
        }

        .point-input {
            width: 70px;
            font-size: 13px;
            font-weight: 700;
            border: 1.5px solid var(--border);
            border-radius: 7px;
            padding: 4px 8px;
            text-align: center;
        }

        .point-input:focus {
            outline: none;
            border-color: var(--orange);
        }

        .point-preview {
            font-size: 11px;
            color: var(--muted);
        }

        .formula-box {
            background: #fff;
            border: 1.5px dashed var(--border);
            border-radius: 7px;
            padding: 8px 12px;
            font-size: 11.5px;
            font-family: monospace;
            color: var(--blue);
            font-weight: 600;
        }

        /* ── Upload zone ─────────────────────────────────────────────── */
        .upload-zone {
            border: 2px dashed var(--border);
            border-radius: var(--radius-sm);
            padding: 20px 12px;
            text-align: center;
            cursor: pointer;
            transition: border-color .2s, background .2s;
        }

        .upload-zone:hover,
        .upload-zone.drag {
            border-color: var(--blue);
            background: var(--blue-lt);
        }

        .upload-zone .icon {
            font-size: 28px;
            display: block;
            margin-bottom: 6px;
        }

        .upload-zone .title {
            font-size: 12.5px;
            font-weight: 700;
            color: var(--text);
        }

        .upload-zone .sub {
            font-size: 11px;
            color: var(--muted);
        }

        .upload-zone input[type=file] {
            display: none;
        }

        /* ── Alert boxes ─────────────────────────────────────────────── */
        .alert-info-box {
            background: var(--blue-lt);
            border: 1px solid #90caf9;
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 11.5px;
            color: #0d47a1;
        }

        .alert-warn-box {
            background: #fff3e0;
            border: 1px solid #ffcc80;
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 11.5px;
            color: #bf360c;
        }

        /* ══════════════════════════════════════════════════════════════
                       PODIUM PER KELAS
                    ══════════════════════════════════════════════════════════════ */
        .class-podium-section {
            margin-bottom: 0;
        }

        .class-podium-section .section-title {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: var(--muted);
            padding: 0 18px 10px;
        }

        /* Horizontal scroll container untuk banyak kelas */
        .class-podiums-scroll {
            display: flex;
            gap: 16px;
            overflow-x: auto;
            padding: 0 18px 18px;
            scroll-snap-type: x mandatory;
            -webkit-overflow-scrolling: touch;
        }

        .class-podiums-scroll::-webkit-scrollbar {
            height: 4px;
        }

        .class-podiums-scroll::-webkit-scrollbar-track {
            background: var(--bg);
        }

        .class-podiums-scroll::-webkit-scrollbar-thumb {
            background: var(--border);
            border-radius: 4px;
        }

        /* Satu kotak podium per kelas */
        .class-podium-card {
            flex: 0 0 220px;
            scroll-snap-align: start;
            background: linear-gradient(160deg, #fff 0%, #f9faff 100%);
            border: 1.5px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
        }

        .class-podium-card .class-label {
            background: linear-gradient(135deg, var(--red) 0%, #e53935 100%);
            color: #fff;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .08em;
            text-align: center;
            padding: 6px 10px;
            text-transform: uppercase;
        }

        .podium-wrap {
            display: flex;
            align-items: flex-end;
            justify-content: center;
            gap: 4px;
            padding: 10px 8px 0;
        }

        .podium-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1;
        }

        .podium-medal {
            font-size: 18px;
            line-height: 1;
            margin-bottom: 4px;
        }

        .podium-name {
            font-size: 9.5px;
            font-weight: 700;
            text-align: center;
            line-height: 1.3;
            max-width: 60px;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            color: var(--text);
            margin-bottom: 2px;
        }

        .podium-noreg {
            font-size: 8.5px;
            color: var(--muted);
            margin-bottom: 4px;
        }

        .podium-block {
            border-radius: 6px 6px 0 0;
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 6px 4px;
        }

        .podium-rank-num {
            font-size: 13px;
            font-weight: 900;
            color: #fff;
            line-height: 1;
        }

        .podium-score {
            font-size: 10px;
            font-weight: 700;
            color: rgba(255, 255, 255, .85);
        }

        .p1 .podium-block {
            background: linear-gradient(170deg, #f9a825, #f57f17);
            min-height: 64px;
        }

        .p2 .podium-block {
            background: linear-gradient(170deg, #90a4ae, #546e7a);
            min-height: 48px;
        }

        .p3 .podium-block {
            background: linear-gradient(170deg, #bcaaa4, #8d6e63);
            min-height: 38px;
        }

        /* Mini skor di bawah podium */
        .class-mini-stats {
            display: flex;
            justify-content: space-around;
            padding: 8px 10px;
            border-top: 1px solid var(--border);
            margin-top: 4px;
        }

        .class-mini-stat {
            text-align: center;
        }

        .class-mini-stat .val {
            font-size: 12px;
            font-weight: 800;
            color: var(--red);
        }

        .class-mini-stat .lbl {
            font-size: 9px;
            color: var(--muted);
        }

        /* Empty state dalam card kelas */
        .class-empty {
            text-align: center;
            padding: 20px 10px;
            color: var(--muted);
            font-size: 11px;
        }

        .class-empty span {
            font-size: 22px;
            display: block;
            margin-bottom: 4px;
        }

        /* ── DataTable nav tabs ─────────────────────────────────────── */
        .nav-tabs-custom {
            display: flex;
            gap: 4px;
            border-bottom: 2px solid var(--border);
            padding: 0 4px;
            margin-bottom: 14px;
            flex-wrap: wrap;
        }

        .nav-tab-btn {
            background: none;
            border: none;
            padding: 8px 14px;
            font-size: 12.5px;
            font-weight: 600;
            color: var(--muted);
            cursor: pointer;
            border-bottom: 2.5px solid transparent;
            margin-bottom: -2px;
            border-radius: 6px 6px 0 0;
            transition: color .15s, border-color .15s;
        }

        .nav-tab-btn:hover {
            color: var(--text);
        }

        .nav-tab-btn.on {
            color: var(--red);
            border-bottom-color: var(--red);
        }

        .nav-tab-content {
            display: none;
        }

        .nav-tab-content.on {
            display: block;
        }

        /* ── Rank badges ─────────────────────────────────────────────── */
        .rank-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 26px;
            height: 26px;
            border-radius: 50%;
            font-weight: 800;
            font-size: 11.5px;
        }

        .rank-1 {
            background: var(--gold-lt);
            color: #f57f17;
            border: 1.5px solid #f9a825;
        }

        .rank-2 {
            background: #f5f5f5;
            color: #546e7a;
            border: 1.5px solid #cfd8dc;
        }

        .rank-3 {
            background: #fbe9e7;
            color: #6d4c41;
            border: 1.5px solid #bcaaa4;
        }

        .rank-n {
            background: var(--bg);
            color: var(--muted);
            border: 1px solid var(--border);
        }

        /* ── Table badges ─────────────────────────────────────────────── */
        .badge-invalid {
            background: #ffebee;
            color: #c62828;
            font-size: 10px;
            padding: 2px 7px;
            border-radius: 99px;
            font-weight: 700;
        }

        .badge-absent {
            background: #fff3e0;
            color: #e65100;
            font-size: 10px;
            padding: 2px 7px;
            border-radius: 99px;
            font-weight: 700;
        }

        .sub-badge {
            background: #e8f5e9;
            color: #2e7d32;
            font-size: 10px;
            padding: 2px 7px;
            border-radius: 99px;
            font-weight: 600;
        }

        /* ── Score value ─────────────────────────────────────────────── */
        .score-val {
            font-size: 13px;
            font-weight: 800;
            color: var(--blue);
        }

        /* ── Empty state ─────────────────────────────────────────────── */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--muted);
        }

        .empty-state .icon {
            font-size: 40px;
            display: block;
            margin-bottom: 10px;
        }

        .empty-state h5 {
            font-size: 15px;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 6px;
        }

        .empty-state p {
            font-size: 13px;
            margin: 0;
        }

        /* ── Responsive tweaks ──────────────────────────────────────── */
        @media (max-width: 768px) {
            .class-podium-card {
                flex: 0 0 180px;
            }

            .card-body {
                padding: 12px 14px;
            }
        }
    </style>
@endpush

@section('topbar-actions')
    <a href="{{ route('admin.events.show', $event) }}" class="btn btn-sm btn-light" style="border-radius:10px;">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
    @if ($stats['has_data'])
        <a href="{{ route('admin.rankings.export', $event) }}" class="btn btn-sm fw-bold"
            style="background:linear-gradient(135deg,#2e7d32,#388e3c);color:#fff;border-radius:10px;border:none;">
            <i class="bi bi-file-earmark-excel me-1"></i> Export Ranking
        </a>
    @endif
@endsection

@section('content')

    {{-- ── Info Header ───────────────────────────────────────────────────── --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div>
            <h5 class="fw-bold mb-0" style="color:var(--red);">🏆 {{ $event->name }}</h5>
            <small class="text-muted">
                <i class="bi bi-building me-1"></i>{{ $event->unit->name }}
                @if ($event->city)
                    · {{ $event->city->name }}
                @endif
            </small>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <div class="stat-pill"><span class="dot" style="background:#2e7d32;"></span>Valid:
                <strong>{{ $stats['total_valid'] }}</strong>
            </div>
            @if ($stats['invalid'] > 0)
                <div class="stat-pill"><span class="dot" style="background:#c62828;"></span>Tidak terdaftar:
                    <strong>{{ $stats['invalid'] }}</strong>
                </div>
            @endif
            @if ($stats['absent'] > 0)
                <div class="stat-pill"><span class="dot" style="background:#e65100;"></span>Tidak hadir:
                    <strong>{{ $stats['absent'] }}</strong>
                </div>
            @endif
            @if ($stats['has_data'])
                <div class="stat-pill"><span class="dot" style="background:#1565c0;"></span>Tertinggi:
                    <strong>{{ $stats['max_score'] }}</strong>
                </div>
                <div class="stat-pill"><span class="dot" style="background:#7b1fa2;"></span>Rata-rata:
                    <strong>{{ $stats['avg_score'] }}</strong>
                </div>
            @endif
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════
     BARIS 1 — 3 kartu sejajar: Konfigurasi | Upload | Format Excel
════════════════════════════════════════════════════════════════════════ --}}
    <div class="row g-3 mb-3 align-items-stretch">

        {{-- ── Kartu 1: Konfigurasi Penilaian ─────────────────────────── --}}
        <div class="col-12 col-lg-4">
            <div class="page-card h-100">
                <div class="card-hd">
                    <div class="card-hd-title">
                        <div class="card-hd-icon" style="background:linear-gradient(135deg,#f57c00,#ffa726);">
                            <i class="bi bi-gear-fill text-white fs-6"></i>
                        </div>
                        <div>
                            <div style="color:#e65100;">Konfigurasi Penilaian</div>
                            <div style="font-size:11px;color:var(--muted);font-weight:400;">Atur poin per jenis jawaban
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.rankings.config', $event) }}" method="POST">
                        @csrf
                        <div class="config-box mb-3">
                            <div class="point-input-group mb-2">
                                <span class="point-label">✅ Benar</span>
                                <input type="number" name="point_benar" class="point-input" id="inp-benar"
                                    value="{{ $config->point_benar }}" min="0" max="100" step="0.5"
                                    oninput="updateFormula()">
                                <span class="point-preview">poin/soal</span>
                            </div>
                            <div class="point-input-group mb-2">
                                <span class="point-label">❌ Salah</span>
                                <input type="number" name="point_salah" class="point-input" id="inp-salah"
                                    value="{{ $config->point_salah }}" min="-100" max="100" step="0.5"
                                    oninput="updateFormula()">
                                <span class="point-preview">poin/soal</span>
                            </div>
                            <div class="point-input-group">
                                <span class="point-label">— Kosong</span>
                                <input type="number" name="point_kosong" class="point-input" id="inp-kosong"
                                    value="{{ $config->point_kosong }}" min="0" max="100" step="0.5"
                                    oninput="updateFormula()">
                                <span class="point-preview">poin/soal</span>
                            </div>
                            <div class="formula-box mt-3" id="formula-preview">
                                Skor = (B × {{ $config->point_benar }}) + (S × {{ $config->point_salah }}) + (K ×
                                {{ $config->point_kosong }})
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="fw-bold" style="font-size:12px;">⏱️ Tiebreaker (skor sama)</label>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" name="tiebreak_by_time" id="tiebreak"
                                    value="1" {{ $config->tiebreak_by_time ? 'checked' : '' }}>
                                <label class="form-check-label" for="tiebreak" style="font-size:12px;">
                                    Jika skor sama → <strong>waktu_akhir lebih awal</strong> menang
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" style="font-size:12px;font-weight:600;">📝 Catatan Aturan</label>
                            <textarea name="scoring_note" class="form-control" rows="2" placeholder="Keterangan tambahan (opsional)"
                                style="border-radius:9px;border:1.5px solid var(--border);font-size:12px;">{{ $config->scoring_note }}</textarea>
                        </div>

                        <button type="submit" class="btn w-100 fw-bold"
                            style="background:linear-gradient(135deg,#e65100,#f57c00);color:#fff;border-radius:10px;border:none;padding:10px;font-size:13px;">
                            <i class="bi bi-save me-1"></i>
                            Simpan{{ $stats['has_data'] ? ' & Recalculate' : '' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- ── Kartu 2: Upload Hasil Ujian ─────────────────────────────── --}}
        <div class="col-12 col-lg-4">
            <div class="page-card h-100 d-flex flex-column">
                <div class="card-hd">
                    <div class="card-hd-title d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <div class="card-hd-icon" style="background:linear-gradient(135deg,#1565c0,#1976d2);">
                                <i class="bi bi-upload text-white" style="font-size:14px;"></i>
                            </div>
                            <div>
                                <div style="color:#1565c0;">Upload Hasil Ujian</div>
                                <div style="font-size:11px;color:var(--muted);font-weight:400;">File Excel dari sistem
                                    ujian</div>
                            </div>
                        </div>
                        <a href="{{ route('admin.rankings.template', $event) }}" class="btn btn-sm"
                            style="background:var(--blue-lt);color:#1565c0;border:none;border-radius:8px;font-size:11px;font-weight:700;white-space:nowrap;">
                            <i class="bi bi-download me-1"></i> Template
                        </a>
                    </div>
                </div>
                <div class="card-body flex-grow-1 d-flex flex-column">
                    <form action="{{ route('admin.rankings.upload', $event) }}" method="POST"
                        enctype="multipart/form-data" id="upload-form" class="d-flex flex-column flex-grow-1">
                        @csrf
                        <div class="upload-zone flex-grow-1" id="drop-zone"
                            onclick="document.getElementById('file-input').click()" ondragover="onDrag(event,true)"
                            ondragleave="onDrag(event,false)" ondrop="onDrop(event)">

                            <i class="icon bi bi-file-earmark-arrow-up"></i>
                            <div class="title" id="file-label">Klik atau drag file ke sini</div>
                            <div class="sub">.xlsx / .xls / .csv · Maks 20MB</div>
                            <input type="file" id="file-input" name="file" accept=".xlsx,.xls,.csv"
                                onchange="onFileSelect(this)">
                        </div>

                        <div id="file-preview" class="mt-2" style="display:none;">
                            <div class="d-flex align-items-center gap-2 p-2 rounded-3"
                                style="background:#e8f5e9;border:1.5px solid #a5d6a7;">
                                <span style="font-size:18px;">📄</span>
                                <div class="flex-grow-1 overflow-hidden">
                                    <div class="fw-bold text-truncate" id="preview-filename" style="font-size:12px;">
                                    </div>
                                    <div class="text-muted" id="preview-filesize" style="font-size:11px;"></div>
                                </div>
                                <button type="button" onclick="clearFile()" class="btn btn-sm btn-outline-danger"
                                    style="border-radius:7px;flex-shrink:0;padding:2px 8px;">✕</button>
                            </div>
                        </div>

                        <button type="submit" id="btn-upload" class="btn w-100 fw-bold mt-2" disabled
                            style="background:linear-gradient(135deg,#1565c0,#1976d2);color:#fff;border-radius:10px;border:none;padding:9px;font-size:13px;">
                            <i class="bi bi-upload me-1"></i> Proses & Hitung Ranking
                        </button>
                    </form>

                    <div class="alert-info-box mt-2">
                        <i class="bi bi-info-circle me-1"></i>
                        Upload menimpa data sebelumnya.
                        <strong>Hanya peserta terdaftar & hadir</strong> yang masuk ranking.
                    </div>

                    @if ($stats['has_data'])
                        <div class="mt-2 text-center">
                            <form action="{{ route('admin.rankings.reset', $event) }}" method="POST"
                                onsubmit="return confirm('Hapus semua data hasil ujian dan ranking?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                    style="border-radius:8px;font-size:12px;">
                                    <i class="bi bi-trash me-1"></i> Reset Data Hasil
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ── Kartu 3: Format Kolom Excel ─────────────────────────────── --}}
        <div class="col-12 col-lg-4">
            <div class="page-card h-100">
                <div class="card-hd">
                    <div class="card-hd-title">
                        <div class="card-hd-icon" style="background:#f5f5f5;font-size:16px;">📋</div>
                        <div>
                            <div style="color:var(--text);">Format Kolom Excel</div>
                            <div style="font-size:11px;color:var(--muted);font-weight:400;">Struktur file yang diterima
                                sistem</div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0" style="font-size:11.5px;">
                        <thead>
                            <tr>
                                <th>Kolom</th>
                                <th style="width:36px;">Wajib</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>no_register</code></td>
                                <td><span style="color:#2e7d32;font-weight:700;">✓</span></td>
                                <td>NOREG peserta</td>
                            </tr>
                            <tr>
                                <td><code>kode_paket</code></td>
                                <td><span style="color:#aaa;">–</span></td>
                                <td>Kode paket soal</td>
                            </tr>
                            <tr>
                                <td><code>nama_kelompok_ujian</code></td>
                                <td><span style="color:#aaa;">–</span></td>
                                <td>Mata ujian</td>
                            </tr>
                            <tr>
                                <td><code>benar</code></td>
                                <td><span style="color:#2e7d32;font-weight:700;">✓</span></td>
                                <td>Jawaban benar</td>
                            </tr>
                            <tr>
                                <td><code>salah</code></td>
                                <td><span style="color:#2e7d32;font-weight:700;">✓</span></td>
                                <td>Jawaban salah</td>
                            </tr>
                            <tr>
                                <td><code>kosong</code></td>
                                <td><span style="color:#2e7d32;font-weight:700;">✓</span></td>
                                <td>Soal kosong</td>
                            </tr>
                            <tr>
                                <td><code>waktu_awal</code></td>
                                <td><span style="color:#aaa;">–</span></td>
                                <td>Waktu mulai</td>
                            </tr>
                            <tr>
                                <td><code>waktu_akhir</code></td>
                                <td><span style="color:#aaa;">–</span></td>
                                <td>Selesai (tiebreaker)</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="alert-info-box mt-3" style="font-size:11px;">
                        💡 Satu peserta boleh punya <strong>lebih dari 1 baris</strong> (beberapa mata ujian). Skor total =
                        jumlah semua baris.
                    </div>

                    @if ($config->scoring_note)
                        <div class="alert-warn-box mt-2" style="font-size:11px;">
                            📝 <strong>Aturan:</strong> {{ $config->scoring_note }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>{{-- /baris 1 --}}


    {{-- ══════════════════════════════════════════════════════════════════════
     BARIS 2 — Podium Ranking Per Tingkat Kelas (scrollable horizontal)
════════════════════════════════════════════════════════════════════════ --}}
    <div class="page-card mb-3 class-podium-section">
        <div class="card-hd">
            <div class="card-hd-title">
                <div class="card-hd-icon" style="background:linear-gradient(135deg,#f9a825,#f57f17);">🏆</div>
                <div>
                    <div style="color:#e65100;">Podium Juara Per Tingkat Kelas</div>
                    <div style="font-size:11px;color:var(--muted);font-weight:400;">
                        3 juara terbaik di masing-masing kelas · Geser untuk melihat semua kelas →
                    </div>
                </div>
            </div>
        </div>

        @if (!$stats['has_data'])
            <div class="card-body">
                <div class="empty-state" style="padding:30px 20px;">
                    <span class="icon">🏆</span>
                    <h5>Belum Ada Data</h5>
                    <p>Upload hasil ujian untuk menampilkan podium per kelas.</p>
                </div>
            </div>
        @elseif (empty($rankingsByClass))
            <div class="card-body mt-10">
                <div class="empty-state" style="padding:30px 20px;">
                    <span class="icon">👥</span>
                    <h5>Data Kelas Tidak Tersedia</h5>
                    <p>Tidak ada informasi kelas pada data peserta.</p>
                </div>
            </div>
        @else
            <div class="class-podiums-scroll">
                @foreach ($rankingsByClass as $className => $classData)
                    @php
                        $medals = ['🥇', '🥈', '🥉'];
                        $top3 = collect($classData['top9']);
                        $r1 = $top3->firstWhere('class_rank', 1);
                        $r2 = $top3->firstWhere('class_rank', 2);
                        $r3 = $top3->firstWhere('class_rank', 3);
                    @endphp
                    <div class="class-podium-card">
                        <div class="class-label">Kelas {{ $className }}</div>

                        @if ($top3->isEmpty())
                            <div class="class-empty">
                                <span>📭</span>
                                Belum ada peserta
                            </div>
                        @else
                            <div class="podium-wrap">
                                {{-- Posisi 2 --}}
                                @if ($r2)
                                    <div class="podium-item p2">
                                        <div class="podium-medal">🥈</div>
                                        <div class="podium-name">{{ $r2['name'] }}</div>
                                        <div class="podium-noreg">{{ $r2['noreg'] }}</div>
                                        <div class="podium-block">
                                            <div class="podium-rank-num">2</div>
                                            <div class="podium-score">{{ $r2['total_score'] }}</div>
                                        </div>
                                    </div>
                                @else
                                    <div class="podium-item p2">
                                        <div class="podium-medal" style="opacity:.3;">🥈</div>
                                        <div class="podium-name" style="opacity:.4;">—</div>
                                        <div class="podium-noreg"></div>
                                        <div class="podium-block" style="opacity:.3;">
                                            <div class="podium-rank-num">2</div>
                                            <div class="podium-score">-</div>
                                        </div>
                                    </div>
                                @endif

                                {{-- Posisi 1 --}}
                                @if ($r1)
                                    <div class="podium-item p1">
                                        <div class="podium-medal">🥇</div>
                                        <div class="podium-name fw-bold">{{ $r1['name'] }}</div>
                                        <div class="podium-noreg">{{ $r1['noreg'] }}</div>
                                        <div class="podium-block">
                                            <div class="podium-rank-num">1</div>
                                            <div class="podium-score">{{ $r1['total_score'] }}</div>
                                        </div>
                                    </div>
                                @else
                                    <div class="podium-item p1">
                                        <div class="podium-medal" style="opacity:.3;">🥇</div>
                                        <div class="podium-name" style="opacity:.4;">—</div>
                                        <div class="podium-noreg"></div>
                                        <div class="podium-block" style="opacity:.3;">
                                            <div class="podium-rank-num">1</div>
                                            <div class="podium-score">-</div>
                                        </div>
                                    </div>
                                @endif

                                {{-- Posisi 3 --}}
                                @if ($r3)
                                    <div class="podium-item p3">
                                        <div class="podium-medal">🥉</div>
                                        <div class="podium-name">{{ $r3['name'] }}</div>
                                        <div class="podium-noreg">{{ $r3['noreg'] }}</div>
                                        <div class="podium-block">
                                            <div class="podium-rank-num">3</div>
                                            <div class="podium-score">{{ $r3['total_score'] }}</div>
                                        </div>
                                    </div>
                                @else
                                    <div class="podium-item p3">
                                        <div class="podium-medal" style="opacity:.3;">🥉</div>
                                        <div class="podium-name" style="opacity:.4;">—</div>
                                        <div class="podium-noreg"></div>
                                        <div class="podium-block" style="opacity:.3;">
                                            <div class="podium-rank-num">3</div>
                                            <div class="podium-score">-</div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="class-mini-stats">
                                <div class="class-mini-stat">
                                    <div class="val">{{ $classData['total'] }}</div>
                                    <div class="lbl">Peserta</div>
                                </div>
                                <div class="class-mini-stat">
                                    <div class="val" style="color:var(--blue);">{{ $classData['avg_score'] }}</div>
                                    <div class="lbl">Rata-rata</div>
                                </div>
                                <div class="class-mini-stat">
                                    <div class="val" style="color:var(--green);">{{ $classData['max_score'] }}</div>
                                    <div class="lbl">Tertinggi</div>
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>{{-- /baris 2 --}}


    {{-- ══════════════════════════════════════════════════════════════════════
     BARIS 3 — DataTable: Ranking Lengkap | Per Kelas | Detail | Bermasalah
════════════════════════════════════════════════════════════════════════ --}}
    @if (!$stats['has_data'])
        <div class="page-card">
            <div class="card-body">
                <div class="empty-state">
                    <span class="icon">📊</span>
                    <h5>Belum Ada Data Hasil</h5>
                    <p>Upload file Excel hasil ujian di atas untuk menampilkan tabel ranking peserta.</p>
                </div>
            </div>
        </div>
    @else
        <div class="page-card">
            <div class="card-body pb-2">

                <div class="nav-tabs-custom">
                    <button class="nav-tab-btn on" onclick="switchNavTab('tab-ranking', this)">
                        📊 Ranking Keseluruhan
                    </button>
                    <button class="nav-tab-btn" onclick="switchNavTab('tab-class', this)">
                        🎓 Ranking Per Kelas
                    </button>
                    <button class="nav-tab-btn" onclick="switchNavTab('tab-detail', this)">
                        📝 Detail Per Soal
                    </button>
                    @if ($invalidNoregs->count() || $absentees->count())
                        <button class="nav-tab-btn" onclick="switchNavTab('tab-invalid', this)">
                            ⚠️ Data Bermasalah
                            <span
                                style="background:#c62828;color:#fff;font-size:10px;padding:1px 6px;border-radius:99px;margin-left:4px;">
                                {{ $invalidNoregs->count() + $absentees->count() }}
                            </span>
                        </button>
                    @endif
                </div>

                {{-- ── TAB: Ranking Keseluruhan ── --}}
                <div class="nav-tab-content on" id="tab-ranking">
                    <div class="table-responsive">
                        <table id="rankingTable" class="table table-hover w-100" style="font-size:12.5px;">
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>NOREG</th>
                                    <th>Nama</th>
                                    <th>Kelas</th>
                                    <th>Sekolah</th>
                                    <th>Ruang</th>
                                    <th>Skor</th>
                                    <th>B</th>
                                    <th>S</th>
                                    <th>K</th>
                                    <th>Waktu Akhir</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($rankings as $r)
                                    <tr>
                                        <td>
                                            @if ($r->rank === 1)
                                                <span class="rank-badge rank-1">1</span>
                                            @elseif ($r->rank === 2)
                                                <span class="rank-badge rank-2">2</span>
                                            @elseif ($r->rank === 3)
                                                <span class="rank-badge rank-3">3</span>
                                            @else
                                                <span class="rank-badge rank-n">{{ $r->rank }}</span>
                                            @endif
                                        </td>
                                        <td><code class="small"
                                                style="background:#f5f5f5;padding:2px 6px;border-radius:5px;">{{ $r->noreg }}</code>
                                        </td>
                                        <td>
                                            <div class="fw-semibold">{{ $r->participant?->name ?? '-' }}</div>
                                            @if ($r->participant?->room)
                                                <div class="text-muted" style="font-size:10.5px;"><i
                                                        class="bi bi-door-open me-1"></i>{{ $r->participant->room }}</div>
                                            @endif
                                        </td>
                                        <td>{{ $r->participant?->class ?? '-' }}</td>
                                        <td>{{ $r->participant?->school ?? '-' }}</td>
                                        <td>{{ $r->participant?->room ?? '-' }}</td>
                                        <td><span class="score-val">{{ $r->total_score }}</span></td>
                                        <td><span class="fw-bold text-success">{{ $r->total_benar }}</span></td>
                                        <td><span class="fw-bold text-danger">{{ $r->total_salah }}</span></td>
                                        <td><span class="fw-bold text-muted">{{ $r->total_kosong }}</span></td>
                                        <td class="small text-secondary text-nowrap">
                                            {{ $r->waktu_akhir?->format('H:i:s') ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- ── TAB: Ranking Per Kelas ── --}}
                <div class="nav-tab-content" id="tab-class">
                    @if (empty($rankingsByClass))
                        <div class="empty-state py-4">
                            <span class="icon">👥</span>
                            <p>Tidak ada data kelas pada peserta.</p>
                        </div>
                    @else
                        {{-- Filter kelas --}}
                        <div class="mb-3 d-flex flex-wrap gap-2" id="class-filter-btns">
                            <button class="btn btn-sm class-filter-btn active" data-class="all"
                                onclick="filterClass('all', this)"
                                style="border-radius:99px;font-size:11.5px;font-weight:700;background:#c62828;color:#fff;border:none;">
                                Semua Kelas
                            </button>
                            @foreach ($rankingsByClass as $className => $classData)
                                <button class="btn btn-sm class-filter-btn" data-class="{{ $className }}"
                                    onclick="filterClass('{{ $className }}', this)"
                                    style="border-radius:99px;font-size:11.5px;font-weight:600;background:#f5f5f5;color:var(--text);border:1px solid var(--border);">
                                    Kelas {{ $className }}
                                    <span
                                        style="background:#e0e0e0;border-radius:99px;padding:0 5px;font-size:10px;margin-left:2px;">
                                        {{ $classData['total'] }}
                                    </span>
                                </button>
                            @endforeach
                        </div>

                        <div class="table-responsive">
                            <table id="classRankingTable" class="table table-hover w-100" style="font-size:12.5px;">
                                <thead>
                                    <tr>
                                        <th>Rank Kelas</th>
                                        <th>Rank Overall</th>
                                        <th>NOREG</th>
                                        <th>Nama</th>
                                        <th>Kelas</th>
                                        <th>Sekolah</th>
                                        <th>Skor</th>
                                        <th>B</th>
                                        <th>S</th>
                                        <th>K</th>
                                        <th>Waktu Akhir</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($rankingsByClass as $className => $classData)
                                        @foreach ($classData['rankings'] as $cr)
                                            <tr data-class="{{ $className }}">
                                                <td>
                                                    @if ($cr['class_rank'] === 1)
                                                        <span class="rank-badge rank-1">1</span>
                                                    @elseif ($cr['class_rank'] === 2)
                                                        <span class="rank-badge rank-2">2</span>
                                                    @elseif ($cr['class_rank'] === 3)
                                                        <span class="rank-badge rank-3">3</span>
                                                    @else
                                                        <span class="rank-badge rank-n">{{ $cr['class_rank'] }}</span>
                                                    @endif
                                                </td>
                                                <td><span class="text-muted small">#{{ $cr['overall_rank'] }}</span></td>
                                                <td><code class="small"
                                                        style="background:#f5f5f5;padding:2px 6px;border-radius:5px;">{{ $cr['noreg'] }}</code>
                                                </td>
                                                <td class="fw-semibold">{{ $cr['name'] }}</td>
                                                <td>
                                                    <span
                                                        style="background:var(--blue-lt);color:var(--blue);font-size:10.5px;padding:2px 8px;border-radius:99px;font-weight:700;">
                                                        {{ $cr['class'] }}
                                                    </span>
                                                </td>
                                                <td>{{ $cr['school'] }}</td>
                                                <td><span class="score-val">{{ $cr['total_score'] }}</span></td>
                                                <td><span class="fw-bold text-success">{{ $cr['total_benar'] }}</span>
                                                </td>
                                                <td><span class="fw-bold text-danger">{{ $cr['total_salah'] }}</span></td>
                                                <td><span class="fw-bold text-muted">{{ $cr['total_kosong'] }}</span></td>
                                                <td class="small text-secondary text-nowrap">{{ $cr['waktu_akhir'] }}</td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                {{-- ── TAB: Detail Per Soal ── --}}
                <div class="nav-tab-content" id="tab-detail">
                    @if ($examDetails->isEmpty())
                        <div class="empty-state py-4">
                            <span class="icon">📝</span>
                            <p>Belum ada detail per soal.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table id="detailTable" class="table table-hover w-100" style="font-size:12px;">
                                <thead>
                                    <tr>
                                        <th>NOREG</th>
                                        <th>Nama</th>
                                        <th>Paket</th>
                                        <th>Kelompok Ujian</th>
                                        <th>Benar</th>
                                        <th>Salah</th>
                                        <th>Kosong</th>
                                        <th>Skor Baris</th>
                                        <th>Waktu Akhir</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($examDetails as $noreg => $rows)
                                        @php $p = $rows->first()?->participant; @endphp
                                        @foreach ($rows as $detail)
                                            <tr>
                                                <td><code
                                                        style="font-size:11px;background:#f5f5f5;padding:2px 6px;border-radius:5px;">{{ $noreg }}</code>
                                                </td>
                                                <td class="fw-semibold" style="font-size:11px;">{{ $p?->name ?? '-' }}
                                                </td>
                                                <td style="color:#666;">{{ $detail->kode_paket ?? '-' }}</td>
                                                <td>
                                                    @if ($detail->nama_kelompok)
                                                        <span class="sub-badge">{{ $detail->nama_kelompok }}</span>
                                                    @else
                                                        <span style="color:#aaa;">-</span>
                                                    @endif
                                                </td>
                                                <td style="color:#2e7d32;font-weight:700;">{{ $detail->benar }}</td>
                                                <td style="color:#c62828;font-weight:700;">{{ $detail->salah }}</td>
                                                <td style="color:#888;">{{ $detail->kosong }}</td>
                                                <td style="font-weight:800;color:#1565c0;">{{ $detail->row_score }}</td>
                                                <td style="color:#555;white-space:nowrap;">
                                                    {{ $detail->waktu_akhir?->format('H:i:s') ?? '-' }}</td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                {{-- ── TAB: Data Bermasalah ── --}}
                @if ($invalidNoregs->count() || $absentees->count())
                    <div class="nav-tab-content" id="tab-invalid">
                        @if ($invalidNoregs->count())
                            <div class="fw-bold mb-2" style="font-size:13px;color:#c62828;">
                                <i class="bi bi-x-circle me-1"></i>NOREG Tidak Terdaftar ({{ $invalidNoregs->count() }})
                            </div>
                            <div class="alert-warn-box mb-3">
                                ⚠️ NOREG ini ada di file Excel tapi tidak terdaftar di database peserta event ini. Data
                                tidak masuk ranking.
                            </div>
                            <div class="table-responsive mb-4">
                                <table class="table table-sm" style="font-size:12px;">
                                    <thead>
                                        <tr>
                                            <th>NOREG</th>
                                            <th>Skor (diabaikan)</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($invalidNoregs as $r)
                                            <tr>
                                                <td><code>{{ $r->noreg }}</code></td>
                                                <td>{{ $r->total_score }}</td>
                                                <td><span class="badge-invalid">NOREG tidak ditemukan</span></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif

                        @if ($absentees->count())
                            <div class="fw-bold mb-2" style="font-size:13px;color:#e65100;">
                                <i class="bi bi-person-x me-1"></i>Peserta Tidak Hadir ({{ $absentees->count() }})
                            </div>
                            <div class="alert-warn-box mb-3">
                                ⚠️ Peserta terdaftar tapi tidak memiliki catatan hadir. Tidak masuk ranking.
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm" style="font-size:12px;">
                                    <thead>
                                        <tr>
                                            <th>NOREG</th>
                                            <th>Nama</th>
                                            <th>Skor (diabaikan)</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($absentees as $r)
                                            <tr>
                                                <td><code>{{ $r->noreg }}</code></td>
                                                <td>{{ $r->participant?->name ?? '-' }}</td>
                                                <td>{{ $r->total_score }}</td>
                                                <td><span class="badge-absent">Tidak hadir</span></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                @endif

            </div>
        </div>
    @endif

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {

            const dtLang = {
                search: "Cari:",
                searchPlaceholder: "Nama, NOREG, sekolah...",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "_START_–_END_ dari _TOTAL_",
                infoEmpty: "Tidak ada data",
                infoFiltered: "(dari _MAX_ total)",
                zeroRecords: "Data tidak ditemukan",
                emptyTable: "Belum ada data.",
                paginate: {
                    first: '«',
                    last: '»',
                    previous: '‹',
                    next: '›'
                },
            };

            if ($('#rankingTable').length) {
                $('#rankingTable').DataTable({
                    responsive: true,
                    pageLength: 25,
                    order: [
                        [0, 'asc']
                    ],
                    language: dtLang
                });
            }

            if ($('#classRankingTable').length) {
                window.classTable = $('#classRankingTable').DataTable({
                    responsive: true,
                    pageLength: 25,
                    order: [
                        [4, 'asc'],
                        [0, 'asc']
                    ],
                    language: dtLang
                });
            }

            if ($('#detailTable').length) {
                $('#detailTable').DataTable({
                    responsive: true,
                    pageLength: 25,
                    order: [
                        [0, 'asc'],
                        [3, 'asc']
                    ],
                    language: dtLang
                });
            }
        });

        // ── Nav tabs ──────────────────────────────────────────────────────
        function switchNavTab(id, btn) {
            document.querySelectorAll('.nav-tab-content').forEach(el => el.classList.remove('on'));
            document.querySelectorAll('.nav-tab-btn').forEach(b => b.classList.remove('on'));
            document.getElementById(id)?.classList.add('on');
            btn.classList.add('on');

            // Adjust DataTable columns after show
            const map = {
                'tab-ranking': '#rankingTable',
                'tab-class': '#classRankingTable',
                'tab-detail': '#detailTable'
            };
            if (map[id] && $.fn.DataTable.isDataTable(map[id])) {
                $(map[id]).DataTable().columns.adjust();
            }
        }

        // ── Filter per kelas pada tab Ranking Per Kelas ──────────────────
        function filterClass(cls, btn) {
            document.querySelectorAll('.class-filter-btn').forEach(b => {
                b.style.background = '#f5f5f5';
                b.style.color = 'var(--text)';
                b.style.border = '1px solid var(--border)';
            });
            btn.style.background = '#c62828';
            btn.style.color = '#fff';
            btn.style.border = 'none';

            if (!window.classTable) return;

            if (cls === 'all') {
                window.classTable.column(4).search('').draw();
            } else {
                window.classTable.column(4).search(cls, false, false).draw();
            }
        }

        // ── Formula preview ───────────────────────────────────────────────
        function updateFormula() {
            const b = document.getElementById('inp-benar').value || 0;
            const s = document.getElementById('inp-salah').value || 0;
            const k = document.getElementById('inp-kosong').value || 0;
            document.getElementById('formula-preview').textContent =
                `Skor = (B × ${b}) + (S × ${s}) + (K × ${k})`;
        }

        // ── File upload zone ──────────────────────────────────────────────
        function onFileSelect(inp) {
            const f = inp.files[0];
            if (!f) return;
            document.getElementById('file-label').textContent = f.name;
            document.getElementById('preview-filename').textContent = f.name;
            document.getElementById('preview-filesize').textContent = (f.size / 1024).toFixed(1) + ' KB';
            document.getElementById('file-preview').style.display = 'block';
            document.getElementById('btn-upload').disabled = false;
            document.getElementById('drop-zone').style.borderColor = '#2e7d32';
        }

        function clearFile() {
            document.getElementById('file-input').value = '';
            document.getElementById('file-label').textContent = 'Klik atau drag file ke sini';
            document.getElementById('file-preview').style.display = 'none';
            document.getElementById('btn-upload').disabled = true;
            document.getElementById('drop-zone').style.borderColor = '';
        }

        function onDrag(e, on) {
            e.preventDefault();
            document.getElementById('drop-zone').classList.toggle('drag', on);
        }

        function onDrop(e) {
            e.preventDefault();
            document.getElementById('drop-zone').classList.remove('drag');
            const f = e.dataTransfer.files[0];
            if (f) {
                const dt = new DataTransfer();
                dt.items.add(f);
                const inp = document.getElementById('file-input');
                inp.files = dt.files;
                onFileSelect(inp);
            }
        }

        // ── Submit loading ────────────────────────────────────────────────
        document.getElementById('upload-form')?.addEventListener('submit', function() {
            const btn = document.getElementById('btn-upload');
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';
            btn.disabled = true;
        });
    </script>
@endpush
