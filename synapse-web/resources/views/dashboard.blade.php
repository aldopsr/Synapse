@extends('layouts.app')
@section('title', 'Dashboard - Synapse')
@section('header_title', 'Dashboard')

@section('content')
<style>
/* ── Greeting ─────────────────────────────────────────── */
.greeting-banner {
    background: linear-gradient(135deg, #279685 0%, #1a6b5e 100%);
    border-radius: 20px; padding: 28px 32px; margin-bottom: 28px;
    display: flex; align-items: center; justify-content: space-between;
    gap: 16px; overflow: hidden; position: relative;
}
.greeting-banner::before {
    content:''; position:absolute; right:-60px; top:-60px;
    width:240px; height:240px; border-radius:50%;
    background:rgba(255,255,255,.06); pointer-events:none;
}
.greeting-banner::after {
    content:''; position:absolute; left:40%; bottom:-80px;
    width:200px; height:200px; border-radius:50%;
    background:rgba(255,255,255,.04); pointer-events:none;
}
.greeting-text h2 { color:#fff; font-size:22px; font-weight:800; margin:0 0 6px; letter-spacing:-.3px; }
.greeting-text p  { color:rgba(255,255,255,.7); font-size:14px; margin:0; }
.greeting-badge {
    background:rgba(255,255,255,.15); color:#fff; font-size:12px;
    font-weight:700; padding:7px 16px; border-radius:99px;
    border:1px solid rgba(255,255,255,.2); white-space:nowrap;
    position:relative; z-index:1; backdrop-filter:blur(8px);
}

/* ── Stat cards ───────────────────────────────────────── */
.stats-grid {
    display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
    gap:16px; margin-bottom:28px;
}
.stat-card {
    background:#fff; border-radius:16px; padding:22px 24px;
    border:1px solid #eff0f2;
    transition:transform .2s, box-shadow .2s; cursor:default;
    position:relative; overflow:hidden;
}
.stat-card:hover { transform:translateY(-3px); box-shadow:0 12px 32px rgba(0,0,0,.08); }
.stat-card::after {
    content:''; position:absolute; bottom:0; left:0; right:0;
    height:3px; border-radius:0 0 16px 16px; opacity:0; transition:opacity .2s;
    background:#279685;
}
.stat-card:hover::after { opacity:1; }

/* SVG icon box */
.stat-icon {
    width:44px; height:44px; border-radius:12px;
    display:flex; align-items:center; justify-content:center;
    margin-bottom:16px;
}
.stat-icon svg { width:22px; height:22px; }
.stat-icon.teal   { background:rgba(39,150,133,.1);  color:#279685; }
.stat-icon.blue   { background:rgba(74,144,226,.1);  color:#4A90E2; }
.stat-icon.purple { background:rgba(124,58,237,.1);  color:#7c3aed; }
.stat-icon.amber  { background:rgba(245,158,11,.1);  color:#d97706; }
.stat-icon.green  { background:rgba(16,185,129,.1);  color:#059669; }

.stat-label { font-size:11px; font-weight:700; color:#9ca3af; text-transform:uppercase; letter-spacing:.06em; margin-bottom:6px; }
.stat-value { font-size:32px; font-weight:800; color:#111827; line-height:1; letter-spacing:-1px; }
.stat-sub   { font-size:12px; color:#c4c8d0; margin-top:5px; }

/* Skeleton */
.skeleton { background:linear-gradient(90deg,#f5f5f5 25%,#eee 50%,#f5f5f5 75%); background-size:200% 100%; animation:shimmer 1.4s infinite; border-radius:8px; }
@keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }
.skeleton-card { background:#fff; border-radius:16px; padding:22px 24px; border:1px solid #eff0f2; }

.section-title { font-size:15px; font-weight:800; color:#111827; margin:0 0 14px; letter-spacing:-.2px; }

/* ── Quick actions ────────────────────────────────────── */
.quick-actions {
    display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr));
    gap:12px; margin-bottom:28px;
}
.qa-btn {
    display:flex; align-items:center; gap:14px;
    padding:16px 18px; border-radius:14px;
    text-decoration:none; border:1.5px solid transparent;
    transition:transform .15s, box-shadow .15s;
    cursor:pointer;
}
.qa-btn:hover { transform:translateY(-2px); box-shadow:0 8px 20px rgba(0,0,0,.09); }
.qa-icon {
    width:40px; height:40px; border-radius:11px;
    display:flex; align-items:center; justify-content:center;
    flex-shrink:0; background:rgba(255,255,255,.55);
}
.qa-icon svg { width:20px; height:20px; }
.qa-label { font-size:13px; font-weight:700; line-height:1.3; }
.qa-sub   { font-size:11px; opacity:.7; margin-top:2px; }
.qa-teal   { background:#e3faf8; color:#0f6e56; border-color:#c0ede8; }
.qa-blue   { background:#e8f1fd; color:#185fa5; border-color:#b5d4f4; }
.qa-purple { background:#f0eeff; color:#534ab7; border-color:#cec8f6; }
.qa-amber  { background:#fef3e2; color:#854f0b; border-color:#fac775; }

/* ── Charts ───────────────────────────────────────────── */
.charts-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(360px,1fr)); gap:20px; margin-bottom:28px; }
.chart-box { background:#fff; border-radius:16px; padding:24px; border:1px solid #eff0f2; }
.chart-box-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; }
.chart-box-header h3 { font-size:14px; font-weight:700; color:#111827; margin:0; }
.chart-badge { font-size:11px; font-weight:700; color:#279685; background:rgba(39,150,133,.1); padding:3px 10px; border-radius:99px; border:1px solid rgba(39,150,133,.2); }

/* ── Leaderboard ──────────────────────────────────────── */
.leaderboard-section { margin-bottom:28px; }
.leaderboard-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(340px,1fr)); gap:20px; }
.lb-box { background:#fff; border-radius:16px; border:1px solid #eff0f2; overflow:hidden; }
.lb-header { padding:16px 20px; border-bottom:1px solid #f5f5f5; display:flex; align-items:center; justify-content:space-between; }
.lb-header h3 { font-size:14px; font-weight:700; color:#111827; margin:0; }
.lb-table { width:100%; border-collapse:collapse; font-size:13px; }
.lb-table th { padding:10px 16px; text-align:left; font-weight:700; color:#9ca3af; font-size:11px; text-transform:uppercase; letter-spacing:.04em; border-bottom:1px solid #f5f5f5; }
.lb-table td { padding:11px 16px; border-bottom:1px solid #fafafa; vertical-align:middle; }
.lb-table tr:last-child td { border-bottom:none; }
.lb-table tr:hover td { background:#f8fffe; }
.rank-badge { display:inline-flex; align-items:center; justify-content:center; width:26px; height:26px; border-radius:8px; font-size:12px; font-weight:700; }
.rank-1 { background:#fef3c7; color:#92400e; }
.rank-2 { background:#e5e7eb; color:#374151; }
.rank-3 { background:#fde8d0; color:#9a3412; }
.rank-n { background:#f5f5f5; color:#9ca3af; }
.score-pill { display:inline-block; padding:2px 9px; border-radius:7px; font-size:11px; font-weight:700; }
.score-high { background:#d1fae5; color:#065f46; }
.score-mid  { background:#fef3c7; color:#92400e; }
.score-low  { background:#fee2e2; color:#991b1b; }
.empty-state { text-align:center; padding:40px; color:#c4c8d0; font-size:14px; }

@media(max-width:680px) {
    .greeting-banner { flex-direction:column; align-items:flex-start; }
    .charts-grid, .leaderboard-grid { grid-template-columns:1fr; }
    .stat-value { font-size:26px; }
}
</style>

{{-- GREETING --}}
<div class="greeting-banner">
    <div class="greeting-text">
        <h2 id="greetingText">Selamat datang!</h2>
        <p id="greetingSubtext">Berikut ringkasan aktivitas Synapse hari ini.</p>
    </div>
    <span class="greeting-badge" id="greetingBadge">Memuat...</span>
</div>

{{-- STAT CARDS --}}
<div class="stats-grid" id="statsContainer">
    @for($i=0;$i<4;$i++)
    <div class="skeleton-card">
        <div class="skeleton" style="width:44px;height:44px;border-radius:12px;margin-bottom:14px;"></div>
        <div class="skeleton" style="width:60%;height:11px;margin-bottom:8px;"></div>
        <div class="skeleton" style="width:40%;height:28px;"></div>
    </div>
    @endfor
</div>

{{-- QUICK ACTIONS --}}
<p class="section-title">Menu cepat</p>
<div class="quick-actions" id="quickActions"></div>

{{-- CHARTS --}}
<p class="section-title" id="chartSectionTitle">Statistik & grafik</p>
<div class="charts-grid" id="chartContainer" style="display:none;">
    <div class="chart-box">
        <div class="chart-box-header">
            <h3 id="chartTitle1">Memuat...</h3>
            <span class="chart-badge" id="chartBadge1">Live</span>
        </div>
        <div style="position:relative;height:260px;"><canvas id="chart1"></canvas></div>
    </div>
    <div class="chart-box">
        <div class="chart-box-header">
            <h3 id="chartTitle2">Memuat...</h3>
            <span class="chart-badge" id="chartBadge2">Live</span>
        </div>
        <div style="position:relative;height:260px;"><canvas id="chart2"></canvas></div>
    </div>
</div>
<div class="charts-grid" id="chartSkeleton">
    <div class="skeleton-card">
        <div class="skeleton" style="width:40%;height:14px;margin-bottom:20px;"></div>
        <div class="skeleton" style="width:100%;height:240px;border-radius:12px;"></div>
    </div>
    <div class="skeleton-card">
        <div class="skeleton" style="width:40%;height:14px;margin-bottom:20px;"></div>
        <div class="skeleton" style="width:100%;height:240px;border-radius:12px;"></div>
    </div>
</div>

{{-- LEADERBOARD --}}
<div class="leaderboard-section" id="leaderboardSection" style="display:none;">
    <p class="section-title" id="lbTitle">Leaderboard</p>
    <div class="leaderboard-grid" id="leaderboardGrid"></div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<script>
(function () {
    const $ = id => document.getElementById(id);
    const userName = window.user?.name ?? 'Pengguna';
    const userRole = window.role || 'dosen';
    const isAdmin  = userRole === 'admin' || userRole === 'superadmin';

    // SVG icons — dipanggil dari JS karena stat cards dirender JS
    const SVG = {
        dosen:     `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>`,
        mahasiswa: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>`,
        materi:    `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>`,
        kuis:      `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>`,
        duel:      `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>`,
        nilai:     `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>`,
        book:      `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>`,
        cube:      `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l9 4.9V17L12 22 3 17V6.9L12 2z"/><polyline points="3 7 12 12 21 7"/><line x1="12" y1="12" x2="12" y2="22"/></svg>`,
        users:     `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>`,
    };

    function statCard(iconKey, colorCls, label, value, sub, valueStyle) {
        return `<div class="stat-card">
            <div class="stat-icon ${colorCls}">${SVG[iconKey]||SVG.materi}</div>
            <div class="stat-label">${label}</div>
            <div class="stat-value"${valueStyle ? ` style="color:${valueStyle}"` : ''}>${value}</div>
            <div class="stat-sub">${sub}</div>
        </div>`;
    }

    function qaCard(href, cls, iconKey, label, sub) {
        return `<a href="${href}" class="qa-btn ${cls}">
            <div class="qa-icon">${SVG[iconKey]||SVG.materi}</div>
            <div><div class="qa-label">${label}</div><div class="qa-sub">${sub}</div></div>
        </a>`;
    }

    function getGreeting() {
        const h = new Date().getHours();
        if (h < 11) return 'Selamat pagi';
        if (h < 15) return 'Selamat siang';
        if (h < 18) return 'Selamat sore';
        return 'Selamat malam';
    }

    $('greetingText').textContent    = `${getGreeting()}, ${userName.split(' ')[0]}!`;
    $('greetingSubtext').textContent = isAdmin ? 'Pantau semua aktivitas sistem Synapse dari sini.' : 'Berikut ringkasan matkul dan kuis kamu.';
    $('greetingBadge').textContent   = isAdmin ? 'Admin' : 'Dosen';

    // Quick actions — SVG icons
    if (isAdmin) {
        $('quickActions').innerHTML =
            qaCard('/mata-kuliah',     'qa-teal',   'book',  'Mata Kuliah',  'Kelola & assign dosen') +
            qaCard('/kelolaAkunDosen', 'qa-blue',   'dosen', 'Kelola Dosen', 'Tambah / hapus akun') +
            qaCard('/kuis',            'qa-purple', 'kuis',  'Kelola Kuis',  'Buat & atur soal') +
            qaCard('/kelola-ar',       'qa-amber',  'cube',  'Aset 3D',      'Upload model 3D');
    } else {
        $('quickActions').innerHTML =
            qaCard('/mata-kuliah', 'qa-teal',   'book', 'Materi Saya', 'Kelola e-modul') +
            qaCard('/kuis',        'qa-purple', 'kuis', 'Kelola Kuis', 'Buat & atur soal') +
            qaCard('/kelola-ar',   'qa-amber',  'cube', 'Aset 3D',     'Upload model 3D');
    }

    let c1, c2;
    function destroyCharts() {
        if (c1) { c1.destroy(); c1 = null; }
        if (c2) { c2.destroy(); c2 = null; }
    }

    Chart.defaults.font.family = 'inherit';
    Chart.defaults.font.size   = 12;
    Chart.defaults.color       = '#888';
    const TEAL = '#279685', BLUE = '#4A90E2', RED = '#ef4444', AMBER = '#f59e0b';

    function rankBadge(i) {
        const cls = i === 0 ? 'rank-1' : i === 1 ? 'rank-2' : i === 2 ? 'rank-3' : 'rank-n';
        const lbl = i === 0 ? '1' : i === 1 ? '2' : i === 2 ? '3' : `${i+1}`;
        return `<span class="rank-badge ${cls}">${lbl}</span>`;
    }
    function scorePill(v) {
        const cls = v >= 80 ? 'score-high' : v >= 60 ? 'score-mid' : 'score-low';
        return `<span class="score-pill ${cls}">${v}%</span>`;
    }

    function renderAdmin(cards, charts, lb) {
        $('statsContainer').innerHTML =
            statCard('dosen',     'teal',   'Total Dosen',      cards.total_dosen??0,      'akun dosen aktif') +
            statCard('mahasiswa', 'blue',   'Total Mahasiswa',  cards.total_mahasiswa??0,  'pengguna terdaftar') +
            statCard('materi',    'purple', 'Total Materi',     cards.total_materi??0,     'e-modul di sistem') +
            statCard('duel',      'amber',  'Total Duel',       cards.total_duel??0,       'semua pertandingan');

        $('chartTitle1').textContent = 'Aktivitas kuis per mata kuliah';
        $('chartBadge1').textContent = 'Attempts';
        const ma = charts.matkul_activity ?? { labels:[], data:[] };
        c1 = new Chart($('chart1'), {
            type: 'bar',
            data: { labels: ma.labels, datasets: [{ label:'Pengerjaan kuis', data: ma.data, backgroundColor: TEAL, borderRadius: 6, borderSkipped: false }] },
            options: { indexAxis:'y', responsive:true, maintainAspectRatio:false, plugins:{ legend:{display:false}, tooltip:{cornerRadius:8, callbacks:{label: ctx => ` ${ctx.parsed.x} pengerjaan`}} }, scales:{ x:{grid:{color:'rgba(0,0,0,.05)'}, border:{display:false}, ticks:{stepSize:1}}, y:{grid:{display:false}, border:{display:false}} } }
        });

        $('chartTitle2').textContent = 'Registrasi pengguna baru';
        $('chartBadge2').textContent = '6 bulan';
        const reg = charts.registrasi ?? { labels:[], data:[] };
        c2 = new Chart($('chart2'), {
            type: 'line',
            data: { labels: reg.labels, datasets: [{ label:'Pengguna baru', data: reg.data, borderColor: BLUE, backgroundColor: BLUE + '20', fill:true, tension:0.4, pointRadius:4, pointBackgroundColor: BLUE }] },
            options: { responsive:true, maintainAspectRatio:false, plugins:{ legend:{display:false}, tooltip:{cornerRadius:8} }, scales:{ x:{grid:{display:false}, border:{display:false}}, y:{grid:{color:'rgba(0,0,0,.05)'}, border:{display:false}, ticks:{stepSize:1}} } }
        });

        $('lbTitle').textContent = 'Leaderboard';
        $('leaderboardGrid').innerHTML = buildDosenLB(lb.dosen??[]) + buildMahasiswaLB(lb.mahasiswa??[]);
        $('leaderboardSection').style.display = '';
    }

    function buildDosenLB(list) {
        if (!list.length) return `<div class="lb-box"><div class="lb-header"><h3>Dosen Teraktif</h3></div><div class="empty-state">Belum ada data</div></div>`;
        return `<div class="lb-box">
            <div class="lb-header"><h3>Dosen Teraktif</h3><span class="chart-badge">Berdasarkan aktivitas</span></div>
            <table class="lb-table">
                <thead><tr><th>#</th><th>Nama</th><th style="text-align:center">Materi</th><th style="text-align:center">Kuis</th><th style="text-align:center">Mahasiswa</th></tr></thead>
                <tbody>${list.map((d,i)=>`<tr>
                    <td>${rankBadge(i)}</td>
                    <td style="font-weight:600">${esc(d.name)}</td>
                    <td style="text-align:center;color:#279685;font-weight:700">${d.jumlah_materi}</td>
                    <td style="text-align:center;color:#4A90E2;font-weight:700">${d.jumlah_kuis}</td>
                    <td style="text-align:center;color:#7c3aed;font-weight:700">${d.mahasiswa_aktif}</td>
                </tr>`).join('')}</tbody>
            </table></div>`;
    }

    function buildMahasiswaLB(list) {
        if (!list.length) return `<div class="lb-box"><div class="lb-header"><h3>Top Mahasiswa</h3></div><div class="empty-state">Belum ada data</div></div>`;
        return `<div class="lb-box">
            <div class="lb-header"><h3>Top Mahasiswa</h3><span class="chart-badge">Nilai + Kuis + Duel</span></div>
            <table class="lb-table">
                <thead><tr><th>#</th><th>Nama</th><th>NIM</th><th style="text-align:center">Nilai</th><th style="text-align:center">Kuis</th><th style="text-align:center">Duel</th></tr></thead>
                <tbody>${list.map((m,i)=>`<tr>
                    <td>${rankBadge(i)}</td>
                    <td style="font-weight:600">${esc(m.name)}</td>
                    <td style="color:#94a3b8;font-size:12px">${esc(m.nim)}</td>
                    <td style="text-align:center">${scorePill(m.avg_score)}</td>
                    <td style="text-align:center;font-weight:700">${m.total_kuis}</td>
                    <td style="text-align:center;font-weight:700;color:#d97706">${m.duel_won}</td>
                </tr>`).join('')}</tbody>
            </table></div>`;
    }

    function renderDosen(cards, charts, lb) {
        const rata = parseFloat(cards.rata_nilai) || 0;
        const nilaiColor = rata >= 70 ? '#279685' : rata >= 50 ? '#f59e0b' : '#ef4444';
        $('statsContainer').innerHTML =
            statCard('book',      'teal',   'Materi Saya',      cards.materi_saya??0,     'e-modul diunggah') +
            statCard('kuis',      'blue',   'Kuis Aktif',        cards.kuis_aktif??0,      'tersedia untuk mahasiswa') +
            statCard('nilai',     'amber',  'Rata-rata Nilai',   rata > 0 ? rata.toFixed(1) : '—', rata > 0 ? 'dari semua percobaan' : 'belum ada percobaan', rata > 0 ? nilaiColor : null) +
            statCard('users',     'purple', 'Mahasiswa Aktif',   cards.mahasiswa_hadir??0, 'unik mengerjakan kuis');

        $('chartTitle1').textContent = 'Tingkat kelulusan per kuis';
        $('chartBadge1').textContent = 'Matkul saya';
        const kl = charts.kelulusan_per_kuis ?? { labels:[], data:[] };
        if (kl.labels.length > 0) {
            const barColors = kl.data.map(v => v >= 70 ? TEAL : v >= 50 ? AMBER : RED);
            c1 = new Chart($('chart1'), {
                type:'bar',
                data:{ labels:kl.labels, datasets:[{ label:'% Lulus', data:kl.data, backgroundColor:barColors, borderRadius:6, borderSkipped:false }] },
                options:{ indexAxis:'y', responsive:true, maintainAspectRatio:false, plugins:{legend:{display:false}, tooltip:{cornerRadius:8, callbacks:{label: ctx => ` ${ctx.parsed.x}% mahasiswa lulus`}}}, scales:{ x:{grid:{color:'rgba(0,0,0,.05)'}, border:{display:false}, min:0, max:100, ticks:{callback: v => v+'%'}}, y:{grid:{display:false}, border:{display:false}} } }
            });
        } else {
            $('chart1').parentElement.innerHTML = `<div class="empty-state">Belum ada data kuis</div>`;
        }

        $('chartTitle2').textContent = 'Hasil keseluruhan mahasiswa';
        $('chartBadge2').textContent = 'Semua kuis';
        const pf = charts.passed_failed ?? { lulus:0, gagal:0 };
        const total = pf.lulus + pf.gagal;
        const pct = total > 0 ? Math.round((pf.lulus / total) * 100) : 0;
        c2 = new Chart($('chart2'), {
            type:'doughnut',
            data:{ labels:[`Lulus (${pf.lulus})`, `Tidak Lulus (${pf.gagal})`], datasets:[{ data:[pf.lulus, pf.gagal], backgroundColor:[TEAL,'#fee2e2'], borderWidth:0, hoverOffset:6 }] },
            options:{ responsive:true, maintainAspectRatio:false, cutout:'68%', plugins:{ legend:{position:'bottom', labels:{padding:16, usePointStyle:true, pointStyle:'circle'}}, tooltip:{cornerRadius:8} } }
        });
        const parent = $('chart2').parentElement;
        parent.style.position = 'relative';
        const ov = document.createElement('div');
        ov.style.cssText = 'position:absolute;top:50%;left:50%;transform:translate(-50%,-60%);text-align:center;pointer-events:none;';
        ov.innerHTML = `<div style="font-size:26px;font-weight:800;color:#1a1a1a">${pct}%</div><div style="font-size:11px;color:#9ca3af;margin-top:2px">Lulus</div>`;
        parent.appendChild(ov);

        if (lb && lb.length > 0) {
            $('lbTitle').textContent = 'Leaderboard Mahasiswaku';
            $('leaderboardGrid').innerHTML = `<div class="lb-box" style="grid-column:1/-1">
                <div class="lb-header"><h3>Top Mahasiswa — Semua Matkul Saya</h3><span class="chart-badge">Nilai + Kuis + Duel</span></div>
                <table class="lb-table">
                    <thead><tr><th>#</th><th>Nama</th><th>NIM</th><th style="text-align:center">Nilai</th><th style="text-align:center">Kuis</th><th style="text-align:center">Lulus</th><th style="text-align:center">Duel</th></tr></thead>
                    <tbody>${lb.map((m,i)=>`<tr>
                        <td>${rankBadge(i)}</td>
                        <td style="font-weight:600">${esc(m.name)}</td>
                        <td style="color:#94a3b8;font-size:12px">${esc(m.nim)}</td>
                        <td style="text-align:center">${scorePill(m.avg_score)}</td>
                        <td style="text-align:center;font-weight:700">${m.total_kuis}</td>
                        <td style="text-align:center;font-weight:700;color:#279685">${m.lulus_count}</td>
                        <td style="text-align:center;font-weight:700;color:#d97706">${m.duel_won}</td>
                    </tr>`).join('')}</tbody>
                </table></div>`;
            $('leaderboardSection').style.display = '';
        }
    }

    function esc(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

    async function fetchDashboardStats() {
        try {
            const res  = await fetch(window.apiBaseUrl + '/dashboard/stats', {
                headers: { Authorization:'Bearer '+window.token, Accept:'application/json' }
            });
            if (res.status === 401) { window.logout(); return; }
            const data = await res.json();
            if (!res.ok || !data.success) throw new Error(data.message || 'Gagal memuat data');

            $('chartSkeleton').style.display  = 'none';
            $('chartContainer').style.display = 'grid';
            destroyCharts();

            const apiRole = data.role || userRole;
            if (apiRole === 'admin' || apiRole === 'superadmin') {
                renderAdmin(data.cards, data.charts, data.leaderboard ?? {});
            } else {
                renderDosen(data.cards, data.charts, data.leaderboard ?? []);
            }
        } catch (err) {
            console.error('[Dashboard]', err);
            $('chartSkeleton').style.display = 'none';
            $('statsContainer').innerHTML = `<div class="empty-state" style="grid-column:1/-1">
                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#e5e7eb" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom:12px"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <p style="font-weight:700;color:#374151;margin-bottom:4px">Gagal memuat data</p>
                <p style="font-size:13px;color:#9ca3af;margin-bottom:16px">${err.message}</p>
                <button onclick="window.fetchDashboardStats()" style="padding:9px 20px;background:#279685;color:#fff;border:none;border-radius:99px;cursor:pointer;font-weight:700;font-size:13px;font-family:inherit">
                    Coba lagi
                </button></div>`;
        }
    }

    window.fetchDashboardStats = fetchDashboardStats;
    fetchDashboardStats();
})();
</script>
@endpush