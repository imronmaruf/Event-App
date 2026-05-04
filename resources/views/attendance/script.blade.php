@push('js')
    <script>
        // ── Config ──
        const SLUG = '{{ $event->slug }}',
            DIGIT = {{ $event->digit_count ?? 'null' }},
            IS_ACTIVE = {{ $event->is_active ? 'true' : 'false' }};
        const BASE = '{{ url('') }}',
            CSRF = document.querySelector('meta[name=csrf-token]')?.content ?? '';
        let allP = [],
            allRg = [],
            flt = '',
            srchQ = '',
            rgQ = '',
            pendingId = null,
            submitting = false,
            toastTmr;

        // ── API ──
        async function api(path, method = 'GET', body = null) {
            const r = await fetch(`${BASE}/absensi/${SLUG}${path}`, {
                method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    'Accept': 'application/json'
                },
                ...(body ? {
                    body: JSON.stringify(body)
                } : {})
            });
            if (!r.ok) throw new Error(r.status);
            return r.json();
        }

        // ── Stats ──
        function updateStats(hadir, total) {
            const belum = Math.max(0, total - hadir),
                pct = total > 0 ? Math.round(hadir / total * 1000) / 10 : 0;
            ['s-hadir', 's-belum', 's-total'].forEach((id, i) => {
                const el = document.getElementById(id),
                    v = [hadir, belum, total][i];
                if (el) el.textContent = v;
            });
            const prog = document.getElementById('prog'),
                lbl = document.getElementById('prog-lbl');
            if (prog) prog.style.width = pct + '%';
            if (lbl) lbl.textContent = pct + '% hadir';
        }

        // ── Tabs ──
        function switchTab(tab, el) {
            document.querySelectorAll('.page').forEach(p => p.classList.remove('on'));
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('on'));
            document.getElementById('tab-' + tab).classList.add('on');
            el.classList.add('on');
            if (tab === 'peserta') loadP();
            if (tab === 'riwayat') loadR();
            if (tab === 'ruang') loadRg();
        }

        // ── Digit input (box mode) ──
        function dkd(e, i) {
            const bb = document.querySelectorAll('.digit-box');
            if (e.key === 'Backspace') {
                if (!bb[i].value && i > 0) {
                    bb[i - 1].value = '';
                    bb[i - 1].classList.remove('filled');
                    bb[i - 1].focus();
                } else {
                    bb[i].value = '';
                    bb[i].classList.remove('filled');
                }
                updHint();
                e.preventDefault();
            }
            if (e.key === 'ArrowLeft' && i > 0) {
                bb[i - 1].focus();
                e.preventDefault();
            }
            if (e.key === 'ArrowRight' && i < bb.length - 1) {
                bb[i + 1].focus();
                e.preventDefault();
            }
            if (e.key === 'Enter') {
                findP();
                e.preventDefault();
            }
        }

        function di(e, i) {
            const bb = document.querySelectorAll('.digit-box');
            const raw = e.target.value.replace(/\D/g, '');
            if (raw.length > 1) {
                raw.split('').forEach((c, j) => {
                    if (bb[i + j]) {
                        bb[i + j].value = c;
                        bb[i + j].classList.add('filled');
                    }
                });
                const nx = i + raw.length;
                if (bb[nx]) bb[nx].focus();
                else bb[bb.length - 1].focus();
            } else {
                e.target.value = raw;
                e.target.classList.toggle('filled', raw !== '');
                if (raw && i < bb.length - 1) bb[i + 1].focus();
            }
            updHint();
            if (getCode().length === DIGIT) setTimeout(findP, 120);
        }

        function dp(e) {
            e.preventDefault();
            const txt = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '');
            const bb = document.querySelectorAll('.digit-box');
            txt.split('').forEach((c, i) => {
                if (bb[i]) {
                    bb[i].value = c;
                    bb[i].classList.add('filled');
                }
            });
            updHint();
            if (txt.length >= DIGIT) setTimeout(findP, 120);
        }
        // ── Single input ──
        function si(inp) {
            inp.value = inp.value.replace(/\D/g, '');
            updHint();
            if (DIGIT && inp.value.length === DIGIT) setTimeout(findP, 120);
        }

        function getCode() {
            if (DIGIT && DIGIT <= 6) return [...document.querySelectorAll('.digit-box')].map(b => b.value).join('');
            return (document.getElementById('single-inp')?.value ?? '').replace(/\D/g, '');
        }

        function updHint() {
            const h = document.getElementById('hint'),
                code = getCode();
            if (!h || !DIGIT) return;
            const rem = DIGIT - code.length;
            h.textContent = rem > 0 ? `Masukkan ${rem} digit lagi` : 'Tekan Cari atau tunggu otomatis';
            h.className = 'hint' + (rem <= 0 ? ' ok' : '');
        }

        // ── CARI ──
        async function findP() {
            const code = getCode();
            if (!code) {
                toast('Masukkan kode terlebih dahulu', 'warn');
                return;
            }
            if (DIGIT && code.length !== DIGIT) {
                toast(`Kode harus ${DIGIT} digit`, 'warn');
                shakeBoxes();
                return;
            }
            showOv('Mencari peserta...');
            try {
                const d = await api('/cari', 'POST', {
                    code
                });
                hideOv();
                renderPreview(d);
            } catch {
                hideOv();
                toast('Gagal menghubungi server', 'err');
            }
        }

        function renderPreview(d) {
            const box = document.getElementById('preview-box'),
                cRow = document.getElementById('pv-confirm');
            box.style.display = 'block';
            cRow.style.display = 'none';
            pendingId = null;
            if (d.status === 'FOUND' && !d.sudahHadir) {
                const p = d.peserta;
                pendingId = p.id;
                box.className = 'found';
                document.getElementById('pv-icon').textContent = '✅';
                document.getElementById('pv-ttl').textContent = 'Peserta Ditemukan!';
                document.getElementById('pv-grid').innerHTML = buildGrid(p, false);
                cRow.style.display = 'flex';
                const btn = document.getElementById('btn-confirm');
                btn.disabled = false;
                btn.textContent = '✅ KONFIRMASI HADIR';
            } else if (d.status === 'FOUND' && d.sudahHadir) {
                box.className = 'already';
                document.getElementById('pv-icon').textContent = '⚠️';
                document.getElementById('pv-ttl').textContent = 'Sudah Hadir Sebelumnya!';
                document.getElementById('pv-grid').innerHTML = buildGrid(d.peserta, true, d.waktuHadir);
            } else {
                box.className = 'notfound';
                document.getElementById('pv-icon').textContent = d.status === 'EVENT_INACTIVE' ? '🔒' : '❌';
                document.getElementById('pv-ttl').textContent = d.status === 'EVENT_INACTIVE' ? 'Event Tidak Aktif' :
                    'Tidak Ditemukan';
                document.getElementById('pv-grid').innerHTML =
                    `<div style="grid-column:span 2;color:var(--r2);font-weight:600;font-size:13px;">${d.message}</div>`;
                shakeBoxes();
            }
        }

        function buildGrid(p, sudah, waktu = null) {
            return [
                ['Kode', `<span class="noreg-badge">${p.code??'-'}</span>`],
                ['NOREG', p.noreg],
                ['Nama', p.nama],
                p.kelas ? ['Kelas', p.kelas] : null,
                p.sekolah ? ['Sekolah', p.sekolah] : null,
                p.ruang ? ['Ruang', p.ruang + (p.pengawas ? ` · ${p.pengawas}` : '')] : null,
                sudah && waktu ? ['Waktu', `<strong style="color:var(--grn)">${waktu}</strong>`] : null,
            ].filter(Boolean).map(([l, v]) => `<span class="ig-l">${l}</span><span class="ig-v">${v}</span>`).join('');
        }

        // ── MARK HADIR ──
        async function markHadir() {
            if (!pendingId || submitting) return;
            submitting = true;
            const btn = document.getElementById('btn-confirm');
            btn.disabled = true;
            btn.textContent = '⏳ Menyimpan...';
            showOv('Mencatat kehadiran...');
            try {
                const d = await api('/hadir', 'POST', {
                    participant_id: pendingId
                });
                hideOv();
                submitting = false;
                if (d.status === 'SUCCESS') {
                    toast(`✅ ${d.peserta.nama} — Hadir!`, 'ok');
                    updateStats(d.stats.hadir, d.stats.total);
                    resetInput();
                    setTimeout(() => {
                        const f = document.querySelector('.digit-box');
                        if (f) f.focus();
                        else document.getElementById('single-inp')?.focus();
                    }, 300);
                } else {
                    toast('❌ ' + d.message, 'err');
                    btn.disabled = false;
                    btn.textContent = '✅ KONFIRMASI HADIR';
                }
            } catch {
                hideOv();
                submitting = false;
                toast('Gagal menyimpan', 'err');
                btn.disabled = false;
                btn.textContent = '✅ KONFIRMASI HADIR';
            }
        }

        function resetInput() {
            document.querySelectorAll('.digit-box').forEach(b => {
                b.value = '';
                b.classList.remove('filled', 'err');
            });
            const si = document.getElementById('single-inp');
            if (si) si.value = '';
            const box = document.getElementById('preview-box');
            box.style.display = 'none';
            box.className = '';
            document.getElementById('pv-confirm').style.display = 'none';
            pendingId = null;
            submitting = false;
            updHint();
            const f = document.querySelector('.digit-box');
            if (f) f.focus();
            else document.getElementById('single-inp')?.focus();
        }

        function shakeBoxes() {
            document.querySelectorAll('.digit-box').forEach(b => {
                b.classList.add('err');
                setTimeout(() => b.classList.remove('err'), 400);
            });
            const si = document.getElementById('single-inp');
            if (si) {
                si.classList.add('err');
                setTimeout(() => si.classList.remove('err'), 400);
            }
        }

        // ── TAB PESERTA ──
        async function loadP(force = false) {
            if (allP.length && !force) {
                renderP();
                return;
            }
            spin('p', true);
            document.getElementById('p-list').innerHTML =
                '<div class="loading"><span></span><span></span><span></span></div>';
            try {
                allP = await api('/peserta');
                renderP();
            } catch {
                document.getElementById('p-list').innerHTML =
                    '<div class="empty"><span class="ei">⚠️</span><p>Gagal memuat.</p></div>';
            }
            spin('p', false);
        }

        function setFlt(f, el) {
            flt = f;
            document.querySelectorAll('.chip').forEach(c => c.classList.remove('on'));
            el.classList.add('on');
            renderP();
        }

        function filterP(q) {
            srchQ = q.toLowerCase().trim();
            renderP();
        }

        function renderP() {
            const el = document.getElementById('p-list');
            let d = [...allP];
            if (flt === 'hadir') d = d.filter(p => p.hadir);
            if (flt === 'belum') d = d.filter(p => !p.hadir);
            if (srchQ) d = d.filter(p => [p.nama, p.code, p.noreg, p.sekolah, p.kelas, p.ruang].some(x => x?.toLowerCase()
                .includes(srchQ)));
            if (!d.length) {
                el.innerHTML = '<div class="empty"><span class="ei">🔍</span><p>Tidak ada data.</p></div>';
                return;
            }
            el.innerHTML = d.map((p, i) => `
    <div class="p-item ${p.hadir?'hadir':'belum'}">
        <div class="rank-no ${p.hadir?'rh':'rb'}">${i+1}</div>
        <span class="code-tag ${p.hadir?'ct-h':'ct-b'}">${p.code??'–'}</span>
        <div class="p-info">
            <div class="p-nama">${p.nama}</div>
            <div class="p-sub">${[p.noreg,p.kelas,p.sekolah,p.ruang].filter(Boolean).join(' · ')}</div>
        </div>
        ${p.hadir?`<div class="sp-time">${p.waktu??''}<br><span style="font-size:9px;color:#aaa">${p.tgl??''}</span></div>`:`<span class="sp sp-b">Belum</span>`}
    </div>`).join('');
        }

        // ── TAB RIWAYAT ──
        async function loadR(force = false) {
            spin('r', true);
            document.getElementById('a-list').innerHTML =
                '<div class="loading"><span></span><span></span><span></span></div>';
            try {
                const d = await api('/riwayat');
                const cnt = document.getElementById('a-count');
                if (cnt) cnt.textContent = d.length + ' orang';
                document.getElementById('a-list').innerHTML = d.length ?
                    d.map((r, i) =>
                        `<div class="a-item"><div class="a-seq">${i+1}</div><div class="a-info"><div class="a-nama">${r.nama}</div><div class="a-sub">${[r.code,r.noreg,r.kelas,r.sekolah,r.ruang].filter(Boolean).join(' · ')}</div></div><div class="a-time"><div class="a-jam">${r.waktu}</div><div class="a-tgl">${r.tgl}</div></div></div>`
                    ).join('') :
                    '<div class="empty"><span class="ei">📋</span><p>Belum ada yang check-in.</p></div>';
            } catch {
                document.getElementById('a-list').innerHTML =
                    '<div class="empty"><span class="ei">⚠️</span><p>Gagal memuat.</p></div>';
            }
            spin('r', false);
        }

        // ── TAB RUANG ──
        async function loadRg(force = false) {
            if (allRg.length && !force) {
                renderRg();
                return;
            }
            spin('rg', true);
            document.getElementById('rg-list').innerHTML =
                '<div class="loading"><span></span><span></span><span></span></div>';
            try {
                allRg = await api('/ruang');
                document.getElementById('rg-n-ruang').textContent = allRg.length;
                document.getElementById('rg-n-hadir').textContent = allRg.reduce((s, r) => s + r.hadir, 0);
                document.getElementById('rg-n-total').textContent = allRg.reduce((s, r) => s + r.total, 0);
                document.getElementById('rg-sum').style.display = 'grid';
                document.getElementById('rg-srch').style.display = 'block';
                renderRg();
            } catch {
                document.getElementById('rg-list').innerHTML =
                    '<div class="empty"><span class="ei">⚠️</span><p>Gagal memuat.</p></div>';
            }
            spin('rg', false);
        }

        function filterRg(q) {
            rgQ = q.toLowerCase().trim();
            renderRg();
        }

        function renderRg() {
            const el = document.getElementById('rg-list');
            let d = allRg;
            if (rgQ) d = d.filter(r => r.room?.toLowerCase().includes(rgQ) || r.supervisor?.toLowerCase().includes(rgQ));
            if (!d.length) {
                el.innerHTML = '<div class="empty"><span class="ei">🚪</span><p>Tidak ada ruang.</p></div>';
                return;
            }
            el.innerHTML = d.map(r => {
                const pct = r.persen,
                    belum = r.total - r.hadir;
                const clr = pct >= 80 ? 'var(--grn-md)' : pct >= 50 ? 'var(--ylw)' : 'var(--r3)';
                return `<div class="rg-card">
            <div class="rg-top"><span class="rg-room">🚪 ${r.room??'<em style="opacity:.5">Tanpa Ruang</em>'}</span><span class="rg-sup">👤 ${r.supervisor??'–'}</span></div>
            <div class="rg-bar-bg"><div class="rg-bar" style="width:${pct}%;background:${clr}"></div></div>
            <div class="rg-nums"><span class="rg-hadir">✅ ${r.hadir} hadir</span><span class="rg-pct" style="color:${clr}">${pct}%</span><span class="rg-total">dari ${r.total}</span></div>
            ${belum>0?`<div class="rg-belum">⏳ ${belum} belum hadir</div>`:'<div class="rg-done">🎉 Semua hadir!</div>'}
        </div>`;
            }).join('');
        }

        // ── Helpers ──
        function showOv(msg) {
            document.getElementById('ov-lbl').textContent = msg;
            document.getElementById('ov').classList.add('on');
        }

        function hideOv() {
            document.getElementById('ov').classList.remove('on');
        }

        function toast(msg, type) {
            const t = document.getElementById('toast');
            t.textContent = msg;
            t.className = `show ${type}`;
            clearTimeout(toastTmr);
            toastTmr = setTimeout(() => t.className = '', 3500);
        }

        function spin(id, on) {
            const btn = document.getElementById('rf-' + id);
            if (btn) {
                btn.disabled = on;
                btn.textContent = on ? '⏳' : '🔄';
            }
        }

        // ── Init ──
        window.addEventListener('load', () => {
            if (!IS_ACTIVE) return;
            const f = document.querySelector('.digit-box');
            if (f) f.focus();
            else document.getElementById('single-inp')?.focus();
        });

        // ── Auto-refresh stats setiap 30 detik ──
        if (IS_ACTIVE) {
            setInterval(async () => {
                if (document.hidden) return;
                try {
                    const d = await api('/peserta');
                    updateStats(d.filter(p => p.hadir).length, d.length);
                    if (allP.length) {
                        allP = d;
                        if (document.getElementById('tab-peserta')?.classList.contains('on')) renderP();
                    }
                } catch {}
            }, 30000);
        }
    </script>
@endpush
