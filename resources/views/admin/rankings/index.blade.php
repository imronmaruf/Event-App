@extends('layouts.admin')
@section('title', 'Hasil & Ranking — ' . $event->name)
@section('page-title', 'Hasil Perlombaan')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <link rel="stylesheet" href="{{ asset('css/admin/rangkings/style.css') }}">
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

    {{-- ── Info Header ── --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div>
            <h5 class="fw-bold mb-0" style="color:#c62828;">🏆 {{ $event->name }}</h5>
            <small class="text-muted">
                <i class="bi bi-building me-1"></i>{{ $event->unit->name }}
                @if ($event->city)
                    · {{ $event->city->name }}
                @endif
            </small>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <div class="stat-pill"><span class="dot" style="background:#2e7d32;"></span> Data Valid:
                <strong>{{ $stats['total_valid'] }}</strong>
            </div>
            @if ($stats['invalid'] > 0)
                <div class="stat-pill"><span class="dot" style="background:#c62828;"></span> Tidak terdaftar:
                    <strong>{{ $stats['invalid'] }}</strong>
                </div>
            @endif
            @if ($stats['absent'] > 0)
                <div class="stat-pill"><span class="dot" style="background:#e65100;"></span> Tidak hadir:
                    <strong>{{ $stats['absent'] }}</strong>
                </div>
            @endif
            @if ($stats['has_data'])
                <div class="stat-pill"><span class="dot" style="background:#1565c0;"></span> Nilai Tertinggi:
                    <strong>{{ $stats['max_score'] }}</strong>
                </div>
                <div class="stat-pill"><span class="dot" style="background:#7b1fa2;"></span> Rata-rata:
                    <strong>{{ $stats['avg_score'] }}</strong>
                </div>
            @endif
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════
         BARIS 1 — 3 kolom sejajar, tinggi sama
         [Konfigurasi Penilaian] | [Upload + Format vertikal] | [Podium]
    ═══════════════════════════════════════════════════════════════════ --}}
    <div class="row g-3 mb-3 align-items-stretch">

        {{-- ── Kolom 1: Konfigurasi Penilaian ── --}}
        <div class="col-12 col-lg-4">
            <div class="page-card h-100">
                <div class="card-hd">
                    <div class="card-hd-title d-flex align-items-center gap-3">

                        <div class="d-flex align-items-center justify-content-center bg-warning bg-gradient rounded-3 text-white"
                            style="width:40px; height:40px;">
                            <i class="bi bi-gear-fill fs-5"></i>
                        </div>

                        <div>
                            <div class="fw-bold text-warning-emphasis">
                                Konfigurasi Penilaian
                            </div>

                            <div class="small text-muted fw-normal">
                                Atur poin per jenis jawaban
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
                                style="border-radius:9px;border:1.5px solid #e0e0e0;font-size:12px;">{{ $config->scoring_note }}</textarea>
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

        {{-- ── Kolom 2: Upload + Format (vertikal, mengisi tinggi kolom 1) ── --}}
        <div class="col-12 col-lg-4 d-flex flex-column" style="gap:12px;">

            {{-- Upload card — flex-grow mengisi sisa ruang ── --}}
            <div class="page-card flex-grow-1">
                <div class="card-hd">
                    <div class="card-hd-title">
                        <div class="card-hd-icon" style="background:linear-gradient(135deg,#1565c0,#1976d2);">📥</div>
                        <div>
                            <div style="color:#1565c0;">Upload Hasil Ujian</div>
                            <div style="font-size:11px;color:#888;font-weight:400;">File Excel dari sistem ujian</div>
                        </div>
                    </div>
                    <a href="{{ route('admin.rankings.template', $event) }}" class="btn btn-sm"
                        style="background:#e3f2fd;color:#1565c0;border:none;border-radius:8px;font-size:11px;font-weight:700;">
                        <i class="bi bi-download me-1"></i> Template
                    </a>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.rankings.upload', $event) }}" method="POST"
                        enctype="multipart/form-data" id="upload-form">
                        @csrf
                        <div class="upload-zone" id="drop-zone" onclick="document.getElementById('file-input').click()"
                            ondragover="onDrag(event,true)" ondragleave="onDrag(event,false)" ondrop="onDrop(event)">
                            <span class="icon">📊</span>
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

            {{-- Format card — tinggi natural (tidak grow) ── --}}
            <div class="page-card">
                <div class="card-hd" style="padding:12px 16px;">
                    <div class="card-hd-title">
                        <div class="card-hd-icon" style="background:#f5f5f5;font-size:14px;">📋</div>
                        <span style="color:#555;">Format Kolom Excel</span>
                    </div>
                </div>
                <div class="card-body" style="padding:10px 14px;">
                    <table class="table table-sm mb-0" style="font-size:11.5px;">
                        <thead>
                            <tr>
                                <th>Kolom</th>
                                <th style="width:40px;">Wajib</th>
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

        </div>{{-- /kolom 2 --}}

        {{-- ── Kolom 3: Podium ── --}}
        <div class="col-12 col-lg-4">
            <div class="page-card h-100">
                <div class="card-hd">
                    <div class="card-hd-title">
                        <div class="card-hd-icon" style="background:linear-gradient(135deg,#f9a825,#f57f17);">🏆</div>
                        <span style="color:#e65100;">Podium Pemenang</span>
                    </div>
                </div>
                <div class="card-body">
                    @if (!$stats['has_data'])
                        <div class="empty-state" style="padding:24px 10px;">
                            <span class="icon">🏆</span>
                            <h5 style="font-size:14px;">Belum Ada Data</h5>
                            <p>Upload hasil ujian untuk menampilkan podium.</p>
                        </div>
                    @else
                        @php
                            $r1 = $rankings->firstWhere('rank', 1);
                            $r2 = $rankings->firstWhere('rank', 2);
                            $r3 = $rankings->firstWhere('rank', 3);
                        @endphp
                        <div class="podium-wrap">
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
                            @if ($r1)
                                <div class="podium-item p1">
                                    <div class="podium-medal">🥇</div>
                                    <div class="podium-name fw-bold" style="font-size:12px;">
                                        {{ $r1->participant?->name ?? $r1->noreg }}</div>
                                    <div class="podium-noreg">{{ $r1->noreg }}</div>
                                    <div class="podium-sekolah">{{ $r1->participant?->school ?? '' }}</div>
                                    <div class="podium-block">
                                        <div class="podium-rank-num">1</div>
                                        <div class="podium-score" style="font-size:16px;">{{ $r1->total_score }}</div>
                                    </div>
                                </div>
                            @endif
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

                        {{-- Mini stats ── --}}
                        <div class="d-flex flex-column mt-3" style="gap:8px;">
                            <div class="mini-stat">
                                <span style="color:#666;">Peserta Valid</span>
                                <strong>{{ $stats['total_valid'] }}</strong>
                            </div>
                            <div class="mini-stat">
                                <span style="color:#666;">Skor Tertinggi</span>
                                <strong style="color:#c62828;">{{ $stats['max_score'] }}</strong>
                            </div>
                            <div class="mini-stat">
                                <span style="color:#666;">Rata-rata Skor</span>
                                <strong style="color:#1565c0;">{{ $stats['avg_score'] }}</strong>
                            </div>
                            @if ($stats['invalid'] > 0)
                                <div class="mini-stat">
                                    <span style="color:#666;">NOREG Tidak Valid</span>
                                    <strong style="color:#c62828;">{{ $stats['invalid'] }}</strong>
                                </div>
                            @endif
                            @if ($stats['absent'] > 0)
                                <div class="mini-stat">
                                    <span style="color:#666;">Tidak Hadir</span>
                                    <strong style="color:#e65100;">{{ $stats['absent'] }}</strong>
                                </div>
                            @endif
                        </div>

                        @if ($config->scoring_note)
                            <div class="alert-info-box mt-3">
                                📝 <strong>Aturan:</strong> {{ $config->scoring_note }}
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>

    </div>{{-- /row baris 1 --}}


    {{-- ══════════════════════════════════════════════════════════════════
         BARIS 2 — DataTable full lebar (tabs: Ranking | Detail | Invalid)
    ═══════════════════════════════════════════════════════════════════ --}}
    @if (!$stats['has_data'])
        <div class="page-card">
            <div class="card-body">
                <div class="empty-state">
                    <span class="icon">📊</span>
                    <h5>Belum Ada Data Hasil</h5>
                    <p>Upload file Excel hasil ujian di atas<br>untuk menampilkan tabel ranking peserta.</p>
                </div>
            </div>
        </div>
    @else
        <div class="page-card">
            <div class="card-body pb-2">

                <div class="nav-tabs-custom">
                    <button class="nav-tab-btn on" onclick="switchNavTab('tab-ranking', this)">
                        📊 Ranking Lengkap
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

                {{-- ── TAB: Ranking ── --}}
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
                                    <tr>
                                        <td>
                                            {{-- <span class="rank-n">{{ $r->rank }}</span> --}}
                                            @if ($r->rank === 1)
                                                <span class="rank-1">1</span>
                                            @elseif($r->rank === 2)
                                                <span class="rank-2">2</span>
                                            @elseif($r->rank === 3)
                                                <span class="rank-3">3</span>
                                            @else
                                                <span class="rank-n">{{ $r->rank }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <code class="small bg-light px-2 py-1 rounded">
                                                {{ $r->noreg }}
                                            </code>
                                        </td>

                                        <td>
                                            <div class="fw-bold medium">
                                                {{ $r->participant?->name ?? '-' }}
                                            </div>

                                            @if ($r->participant?->room)
                                                <div class="text-muted small">
                                                    <i class="bi bi-door-open me-1"></i>{{ $r->participant->room }}
                                                </div>
                                            @endif
                                        </td>

                                        <td>{{ $r->participant?->class ?? '-' }}</td>
                                        <td>
                                            {{ $r->participant?->school ?? '-' }}
                                        </td>
                                        <td>
                                            {{-- Bar Nilai --}}
                                            {{-- @php
                                                $maxS = $stats['max_score'] ?: 1;
                                                $pct = min(100, round(($r->total_score / $maxS) * 100));
                                            @endphp
                                            <div class="score-bar-wrap">
                                                <div class="score-bar-bg">
                                                    <div class="score-bar-fill" style="width:{{ $pct }}%"></div>
                                                </div>
                                            </div> --}}
                                            <span class="score-val">{{ $r->total_score }}</span>
                                        </td>
                                        <td><span class="fw-bold text-success">{{ $r->total_benar }}</span></td>
                                        <td><span class="fw-bold text-danger">{{ $r->total_salah }}</span></td>
                                        <td><span class="fw-bold text-muted">{{ $r->total_kosong }}</span></td>
                                        <td class="small text-secondary text-nowrap">
                                            {{ $r->waktu_akhir?->format('H:i:s') ?? '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- ── TAB: Detail per soal ── --}}
                <div class="nav-tab-content" id="tab-detail">
                    @if ($examDetails->isEmpty())
                        <div class="empty-state py-4"><span class="icon">📝</span>
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

                {{-- ── TAB: Data bermasalah ── --}}
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

            if ($('#rankingTable').length) {
                $('#rankingTable').DataTable({
                    responsive: true,
                    pageLength: 25,
                    order: [
                        [0, 'asc']
                    ],
                    language: {
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
                    },
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
                    language: {
                        search: "Cari:",
                        lengthMenu: "Tampilkan _MENU_ data",
                        info: "_START_–_END_ dari _TOTAL_",
                        paginate: {
                            previous: '‹',
                            next: '›'
                        },
                    },
                });
            }
        });

        // ── Nav tabs ────────────────────────────────────────────────
        function switchNavTab(id, btn) {
            document.querySelectorAll('.nav-tab-content').forEach(el => el.classList.remove('on'));
            document.querySelectorAll('.nav-tab-btn').forEach(b => b.classList.remove('on'));
            document.getElementById(id)?.classList.add('on');
            btn.classList.add('on');
            if (id === 'tab-ranking' && $.fn.DataTable.isDataTable('#rankingTable'))
                $('#rankingTable').DataTable().columns.adjust();
            if (id === 'tab-detail' && $.fn.DataTable.isDataTable('#detailTable'))
                $('#detailTable').DataTable().columns.adjust();
        }

        // ── Formula preview ─────────────────────────────────────────
        function updateFormula() {
            const b = document.getElementById('inp-benar').value || 0;
            const s = document.getElementById('inp-salah').value || 0;
            const k = document.getElementById('inp-kosong').value || 0;
            document.getElementById('formula-preview').textContent =
                `Skor = (B × ${b}) + (S × ${s}) + (K × ${k})`;
        }

        // ── File upload zone ────────────────────────────────────────
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

        // ── Submit loading state ────────────────────────────────────
        document.getElementById('upload-form')?.addEventListener('submit', function() {
            const btn = document.getElementById('btn-upload');
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';
            btn.disabled = true;
        });
    </script>
@endpush
