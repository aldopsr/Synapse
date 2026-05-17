@extends('layouts.app')

@section('title', 'Dashboard - Synapse')
@section('header_title', 'Dashboard')

@section('content')
    <style>
        /* Styling khusus untuk Dashboard */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 25px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); display: flex; flex-direction: column; position: relative; overflow: hidden; border: 1px solid #eee; }
        .stat-card::before { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 6px; }
        .stat-card.green::before { background-color: #279685; }
        .stat-card.blue::before { background-color: #4A90E2; }
        .stat-card h3 { margin: 0; color: #666; font-size: 15px; font-weight: 600; }
        .stat-card p { margin: 10px 0 0 0; font-size: 32px; font-weight: 700; color: #222; }
        
        .charts-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 25px; }
        .chart-box { background: white; padding: 25px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid #eee; }
        .chart-box h3 { margin-top: 0; margin-bottom: 20px; color: #444; font-size: 16px; border-bottom: 1px solid #eee; padding-bottom: 15px; }
    </style>

    <div class="stats-grid" id="statsContainer">
        <p style="color: #888;">Memuat data statistik...</p>
    </div>

    <div class="charts-grid" style="display: none;" id="chartContainer">
        <div class="chart-box">
            <h3 id="chartTitle1">Loading...</h3>
            <div style="position: relative; height: 300px; width: 100%;">
                <canvas id="chart1"></canvas>
            </div>
        </div>

        <div class="chart-box">
            <h3 id="chartTitle2">Loading...</h3>
            <div style="position: relative; height: 300px; width: 100%;">
                <canvas id="chart2"></canvas>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    async function fetchDashboardStats() {
        try {
            const response = await fetch(window.apiBaseUrl + '/dashboard/stats', {
                method: 'GET',
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });

            const data = await response.json();

            if (response.ok && data.success) {
                document.getElementById('chartContainer').style.display = 'grid'; // Tampilkan container chart
                renderDashboard(data);
            } else {
                console.error("Gagal mengambil data:", data.message);
                if(response.status === 401) logout();
            }
        } catch (error) {
            console.error('Terjadi kesalahan:', error);
            document.getElementById('statsContainer').innerHTML = "<p style='color:red;'>Gagal memuat data server. Pastikan API berjalan.</p>";
        }
    }

    function renderDashboard(data) {
        const stats = data.cards;
        const charts = data.charts;
        const container = document.getElementById('statsContainer');

        // ==== TAMPILAN KHUSUS ADMIN ====
        if (role === 'admin' || role === 'superadmin') {
            container.innerHTML = `
                <div class="stat-card green">
                    <h3>Total Dosen</h3><p>${stats.total_dosen || 0}</p>
                </div>
                <div class="stat-card blue">
                    <h3>Total Mahasiswa</h3><p>${stats.total_mahasiswa || 0}</p>
                </div>
                <div class="stat-card green">
                    <h3>Total Materi</h3><p>${stats.total_materi || 0}</p>
                </div>
                <div class="stat-card blue">
                    <h3>File AR Aktif</h3><p>${stats.total_ar || 0}</p>
                </div>
            `;

            document.getElementById('chartTitle1').innerText = "Proporsi Pengguna";
            new Chart(document.getElementById('chart1'), {
                type: 'doughnut', // Doughnut terlihat lebih modern dari pie
                data: {
                    labels: charts.pie?.labels || ['Dosen', 'Mahasiswa'],
                    datasets: [{ data: charts.pie?.data || [0,0], backgroundColor: ['#279685', '#4A90E2'] }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });

            document.getElementById('chartTitle2').innerText = "Aktivitas Unggah Bulanan";
            new Chart(document.getElementById('chart2'), {
                type: 'bar',
                data: {
                    labels: charts.bar?.labels || [],
                    datasets: [{ label: 'Materi Baru', data: charts.bar?.data || [], backgroundColor: '#279685', borderRadius: 5 }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });

        // ==== TAMPILAN KHUSUS DOSEN ====
        } else {
            container.innerHTML = `
                <div class="stat-card green">
                    <h3>Materi Saya</h3><p>${stats.materi_saya || 0}</p>
                </div>
                <div class="stat-card blue">
                    <h3>Kuis Aktif</h3><p>${stats.kuis_aktif || 0}</p>
                </div>
                <div class="stat-card green">
                    <h3>Rata-rata Nilai</h3><p>${stats.rata_nilai || 0}</p>
                </div>
                <div class="stat-card blue">
                    <h3>Mahasiswa Menjawab</h3><p>${stats.mahasiswa_hadir || 0}</p>
                </div>
            `;

            document.getElementById('chartTitle1').innerText = "Persebaran Nilai Kuis (A-E)";
            new Chart(document.getElementById('chart1'), {
                type: 'bar',
                data: {
                    labels: charts.bar?.labels || ['A', 'B', 'C', 'D', 'E'],
                    datasets: [{ label: 'Jumlah Mahasiswa', data: charts.bar?.data || [0,0,0,0,0], backgroundColor: '#279685', borderRadius: 5 }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });

            document.getElementById('chartTitle2').innerText = "Partisipasi Kuis per Minggu";
            new Chart(document.getElementById('chart2'), {
                type: 'line',
                data: {
                    labels: charts.line?.labels || [],
                    datasets: [{ 
                        label: 'Kehadiran (%)', 
                        data: charts.line?.data || [], 
                        borderColor: '#4A90E2', 
                        backgroundColor: 'rgba(74, 144, 226, 0.1)',
                        fill: true,
                        tension: 0.4 // Membuat garis melengkung halus
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
        }
    }

    // Jalankan saat halaman dimuat
    fetchDashboardStats();
</script>
@endpush