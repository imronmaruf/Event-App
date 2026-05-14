<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ $event->name }} — Presensi</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#9f1239">

    {{-- Tailwind CSS CDN --}}
    {{-- <script src="https://cdn.tailwindcss.com"></script> --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Outfit"', 'system-ui', 'sans-serif'],
                        mono: ['"DM Mono"', 'monospace'],
                    },
                    colors: {
                        brand: {
                            50: '#fff0f3',
                            100: '#ffe4ea',
                            500: '#be123c',
                            600: '#9f1239',
                            700: '#881337'
                        },
                        rose: {
                            400: '#fb7185',
                            500: '#f43f5e'
                        },
                    },
                    borderRadius: {
                        '4xl': '2rem'
                    },
                    boxShadow: {
                        'brand': '0 8px 32px rgba(159,18,57,0.28)',
                        'card': '0 2px 20px rgba(0,0,0,0.07), 0 1px 4px rgba(0,0,0,0.04)',
                        'card-lg': '0 8px 40px rgba(0,0,0,0.10)',
                        'emerald': '0 4px 18px rgba(5,150,105,0.28)',
                    }
                }
            }
        }
    </script>

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=DM+Mono:wght@400;500&display=swap"
        rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/public_attendance/style.css') }}?v=1.0.3">
</head>

