@extends('layouts.app')

@section('title', 'Kelola Materi Mata Kuliah - Synapse')
@section('header_title', 'Daftar Materi')

@section('content')
<style>
    .top-navigation { margin-bottom: 20px; }

    .btn-kembali {
        background: #f5f7fa;
        color: #333;
        padding: 10px 18px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        font-size: 14px;
        border: 1px solid #ddd;
        transition: 0.2s;
    }
    .btn-kembali:hover { background: #e9ecef; }

    .header-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .btn-tambah {
        background: linear-gradient(135deg, #279685, #1f7a6c);
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
        text-decoration: none;
        transition: 0.2s;
    }
    .btn-tambah:hover { transform: translateY(-2px); }

    .table-container {
        background: white;
        border-radius: 14px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.05);
        overflow: hidden;
        border: 1px solid #eee;
    }

    table { width: 100%; border-collapse: collapse; }

    th, td {
        padding: 16px 20px;
        border-bottom: 1px solid #f1f1f1;
    }

    th {
        background: #E3FAF8;
        font-weight: 700;
        font-size: 14px;
        color: #333;
    }

    tr:hover { background: #fafafa; }

    .badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
        display: inline-block;
    }

    .badge-yes {
        background: #d4edda;
        color: #1e7e34;
    }

    .badge-no {
        background: #f8d7da;
        color: #721c24;
    }

    .action-buttons {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .icon-btn {
        border: none;
        padding: 8px 10px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        font-size: 13px;
        transition: 0.2s;
        text-decoration: none;
        color: white;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .btn-edit { background: #f39c12; }
    .btn-edit:hover { background: #d68910; }

    .btn-delete { background: #e74c3c; }
    .btn-delete:hover { background: #c0392b; }

    /* Penyesuaian tombol kelola latihan */
    .btn-practice { background: #3498db; }
    .btn-practice:hover { background: #2c80b4; }
</style>

<div class="top-navigation">
    <a href="/mata-kuliah" class="btn-kembali" id="btnKembali">⬅ Kembali ke Daftar Matkul</a>
</div>

<div class="header-actions">
    <h2 style="margin: 0; font-size: 20px; color: #333;" id="judulHalaman">Materi Mata Kuliah</h2>
    <a href="/mata-kuliah/{{ $course_id }}/tambah-materi" class="btn-tambah" id="btnTambah">+ Tambah Materi</a>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Judul Materi</th>
                <th>Deskripsi</th>
                <th>Status Latihan</th>
                <th style="width:280px;">Aksi</th>
            </tr>
        </thead>
        <tbody id="tabelMateri">
            <tr>
                <td colspan="5" style="text-align:center; padding:30px; color:#888;">
                    Loading data...
                </td>
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

    let activeCourseId = "{{ $course_id ?? '' }}"; 
    const userStr = localStorage.getItem('user');

    if (userStr) {
        try { 
            const user = JSON.parse(userStr); 
            if (user.role === 'dosen') {
                activeCourseId = user.course_id; 
                document.getElementById('btnKembali').style.display = 'none';
                document.getElementById('judulHalaman').innerText = "Materi Kuliah Saya";
                
                // Update href tombol tambah karena id course bisa jadi dari localStorage
                document.getElementById('btnTambah').href = `/mata-kuliah/${activeCourseId}/tambah-materi`;
            }
        } catch (e) {}
    }

    const baseUrl = `${window.apiBaseUrl}/api/courses/${activeCourseId}/materials`;

    if (activeCourseId) fetchMaterials();

    async function fetchMaterials() {
        try {
            const response = await fetch(baseUrl, {
                headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
            });
            const data = await response.json();
            if (response.ok) renderTable(data.data || data);
        } catch (error) {
            document.getElementById('tabelMateri').innerHTML =
                '<tr><td colspan="5" style="text-align:center;color:red;">Gagal memuat data.</td></tr>';
        }
    }

    function renderTable(materials) {
    const tbody = document.getElementById('tabelMateri');
    tbody.innerHTML = '';

    if (!materials || materials.length === 0) {
        tbody.innerHTML =
            '<tr><td colspan="5" style="text-align:center;padding:30px;">Belum ada materi.</td></tr>';
        return;
    }

    materials.forEach((materi, index) => {
        const materiId = materi._id || materi.id;
        
        // Logika Status Latihan (Practice)
        const hasPractice = (materi.questions && materi.questions.length > 0) || materi.has_practice; 

        const badge = hasPractice
            ? `<span class="badge badge-yes">Ada Soal</span>`
            : `<span class="badge badge-no">Belum Ada Soal</span>`;

        const hasAr = (materi.ar_assets && materi.ar_assets.length > 0) || 
                  (materi.ar_assets_count > 0) || 
                  materi.has_ar; 

        const arBadge = hasAr 
            ? `<span class="badge" style="background:#E1BEE7; color:#4A148C; margin-left:8px; font-size:11px; padding:4px 8px;">✨ AR Ready</span>` 
            : '';

        // Tombol "Kelola Practice" selalu muncul
        const practiceBtn = `<a href="/mata-kuliah/${activeCourseId}/materi/${materiId}/practice" class="icon-btn btn-practice">🎯 Kelola Latihan</a>`;

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${index + 1}</td>
            <td><strong>${materi.title}</strong> ${arBadge}</td>
            <td>${materi.description || '-'}</td>
            <td>${badge}</td>
            <td>
                <div class="action-buttons">
                    ${practiceBtn}
                    <a href="/mata-kuliah/${activeCourseId}/edit-materi/${materiId}" class="icon-btn btn-edit">✏ Edit</a>
                    <button class="icon-btn btn-delete" onclick="hapusMateri('${materiId}')">🗑 Hapus</button>
                </div>
            </td>
        `;
        tbody.appendChild(tr);
    });
}
    window.hapusMateri = async function(materiId) {
        if(confirm("Yakin ingin menghapus materi ini?")) {
            try {
                const response = await fetch(`${window.apiBaseUrl}/api/materials/${materiId}`, {
                    method: 'DELETE',
                    headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
                });

                if(response.ok) {
                    alert('Materi dihapus!');
                    fetchMaterials(); 
                } else {
                    const data = await response.json();
                    alert('Gagal menghapus: ' + (data.message || 'Cek rute DELETE di Laravel'));
                }
            } catch (error) {
                alert('Gagal terhubung ke server saat menghapus.');
            }
        }
    };
})();
</script>
@endpush