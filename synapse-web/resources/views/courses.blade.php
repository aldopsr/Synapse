@extends('layouts.app')

@section('title', 'Kelola Mata Kuliah - Synapse')
@section('header_title', 'Mata Kuliah')

@section('content')
    <style>
        /* CSS KHUSUS HALAMAN MATA KULIAH */
        .header-actions { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .btn-tambah { background-color: #279685; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 600; transition: 0.2s; }
        .btn-tambah:hover { background-color: #1f7a6c; transform: translateY(-2px); }
        
        /* TABEL */
        .table-container { background: white; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); overflow: hidden; border: 1px solid #eee; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 15px 20px; text-align: left; border-bottom: 1px solid #f0f0f0; }
        th { background-color: #E3FAF8; color: #333; font-weight: 600; }
        tr:last-child td { border-bottom: none; }
        
        /* MODAL (POP-UP) */
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 1000; }
        .modal-content { background: white; padding: 30px; border-radius: 12px; width: 400px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .modal-content h3 { margin-top: 0; margin-bottom: 20px; color: #333; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px; color: #555; }
        .form-group input, .form-group textarea { width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; font-family: inherit; }
        .btn-simpan { background-color: #279685; color: white; border: none; padding: 12px; width: 100%; border-radius: 6px; cursor: pointer; margin-top: 10px; font-weight: bold; }
        .btn-tutup { background-color: #f1f1f1; color: #333; border: none; padding: 12px; width: 100%; border-radius: 6px; cursor: pointer; margin-top: 10px; font-weight: bold; }
        .btn-tutup:hover { background-color: #ddd; }
    </style>

    <div class="header-actions">
        <h2 style="margin: 0; font-size: 20px; color: #333;">Daftar Mata Kuliah Saya</h2>
        <button class="btn-tambah" onclick="bukaModal()">+ Tambah Matkul</button>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Mata Kuliah</th>
                    <th>Deskripsi</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody id="tabelMatkul">
                <tr><td colspan="4" style="text-align: center; color: #888;">Loading data...</td></tr>
            </tbody>
        </table>
    </div>

    <div id="modalMatkul" class="modal">
        <div class="modal-content">
            <h3>Tambah Mata Kuliah Baru</h3>
            <form id="formMatkul">
                <div class="form-group">
                    <label>Judul Mata Kuliah</label>
                    <input type="text" id="title" required placeholder="Contoh: Pemrograman Web">
                </div>
                <div class="form-group">
                    <label>Deskripsi Singkat</label>
                    <textarea id="description" rows="3" placeholder="Contoh: Belajar HTML, CSS, JS"></textarea>
                </div>
                <button type="submit" class="btn-simpan">Simpan Mata Kuliah</button>
                <button type="button" class="btn-tutup" onclick="tutupModal()">Batal</button>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Catatan: variabel 'token' sudah tidak perlu didefinisikan lagi 
    // karena sudah dideklarasikan secara global di layouts/app.blade.php

    const apiUrl = 'http://127.0.0.1:8000/api/courses'; 

    // FUNGSI 1: AMBIL DATA DARI API
    async function fetchCourses() {
        try {
            const response = await fetch(apiUrl, {
                method: 'GET',
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Accept': 'application/json'
                }
            });
            const data = await response.json();
            
            if (data.success) {
                renderTable(data.data);
            } else {
                alert("Gagal mengambil data");
            }
        } catch (error) {
            console.error("Error:", error);
            document.getElementById('tabelMatkul').innerHTML = '<tr><td colspan="4" style="text-align: center; color: red;">Gagal terhubung ke server.</td></tr>';
        }
    }

    // FUNGSI 2: TAMPILKAN KE TABEL
    function renderTable(courses) {
        const tbody = document.getElementById('tabelMatkul');
        tbody.innerHTML = '';

        if (!courses || courses.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" style="text-align: center; padding: 30px;">Belum ada mata kuliah.</td></tr>';
            return;
        }

        courses.forEach((course, index) => {
            const idMatkul = course._id || course.id; 
            
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${index + 1}</td>
                <td><b>${course.title}</b></td>
                <td>${course.description || '-'}</td>
                <td>
                    <button onclick="window.location.href='/mata-kuliah/${idMatkul}/materi'" style="background:#4A90E2; color:white; border:none; padding:8px 12px; border-radius:6px; cursor:pointer; font-weight:600;">
                        Kelola Materi
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    // FUNGSI 3: KIRIM DATA KE API (POST)
    document.getElementById('formMatkul').addEventListener('submit', async function(e) {
        e.preventDefault(); 
        
        const title = document.getElementById('title').value;
        const description = document.getElementById('description').value;

        try {
            const response = await fetch(apiUrl, {
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ title, description })
            });

            const data = await response.json();
            
            if (data.success) {
                alert("Mata Kuliah berhasil ditambahkan!");
                tutupModal(); 
                fetchCourses(); 
                document.getElementById('formMatkul').reset(); 
            } else {
                alert("Gagal menambahkan data!");
            }
        } catch (error) {
            console.error("Error:", error);
        }
    });

    // FUNGSI MODAL
    function bukaModal() { document.getElementById('modalMatkul').style.display = 'flex'; }
    function tutupModal() { document.getElementById('modalMatkul').style.display = 'none'; }

    // Panggil fungsi saat halaman pertama kali dimuat
    fetchCourses();
</script>
@endpush