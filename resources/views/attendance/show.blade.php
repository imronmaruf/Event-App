<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <title>{{ $event->name }} — Presensi</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#b71c1c">
    <link rel="stylesheet" href="{{ asset('css/public_attendance/style.css') }}">
</head>

<body>
    <div class="wrap">

        {{-- ══ HERO ══ --}}
        <div class="hero">
            <div class="hero-logo">🏆</div>
            <h1>{{ $event->name }}</h1>
            <p class="sub">{{ $event->unit->name }}@if ($event->city)
                    · {{ $event->city->name }}
                @endif
            </p>
            <div class="event-meta">
                @if ($event->event_date)
                    <span class="meta-chip">📅 {{ $event->event_date->format('d/m/Y') }}</span>
                @endif
                @if ($event->event_time)
                    <span class="meta-chip">⏰ {{ $event->event_time }}</span>
                @endif
                @if ($event->venue)
                    <span class="meta-chip">📍 {{ $event->venue }}</span>
                @endif
            </div>
        </div>

        {{-- ════════════════════════════════ EVENT NON-AKTIF ════════════════════════════════ --}}
        @if (!$event->is_active)
            <div class="inactive-screen">
                <span class="inactive-icon">🔒</span>
                <div class="inactive-title">Absensi Belum Dibuka</div>
                <p class="inactive-desc">
                    Event ini belum diaktifkan oleh panitia.<br>
                    Silakan hubungi admin unit untuk membuka absensi.
                </p>
                <div class="contact-card">
                    <div class="row-item">
                        <div class="lbl">📋 Unit Penyelenggara</div>
                        <div class="val">{{ $event->unit->name }}</div>
                    </div>
                    @if ($event->unit->contact_person)
                        <div class="row-item">
                            <div class="lbl">👤 Penanggung Jawab</div>
                            <div class="val">{{ $event->unit->contact_person }}</div>
                        </div>
                    @endif
                    @if ($event->unit->contact_phone)
                        <div class="row-item">
                            <div class="lbl">📞 Kontak</div>
                            <div class="val">{{ $event->unit->contact_phone }}</div>
                        </div>
                    @endif
                </div>
                <button class="btn-reload" onclick="location.reload()">🔄 Muat Ulang</button>
                <div style="margin-top:16px;font-size:11px;opacity:.55;">Halaman akan bisa digunakan setelah admin
                    mengaktifkan absensi.</div>
            </div>

            {{-- ════════════════════════════════ EVENT AKTIF ════════════════════════════════ --}}
        @else
            {{-- STATS --}}
            <div class="stats">
                <div class="sc sc-total">
                    <div class="n" id="s-total">{{ $stats['total'] }}</div>
                    <div class="l">Total</div>
                </div>
                <div class="sc sc-hadir">
                    <div class="n" id="s-hadir">{{ $stats['hadir'] }}</div>
                    <div class="l">Hadir</div>
                </div>
                <div class="sc sc-belum">
                    <div class="n" id="s-belum">{{ $stats['belum'] }}</div>
                    <div class="l">Belum</div>
                </div>
            </div>

            {{-- PROGRESS --}}
            <div class="prog-wrap">
                <div class="prog-bg">
                    <div class="prog-fill" id="prog" style="width:{{ $stats['persen'] }}%"></div>
                </div>
                <div class="prog-lbl" id="prog-lbl">{{ $stats['persen'] }}% hadir</div>
            </div>

            {{-- TABS --}}
            <div class="tabs">
                <button class="tab on" onclick="switchTab('scan',this)">✏️ Absensi</button>
                <button class="tab" onclick="switchTab('peserta',this)">👥 Peserta</button>
                <button class="tab" onclick="switchTab('riwayat',this)">📋 Riwayat</button>
                <button class="tab" onclick="switchTab('ruang',this)">🚪 Ruang</button>
            </div>

            {{-- ════ TAB SCAN ════ --}}
            <div class="page on" id="tab-scan">
                <div class="card">
                    <div class="card-hd">
                        <div class="card-hd-icon">🔑</div> Input Kode Absensi
                    </div>

                    @php $digit = $event->digit_count ?? 4; @endphp

                    <div class="hint" id="hint">
                        Masukkan {{ $event->digit_count ? $digit . ' digit' : '' }} kode absensi
                    </div>

                    @if ($digit <= 6)
                        {{-- KOTAK PER DIGIT --}}
                        <div class="digit-boxes" id="digit-boxes">
                            @for ($i = 0; $i < $digit; $i++)
                                <input type="tel" maxlength="1" class="digit-box" id="db{{ $i }}"
                                    inputmode="numeric" pattern="[0-9]*" autocomplete="off"
                                    onkeydown="dkd(event,{{ $i }})" oninput="di(event,{{ $i }})"
                                    onpaste="dp(event)">
                            @endfor
                        </div>
                    @else
                        {{-- INPUT TUNGGAL --}}
                        <div style="margin:12px 0 4px;">
                            <input type="tel" id="single-inp" class="single-inp"
                                placeholder="{{ str_repeat('•', $digit) }}" maxlength="{{ $digit }}"
                                inputmode="numeric" autocomplete="off" oninput="si(this)"
                                onkeydown="if(event.key==='Enter')findP()">
                        </div>
                    @endif

                    <div class="hint" id="hint-sub" style="margin-top:3px;font-size:11px;opacity:.65;">
                        @if ($event->digit_count)
                            {{ $digit }} digit dari bagian
                            {{ $event->digit_position === 'suffix' ? 'akhir' : 'awal' }} nomor registrasi
                        @endif
                    </div>

                    {{-- PREVIEW --}}
                    <div id="preview-box">
                        <div class="prev-hd">
                            <span class="prev-icon" id="pv-icon"></span>
                            <span class="prev-ttl" id="pv-ttl"></span>
                        </div>
                        <div class="info-grid" id="pv-grid"></div>
                        <div class="confirm-row" id="pv-confirm" style="display:none;">
                            <button class="btn-confirm" id="btn-confirm" onclick="markHadir()">✅ KONFIRMASI
                                HADIR</button>
                            <button class="btn-xcancel" onclick="resetInput()">✕</button>
                        </div>
                    </div>

                    <div class="btn-row">
                        <button class="btn btn-cari" id="btn-cari" onclick="findP()">🔍 Cari Peserta</button>
                        <button class="btn btn-rst" onclick="resetInput()">↩</button>
                    </div>
                </div>
                <div style="text-align:center;padding:2px 0 8px;color:rgba(255,255,255,.6);font-size:11px;">
                    🔑 {{ $digit }} digit · {{ $event->unit->name }}
                </div>
            </div>

            {{-- ════ TAB PESERTA ════ --}}
            <div class="page" id="tab-peserta">
                <div class="card">
                    <div class="card-hd">
                        <div class="card-hd-icon">👥</div>
                        <span class="flex-1">Daftar Peserta</span>
                        <button class="btn-rf card-variant" onclick="loadP(true)" id="rf-p">🔄</button>
                    </div>
                    <div class="srch"><span class="srch-ic">🔍</span>
                        <input type="text" placeholder="Cari nama, kode, NOREG, sekolah, kelas..."
                            oninput="filterP(this.value)">
                    </div>
                    <div class="chips">
                        <button class="chip on" onclick="setFlt('all',this)">Semua</button>
                        <button class="chip" onclick="setFlt('hadir',this)">✅ Hadir</button>
                        <button class="chip" onclick="setFlt('belum',this)">⏳ Belum</button>
                    </div>
                    <div class="p-list" id="p-list">
                        <div class="loading"><span></span><span></span><span></span></div>
                    </div>
                </div>
            </div>

            {{-- ════ TAB RIWAYAT ════ --}}
            <div class="page" id="tab-riwayat">
                <div class="card">
                    <div class="a-hdr">
                        <div class="card-hd mb-0" style="border:none;padding:0;margin:0;">
                            <div class="card-hd-icon">📋</div> Riwayat Check-in
                        </div>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <span class="a-count" id="a-count">–</span>
                            <button class="btn-rf card-variant" onclick="loadR(true)" id="rf-r">🔄</button>
                        </div>
                    </div>
                    <div class="a-list" id="a-list">
                        <div class="loading"><span></span><span></span><span></span></div>
                    </div>
                </div>
            </div>

            {{-- ════ TAB RUANG ════ --}}
            <div class="page" id="tab-ruang">
                <div class="card">
                    <div class="card-hd">
                        <div class="card-hd-icon">🚪</div>
                        <span class="flex-1">Per Ruang & Pengawas</span>
                        <button class="btn-rf card-variant" onclick="loadRg(true)" id="rf-rg">🔄</button>
                    </div>
                    <div class="rg-sum" id="rg-sum" style="display:none;">
                        <div class="rg-si">
                            <div class="n" id="rg-n-ruang" style="color:var(--r2)">–</div>
                            <div class="l">Ruang</div>
                        </div>
                        <div class="rg-si">
                            <div class="n" id="rg-n-hadir" style="color:var(--grn)">–</div>
                            <div class="l">Hadir</div>
                        </div>
                        <div class="rg-si">
                            <div class="n" id="rg-n-total" style="color:#1565c0">–</div>
                            <div class="l">Peserta</div>
                        </div>
                    </div>
                    <div class="srch" id="rg-srch" style="display:none;">
                        <span class="srch-ic">🔍</span>
                        <input type="text" placeholder="Cari ruang atau pengawas..."
                            oninput="filterRg(this.value)">
                    </div>
                    <div class="rg-list" id="rg-list">
                        <div class="loading"><span></span><span></span><span></span></div>
                    </div>
                </div>
            </div>

        @endif {{-- end is_active --}}

    </div><!-- .wrap -->

    <div class="ov" id="ov">
        <div class="ov-box">
            <div class="ov-spin"></div>
            <div class="ov-lbl" id="ov-lbl">Memproses...</div>
        </div>
    </div>
    <div id="toast"></div>


    @include('attendance.script')
    @stack('js')

</body>

</html>