<body>

    <div class="max-w-xl mx-auto flex flex-col min-h-screen app-wrap">

        {{-- ══════════ HERO ══════════ --}}
        <div class="pt-8 pb-4 px-5 text-center text-white">

            {{-- Logo badge with spinning ring --}}
            <div class="animate-logo logo-wrap">
                <div class="logo-ring"></div>
                <div class="logo-inner">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo" class="w-14 h-14 object-contain"
                        onerror="this.replaceWith(Object.assign(document.createElement('span'),{textContent:'🏆',style:'font-size:2.2rem;line-height:1'}))">
                </div>
            </div>

            <h1 class="text-xl font-black leading-tight tracking-tight" style="text-shadow:0 2px 14px rgba(0,0,0,.35)">
                {{ $event->name }}
            </h1>
            <p class="text-xs mt-1.5 font-medium" style="color:rgba(255,255,255,.75)">
                {{ $event->unit->name }}
                @if ($event->city)
                    &middot; {{ $event->city->name }}
                @endif
            </p>

            {{-- Meta chips --}}
            <div class="flex justify-center flex-wrap gap-2 mt-4">
                @if ($event->event_date)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold"
                        style="background:rgba(255,255,255,.15);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,.2)">
                        {{-- Calendar icon --}}
                        <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                            stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" />
                            <line x1="16" y1="2" x2="16" y2="6" />
                            <line x1="8" y1="2" x2="8" y2="6" />
                            <line x1="3" y1="10" x2="21" y2="10" />
                        </svg>
                        {{ $event->event_date->format('d/m/Y') }}
                    </span>
                @endif
                @if ($event->event_time)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold"
                        style="background:rgba(255,255,255,.15);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,.2)">
                        <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                            stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10" />
                            <polyline points="12 6 12 12 16 14" />
                        </svg>
                        {{ $event->event_time }}
                    </span>
                @endif
                @if ($event->venue)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold"
                        style="background:rgba(255,255,255,.15);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,.2)">
                        <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                            <circle cx="12" cy="10" r="3" />
                        </svg>
                        {{ $event->venue }}
                    </span>
                @endif
            </div>
        </div>

        {{-- ══════════ NON-AKTIF ══════════ --}}
        @if (!$event->is_active)
            <div class="mx-4 my-4 flex-1 flex flex-col items-center justify-center text-center text-white rounded-3xl p-"
                style="background:rgba(255,255,255,.10);backdrop-filter:blur(18px);
                    border:1.5px solid rgba(255,255,255,.18);animation:fadeUp .5s ease">

                <div class="animate-pulse-slow mb-5" style="font-size:3.5rem">🔒</div>
                <div class="text-2xl font-black mb-2">Absensi Belum Dibuka</div>
                <p class="text-sm leading-relaxed max-w-xs" style="color:rgba(255,255,255,.78)">
                    Event ini belum diaktifkan oleh panitia.<br>
                    Silakan hubungi admin untuk membuka absensi.
                </p>

                <div class="mt-7 w-full max-w-xs text-left rounded-2xl p-5"
                    style="background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.22)">
                    <div class="mb-4">
                        <div class="text-xs font-bold uppercase tracking-widest mb-1"
                            style="color:rgba(255,255,255,.55)">Unit Penyelenggara</div>
                        <div class="text-sm font-extrabold">{{ $event->unit->name }}</div>
                    </div>
                    @if ($event->unit->contact_person)
                        <div class="mb-4">
                            <div class="text-xs font-bold uppercase tracking-widest mb-1"
                                style="color:rgba(255,255,255,.55)">Penanggung Jawab</div>
                            <div class="text-sm font-extrabold">{{ $event->unit->contact_person }}</div>
                        </div>
                    @endif
                    @if ($event->unit->contact_phone)
                        <div>
                            <div class="text-xs font-bold uppercase tracking-widest mb-1"
                                style="color:rgba(255,255,255,.55)">Kontak</div>
                            <div class="text-sm font-extrabold">{{ $event->unit->contact_phone }}</div>
                        </div>
                    @endif
                </div>

                <button onclick="location.reload()"
                    class="mt-6 px-7 py-2.5 rounded-full text-sm font-bold cursor-pointer
                           transition-all flex items-center gap-2"
                    style="background:rgba(255,255,255,.18);border:1.5px solid rgba(255,255,255,.32);color:#fff">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                        stroke-linejoin="round">
                        <polyline points="23 4 23 10 17 10" />
                        <polyline points="1 20 1 14 7 14" />
                        <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15" />
                    </svg>
                    Muat Ulang
                </button>
                <div class="mt-5 text-xs" style="color:rgba(255,255,255,.4)">
                    Halaman aktif setelah admin membuka absensi
                </div>
            </div>

            {{-- ══════════ AKTIF ══════════ --}}
        @else
            {{-- STATS --}}
            <div class="grid grid-cols-3 gap-2.5 px-2 pt-2">
                <div class="sc rounded-2xl py-4 text-center text-white"
                    style="background:rgba(255,255,255,.17);backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,.12)">
                    <div class="text-4xl font-black leading-none" id="s-total">{{ $stats['total'] }}</div>
                    <div class="text-xs font-bold mt-2 uppercase tracking-widest" style="color:rgba(255,255,255,.65)">
                        Total</div>
                </div>
                <div class="sc rounded-2xl py-4 text-center text-white"
                    style="background:rgba(5,150,105,.52);backdrop-filter:blur(12px);border:1px solid rgba(52,211,153,.22)">
                    <div class="text-4xl font-black leading-none" id="s-hadir">{{ $stats['hadir'] }}</div>
                    <div class="text-xs font-bold mt-2 uppercase tracking-widest" style="color:rgba(255,255,255,.78)">
                        Hadir</div>
                </div>
                <div class="sc rounded-2xl py-4 text-center text-white"
                    style="background:rgba(194,65,12,.52);backdrop-filter:blur(12px);border:1px solid rgba(251,146,60,.22)">
                    <div class="text-4xl font-black leading-none" id="s-belum">{{ $stats['belum'] }}</div>
                    <div class="text-xs font-bold mt-2 uppercase tracking-widest" style="color:rgba(255,255,255,.78)">
                        Belum</div>
                </div>
            </div>

            {{-- PROGRESS BAR --}}
            <div class="px-4 pt-3">
                <div class="rounded-full h-2 overflow-hidden" style="background:rgba(255,255,255,.15)">
                    <div id="prog" class="h-full rounded-full transition-all duration-700"
                        style="width:{{ $stats['persen'] }}%;
                            background:linear-gradient(90deg,#34d399,#10b981,#059669);
                            box-shadow:0 0 14px rgba(52,211,153,.5)">
                    </div>
                </div>
                <div class="text-right text-xs font-semibold mt-1.5" style="color:rgba(255,255,255,.72)"
                    id="prog-lbl">
                    {{ $stats['persen'] }}% hadir
                </div>
            </div>

            {{-- TABS --}}
            <div class="flex mx-4 mt-3 rounded-2xl gap-1 p-1.5"
                style="background:rgba(0,0,0,.28);backdrop-filter:blur(10px)">

                {{-- Tab: Absensi --}}
                <button class="tab on" onclick="switchTab('scan',this)">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 20h9" />
                        <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z" />
                    </svg>
                    Absensi
                </button>

                {{-- Tab: Peserta --}}
                <button class="tab" onclick="switchTab('peserta',this)">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                        <circle cx="9" cy="7" r="4" />
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                        <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                    </svg>
                    Peserta
                </button>

                {{-- Tab: Riwayat --}}
                <button class="tab" onclick="switchTab('riwayat',this)">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                        <polyline points="14 2 14 8 20 8" />
                        <line x1="16" y1="13" x2="8" y2="13" />
                        <line x1="16" y1="17" x2="8" y2="17" />
                        <polyline points="10 9 9 9 8 9" />
                    </svg>
                    Riwayat
                </button>

                {{-- Tab: Ruang --}}
                <button class="tab" onclick="switchTab('ruang',this)">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                        <polyline points="9 22 9 12 15 12 15 22" />
                    </svg>
                    Ruang
                </button>
            </div>

            {{-- ════ TAB SCAN ════ --}}
            <div class="page on px-4 pt-3" id="tab-scan">
                <div class="bg-white rounded-3xl p-5 mb-3"
                    style="box-shadow:0 2px 20px rgba(0,0,0,.08);border:1px solid rgba(0,0,0,.04)">

                    <div class="card-hd">
                        <div class="card-hd-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path
                                    d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4" />
                            </svg>
                        </div>
                        Input Kode Absensi
                    </div>

                    @php $digit = $event->digit_count ?? 4; @endphp
                    <div class="hint" id="hint">
                        Masukkan {{ $event->digit_count ? $digit . ' digit' : '' }} kode absensi
                    </div>

                    @if ($digit <= 6)
                        <div class="flex gap-2.5 justify-center my-4 flex-wrap" id="digit-boxes">
                            @for ($i = 0; $i < $digit; $i++)
                                <input type="tel" maxlength="1" class="digit-box" id="db{{ $i }}"
                                    inputmode="numeric" pattern="[0-9]*" autocomplete="off"
                                    onkeydown="dkd(event,{{ $i }})"
                                    oninput="di(event,{{ $i }})" onpaste="dp(event)">
                            @endfor
                        </div>
                    @else
                        <div class="my-4">
                            <input type="tel" id="single-inp" class="single-inp"
                                placeholder="{{ str_repeat('•', $digit) }}" maxlength="{{ $digit }}"
                                inputmode="numeric" autocomplete="off" oninput="si(this)"
                                onkeydown="if(event.key==='Enter')findP()">
                        </div>
                    @endif

                    <div class="hint text-center" id="hint-sub" style="margin-top:2px;font-size:11px">
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
                        <div class="confirm-row" id="pv-confirm" style="display:none">
                            <button class="btn-confirm" id="btn-confirm" onclick="markHadir()">
                                ✅ KONFIRMASI HADIR
                            </button>
                            <button class="btn-xcancel" onclick="resetInput()">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="18" y1="6" x2="6" y2="18" />
                                    <line x1="6" y1="6" x2="18" y2="18" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Action buttons --}}
                    <div class="flex gap-2.5 mt-5">
                        <button id="btn-cari" onclick="findP()"
                            class="flex-1 py-3.5 rounded-2xl font-bold text-sm text-white cursor-pointer
                                   transition-all flex items-center justify-center gap-2"
                            style="background:linear-gradient(135deg,var(--brand),var(--orange));
                                   box-shadow:0 4px 20px rgba(190,18,60,.35)">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                                stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8" />
                                <line x1="21" y1="21" x2="16.65" y2="16.65" />
                            </svg>
                            Cari Peserta
                        </button>
                        <button onclick="resetInput()"
                            class="w-12 flex items-center justify-center rounded-2xl cursor-pointer transition-all"
                            style="background:#f1f5f9;border:1.5px solid var(--border);color:var(--text2)">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                                stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="1 4 1 10 7 10" />
                                <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="text-center pb-2 text-xs font-medium" style="color:rgba(255,255,255,.52)">
                    {{ $digit }} digit &middot; {{ $event->unit->name }}
                </div>
            </div>

            {{-- ════ TAB PESERTA ════ --}}
            <div class="page px-4 pt-3" id="tab-peserta">
                <div class="bg-white rounded-3xl p-5 mb-3"
                    style="box-shadow:0 2px 20px rgba(0,0,0,.08);border:1px solid rgba(0,0,0,.04)">
                    <div class="card-hd">
                        <div class="card-hd-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                                <circle cx="9" cy="7" r="4" />
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                                <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                            </svg>
                        </div>
                        <span class="flex-1">Daftar Peserta</span>
                        <button class="btn-rf card-variant" onclick="loadP(true)" id="rf-p">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                stroke-linejoin="round">
                                <polyline points="23 4 23 10 17 10" />
                                <polyline points="1 20 1 14 7 14" />
                                <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15" />
                            </svg>
                            Refresh
                        </button>
                    </div>
                    <div class="srch">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8" />
                            <line x1="21" y1="21" x2="16.65" y2="16.65" />
                        </svg>
                        <input type="text" placeholder="Cari nama, kode, NOREG, sekolah, kelas..."
                            oninput="filterP(this.value)">
                    </div>
                    <div class="flex gap-2 mb-3 flex-wrap">
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
            <div class="page px-4 pt-3" id="tab-riwayat">
                <div class="bg-white rounded-3xl p-5 mb-3"
                    style="box-shadow:0 2px 20px rgba(0,0,0,.08);border:1px solid rgba(0,0,0,.04)">
                    <div class="a-hdr">
                        <div class="card-hd mb-0" style="border:none;padding:0;margin:0">
                            <div class="card-hd-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                    <polyline points="14 2 14 8 20 8" />
                                    <line x1="16" y1="13" x2="8" y2="13" />
                                    <line x1="16" y1="17" x2="8" y2="17" />
                                    <polyline points="10 9 9 9 8 9" />
                                </svg>
                            </div>
                            Riwayat Check-in
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="a-count" id="a-count">–</span>
                            <button class="btn-rf card-variant" onclick="loadR(true)" id="rf-r">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <polyline points="23 4 23 10 17 10" />
                                    <polyline points="1 20 1 14 7 14" />
                                    <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="a-list" id="a-list">
                        <div class="loading"><span></span><span></span><span></span></div>
                    </div>
                </div>
            </div>

            {{-- ════ TAB RUANG ════ --}}
            <div class="page px-4 pt-3" id="tab-ruang">
                <div class="bg-white rounded-3xl p-5 mb-3"
                    style="box-shadow:0 2px 20px rgba(0,0,0,.08);border:1px solid rgba(0,0,0,.04)">
                    <div class="card-hd">
                        <div class="card-hd-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                                <polyline points="9 22 9 12 15 12 15 22" />
                            </svg>
                        </div>
                        <span class="flex-1">Per Ruang &amp; Pengawas</span>
                        <button class="btn-rf card-variant" onclick="loadRg(true)" id="rf-rg">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                stroke-linejoin="round">
                                <polyline points="23 4 23 10 17 10" />
                                <polyline points="1 20 1 14 7 14" />
                                <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15" />
                            </svg>
                        </button>
                    </div>

                    {{-- Room summary --}}
                    <div class="rg-sum grid grid-cols-3 gap-3 mb-5" id="rg-sum" style="display:none!important">
                        <div class="rg-si rounded-2xl p-3 text-center"
                            style="background:#fff0f3;border:1.5px solid #fecdd3">
                            <div class="text-2xl font-black" id="rg-n-ruang" style="color:var(--brand-dk)">–</div>
                            <div class="text-xs font-bold uppercase tracking-wide mt-1" style="color:var(--brand)">
                                Ruang</div>
                        </div>
                        <div class="rg-si rounded-2xl p-3 text-center"
                            style="background:#f0fdf4;border:1.5px solid #86efac">
                            <div class="text-2xl font-black" id="rg-n-hadir" style="color:var(--emerald)">–</div>
                            <div class="text-xs font-bold uppercase tracking-wide mt-1" style="color:var(--emerald)">
                                Hadir</div>
                        </div>
                        <div class="rg-si rounded-2xl p-3 text-center"
                            style="background:#eff6ff;border:1.5px solid #93c5fd">
                            <div class="text-2xl font-black" id="rg-n-total" style="color:#1d4ed8">–</div>
                            <div class="text-xs font-bold uppercase tracking-wide mt-1" style="color:#3b82f6">Peserta
                            </div>
                        </div>
                    </div>

                    <div class="srch" id="rg-srch" style="display:none">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8" />
                            <line x1="21" y1="21" x2="16.65" y2="16.65" />
                        </svg>
                        <input type="text" placeholder="Cari ruang atau pengawas..."
                            oninput="filterRg(this.value)">
                    </div>

                    <div class="rg-list" id="rg-list">
                        <div class="loading"><span></span><span></span><span></span></div>
                    </div>
                </div>
            </div>

        @endif {{-- end is_active --}}

    </div><!-- end app-wrap -->

    {{-- ════ FIXED FOOTER ════ --}}
    <footer class="footer-fixed">
        <div class="max-w-xl mx-auto flex items-center justify-between px-5 py-2.5">
            <div class="flex items-center gap-2">
                <div class="w-6 h-6 rounded-full overflow-hidden flex-shrink-0"
                    style="background:linear-gradient(135deg,#fbbf24,#be123c)">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo" class="w-full h-full object-contain"
                        onerror="this.style.display='none'">
                </div>
                <span class="text-white text-xs font-bold" style="opacity:.75">
                    {{ $event->unit->name ?? 'Presensi' }}
                </span>
            </div>
            <div class="text-xs font-semibold" style="color:rgba(255,255,255,.45)">
                @if ($event->event_date)
                    {{ $event->event_date->format('d/m/Y') }}
                @endif
            </div>
            <div class="text-xs" style="color:rgba(255,255,255,.3)">Made With ❤️ by ImronMF</div>
        </div>
    </footer>

    {{-- Overlay --}}
    <div class="ov" id="ov">
        <div class="bg-white rounded-3xl p-8 text-center"
            style="box-shadow:0 24px 64px rgba(0,0,0,.35);min-width:180px">
            <div class="ov-spin"></div>
            <div class="text-sm font-bold" style="color:var(--text2)" id="ov-lbl">Memproses...</div>
        </div>
    </div>

    <div id="toast"></div>

    @include('attendance.script')
    @stack('js')

</body>

</html>
