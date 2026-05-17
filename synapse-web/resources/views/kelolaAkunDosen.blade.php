<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Synapse') }}</title>
    <link rel="stylesheet" href="{{asset('assets/css/style.css')}}">
    <style>
        /* Grid disesuaikan untuk 4 kolom: Nama, Matkul, Statistik, Aksi */
        .table-crud {
            display: grid;
            grid-template-columns: 2fr 2fr 1.5fr 1fr; 
            width: 100%;
            border-radius: 8px;
            overflow: hidden;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
        }
        .btn-edit { background-color: #F5A623; color: white; padding: 5px 15px; border-radius: 5px; text-decoration: none; font-size: 14px; }
        .btn-delete { background-color: #D0021B; color: white; padding: 5px 15px; border-radius: 5px; border: none; cursor: pointer; font-size: 14px; }
        .stats-badge { font-size: 13px; background: #279685; color: white; padding: 2px 8px; border-radius: 12px; margin-right: 5px;}
    </style>
</head>
<body>
    
    <div class="sidebar">
        <a href="/dashboard" class="menu-item" style="background: none;">
            <img src="{{asset('assets/image/synapseLogo.png')}}" style="width: 50px; height: auto;">
            <h1 style="text-align:center; width:100%">SYNAPSE</h1>
        </a>

        <div class="menu">
            <a href="/dashboard" class="menu-item">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#FFFFFF" style="width:27px; height:auto;">
                    <path d="M11.47 3.841a.75.75 0 0 1 1.06 0l8.69 8.69a.75.75 0 1 0 1.06-1.061l-8.689-8.69a2.25 2.25 0 0 0-3.182 0l-8.69 8.69a.75.75 0 1 0 1.061 1.06l8.69-8.689Z" />
                    <path d="m12 5.432 8.159 8.159c.03.03.06.058.091.086v6.198c0 1.035-.84 1.875-1.875 1.875H15a.75.75 0 0 1-.75-.75v-4.5a.75.75 0 0 0-.75-.75h-3a.75.75 0 0 0-.75.75V21a.75.75 0 0 1-.75.75H5.625a1.875 1.875 0 0 1-1.875-1.875v-6.198a2.29 2.29 0 0 0 .091-.086L12 5.432Z" />
                </svg>
                <p>Dashboard</p>
            </a>
            <a href="/kelolaAkunDosen" class="menu-item">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#FFFFFF" style="width:27px; height:auto;">
                    <path fill-rule="evenodd" d="M18.685 19.097A9.723 9.723 0 0 0 21.75 12c0-5.385-4.365-9.75-9.75-9.75S2.25 6.615 2.25 12a9.723 9.723 0 0 0 3.065 7.097A9.716 9.716 0 0 0 12 21.75a9.716 9.716 0 0 0 6.685-2.653Zm-12.54-1.285A7.486 7.486 0 0 1 12 15a7.486 7.486 0 0 1 5.855 2.812A8.224 8.224 0 0 1 12 20.25a8.224 8.224 0 0 1-5.855-2.438ZM15.75 9a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" clip-rule="evenodd" />
                </svg>
                <p>Kelola Akun Dosen</p>
            </a>
        </div>
    </div>

    <div class="main" style="gap: 40px;">

        <div class="topbar">
            <h2 style="color: #44474D;">Kelola Akun Dosen</h2>
            <a href="/buatAkunDosen" class="button1" style="width: 250px;">TAMBAH AKUN DOSEN</a>
        </div>

        <div class="table table-crud">
            <div class="table-text" style="justify-content:flex-start; padding-left: 20px; font-size: 20px; font-weight:600;">Nama Dosen</div>
            <div class="table-text" style="justify-content:center; font-size: 20px; font-weight:600;">Mata Kuliah</div>
            <div class="table-text" style="justify-content:center; font-size: 20px; font-weight:600;">Statistik</div>
            <div class="table-text" style="justify-content:center; font-size: 20px; font-weight:600;">Aksi</div>

            <div id="tableBody" style="display: contents;">
                <div class="table-text" style="grid-column: span 4; justify-content:center;">Memuat data...</div>
            </div>
        </div>
    </div>

    <script>
        const apiDosenUrl = window.apiBaseUrl + '/api/dosen'; 

        document.addEventListener('DOMContentLoaded', fetchDosen);

        async function fetchDosen() {
            const tableBody = document.getElementById('tableBody');
            
            try {
                const response = await fetch(apiDosenUrl, {
                    headers: {
                        'Accept': 'application/json',
                        'Authorization': 'Bearer ' + window.token
                    }
                });

                const result = await response.json();

                if (response.ok && result.data) {
                    tableBody.innerHTML = ''; 

                    if(result.data.length === 0) {
                        tableBody.innerHTML = '<div class="table-text" style="grid-column: span 4; justify-content:center;">Belum ada akun dosen.</div>';
                        return;
                    }

                    result.data.forEach((dosen, index) => {
                        const bgClass = index % 2 === 0 ? 'green-background' : '';
                        
                        // AMBIL DATA DARI BACKEND (Pastikan nama key/field-nya sesuai dengan response JSON API Kapten)
                        // Asumsi: dosen.course.title berisi nama matkul, dosen.materis_count, dll
                        const matkulName = dosen.course ? dosen.course.title : 'Belum Ditugaskan';
                        const totalMateri = dosen.materis_count || 0;
                        const totalQuiz = dosen.quizzes_count || 0;

                        const rowHTML = `
                            <div class="table-text ${bgClass}" style="padding-left: 20px; justify-content:flex-start;">
                                <div>
                                    <span style="font-weight:bold; display:block;">${dosen.name}</span>
                                    <span style="font-size:12px; color:#667C89;">${dosen.email}</span>
                                </div>
                            </div>
                            <div class="table-text ${bgClass}" style="justify-content:center;">${matkulName}</div>
                            <div class="table-text ${bgClass}" style="justify-content:center; flex-direction:column; gap:4px;">
                                <div><span class="stats-badge">${totalMateri}</span> Materi</div>
                                <div><span class="stats-badge">${totalQuiz}</span> Quiz</div>
                            </div>
                            <div class="table-text ${bgClass} action-buttons">
                                <a href="/editAkunDosen?id=${dosen.id}" class="btn-edit">Edit</a>
                                <button onclick="deleteDosen('${dosen.id}')" class="btn-delete">Hapus</button>
                            </div>
                        `;
                        tableBody.innerHTML += rowHTML;
                    });
                }
            } catch (error) {
                tableBody.innerHTML = '<div class="table-text" style="grid-column: span 4; justify-content:center; color: red;">Koneksi error.</div>';
            }
        }

        async function deleteDosen(id) {
            if (!confirm('Hapus akun dosen ini beserta semua datanya?')) return;
            try {
                const response = await fetch(`${apiDosenUrl}/${id}`, {
                    method: 'DELETE',
                    headers: { 'Authorization': 'Bearer ' + window.token }
                });
                if (response.ok) fetchDosen(); 
                else alert('Gagal menghapus.');
            } catch (error) {
                alert('Terjadi masalah jaringan.');
            }
        }
    </script>
</body>
</html>