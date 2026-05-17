@extends('layouts.app')

@section('title', 'Kelola Kuis - Synapse')
@section('header_title', 'Kelola Kuis')

@section('content')
<style>
    .header-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 10px;
    }
    .btn-tambah {
        background: linear-gradient(135deg, #279685, #1f7a6c);
        color: white;
        border: none;
        padding: 11px 20px;
        border-radius: 8px;
        font-weight: 600;
        text-decoration: none;
        transition: 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .btn-tambah:hover { transform: translateY(-2px); box-shadow: 0 6px 14px rgba(39,150,133,0.3); }

    .filter-bar {
        background: white;
        padding: 15px 20px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        margin-bottom: 20px;
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        align-items: center;
    }
    .filter-bar label { font-weight: 600; font-size: 13px; color: #555; }
    .filter-bar select {
        padding: 9px 14px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 13px;
        background: #f8f9fa;
        cursor: pointer;
    }
    .filter-pill {
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        border: 1px solid #ddd;
        background: white;
        transition: 0.2s;
    }
    .filter-pill.active { background: #279685; color: white; border-color: #279685; }

    .table-container {
        background: white;
        border-radius: 14px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.05);
        overflow: hidden;
        border: 1px solid #eee;
    }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 14px 18px; border-bottom: 1px solid #f1f1f1; }
    th { background: #E3FAF8; font-weight: 700; font-size: 13px; color: #333; text-align: left; }
    tr:hover { background: #fafafa; }

    /* Status badges */
    .status-badge {
        padding: 5px 11px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    .status-aktif { background: #d4edda; color: #1e7e34; }
    .status-nonaktif { background: #f8d7da; color: #721c24; }
    .status-belum_mulai { background: #fff3cd; color: #856404; }
    .status-sudah_selesai { background: #e2e3e5; color: #495057; }

    /* Toggle switch */
    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 44px;
        height: 24px;
    }
    .toggle-switch input { opacity: 0; width: 0; height: 0; }
    .toggle-slider {
        position: absolute;
        cursor: pointer;
        top: 0; left: 0; right: 0; bottom: 0;
        background-color: #ccc;
        border-radius: 24px;
        transition: 0.3s;
    }
    .toggle-slider:before {
        position: absolute;
        content: "";
        height: 18px; width: 18px;
        left: 3px; bottom: 3px;
        background-color: white;
        border-radius: 50%;
        transition: 0.3s;
    }
    input:checked + .toggle-slider { background-color: #279685; }
    input:checked + .toggle-slider:before { transform: translateX(20px); }

    /* Action buttons */
    .action-buttons { display: flex; gap: 6px; flex-wrap: wrap; }
    .icon-btn {
        border: none;
        padding: 7px 11px;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
        font-size: 12px;
        transition: 0.2s;
        text-decoration: none;
        color: white;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .btn-soal { background: #3498db; }
    .btn-soal:hover { background: #2c80b4; }
    .btn-edit { background: #f39c12; }
    .btn-edit:hover { background: #d68910; }
    .btn-delete { background: #e74c3c; }
    .btn-delete:hover { background: #c0392b; }

    /* Schedule info */
    .schedule-info { font-size: 12px; color: #666; line-height: 1.6; }
    .schedule-info .label { font-weight: 600; color: #279685; }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #888;
    }
    .empty-state .icon { font-size: 48px; margin-bottom: 12px; }
</style>

<div class="header-actions">
    <h2 style="margin: 0; font-size: 20px; color: #333;">Daftar Kuis</h2>
    <a href="/kuis/buat" class="btn-tambah">+ Buat Kuis Baru</a>
</div>

<!-- Filter Bar -->
<div class="filter-bar">
    <label>Filter Status:</label>
    <button class="filter-pill active" data-filter="semua">Semua</button>
    <button class="filter-pill" data-filter="aktif">🟢 Aktif</button>
    <button class="filter-pill" data-filter="belum_mulai">🟡 Belum Mulai</button>
    <button class="filter-pill" data-filter="sudah_selesai">⚪ Selesai</button>
    <button class="filter-pill" data-filter="nonaktif">🔴 Nonaktif</button>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Judul Kuis</th>
                <th>Mata Kuliah</th>
                <th>Soal</th>
                <th>Schedule</th>
                <th>Status</th>
                <th>Aktif</th>
                <th style="width:300px;">Aksi</th>
            </tr>
        </thead>
        <tbody id="tabelKuis">
            <tr>
                <td colspan="8" style="text-align:center; padding:30px; color:#888;">Loading data...</td>
            </tr>
        </tbody>
    </table>
</div>
@endsection

@push('scripts')
<script>
(function() {
    const token = window.token || localStorage.getItem('token');
    if (!token) { window.location.href = '/'; return; }

    const API_BASE = window.apiBaseUrl;
    let allQuizzes = [];
    let activeFilter = 'semua';

    fetchQuizzes();

    async function fetchQuizzes() {
        try {
            const res = await fetch(`${API_BASE}/admin/quizzes`, {
                headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
            });
            const data = await res.json();
            allQuizzes = data.data || [];
            renderTable();
        } catch (e) {
            console.error(e);
            document.getElementById('tabelKuis').innerHTML =
                '<tr><td colspan="8" style="text-align:center;color:red;">Gagal memuat data.</td></tr>';
        }
    }

    function renderTable() {
        const tbody = document.getElementById('tabelKuis');
        tbody.innerHTML = '';

        // Filter berdasarkan status aktif
        const filtered = activeFilter === 'semua'
            ? allQuizzes
            : allQuizzes.filter(q => q.status === activeFilter);

        if (filtered.length === 0) {
            tbody.innerHTML = `
                <tr><td colspan="8">
                    <div class="empty-state">
                        <div class="icon">📝</div>
                        <strong>Belum ada kuis</strong><br>
                        <small>Klik tombol "Buat Kuis Baru" untuk mulai membuat kuis pertama.</small>
                    </div>
                </td></tr>`;
            return;
        }

        filtered.forEach((quiz, idx) => {
            const id = quiz._id || quiz.id;
            const status = quiz.status || 'aktif';
            const statusLabel = {
                aktif: '🟢 Aktif',
                nonaktif: '🔴 Nonaktif',
                belum_mulai: '🟡 Belum Mulai',
                sudah_selesai: '⚪ Selesai'
            }[status] || status;

            const courseTitle = quiz.course ? quiz.course.title : '<i style="color:#aaa;">tidak ada</i>';
            const questionsCount = quiz.questions_count || 0;

            // Format schedule
            let scheduleHtml = '<span style="color:#aaa; font-size:12px;">Tidak terjadwal</span>';
            if (quiz.start_at || quiz.end_at) {
                const start = quiz.start_at ? formatDate(quiz.start_at) : 'Sekarang';
                const end = quiz.end_at ? formatDate(quiz.end_at) : 'Selamanya';
                scheduleHtml = `
                    <div class="schedule-info">
                        <span class="label">Mulai:</span> ${start}<br>
                        <span class="label">Selesai:</span> ${end}
                    </div>`;
            }

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${idx + 1}</td>
                <td>
                    <strong>${escapeHtml(quiz.title || 'Tanpa Judul')}</strong>
                    ${quiz.description ? `<br><small style="color:#888;">${escapeHtml(quiz.description.substring(0,60))}${quiz.description.length>60?'...':''}</small>` : ''}
                    <br><small style="color:#999;">⏱ ${quiz.duration_minutes || 0} menit</small>
                </td>
                <td>${escapeHtml(courseTitle)}</td>
                <td><span style="font-weight:bold; color:${questionsCount > 0 ? '#279685' : '#aaa'}">${questionsCount} soal</span></td>
                <td>${scheduleHtml}</td>
                <td><span class="status-badge status-${status}">${statusLabel}</span></td>
                <td>
                    <label class="toggle-switch">
                        <input type="checkbox" ${quiz.is_active ? 'checked' : ''} onchange="toggleQuiz('${id}')">
                        <span class="toggle-slider"></span>
                    </label>
                </td>
                <td>
                    <div class="action-buttons">
                        <a href="/kuis/${id}/soal" class="icon-btn btn-soal">📝 Soal</a>
                        <a href="/kuis/${id}/edit" class="icon-btn btn-edit">✏ Edit</a>
                        <button class="icon-btn btn-delete" onclick="hapusQuiz('${id}', '${escapeJs(quiz.title)}')">🗑</button>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    function formatDate(dateStr) {
        const d = new Date(dateStr);
        if (isNaN(d.getTime())) return dateStr;
        return d.toLocaleString('id-ID', {
            day: '2-digit', month: 'short', year: 'numeric',
            hour: '2-digit', minute: '2-digit'
        });
    }

    function escapeHtml(s) {
        if (!s) return '';
        return String(s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function escapeJs(s) {
        if (!s) return '';
        return String(s).replace(/'/g, "\\'").replace(/"/g, '\\"');
    }

    // Filter pill click
    document.querySelectorAll('.filter-pill').forEach(pill => {
        pill.addEventListener('click', () => {
            document.querySelectorAll('.filter-pill').forEach(p => p.classList.remove('active'));
            pill.classList.add('active');
            activeFilter = pill.dataset.filter;
            renderTable();
        });
    });

    // ========================================
    // TOGGLE AKTIF/NONAKTIF
    // ========================================
    window.toggleQuiz = async function(id) {
        try {
            const res = await fetch(`${API_BASE}/admin/quizzes/${id}/toggle`, {
                method: 'POST',
                headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
            });
            if (res.ok) {
                const data = await res.json();
                // Update data lokal supaya tabel ke-refresh
                fetchQuizzes();
            } else {
                alert('Gagal mengubah status quiz');
                fetchQuizzes();
            }
        } catch (e) {
            alert('Error jaringan: ' + e.message);
            fetchQuizzes();
        }
    };

    // ========================================
    // HAPUS QUIZ
    // ========================================
    window.hapusQuiz = async function(id, title) {
        if (!confirm(`Yakin mau hapus quiz "${title}"?\nSemua soal di dalamnya juga akan terhapus permanen.`)) return;

        try {
            const res = await fetch(`${API_BASE}/admin/quizzes/${id}`, {
                method: 'DELETE',
                headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
            });
            if (res.ok) {
                alert('✅ Quiz berhasil dihapus');
                fetchQuizzes();
            } else {
                const err = await res.json();
                alert('❌ Gagal hapus: ' + (err.message || 'Cek koneksi'));
            }
        } catch (e) {
            alert('Error jaringan: ' + e.message);
        }
    };
})();
</script>
@endpush