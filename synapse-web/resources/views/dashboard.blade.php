@extends('layouts.app')

@section('title', 'Dashboard - Synapse')
@section('header_title', 'Dashboard')

@section('content')
<style>
    /* =============================================
       DASHBOARD — SYNAPSE WEB (Modernized)
       ============================================= */

    /* --- Greeting Banner --- */
    .greeting-banner {
        background: linear-gradient(135deg, #279685 0%, #1a6b5e 100%);
        border-radius: 18px;
        padding: 28px 32px;
        margin-bottom: 28px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        overflow: hidden;
        position: relative;
    }
    .greeting-banner::after {
        content: '';
        position: absolute;
        right: -40px;
        top: -40px;
        width: 220px;
        height: 220px;
        border-radius: 50%;
        background: rgba(255,255,255,0.06);
    }
    .greeting-banner::before {
        content: '';
        position: absolute;
        right: 60px;
        bottom: -60px;
        width: 160px;
        height: 160px;
        border-radius: 50%;
        background: rgba(255,255,255,0.04);
    }
    .greeting-text h2 {
        color: #fff;
        font-size: 22px;
        font-weight: 700;
        margin: 0 0 6px 0;
    }
    .greeting-text p {
        color: rgba(255,255,255,0.75);
        font-size: 14px;
        margin: 0;
    }
    .greeting-badge {
        background: rgba(255,255,255,0.18);
        color: #fff;
        font-size: 12px;
        font-weight: 600;
        padding: 6px 14px;
        border-radius: 99px;
        border: 1px solid rgba(255,255,255,0.25);
        white-space: nowrap;
        position: relative;
        z-index: 1;
    }

    /* --- Stat Cards --- */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        margin-bottom: 28px;
    }
    .stat-card {
        background: #fff;
        border-radius: 16px;
        padding: 22px 24px;
        border: 1px solid #f0f0f0;
        position: relative;
        overflow: hidden;
        transition: transform 0.2s, box-shadow 0.2s;
        cursor: default;
    }
    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 30px rgba(0,0,0,0.07);
    }
    .stat-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 14px;
        font-size: 20px;
    }
    .stat-icon.teal   { background: #e3faf8; }
    .stat-icon.blue   { background: #e8f1fd; }
    .stat-icon.purple { background: #f0eeff; }
    .stat-icon.amber  { background: #fef3e2; }
    .stat-label {
        font-size: 12px;
        font-weight: 600;
        color: #888;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 6px;
    }
    .stat-value {
        font-size: 30px;
        font-weight: 700;
        color: #1a1a1a;
        line-height: 1;
    }
    .stat-sub {
        font-size: 12px;
        color: #aaa;
        margin-top: 4px;
    }
    /* Accent bar at bottom */
    .stat-card::after {
        content: '';
        position: absolute;
        bottom: 0; left: 0; right: 0;
        height: 3px;
        border-radius: 0 0 16px 16px;
        opacity: 0;
        transition: opacity 0.2s;
    }
    .stat-card:hover::after { opacity: 1; }
    .stat-card.teal::after   { background: #279685; }
    .stat-card.blue::after   { background: #4A90E2; }
    .stat-card.purple::after { background: #7c3aed; }
    .stat-card.amber::after  { background: #f59e0b; }

    /* --- Skeleton Loader --- */
    .skeleton {
        background: linear-gradient(90deg, #f0f0f0 25%, #e8e8e8 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        animation: shimmer 1.4s infinite;
        border-radius: 8px;
    }
    @keyframes shimmer {
        0%   { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }
    .skeleton-card {
        background: #fff;
        border-radius: 16px;
        padding: 22px 24px;
        border: 1px solid #f0f0f0;
    }

    /* --- Charts Grid --- */
    .charts-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(360px, 1fr));
        gap: 20px;
        margin-bottom: 28px;
    }
    .chart-box {
        background: #fff;
        border-radius: 16px;
        padding: 24px;
        border: 1px solid #f0f0f0;
    }
    .chart-box-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
    }
    .chart-box-header h3 {
        font-size: 15px;
        font-weight: 700;
        color: #1a1a1a;
        margin: 0;
    }
    .chart-badge {
        font-size: 11px;
        font-weight: 600;
        color: #279685;
        background: #e3faf8;
        padding: 3px 10px;
        border-radius: 99px;
    }

    /* --- Quick Actions --- */
    .section-title {
        font-size: 16px;
        font-weight: 700;
        color: #1a1a1a;
        margin: 0 0 14px 0;
    }
    .quick-actions {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 12px;
        margin-bottom: 28px;
    }
    .qa-btn {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
        padding: 18px 20px;
        border-radius: 14px;
        text-decoration: none;
        border: 1px solid transparent;
        transition: transform 0.18s, box-shadow 0.18s, border-color 0.18s;
        cursor: pointer;
    }
    .qa-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 24px rgba(0,0,0,0.1);
    }
    .qa-btn .qa-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }
    .qa-btn .qa-label {
        font-size: 13px;
        font-weight: 700;
        line-height: 1.3;
    }
    .qa-btn .qa-sub {
        font-size: 11px;
        opacity: 0.7;
        font-weight: 400;
    }

    .qa-teal   { background: #e3faf8; color: #0f6e56; border-color: #c0ede8; }
    .qa-blue   { background: #e8f1fd; color: #185fa5; border-color: #b5d4f4; }
    .qa-purple { background: #f0eeff; color: #534ab7; border-color: #cec8f6; }
    .qa-amber  { background: #fef3e2; color: #854f0b; border-color: #fac775; }
    .qa-red    { background: #fdeaea; color: #991b1b; border-color: #f7c1c1; }

    .qa-teal   .qa-icon { background: rgba(255,255,255,0.55); }
    .qa-blue   .qa-icon { background: rgba(255,255,255,0.55); }
    .qa-purple .qa-icon { background: rgba(255,255,255,0.55); }
    .qa-amber  .qa-icon { background: rgba(255,255,255,0.55); }
    .qa-red    .qa-icon { background: rgba(255,255,255,0.55); }

    /* --- Empty / Error state --- */
    .empty-state {
        text-align: center;
        padding: 40px;
        color: #aaa;
        font-size: 14px;
    }

    /* --- Responsive --- */
    @media (max-width: 680px) {
        .greeting-banner { flex-direction: column; align-items: flex-start; }
        .charts-grid { grid-template-columns: 1fr; }
    }
</style>

{{-- ===================== GREETING BANNER ===================== --}}
<div class="greeting-banner">
    <div class="greeting-text">
        <h2 id="greetingText">Selamat datang! 👋</h2>
        <p id="greetingSubtext">Berikut ringkasan aktivitas Synapse hari ini.</p>
    </div>
    <span class="greeting-badge" id="greetingBadge">Memuat...</span>
</div>

{{-- ===================== STAT CARDS ===================== --}}
<div class="stats-grid" id="statsContainer">
    {{-- Skeleton placeholders --}}
    <div class="skeleton-card">
        <div class="skeleton" style="width:44px;height:44px;border-radius:12px;margin-bottom:14px;"></div>
        <div class="skeleton" style="width:60%;height:11px;margin-bottom:8px;"></div>
        <div class="skeleton" style="width:40%;height:28px;"></div>
    </div>
    <div class="skeleton-card">
        <div class="skeleton" style="width:44px;height:44px;border-radius:12px;margin-bottom:14px;"></div>
        <div class="skeleton" style="width:60%;height:11px;margin-bottom:8px;"></div>
        <div class="skeleton" style="width:40%;height:28px;"></div>
    </div>
    <div class="skeleton-card">
        <div class="skeleton" style="width:44px;height:44px;border-radius:12px;margin-bottom:14px;"></div>
        <div class="skeleton" style="width:60%;height:11px;margin-bottom:8px;"></div>
        <div class="skeleton" style="width:40%;height:28px;"></div>
    </div>
    <div class="skeleton-card">
        <div class="skeleton" style="width:44px;height:44px;border-radius:12px;margin-bottom:14px;"></div>
        <div class="skeleton" style="width:60%;height:11px;margin-bottom:8px;"></div>
        <div class="skeleton" style="width:40%;height:28px;"></div>
    </div>
</div>

{{-- ===================== QUICK ACTIONS ===================== --}}
<p class="section-title">Menu cepat</p>
<div class="quick-actions" id="quickActions">
    {{-- Diisi oleh JS berdasarkan role --}}
</div>

{{-- ===================== CHARTS ===================== --}}
<p class="section-title">Statistik & grafik</p>
<div class="charts-grid" id="chartContainer" style="display:none;">
    <div class="chart-box">
        <div class="chart-box-header">
            <h3 id="chartTitle1">Memuat...</h3>
            <span class="chart-badge" id="chartBadge1">Live</span>
        </div>
        <div style="position:relative;height:260px;">
            <canvas id="chart1"></canvas>
        </div>
    </div>
    <div class="chart-box">
        <div class="chart-box-header">
            <h3 id="chartTitle2">Memuat...</h3>
            <span class="chart-badge" id="chartBadge2">Live</span>
        </div>
        <div style="position:relative;height:260px;">
            <canvas id="chart2"></canvas>
        </div>
    </div>
</div>

{{-- Skeleton charts --}}
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

@endsection

@push('scripts')
{{-- Chart.js CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>

<script>
(function () {
    // ==========================================
    // HELPERS
    // ==========================================
    const $ = id => document.getElementById(id);
    const userName = (window.user && window.user.name) ? window.user.name : 'Pengguna';
    const userRole = window.role || 'dosen';

    // Greeting berdasarkan jam
    function getGreeting() {
        const h = new Date().getHours();
        if (h < 11) return 'Selamat pagi';
        if (h < 15) return 'Selamat siang';
        if (h < 18) return 'Selamat sore';
        return 'Selamat malam';
    }

    // Set greeting banner
    const isAdmin = userRole === 'admin' || userRole === 'superadmin';
    $('greetingText').textContent   = `${getGreeting()}, ${userName}! 👋`;
    $('greetingSubtext').textContent = isAdmin
        ? 'Pantau semua aktivitas sistem Synapse dari sini.'
        : 'Berikut ringkasan materi dan kuis kamu hari ini.';
    $('greetingBadge').textContent  = isAdmin ? '👑 Admin' : '👨‍🏫 Dosen';

    // ==========================================
    // QUICK ACTIONS (role-aware)
    // ==========================================
    const adminActions = [
        { href: '/mata-kuliah',     cls: 'qa-teal',   icon: '📚', label: 'Mata Kuliah',   sub: 'Kelola & assign dosen' },
        { href: '/kelolaAkunDosen', cls: 'qa-blue',   icon: '👤', label: 'Kelola Dosen',  sub: 'Tambah / hapus akun' },
        { href: '/kuis',            cls: 'qa-purple',  icon: '📝', label: 'Kelola Kuis',   sub: 'Buat & atur soal' },
        { href: '/kelola-ar',       cls: 'qa-amber',  icon: '🌐', label: 'Aset AR',        sub: 'Upload model 3D' },
    ];
    const dosenActions = [
        { href: '/mata-kuliah',     cls: 'qa-teal',   icon: '📚', label: 'Materi Saya',   sub: 'Kelola e-modul' },
        { href: '/kuis',            cls: 'qa-purple',  icon: '📝', label: 'Kelola Kuis',   sub: 'Buat & atur soal' },
        { href: '/kelola-ar',       cls: 'qa-amber',  icon: '🌐', label: 'Aset AR',        sub: 'Upload model 3D' },
    ];

    const actions = isAdmin ? adminActions : dosenActions;
    $('quickActions').innerHTML = actions.map(a => `
        <a href="${a.href}" class="qa-btn ${a.cls}">
            <div class="qa-icon">${a.icon}</div>
            <div>
                <div class="qa-label">${a.label}</div>
                <div class="qa-sub">${a.sub}</div>
            </div>
        </a>
    `).join('');

    // ==========================================
    // CHART INSTANCES (destroy before re-render)
    // ==========================================
    let chart1Instance = null;
    let chart2Instance = null;

    function destroyCharts() {
        if (chart1Instance) { chart1Instance.destroy(); chart1Instance = null; }
        if (chart2Instance) { chart2Instance.destroy(); chart2Instance = null; }
    }

    // ==========================================
    // CHART.JS DEFAULTS (clean & modern)
    // ==========================================
    Chart.defaults.font.family = 'inter, sans-serif';
    Chart.defaults.font.size   = 12;
    Chart.defaults.color       = '#888';

    const TEAL   = '#279685';
    const BLUE   = '#4A90E2';
    const PURPLE = '#7c3aed';

    function makeBarOptions(yLabel) {
        return {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: { cornerRadius: 8 } },
            scales: {
                x: { grid: { display: false }, border: { display: false } },
                y: {
                    grid: { color: 'rgba(0,0,0,0.05)' },
                    border: { display: false },
                    ticks: { stepSize: 1 },
                    title: { display: !!yLabel, text: yLabel, font: { size: 11 } }
                }
            }
        };
    }

    function makeDoughnutOptions() {
        return {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '68%',
            plugins: {
                legend: { position: 'bottom', labels: { padding: 16, usePointStyle: true, pointStyle: 'circle' } },
                tooltip: { cornerRadius: 8 }
            }
        };
    }

    function makeLineOptions() {
        return {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: { cornerRadius: 8 } },
            scales: {
                x: { grid: { display: false }, border: { display: false } },
                y: {
                    grid: { color: 'rgba(0,0,0,0.05)' },
                    border: { display: false },
                    min: 0, max: 100,
                    ticks: { callback: v => v + '%' }
                }
            }
        };
    }

    // ==========================================
    // RENDER — ADMIN
    // ==========================================
    function renderAdmin(cards, charts) {
        $('statsContainer').innerHTML = `
            <div class="stat-card teal">
                <div class="stat-icon teal">👨‍🏫</div>
                <div class="stat-label">Total Dosen</div>
                <div class="stat-value">${cards.total_dosen ?? 0}</div>
                <div class="stat-sub">akun dosen aktif</div>
            </div>
            <div class="stat-card blue">
                <div class="stat-icon blue">🎓</div>
                <div class="stat-label">Total Mahasiswa</div>
                <div class="stat-value">${cards.total_mahasiswa ?? 0}</div>
                <div class="stat-sub">pengguna terdaftar</div>
            </div>
            <div class="stat-card purple">
                <div class="stat-icon purple">📚</div>
                <div class="stat-label">Total Materi</div>
                <div class="stat-value">${cards.total_materi ?? 0}</div>
                <div class="stat-sub">e-modul di sistem</div>
            </div>
            <div class="stat-card amber">
                <div class="stat-icon amber">🌐</div>
                <div class="stat-label">Aset AR Aktif</div>
                <div class="stat-value">${cards.total_ar ?? 0}</div>
                <div class="stat-sub">model 3D tersimpan</div>
            </div>
        `;

        // Chart 1 — Doughnut proporsi pengguna
        $('chartTitle1').textContent = 'Proporsi pengguna';
        $('chartBadge1').textContent = 'Sistem';
        chart1Instance = new Chart($('chart1'), {
            type: 'doughnut',
            data: {
                labels: charts.pie?.labels ?? ['Dosen', 'Mahasiswa'],
                datasets: [{
                    data: charts.pie?.data ?? [0, 0],
                    backgroundColor: [TEAL, BLUE],
                    borderWidth: 0,
                    hoverOffset: 6
                }]
            },
            options: makeDoughnutOptions()
        });

        // Chart 2 — Bar aktivitas unggah bulanan
        $('chartTitle2').textContent = 'Materi baru per bulan';
        $('chartBadge2').textContent = '5 bulan';
        chart2Instance = new Chart($('chart2'), {
            type: 'bar',
            data: {
                labels: charts.bar?.labels ?? [],
                datasets: [{
                    label: 'Materi baru',
                    data: charts.bar?.data ?? [],
                    backgroundColor: TEAL,
                    borderRadius: 8,
                    borderSkipped: false
                }]
            },
            options: makeBarOptions('Jumlah materi')
        });
    }

    // ==========================================
    // RENDER — DOSEN
    // ==========================================
    function renderDosen(cards, charts) {
        // Warna rata-rata nilai: hijau ≥70, kuning ≥50, merah <50
        const rata = parseFloat(cards.rata_nilai) || 0;
        const nilaiColor = rata >= 70 ? '#279685' : rata >= 50 ? '#f59e0b' : '#ef4444';

        $('statsContainer').innerHTML = `
            <div class="stat-card teal">
                <div class="stat-icon teal">📚</div>
                <div class="stat-label">Materi saya</div>
                <div class="stat-value">${cards.materi_saya ?? 0}</div>
                <div class="stat-sub">e-modul diunggah</div>
            </div>
            <div class="stat-card blue">
                <div class="stat-icon blue">📝</div>
                <div class="stat-label">Kuis aktif</div>
                <div class="stat-value">${cards.kuis_aktif ?? 0}</div>
                <div class="stat-sub">kuis tersedia untuk mahasiswa</div>
            </div>
            <div class="stat-card amber">
                <div class="stat-icon amber">⭐</div>
                <div class="stat-label">Rata-rata nilai</div>
                <div class="stat-value" style="color:${nilaiColor}">${rata > 0 ? rata.toFixed(1) : '—'}</div>
                <div class="stat-sub">${rata > 0 ? 'dari semua percobaan' : 'belum ada percobaan'}</div>
            </div>
            <div class="stat-card purple">
                <div class="stat-icon purple">👥</div>
                <div class="stat-label">Mahasiswa menjawab</div>
                <div class="stat-value">${cards.mahasiswa_hadir ?? 0}</div>
                <div class="stat-sub">unik berpartisipasi</div>
            </div>
        `;

        // ── Chart 1: Persebaran nilai grade A–E (bar per warna grade)
        $('chartTitle1').textContent = 'Persebaran nilai kuis';
        $('chartBadge1').textContent = 'Grade A – E';
        const gradeLabels = charts.bar?.labels ?? ['A (90–100)', 'B (80–89)', 'C (70–79)', 'D (60–69)', 'E (<60)'];
        const gradeData   = charts.bar?.data   ?? [0, 0, 0, 0, 0];
        const gradeColors = [TEAL, BLUE, PURPLE, '#f59e0b', '#ef4444'];
        chart1Instance = new Chart($('chart1'), {
            type: 'bar',
            data: {
                labels: gradeLabels,
                datasets: [{
                    label: 'Jumlah mahasiswa',
                    data: gradeData,
                    backgroundColor: gradeColors,
                    borderRadius: 8,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        cornerRadius: 8,
                        callbacks: {
                            label: ctx => ` ${ctx.parsed.y} mahasiswa`
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false }, border: { display: false } },
                    y: {
                        grid: { color: 'rgba(0,0,0,0.05)' },
                        border: { display: false },
                        ticks: { stepSize: 1, precision: 0 },
                        title: { display: true, text: 'Jumlah mahasiswa', font: { size: 11 } }
                    }
                }
            }
        });

        // ── Chart 2: Jumlah soal per kuis (horizontal bar)
        // Data dari charts.kuis_soal yang dikirim DashboardController
        // Fallback: tampilkan kuis aktif vs nonaktif kalau data soal tidak ada
        $('chartBadge2').textContent = 'Semua kuis';

        const kuisLabels = charts.kuis_soal?.labels ?? charts.kuis?.labels ?? null;
        const kuisData   = charts.kuis_soal?.data   ?? charts.kuis?.data   ?? null;

        if (kuisLabels && kuisLabels.length > 0) {
            $('chartTitle2').textContent = 'Jumlah soal per kuis';
            chart2Instance = new Chart($('chart2'), {
                type: 'bar',
                data: {
                    labels: kuisLabels,
                    datasets: [{
                        label: 'Jumlah soal',
                        data: kuisData,
                        backgroundColor: BLUE,
                        borderRadius: 6,
                        borderSkipped: false,
                    }]
                },
                options: {
                    indexAxis: 'y',   // horizontal bar
                    responsive: true, maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { cornerRadius: 8, callbacks: { label: ctx => ` ${ctx.parsed.x} soal` } }
                    },
                    scales: {
                        x: {
                            grid: { color: 'rgba(0,0,0,0.05)' },
                            border: { display: false },
                            ticks: { stepSize: 1, precision: 0 },
                            title: { display: true, text: 'Jumlah soal', font: { size: 11 } }
                        },
                        y: { grid: { display: false }, border: { display: false } }
                    }
                }
            });
        } else {
            // Fallback: doughnut kuis aktif vs nonaktif
            $('chartTitle2').textContent = 'Status kuis';
            const aktif    = cards.kuis_aktif    ?? 0;
            const nonaktif = cards.kuis_nonaktif ?? 0;
            chart2Instance = new Chart($('chart2'), {
                type: 'doughnut',
                data: {
                    labels: ['Aktif', 'Nonaktif'],
                    datasets: [{
                        data: [aktif, nonaktif],
                        backgroundColor: [TEAL, '#e5e7eb'],
                        borderWidth: 0,
                        hoverOffset: 6
                    }]
                },
                options: {
                    ...makeDoughnutOptions(),
                    plugins: {
                        ...makeDoughnutOptions().plugins,
                        tooltip: { cornerRadius: 8, callbacks: { label: ctx => ` ${ctx.parsed} kuis` } }
                    }
                }
            });
        }
    }

    // ==========================================
    // FETCH DASHBOARD STATS
    // ==========================================
    async function fetchDashboardStats() {
        try {
            const res = await fetch(window.apiBaseUrl + '/dashboard/stats', {
                headers: {
                    'Authorization': 'Bearer ' + window.token,
                    'Accept': 'application/json'
                }
            });

            // 401 → paksa logout
            if (res.status === 401) { logout(); return; }

            const data = await res.json();

            if (!res.ok || !data.success) {
                throw new Error(data.message || 'Gagal memuat data');
            }

            // Hilangkan skeleton, tampilkan chart area
            $('chartSkeleton').style.display  = 'none';
            $('chartContainer').style.display = 'grid';

            destroyCharts();

            // Tentukan renderer berdasarkan role yang dikembalikan API
            // (lebih aman daripada hanya pakai localStorage)
            const apiRole = data.role || userRole;
            if (apiRole === 'admin' || apiRole === 'superadmin') {
                renderAdmin(data.cards, data.charts);
            } else {
                renderDosen(data.cards, data.charts);
            }

        } catch (err) {
            console.error('[Dashboard]', err);
            $('chartSkeleton').style.display = 'none';
            $('statsContainer').innerHTML = `
                <div class="empty-state" style="grid-column:1/-1;">
                    <p style="font-size:32px;margin-bottom:10px;">⚠️</p>
                    <p style="font-weight:600;color:#555;margin-bottom:4px;">Gagal memuat data</p>
                    <p style="font-size:13px;">${err.message}</p>
                    <button onclick="fetchDashboardStats()" style="margin-top:16px;padding:8px 20px;background:#279685;color:#fff;border:none;border-radius:8px;cursor:pointer;font-weight:600;">
                        Coba lagi
                    </button>
                </div>
            `;
        }
    }

    // Expose untuk tombol "Coba lagi"
    window.fetchDashboardStats = fetchDashboardStats;

    // Jalankan
    fetchDashboardStats();
})();
</script>
@endpush