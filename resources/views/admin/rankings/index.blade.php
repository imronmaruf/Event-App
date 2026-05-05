@extends('layouts.admin')
@section('title', 'Hasil & Ranking — ' . $event->name)
@section('page-title', 'Hasil Perlombaan')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <style>
        /* ── Layout ── */
        .page-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 16px rgba(0, 0, 0, .07);
            border: 1px solid #f0f0f0;
            margin-bottom: 20px;
            overflow: hidden;
        }

        .page-card .card-hd {
            padding: 16px 20px;
            border-bottom: 1px solid #f5f5f5;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 8px;
        }

        .page-card .card-hd-title {
            font-size: 14px;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .page-card .card-hd-icon {
            width: 32px;
            height: 32px;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
        }

        .page-card .card-body {
            padding: 20px;
        }

        /* ── Podium ── */
        .podium-wrap {
            display: flex;
            align-items: flex-end;
            justify-content: center;
            gap: 12px;
            padding: 24px 16px 0;
        }

        .podium-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
        }

        .podium-medal {
            font-size: 36px;
            line-height: 1;
        }

        .podium-name {
            font-size: 13px;
            font-weight: 700;
            text-align: center;
            max-width: 130px;
            color: #1a1a1a;
        }

        .podium-noreg {
            font-size: 10px;
            color: #888;
        }

        .podium-sekolah {
            font-size: 10px;
            color: #555;
            text-align: center;
            max-width: 130px;
        }

        .podium-score {
            font-size: 18px;
            font-weight: 900;
        }

        .podium-block {
            border-radius: 12px 12px 0 0;
            width: 110px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-end;
            padding: 14px 8px;
        }

        .p1 .podium-block {
            height: 120px;
            background: linear-gradient(160deg, #f9a825, #f57f17);
            color: #fff;
            box-shadow: 0 -4px 20px rgba(249, 168, 37, .4);
        }

        .p2 .podium-block {
            height: 90px;
            background: linear-gradient(160deg, #bdbdbd, #757575);
            color: #fff;
            box-shadow: 0 -4px 16px rgba(189, 189, 189, .4);
        }

        .p3 .podium-block {
            height: 70px;
            background: linear-gradient(160deg, #ff8a65, #d84315);
            color: #fff;
            box-shadow: 0 -4px 16px rgba(255, 138, 101, .4);
        }

        .podium-rank-num {
            font-size: 22px;
            font-weight: 900;
            line-height: 1;
        }

        /* ── Stat pills ── */
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

        .stat-pill .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
        }

        /* ── Config form ── */
        .config-box {
            background: #fff8f0;
            border: 1.5px solid #ffe0b2;
            border-radius: 14px;
            padding: 16px;
        }

        .point-input-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .point-label {
            font-size: 13px;
            font-weight: 600;
            color: #444;
            min-width: 80px;
        }

        .point-input {
            width: 80px;
            padding: 8px 12px;
            border: 1.5px solid #e0e0e0;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 700;
            text-align: center;
            outline: none;
        }

        .point-input:focus {
            border-color: #e65100;
            box-shadow: 0 0 0 3px rgba(230, 81, 0, .12);
        }

        .point-preview {
            font-size: 12px;
            color: #888;
        }

        /* ── Upload zone ── */
        .upload-zone {
            border: 2.5px dashed #e0e0e0;
            border-radius: 16px;
            padding: 32px 20px;
            text-align: center;
            cursor: pointer;
            transition: all .2s;
            background: #fafafa;
        }

        .upload-zone:hover,
        .upload-zone.drag {
            border-color: #c62828;
            background: #fff5f5;
        }

        .upload-zone .icon {
            font-size: 40px;
            margin-bottom: 10px;
            display: block;
        }

        .upload-zone .title {
            font-size: 15px;
            font-weight: 700;
            color: #1a1a1a;
        }

        .upload-zone .sub {
            font-size: 12px;
            color: #888;
            margin-top: 4px;
        }

        .upload-zone input[type=file] {
            display: none;
        }

        /* ── Table ── */
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
            border-color: #c62828;
            outline: none;
            box-shadow: 0 0 0 3px rgba(198, 40, 40, .1);
        }

        .dataTables_wrapper .dataTables_length select {
            border-radius: 8px;
            border: 1.5px solid #e0e0e0;
            padding: 5px 10px;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: linear-gradient(135deg, #c62828, #e64a19) !important;
            border-color: transparent !important;
            color: #fff !important;
            border-radius: 8px;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #ffebee !important;
            border-color: transparent !important;
            color: #c62828 !important;
            border-radius: 8px;
        }

        /* ── Rank badges ── */
        .rank-1 {
            background: linear-gradient(135deg, #f9a825, #f57f17);
            color: #fff;
            font-weight: 900;
            padding: 4px 12px;
            border-radius: 99px;
            font-size: 13px;
        }

        .rank-2 {
            background: linear-gradient(135deg, #bdbdbd, #757575);
            color: #fff;
            font-weight: 900;
            padding: 4px 12px;
            border-radius: 99px;
            font-size: 13px;
        }

        .rank-3 {
            background: linear-gradient(135deg, #ff8a65, #d84315);
            color: #fff;
            font-weight: 900;
            padding: 4px 12px;
            border-radius: 99px;
            font-size: 13px;
        }

        .rank-n {
            background: #f5f5f5;
            color: #555;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 99px;
            font-size: 13px;
        }

        /* Score bar */
        .score-bar-wrap {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .score-bar-bg {
            flex: 1;
            background: #f0f0f0;
            border-radius: 99px;
            height: 7px;
            overflow: hidden;
            min-width: 60px;
        }

        .score-bar-fill {
            height: 100%;
            border-radius: 99px;
            background: linear-gradient(90deg, #c62828, #e64a19);
        }

        .score-val {
            font-size: 13px;
            font-weight: 800;
            color: #c62828;
            min-width: 42px;
            text-align: right;
        }

        /* ── Detail subjects ── */
        .sub-row {
            font-size: 11.5px;
            color: #555;
        }

        .sub-badge {
            display: inline-flex;
            gap: 5px;
            align-items: center;
            background: #f0f4f8;
            border-radius: 8px;
            padding: 2px 8px;
            font-size: 11px;
            font-weight: 600;
        }

        /* ── Status badges ── */
        .badge-valid {
            background: #e8f5e9;
            color: #2e7d32;
            font-weight: 700;
            font-size: 11px;
            padding: 3px 10px;
            border-radius: 99px;
        }

        .badge-invalid {
            background: #ffebee;
            color: #c62828;
            font-weight: 700;
            font-size: 11px;
            padding: 3px 10px;
            border-radius: 99px;
        }

        .badge-absent {
            background: #fff3e0;
            color: #e65100;
            font-weight: 700;
            font-size: 11px;
            padding: 3px 10px;
            border-radius: 99px;
        }

        /* ── Alert box ── */
        .alert-info-box {
            background: #e3f2fd;
            border: 1.5px solid #90caf9;
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 13px;
            color: #1565c0;
        }

        .alert-warn-box {
            background: #fff8e1;
            border: 1.5px solid #ffe082;
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 13px;
            color: #f57f17;
        }

        /* ── Tabs ── */
        .nav-tabs-custom {
            border-bottom: 2px solid #f0f0f0;
            margin-bottom: 16px;
            display: flex;
            gap: 4px;
            flex-wrap: wrap;
        }

        .nav-tab-btn {
            padding: 10px 18px;
            border: none;
            background: transparent;
            font-size: 13px;
            font-weight: 600;
            color: #888;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            margin-bottom: -2px;
            transition: all .2s;
            border-radius: 10px 10px 0 0;
        }

        .nav-tab-btn.on {
            color: #c62828;
            border-bottom-color: #c62828;
            background: #fff5f5;
            font-weight: 800;
        }

        .nav-tab-content {
            display: none;
        }

        .nav-tab-content.on {
            display: block;
        }

        /* ── Buttons ── */
        .btn-action {
            width: 32px;
            height: 32px;
            padding: 0;
            border-radius: 9px;
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

        .formula-box {
            background: #1a1a2e;
            border-radius: 12px;
            padding: 14px 18px;
            font-family: monospace;
            font-size: 13px;
            color: #a5d6a7;
        }

        /* ── Empty state ── */
        .empty-state {
            text-align: center;
            padding: 48px 20px;
        }

        .empty-state .icon {
            font-size: 52px;
            margin-bottom: 12px;
            display: block;
            opacity: .5;
        }

        .empty-state h5 {
            color: #888;
            font-weight: 700;
        }

        .empty-state p {
            color: #aaa;
            font-size: 13px;
        }

        @media(max-width:768px) {
            .podium-wrap {
                gap: 6px;
            }

            .podium-block {
                width: 90px;
            }

            .point-input-group {
                flex-wrap: wrap;
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

    {{-- ── INFO HEADER ── --}}
    <div class="d-flex flex-wrap gap-2 mb-3 align-items-center">
        <div>
            <h5 class="fw-bold mb-0" style="color:#c62828;">🏆 {{ $event->name }}</h5>
            <small class="text-muted"><i class="bi bi-building me-1"></i>{{ $event->unit->name }}
                @if ($event->city)
                    · {{ $event->city->name }}
                @endif
            </small>
        </div>
    </div>

    {{-- Stat pills --}}
    <div class="d-flex flex-wrap gap-2 mb-4">
        <div class="stat-pill"><span class="dot" style="background:#2e7d32;"></span> Valid:
            <strong>{{ $stats['total_valid'] }}</strong>
        </div>
        @if ($stats['invalid'] > 0)
            <div class="stat-pill"><span class="dot" style="background:#c62828;"></span> NOREG tidak terdaftar:
                <strong>{{ $stats['invalid'] }}</strong>
            </div>
        @endif
        @if ($stats['absent'] > 0)
            <div class="stat-pill"><span class="dot" style="background:#e65100;"></span> Tidak hadir (diabaikan):
                <strong>{{ $stats['absent'] }}</strong>
            </div>
        @endif
        @if ($stats['has_data'])
            <div class="stat-pill"><span class="dot" style="background:#1565c0;"></span> Skor tertinggi:
                <strong>{{ $stats['max_score'] }}</strong>
            </div>
            <div class="stat-pill"><span class="dot" style="background:#7b1fa2;"></span> Rata-rata:
                <strong>{{ $stats['avg_score'] }}</strong>
            </div>
        @endif
    </div>

    <div class="row g-4">

        {{-- ══ KOLOM KIRI: KONFIGURASI + UPLOAD ══ --}}
        <div class="col-12 col-xl-4">

            {{-- Konfigurasi Poin --}}
            <div class="page-card">
                <div class="card-hd">
                    <div class="card-hd-title">
                        <div class="card-hd-icon" style="background:linear-gradient(135deg,#e65100,#f57c00);">⚙️</div>
                        <div>
                            <div style="color:#e65100;">Konfigurasi Penilaian</div>
                            <div style="font-size:11px;color:#888;font-weight:400;">Atur poin per jenis jawaban</div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.rankings.config', $event) }}" method="POST">
                        @csrf
                        <div class="config-box mb-3">
                            <div class="mb-3">
                                <div class="point-input-group mb-2">
                                    <span class="point-label">✅ Benar</span>
                                    <input type="number" name="point_benar" class="point-input" id="inp-benar"
                                        value="{{ $config->point_benar }}" min="0" max="100" step="0.5"
                                        oninput="updateFormula()">
                                    <span class="point-preview">poin / soal</span>
                                </div>
                                <div class="point-input-group mb-2">
                                    <span class="point-label">❌ Salah</span>
                                    <input type="number" name="point_salah" class="point-input" id="inp-salah"
                                        value="{{ $config->point_salah }}" min="-100" max="100" step="0.5"
                                        oninput="updateFormula()">
                                    <span class="point-preview">poin / soal</span>
                                </div>
                                <div class="point-input-group">
                                    <span class="point-label">— Kosong</span>
                                    <input type="number" name="point_kosong" class="point-input" id="inp-kosong"
                                        value="{{ $config->point_kosong }}" min="0" max="100" step="0.5"
                                        oninput="updateFormula()">
                                    <span class="point-preview">poin / soal</span>
                                </div>
                            </div>

                            {{-- Formula preview --}}
                            <div class="formula-box mt-3" id="formula-preview">
                                Skor = (B × {{ $config->point_benar }}) + (S × {{ $config->point_salah }}) + (K ×
                                {{ $config->point_kosong }})
                            </div>
                        </div>

                        {{-- Tiebreaker --}}
                        <div class="mb-3">
                            <label class="fw-bold" style="font-size:13px;">⏱️ Tiebreaker (skor sama)</label>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" name="tiebreak_by_time" id="tiebreak"
                                    value="1" {{ $config->tiebreak_by_time ? 'checked' : '' }}>
                                <label class="form-check-label" for="tiebreak" style="font-size:13px;">
                                    Jika skor sama → yang <strong>waktu_akhir lebih awal</strong> menang
                                </label>
                            </div>
                        </div>

                        {{-- Catatan aturan --}}
                        <div class="mb-3">
                            <label class="form-label" style="font-size:13px;font-weight:600;">📝 Catatan Aturan</label>
                            <textarea name="scoring_note" class="form-control" rows="2"
                                placeholder="Keterangan tambahan aturan penilaian (opsional)"
                                style="border-radius:10px;border:1.5px solid #e0e0e0;font-size:13px;">{{ $config->scoring_note }}</textarea>
                        </div>

                        <button type="submit" class="btn w-100 fw-bold"
                            style="background:linear-gradient(135deg,#e65100,#f57c00);color:#fff;border-radius:11px;border:none;padding:11px;">
                            <i class="bi bi-save me-1"></i>
                            Simpan Konfigurasi{{ $stats['has_data'] ? ' & Recalculate' : '' }}
                        </button>
                    </form>
                </div>
            </div>

            {{-- Upload File Hasil ──}}
        <div class="page-card">
            <div class="card-hd">
                <div class="card-hd-title">
                    <div class="card-hd-icon" style="background:linear-gradient(135deg,#1565c0,#1976d2);">📥</div>
                    <div>
                        <div style="color:#1565c0;">Upload Hasil Ujian</div>
                        <div style="font-size:11px;color:#888;font-weight:400;">File Excel dari sistem ujian</div>
                    </div>
                </div>
                <a href="{{ route('admin.rankings.template', $event) }}" class="btn btn-sm"
                    style="background:#e3f2fd;color:#1565c0;border:none;border-radius:9px;font-size:12px;font-weight:700;">
                    <i class="bi bi-download me-1"></i> Template
                </a>
            </div>
            <div class="card-body">

                {{-- Upload zone --}}
            <form action="{{ route('admin.rankings.upload', $event) }}" method="POST" enctype="multipart/form-data"
                id="upload-form">
                @csrf
                <div class="upload-zone" id="drop-zone" onclick="document.getElementById('file-input').click()"
                    ondragover="onDrag(event,true)" ondragleave="onDrag(event,false)" ondrop="onDrop(event)">
                    <span class="icon">📊</span>
                    <div class="title" id="file-label">Klik atau drag file Excel ke sini</div>
                    <div class="sub">.xlsx / .xls / .csv · Maks 20MB</div>
                    <input type="file" id="file-input" name="file" accept=".xlsx,.xls,.csv"
                        onchange="onFileSelect(this)">
                </div>

                <div id="file-preview" class="mt-3" style="display:none;">
                    <div class="d-flex align-items-center gap-2 p-3 rounded-3"
                        style="background:#e8f5e9;border:1.5px solid #a5d6a7;">
                        <span style="font-size:22px;">📄</span>
                        <div class="flex-grow-1">
                            <div class="fw-bold" id="preview-filename" style="font-size:13px;"></div>
                            <div class="text-muted" id="preview-filesize" style="font-size:11px;"></div>
                        </div>
                        <button type="button" onclick="clearFile()" class="btn btn-sm btn-outline-danger"
                            style="border-radius:8px;">✕</button>
                    </div>
                </div>

                <button type="submit" id="btn-upload" class="btn w-100 fw-bold mt-3" disabled
                    style="background:linear-gradient(135deg,#1565c0,#1976d2);color:#fff;border-radius:11px;border:none;padding:11px;">
                    <i class="bi bi-upload me-1"></i> Proses & Hitung Ranking
                </button>
            </form>

            <div class="alert-info-box mt-3">
                <i class="bi bi-info-circle me-1"></i>
                Upload akan menimpa data sebelumnya. Pastikan format Excel sesuai template.<br>
                <strong>Hanya peserta yang terdaftar dan hadir</strong> yang masuk ranking.
            </div>

            @if ($stats['has_data'])
                <div class="mt-3 text-center">
                    <form action="{{ route('admin.rankings.reset', $event) }}" method="POST"
                        onsubmit="return confirm('Hapus semua data hasil ujian dan ranking? Tidak bisa dibatalkan!')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger"
                            style="border-radius:9px;font-size:12px;">
                            <i class="bi bi-trash me-1"></i> Reset Data Hasil
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>

    {{-- Kolom wajib --}}
    <div class="page-card">
        <div class="card-hd">
            <div class="card-hd-title">
                <div class="card-hd-icon" style="background:#f5f5f5;">📋</div>
                <span style="color:#555;">Format Kolom Excel</span>
            </div>
        </div>
        <div class="card-body" style="padding:14px 18px;">
            <table class="table table-sm mb-0" style="font-size:12px;">
                <thead>
                    <tr>
                        <th>Kolom</th>
                        <th>Wajib</th>
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
                        <td>Jml jawaban benar</td>
                    </tr>
                    <tr>
                        <td><code>salah</code></td>
                        <td><span style="color:#2e7d32;font-weight:700;">✓</span></td>
                        <td>Jml jawaban salah</td>
                    </tr>
                    <tr>
                        <td><code>kosong</code></td>
                        <td><span style="color:#2e7d32;font-weight:700;">✓</span></td>
                        <td>Jml soal kosong</td>
                    </tr>
                    <tr>
                        <td><code>waktu_awal</code></td>
                        <td><span style="color:#aaa;">–</span></td>
                        <td>Waktu mulai</td>
                    </tr>
                    <tr>
                        <td><code>waktu_akhir</code></td>
                        <td><span style="color:#aaa;">–</span></td>
                        <td>Waktu selesai (tiebreaker)</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    </div>

    {{-- ══ KOLOM KANAN: HASIL & RANKING ══ --}}
    <div class="col-12 col-xl-8">

        @if (!$stats['has_data'])
            {{-- Empty state --}}
            <div class="page-card">
                <div class="card-body">
                    <div class="empty-state">
                        <span class="icon">🏆</span>
                        <h5>Belum Ada Data Hasil</h5>
                        <p>Upload file Excel hasil ujian terlebih dahulu<br>untuk menampilkan ranking peserta.</p>
                    </div>
                </div>
            </div>
        @else
            {{-- ══ PODIUM TOP 3 ══ --}}
            @if ($stats['top3']->count() > 0)
                <div class="page-card mb-4">
                    <div class="card-hd">
                        <div class="card-hd-title">
                            <div class="card-hd-icon" style="background:linear-gradient(135deg,#f9a825,#f57f17);">🏆</div>
                            <span style="color:#e65100;">Podium Pemenang</span>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        @php
                            $top = $rankings->where('rank', '<=', 3)->sortBy('rank');
                            $r1 = $top->firstWhere('rank', 1);
                            $r2 = $top->firstWhere('rank', 2);
                            $r3 = $top->firstWhere('rank', 3);
                        @endphp
                        <div class="podium-wrap">
                            {{-- Rank 2 --}}
                            @if ($r2)
                                <div class="podium-item p2">
                                    <div class="podium-medal">🥈</div>
                                    <div class="podium-name">{{ $r2->participant?->name ?? $r2->noreg }}</div>
                                    <div class="podium-noreg">{{ $r2->noreg }}</div>
                                    <div class="podium-sekolah">{{ $r2->participant?->school ?? '' }}</div>
                                    <div class="podium-block">
                                        <div class="podium-rank-num">2</div>
                                        <div class="podium-score">{{ $r2->total_score }}</div>
                                    </div>
                                </div>
                            @endif
                            {{-- Rank 1 --}}
                            @if ($r1)
                                <div class="podium-item p1">
                                    <div class="podium-medal">🥇</div>
                                    <div class="podium-name fw-bold" style="font-size:14px;">
                                        {{ $r1->participant?->name ?? $r1->noreg }}</div>
                                    <div class="podium-noreg">{{ $r1->noreg }}</div>
                                    <div class="podium-sekolah">{{ $r1->participant?->school ?? '' }}</div>
                                    <div class="podium-block">
                                        <div class="podium-rank-num">1</div>
                                        <div class="podium-score" style="font-size:22px;">{{ $r1->total_score }}</div>
                                    </div>
                                </div>
                            @endif
                            {{-- Rank 3 --}}
                            @if ($r3)
                                <div class="podium-item p3">
                                    <div class="podium-medal">🥉</div>
                                    <div class="podium-name">{{ $r3->participant?->name ?? $r3->noreg }}</div>
                                    <div class="podium-noreg">{{ $r3->noreg }}</div>
                                    <div class="podium-sekolah">{{ $r3->participant?->school ?? '' }}</div>
                                    <div class="podium-block">
                                        <div class="podium-rank-num">3</div>
                                        <div class="podium-score">{{ $r3->total_score }}</div>
                                    </div>
                                </div>
                            @endif
                        </div>
                        @if ($config->scoring_note)
                            <div class="alert-info-box mt-3">
                                📝 <strong>Aturan:</strong> {{ $config->scoring_note }}
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- ══ TABS: RANKING | DETAIL SOAL | TIDAK VALID ══ --}}
            <div class="page-card">
                <div class="card-body pb-2">
                    <div class="nav-tabs-custom">
                        <button class="nav-tab-btn on" onclick="switchNavTab('tab-ranking', this)">📊 Ranking
                            Lengkap</button>
                        <button class="nav-tab-btn" onclick="switchNavTab('tab-detail', this)">📝 Detail Per Soal</button>
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

                    {{-- TAB: Ranking --}}
                    <div class="nav-tab-content on" id="tab-ranking">
                        <div class="table-responsive">
                            <table id="rankingTable" class="table table-hover w-100">
                                <thead>
                                    <tr>
                                        <th>Rank</th>
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
                                    @foreach ($rankings as $r)
                                        <tr @class([
                                            'table-warning fw-bold' => $r->rank === 1,
                                            'table-light' => $r->rank === 2,
                                            'table-danger' => $r->rank === 3 && false,
                                        ])>
                                            <td>
                                                @if ($r->rank === 1)
                                                    <span class="rank-1">🥇 1</span>
                                                @elseif($r->rank === 2)
                                                    <span class="rank-2">🥈 2</span>
                                                @elseif($r->rank === 3)
                                                    <span class="rank-3">🥉 3</span>
                                                @else
                                                    <span class="rank-n">{{ $r->rank }}</span>
                                                @endif
                                            </td>
                                            <td><code
                                                    style="font-size:12px;background:#f5f5f5;padding:2px 7px;border-radius:6px;">{{ $r->noreg }}</code>
                                            </td>
                                            <td>
                                                <div class="fw-bold" style="font-size:13px;">
                                                    {{ $r->participant?->name ?? '-' }}</div>
                                                @if ($r->participant?->room)
                                                    <div style="font-size:11px;color:#888;"><i
                                                            class="bi bi-door-open me-1"></i>{{ $r->participant->room }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td style="font-size:12px;">{{ $r->participant?->class ?? '-' }}</td>
                                            <td
                                                style="font-size:12px;max-width:150px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                                {{ $r->participant?->school ?? '-' }}
                                            </td>
                                            <td>
                                                @php
                                                    $maxS = $stats['max_score'] ?: 1;
                                                    $pct = min(100, round(($r->total_score / $maxS) * 100));
                                                @endphp
                                                <div class="score-bar-wrap">
                                                    <div class="score-bar-bg">
                                                        <div class="score-bar-fill" style="width:{{ $pct }}%">
                                                        </div>
                                                    </div>
                                                    <span class="score-val">{{ $r->total_score }}</span>
                                                </div>
                                            </td>
                                            <td><span style="color:#2e7d32;font-weight:700;">{{ $r->total_benar }}</span>
                                            </td>
                                            <td><span style="color:#c62828;font-weight:700;">{{ $r->total_salah }}</span>
                                            </td>
                                            <td><span style="color:#888;">{{ $r->total_kosong }}</span></td>
                                            <td style="font-size:12px;white-space:nowrap;color:#555;">
                                                {{ $r->waktu_akhir?->format('H:i:s') ?? '-' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- TAB: Detail per soal --}}
                    <div class="nav-tab-content" id="tab-detail">
                        @if ($examDetails->isEmpty())
                            <div class="empty-state py-4"><span class="icon">📝</span>
                                <p>Belum ada detail per soal.</p>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table id="detailTable" class="table table-hover w-100" style="font-size:12.5px;">
                                    <thead>
                                        <tr>
                                            <th>NOREG</th>
                                            <th>Nama</th>
                                            <th>Paket</th>
                                            <th>Kelompok</th>
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
                                                    <td class="fw-semibold" style="font-size:12px;">
                                                        {{ $p?->name ?? '-' }}</td>
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
                                                    <td style="font-weight:800;color:#1565c0;">{{ $detail->row_score }}
                                                    </td>
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

                    {{-- TAB: Data bermasalah --}}
                    @if ($invalidNoregs->count() || $absentees->count())
                        <div class="nav-tab-content" id="tab-invalid">
                            @if ($invalidNoregs->count())
                                <div class="fw-bold mb-2" style="font-size:13px;color:#c62828;"><i
                                        class="bi bi-x-circle me-1"></i>NOREG Tidak Terdaftar
                                    ({{ $invalidNoregs->count() }})</div>
                                <div class="alert-warn-box mb-3">
                                    ⚠️ NOREG di bawah ada di file Excel tapi tidak terdaftar di database peserta event ini.
                                    Data ini tidak masuk ranking.
                                </div>
                                <div class="table-responsive mb-4">
                                    <table class="table table-sm" style="font-size:13px;">
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
                                <div class="fw-bold mb-2" style="font-size:13px;color:#e65100;"><i
                                        class="bi bi-person-x me-1"></i>Peserta Tidak Hadir ({{ $absentees->count() }})
                                </div>
                                <div class="alert-warn-box mb-3">
                                    ⚠️ Peserta ini terdaftar di event tapi tidak memiliki catatan hadir. Tidak masuk
                                    ranking.
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-sm" style="font-size:13px;">
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

        @endif {{-- end has_data --}}
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
        $(document).ready(function() {
            if ($('#rankingTable').length) {
                $('#rankingTable').DataTable({
                    responsive: true,
                    language: {
                        search: "Cari:",
                        lengthMenu: "Tampilkan _MENU_ data",
                        info: "_START_–_END_ dari _TOTAL_",
                        paginate: {
                            previous: "‹",
                            next: "›"
                        },
                        emptyTable: "Belum ada data."
                    },
                    order: [
                        [0, 'asc']
                    ],
                    pageLength: 25,
                });
            }
            if ($('#detailTable').length) {
                $('#detailTable').DataTable({
                    responsive: true,
                    language: {
                        search: "Cari:",
                        lengthMenu: "Tampilkan _MENU_ data",
                        info: "_START_–_END_ dari _TOTAL_",
                        paginate: {
                            previous: "‹",
                            next: "›"
                        }
                    },
                    order: [
                        [0, 'asc'],
                        [3, 'asc']
                    ],
                    pageLength: 25,
                });
            }
        });

        // Nav tabs
        function switchNavTab(id, btn) {
            document.querySelectorAll('.nav-tab-content').forEach(el => el.classList.remove('on'));
            document.querySelectorAll('.nav-tab-btn').forEach(b => b.classList.remove('on'));
            document.getElementById(id)?.classList.add('on');
            btn.classList.add('on');
            // Reinit DataTable after tab switch
            if (id === 'tab-ranking' && $.fn.DataTable.isDataTable('#rankingTable')) {
                $('#rankingTable').DataTable().columns.adjust();
            }
            if (id === 'tab-detail' && $.fn.DataTable.isDataTable('#detailTable')) {
                $('#detailTable').DataTable().columns.adjust();
            }
        }

        // Formula preview
        function updateFormula() {
            const b = document.getElementById('inp-benar').value || 0;
            const s = document.getElementById('inp-salah').value || 0;
            const k = document.getElementById('inp-kosong').value || 0;
            document.getElementById('formula-preview').textContent =
                `Skor = (B × ${b}) + (S × ${s}) + (K × ${k})`;
        }

        // File upload zone
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
            document.getElementById('file-label').textContent = 'Klik atau drag file Excel ke sini';
            document.getElementById('file-preview').style.display = 'none';
            document.getElementById('btn-upload').disabled = true;
            document.getElementById('drop-zone').style.borderColor = '#e0e0e0';
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

        // Submit loading state
        document.getElementById('upload-form')?.addEventListener('submit', function() {
            const btn = document.getElementById('btn-upload');
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';
            btn.disabled = true;
        });
    </script>
@endpush
