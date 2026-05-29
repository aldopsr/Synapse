@extends('layouts.app')
@section('title', 'Dashboard - Synapse')
@section('header_title', 'Dashboard')

@section('content')
<style>
.greeting-banner {
    background: linear-gradient(135deg, #279685 0%, #1a6b5e 100%);
    border-radius: 18px; padding: 28px 32px; margin-bottom: 28px;
    display: flex; align-items: center; justify-content: space-between;
    gap: 16px; overflow: hidden; position: relative;
}
.greeting-banner::after {
    content:''; position:absolute; right:-40px; top:-40px;
    width:220px; height:220px; border-radius:50%;
    background:rgba(255,255,255,0.06);
}
.greeting-text h2 { color:#fff; font-size:22px; font-weight:700; margin:0 0 6px; }
.greeting-text p  { color:rgba(255,255,255,.75); font-size:14px; margin:0; }
.greeting-badge {
    background:rgba(255,255,255,.18); color:#fff; font-size:12px;
    font-weight:600; padding:6px 14px; border-radius:99px;
    border:1px solid rgba(255,255,255,.25); white-space:nowrap;
    position:relative; z-index:1;
}
.stats-grid {
    display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
    gap:16px; margin-bottom:28px;
}
.stat-card {
    background:#fff; border-radius:16px; padding:22px 24px;
    border:1px solid #f0f0f0; position:relative; overflow:hidden;
    transition:transform .2s, box-shadow .2s; cursor:default;
}
.stat-card:hover { transform:translateY(-3px); box-shadow:0 12px 30px rgba(0,0,0,.07); }
.stat-icon { width:44px; height:44px; border-radius:12px; display:flex; align-items:center; justify-content:center; margin-bottom:14px; font-size:20px; }
.stat-icon.teal   { background:#e3faf8; }
.stat-icon.blue   { background:#e8f1fd; }
.stat-icon.purple { background:#f0eeff; }
.stat-icon.amber  { background:#fef3e2; }
.stat-icon.green  { background:#d1fae5; }
.stat-label { font-size:12px; font-weight:600; color:#888; text-transform:uppercase; letter-spacing:.05em; margin-bottom:6px; }
.stat-value { font-size:30px; font-weight:700; color:#1a1a1a; line-height:1; }
.stat-sub   { font-size:12px; color:#aaa; margin-top:4px; }
.stat-card::after {
    content:''; position:absolute; bottom:0; left:0; right:0;
    height:3px; border-radius:0 0 16px 16px; opacity:0; transition:opacity .2s;
}
.stat-card:hover::after { opacity:1; }
.stat-card.teal::after   { background:#279685; }
.stat-card.blue::after   { background:#4A90E2; }
.stat-card.purple::after { background:#7c3aed; }
.stat-card.amber::after  { background:#f59e0b; }
.stat-card.green::after  { background:#10b981; }

.skeleton {
    background:linear-gradient(90deg,#f0f0f0 25%,#e8e8e8 50%,#f0f0f0 75%);
    background-size:200% 100%; animation:shimmer 1.4s infinite; border-radius:8px;
}
@keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }
.skeleton-card { background:#fff; border-radius:16px; padding:22px 24px; border:1px solid #f0f0f0; }

.section-title { font-size:16px; font-weight:700; color:#1a1a1a; margin:0 0 14px; }

.quick-actions {
    display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr));
    gap:12px; margin-bottom:28px;
}
.qa-btn {
    display:flex; flex-direction:column; align-items:flex-start;
    gap:10px; padding:18px 20px; border-radius:14px;
    text-decoration:none; border:1px solid transparent;
    transition:transform .18s, box-shadow .18s; cursor:pointer;
}
.qa-btn:hover { transform:translateY(-3px); box-shadow:0 10px 24px rgba(0,0,0,.1); }
.qa-btn .qa-icon { width:38px; height:38px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:18px; }
.qa-btn .qa-label { font-size:13px; font-weight:700; line-height:1.3; }
.qa-btn .qa-sub   { font-size:11px; opacity:.7; font-weight:400; }
.qa-teal   { background:#e3faf8; color:#0f6e56; border-color:#c0ede8; }
.qa-blue   { background:#e8f1fd; color:#185fa5; border-color:#b5d4f4; }
.qa-purple { background:#f0eeff; color:#534ab7; border-color:#cec8f6; }
.qa-amber  { background:#fef3e2; color:#854f0b; border-color:#fac775; }
.qa-teal .qa-icon, .qa-blue .qa-icon, .qa-purple .qa-icon, .qa-amber .qa-icon
    { background:rgba(255,255,255,.55); }

/* Charts */
.charts-grid {
    display:grid; grid-template-columns:repeat(auto-fit,minmax(360px,1fr));
    gap:20px; margin-bottom:28px;
}
.chart-box { background:#fff; border-radius:16px; padding:24px; border:1px solid #f0f0f0; }
.chart-box-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; }
.chart-box-header h3 { font-size:15px; font-weight:700; color:#1a1a1a; margin:0; }
.chart-badge { font-size:11px; font-weight:600; color:#279685; background:#e3faf8; padding:3px 10px; border-radius:99px; }

/* Leaderboard */
.leaderboard-section { margin-bottom:28px; }
.leaderboard-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(340px,1fr)); gap:20px; }
.lb-box { background:#fff; border-radius:16px; border:1px solid #f0f0f0; overflow:hidden; }
.lb-header {
    padding:16px 20px; border-bottom:1px solid #f0f0f0;
    display:flex; align-items:center; justify-content:space-between;
}
.lb-header h3 { font-size:14px; font-weight:700; color:#1a1a1a; margin:0; }
.lb-table { width:100%; border-collapse:collapse; font-size:13px; }
.lb-table th { padding:10px 16px; text-align:left; font-weight:700; color:#888; font-size:11px; text-transform:uppercase; border-bottom:1px solid #f5f5f5; }
.lb-table td { padding:11px 16px; border-bottom:1px solid #f9f9f9; }
.lb-table tr:last-child td { border-bottom:none; }
.lb-table tr:hover td { background:#fafafa; }
.rank-badge {
    display:inline-flex; align-items:center; justify-content:center;
    width:24px; height:24px; border-radius:8px;
    font-size:12px; font-weight:700;
}
.rank-1 { background:#fef3c7; color:#92400e; }
.rank-2 { background:#e5e7eb; color:#374151; }
.rank-3 { background:#fde8d0; color:#9a3412; }
.rank-n { background:#f0f0f0; color:#888; }
.score-pill {
    display:inline-block; padding:2px 8px; border-radius:6px;
    font-size:11px; font-weight:700;
}
.score-high { background:#d1fae5; color:#065f46; }
.score-mid  { background:#fef3c7; color:#92400e; }
.score-low  { background:#fee2e2; color:#991b1b; }

.empty-state { text-align:center; padding:40px; color:#aaa; font-size:14px; }
@media(max-width:680px) {
    .greeting-banner { flex-direction:column; align-items:flex-start; }
    .charts-grid, .leaderboard-grid { grid-template-columns:1fr; }
}
</style>

{{-- GREETING --}}
<div class="greeting-banner">
    <div class="greeting-text">
        <h2 id="greetingText">Selamat datang! 👋</h2>
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
    <p class="section-title" id="lbTitle">🏆 Leaderboard</p>
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

    function getGreeting() {
        const h = new Date().getHours();
        if (h < 11) return 'Selamat pagi';
        if (h < 15) return 'Selamat siang';
        if (h < 18) return 'Selamat sore';
        return 'Selamat malam';
    }

    $('greetingText').textContent    = `${getGreeting()}, ${userName}! 👋`;
    $('greetingSubtext').textContent = isAdmin
        ? 'Pantau semua aktivitas sistem Synapse dari sini.'
        : 'Berikut ringkasan matkul dan kuis kamu.';
    $('greetingBadge').textContent   = isAdmin ? '👑 Admin' : '👨‍🏫 Dosen';

    // Quick actions
    const actions = isAdmin ? [
        { href:'/mata-kuliah',     cls:'qa-teal',   icon:'📚', label:'Mata Kuliah',  sub:'Kelola & assign dosen' },
        { href:'/kelolaAkunDosen', cls:'qa-blue',   icon:'👤', label:'Kelola Dosen', sub:'Tambah / hapus akun' },
        { href:'/kuis',            cls:'qa-purple', icon:'📝', label:'Kelola Kuis',  sub:'Buat & atur soal' },
        { href:'/kelola-ar',       cls:'qa-amber',  icon:'🌐', label:'Aset 3D',      sub:'Upload model 3D' },
    ] : [
        { href:'/mata-kuliah', cls:'qa-teal',   icon:'📚', label:'Materi Saya',  sub:'Kelola e-modul' },
        { href:'/kuis',        cls:'qa-purple', icon:'📝', label:'Kelola Kuis',  sub:'Buat & atur soal' },
        { href:'/kelola-ar',   cls:'qa-amber',  icon:'🌐', label:'Aset 3D',      sub:'Upload model 3D' },
    ];
    $('quickActions').innerHTML = actions.map(a => `
        <a href="${a.href}" class="qa-btn ${a.cls}">
            <div class="qa-icon">${a.icon}</div>
            <div><div class="qa-label">${a.label}</div><div class="qa-sub">${a.sub}</div></div>
        </a>`).join('');

    // Chart instances
    let c1, c2;
    function destroyCharts() {
        if (c1) { c1.destroy(); c1 = null; }
        if (c2) { c2.destroy(); c2 = null; }
    }

    Chart.defaults.font.family = 'inherit';
    Chart.defaults.font.size   = 12;
    Chart.defaults.color       = '#888';
    const TEAL = '#279685', BLUE = '#4A90E2', RED = '#ef4444', AMBER = '#f59e0b';

    // ── Rank badge ─────────────────────────────────────────
    function rankBadge(i) {
        const cls = i === 0 ? 'rank-1' : i === 1 ? 'rank-2' : i === 2 ? 'rank-3' : 'rank-n';
        const lbl = i === 0 ? '🥇' : i === 1 ? '🥈' : i === 2 ? '🥉' : `${i+1}`;
        return `<span class="rank-badge ${cls}">${lbl}</span>`;
    }
    function scorePill(v) {
        const cls = v >= 80 ? 'score-high' : v >= 60 ? 'score-mid' : 'score-low';
        return `<span class="score-pill ${cls}">${v}%</span>`;
    }

    // ── RENDER ADMIN ────────────────────────────────────────
    function renderAdmin(cards, charts, lb) {
        $('statsContainer').innerHTML = `
            <div class="stat-card teal"><div class="stat-icon teal">👨‍🏫</div>
                <div class="stat-label">Total Dosen</div>
                <div class="stat-value">${cards.total_dosen??0}</div>
                <div class="stat-sub">akun dosen aktif</div></div>
            <div class="stat-card blue"><div class="stat-icon blue">🎓</div>
                <div class="stat-label">Total Mahasiswa</div>
                <div class="stat-value">${cards.total_mahasiswa??0}</div>
                <div class="stat-sub">pengguna terdaftar</div></div>
            <div class="stat-card purple"><div class="stat-icon purple">📚</div>
                <div class="stat-label">Total Materi</div>
                <div class="stat-value">${cards.total_materi??0}</div>
                <div class="stat-sub">e-modul di sistem</div></div>
            <div class="stat-card amber"><div class="stat-icon amber">⚔️</div>
                <div class="stat-label">Total Duel</div>
                <div class="stat-value">${cards.total_duel??0}</div>
                <div class="stat-sub">semua pertandingan</div></div>`;

        // Chart 1: Aktivitas per matkul (horizontal bar)
        $('chartTitle1').textContent = 'Aktivitas kuis per mata kuliah';
        $('chartBadge1').textContent = 'Attempts';
        const ma = charts.matkul_activity ?? { labels:[], data:[] };
        c1 = new Chart($('chart1'), {
            type: 'bar',
            data: {
                labels: ma.labels,
                datasets: [{ label:'Pengerjaan kuis', data: ma.data,
                    backgroundColor: TEAL, borderRadius: 6, borderSkipped: false }]
            },
            options: {
                indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                plugins: { legend:{ display:false }, tooltip:{ cornerRadius:8,
                    callbacks:{ label: ctx => ` ${ctx.parsed.x} pengerjaan` } } },
                scales: {
                    x: { grid:{ color:'rgba(0,0,0,.05)' }, border:{ display:false },
                         ticks:{ stepSize:1 },
                         title:{ display:true, text:'Jumlah pengerjaan', font:{ size:11 } } },
                    y: { grid:{ display:false }, border:{ display:false } }
                }
            }
        });

        // Chart 2: Registrasi per bulan (line)
        $('chartTitle2').textContent = 'Registrasi pengguna baru';
        $('chartBadge2').textContent = '6 bulan';
        const reg = charts.registrasi ?? { labels:[], data:[] };
        c2 = new Chart($('chart2'), {
            type: 'line',
            data: {
                labels: reg.labels,
                datasets: [{ label:'Pengguna baru', data: reg.data,
                    borderColor: BLUE, backgroundColor: BLUE + '20',
                    fill: true, tension: 0.4, pointRadius: 4,
                    pointBackgroundColor: BLUE }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend:{ display:false }, tooltip:{ cornerRadius:8 } },
                scales: {
                    x: { grid:{ display:false }, border:{ display:false } },
                    y: { grid:{ color:'rgba(0,0,0,.05)' }, border:{ display:false },
                         ticks:{ stepSize:1 } }
                }
            }
        });

        // Leaderboard
        $('lbTitle').textContent = '🏆 Leaderboard';
        $('leaderboardGrid').innerHTML = `
            ${buildDosenLB(lb.dosen ?? [])}
            ${buildMahasiswaLB(lb.mahasiswa ?? [])}`;
        $('leaderboardSection').style.display = '';
    }

    function buildDosenLB(list) {
        if (!list.length) return `<div class="lb-box"><div class="lb-header"><h3>👨‍🏫 Dosen Teraktif</h3></div><div class="empty-state">Belum ada data</div></div>`;
        return `<div class="lb-box">
            <div class="lb-header">
                <h3>👨‍🏫 Dosen Teraktif</h3>
                <span class="chart-badge">Berdasarkan aktivitas</span>
            </div>
            <table class="lb-table">
                <thead><tr>
                    <th>#</th><th>Nama</th>
                    <th style="text-align:center;">Materi</th>
                    <th style="text-align:center;">Kuis</th>
                    <th style="text-align:center;">Mahasiswa</th>
                </tr></thead>
                <tbody>${list.map((d,i) => `<tr>
                    <td>${rankBadge(i)}</td>
                    <td style="font-weight:600;">${esc(d.name)}</td>
                    <td style="text-align:center;color:#279685;font-weight:700;">${d.jumlah_materi}</td>
                    <td style="text-align:center;color:#4A90E2;font-weight:700;">${d.jumlah_kuis}</td>
                    <td style="text-align:center;color:#7c3aed;font-weight:700;">${d.mahasiswa_aktif}</td>
                </tr>`).join('')}</tbody>
            </table></div>`;
    }

    function buildMahasiswaLB(list) {
        if (!list.length) return `<div class="lb-box"><div class="lb-header"><h3>🎓 Top Mahasiswa</h3></div><div class="empty-state">Belum ada data</div></div>`;
        return `<div class="lb-box">
            <div class="lb-header">
                <h3>🎓 Top Mahasiswa Sistem</h3>
                <span class="chart-badge">Nilai + Kuis + Duel</span>
            </div>
            <table class="lb-table">
                <thead><tr>
                    <th>#</th><th>Nama</th><th>NIM</th>
                    <th style="text-align:center;">Rata Nilai</th>
                    <th style="text-align:center;">Kuis</th>
                    <th style="text-align:center;">Duel 🏆</th>
                </tr></thead>
                <tbody>${list.map((m,i) => `<tr>
                    <td>${rankBadge(i)}</td>
                    <td style="font-weight:600;">${esc(m.name)}</td>
                    <td style="color:#94a3b8;font-size:12px;">${esc(m.nim)}</td>
                    <td style="text-align:center;">${scorePill(m.avg_score)}</td>
                    <td style="text-align:center;font-weight:700;">${m.total_kuis}</td>
                    <td style="text-align:center;font-weight:700;color:#f59e0b;">${m.duel_won}</td>
                </tr>`).join('')}</tbody>
            </table></div>`;
    }

    // ── RENDER DOSEN ────────────────────────────────────────
    function renderDosen(cards, charts, lb) {
        const rata = parseFloat(cards.rata_nilai) || 0;
        const nilaiColor = rata >= 70 ? '#279685' : rata >= 50 ? '#f59e0b' : '#ef4444';

        $('statsContainer').innerHTML = `
            <div class="stat-card teal"><div class="stat-icon teal">📚</div>
                <div class="stat-label">Materi Saya</div>
                <div class="stat-value">${cards.materi_saya??0}</div>
                <div class="stat-sub">e-modul diunggah</div></div>
            <div class="stat-card blue"><div class="stat-icon blue">📝</div>
                <div class="stat-label">Kuis Aktif</div>
                <div class="stat-value">${cards.kuis_aktif??0}</div>
                <div class="stat-sub">tersedia untuk mahasiswa</div></div>
            <div class="stat-card amber"><div class="stat-icon amber">⭐</div>
                <div class="stat-label">Rata-rata Nilai</div>
                <div class="stat-value" style="color:${nilaiColor}">${rata > 0 ? rata.toFixed(1) : '—'}</div>
                <div class="stat-sub">${rata > 0 ? 'dari semua percobaan' : 'belum ada percobaan'}</div></div>
            <div class="stat-card purple"><div class="stat-icon purple">👥</div>
                <div class="stat-label">Mahasiswa Aktif</div>
                <div class="stat-value">${cards.mahasiswa_hadir??0}</div>
                <div class="stat-sub">unik mengerjakan kuis</div></div>`;

        // Chart 1: % kelulusan per kuis (horizontal bar)
        $('chartTitle1').textContent = 'Tingkat kelulusan per kuis';
        $('chartBadge1').textContent = 'Matkul saya';
        const kl = charts.kelulusan_per_kuis ?? { labels:[], data:[] };
        if (kl.labels.length > 0) {
            const barColors = kl.data.map(v => v >= 70 ? TEAL : v >= 50 ? AMBER : RED);
            c1 = new Chart($('chart1'), {
                type: 'bar',
                data: {
                    labels: kl.labels,
                    datasets: [{ label:'% Lulus', data: kl.data,
                        backgroundColor: barColors, borderRadius: 6, borderSkipped: false }]
                },
                options: {
                    indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                    plugins: { legend:{ display:false }, tooltip:{ cornerRadius:8,
                        callbacks:{ label: ctx => ` ${ctx.parsed.x}% mahasiswa lulus` } } },
                    scales: {
                        x: { grid:{ color:'rgba(0,0,0,.05)' }, border:{ display:false },
                             min:0, max:100,
                             ticks:{ callback: v => v+'%' },
                             title:{ display:true, text:'% Lulus', font:{ size:11 } } },
                        y: { grid:{ display:false }, border:{ display:false } }
                    }
                }
            });
        } else {
            $('chart1').parentElement.innerHTML = `<div class="empty-state">Belum ada data kuis</div>`;
        }

        // Chart 2: Passed vs Failed donut
        $('chartTitle2').textContent = 'Hasil keseluruhan mahasiswa';
        $('chartBadge2').textContent = 'Semua kuis';
        const pf     = charts.passed_failed ?? { lulus:0, gagal:0 };
        const total  = pf.lulus + pf.gagal;
        const pct    = total > 0 ? Math.round((pf.lulus / total) * 100) : 0;
        c2 = new Chart($('chart2'), {
            type: 'doughnut',
            data: {
                labels: [`Lulus (${pf.lulus})`, `Tidak Lulus (${pf.gagal})`],
                datasets: [{ data:[pf.lulus, pf.gagal],
                    backgroundColor:[TEAL, '#fee2e2'],
                    borderWidth:0, hoverOffset:6 }]
            },
            options: {
                responsive: true, maintainAspectRatio: false, cutout:'68%',
                plugins: {
                    legend:{ position:'bottom', labels:{ padding:16, usePointStyle:true, pointStyle:'circle' } },
                    tooltip:{ cornerRadius:8 }
                }
            }
        });
        // Angka % di tengah donut
        const parent = $('chart2').parentElement;
        parent.style.position = 'relative';
        const overlay = document.createElement('div');
        overlay.style.cssText = `
            position:absolute;top:50%;left:50%;transform:translate(-50%,-60%);
            text-align:center;pointer-events:none;`;
        overlay.innerHTML = `<div style="font-size:26px;font-weight:800;color:#1a1a1a;">${pct}%</div>
            <div style="font-size:11px;color:#888;margin-top:2px;">Lulus</div>`;
        parent.appendChild(overlay);

        // Leaderboard mahasiswa matkul dosen
        if (lb && lb.length > 0) {
            $('lbTitle').textContent = '🏆 Leaderboard Mahasiswaku';
            $('leaderboardGrid').innerHTML = `<div class="lb-box" style="grid-column:1/-1;">
                <div class="lb-header">
                    <h3>🎓 Top Mahasiswa — Semua Matkul Saya</h3>
                    <span class="chart-badge">Nilai + Kuis + Duel</span>
                </div>
                <table class="lb-table">
                    <thead><tr>
                        <th>#</th><th>Nama</th><th>NIM</th>
                        <th style="text-align:center;">Rata Nilai</th>
                        <th style="text-align:center;">Kuis Dikerjakan</th>
                        <th style="text-align:center;">Kuis Lulus</th>
                        <th style="text-align:center;">Duel 🏆</th>
                    </tr></thead>
                    <tbody>${lb.map((m,i) => `<tr>
                        <td>${rankBadge(i)}</td>
                        <td style="font-weight:600;">${esc(m.name)}</td>
                        <td style="color:#94a3b8;font-size:12px;">${esc(m.nim)}</td>
                        <td style="text-align:center;">${scorePill(m.avg_score)}</td>
                        <td style="text-align:center;font-weight:700;">${m.total_kuis}</td>
                        <td style="text-align:center;font-weight:700;color:#279685;">${m.lulus_count}</td>
                        <td style="text-align:center;font-weight:700;color:#f59e0b;">${m.duel_won}</td>
                    </tr>`).join('')}</tbody>
                </table></div>`;
            $('leaderboardSection').style.display = '';
        }
    }

    function esc(s) {
        return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    // ── FETCH ──────────────────────────────────────────────
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
            $('statsContainer').innerHTML = `<div class="empty-state" style="grid-column:1/-1;">
                <p style="font-size:32px;margin-bottom:10px;">⚠️</p>
                <p style="font-weight:600;color:#555;margin-bottom:4px;">Gagal memuat data</p>
                <p style="font-size:13px;">${err.message}</p>
                <button onclick="window.fetchDashboardStats()"
                    style="margin-top:16px;padding:8px 20px;background:#279685;color:#fff;border:none;border-radius:8px;cursor:pointer;font-weight:600;">
                    Coba lagi
                </button></div>`;
        }
    }

    window.fetchDashboardStats = fetchDashboardStats;
    fetchDashboardStats();
})();
</script>
@endpush