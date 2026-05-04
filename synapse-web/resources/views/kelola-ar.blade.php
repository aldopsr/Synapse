@extends('layouts.app')

@section('title', 'Kelola Aset AR - Synapse')
@section('header_title', 'Kelola Aset Augmented Reality (AR)')

@section('content')
<style>
    .layout-container { display: flex; gap: 25px; flex-wrap: wrap; align-items: flex-start; }
    .card { background: white; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #eee; padding: 25px; box-sizing: border-box; }
    .col-kiri { flex: 1; min-width: 300px; max-width: 400px; }
    .col-kanan { flex: 2; min-width: 400px; }

    .card-title { font-size: 16px; font-weight: bold; color: #333; margin-top: 0; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
    
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; margin-bottom: 6px; font-weight: 600; font-size: 13px; color: #555; }
    .form-control { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 8px; box-sizing: border-box; font-family: 'Inter', sans-serif; }
    
    .btn-simpan { background-color: #4A148C; color: white; border: none; padding: 12px 20px; border-radius: 8px; cursor: pointer; font-weight: bold; width: 100%; transition: 0.2s; }
    .btn-simpan:hover { background-color: #380b6e; }

    /* Tabel AR */
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #eee; }
    th { background: #F3E5F5; color: #4A148C; font-size: 14px; }
    .badge-ar { background: #E1BEE7; color: #4A148C; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: bold; }
</style>

<div class="layout-container">
    
    <div class="card col-kiri">
        <h3 class="card-title">✨ Tambah Aset AR ke Materi</h3>
        <form id="formTambahAR">
            <div class="form-group">
                <label>Pilih Materi</label>
                <select id="selectMateri" class="form-control" required>
                    <option value="">-- Memuat Materi... --</option>
                </select>
            </div>

            <div class="form-group">
                <label>Upload File 3D (.glb / .gltf)</label>
                <input type="file" id="fileAR" class="form-control" accept=".glb,.gltf" required>
                <small style="color:#888; font-size:11px;">Maksimal ukuran file 20MB.</small>
            </div>

            <button type="submit" class="btn-simpan" id="btnUpload">Upload & Pasang AR</button>
        </form>
    </div>

    <div class="card col-kanan">
        <h3 class="card-title">🖼️ Galeri Aset AR <span id="labelTampilan" style="font-weight:normal; font-size:13px; color:#888;"></span></h3>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Judul Materi</th>
                    <th>Status AR</th>
                </tr>
            </thead>
            <tbody id="tabelAR">
                <tr><td colspan="3" style="text-align:center;">Loading data...</td></tr>
            </tbody>
        </table>
    </div>

</div>
@endsection

@push('scripts')
<script>
(function() {
    const token = window.token || localStorage.getItem('token');
    const role = window.role || localStorage.getItem('role');
    let user = null;
    try { user = JSON.parse(localStorage.getItem('user')); } catch(e) {}

    if (!token) { window.location.href = '/'; return; }

    // Set Label Admin / Dosen
    document.getElementById('labelTampilan').innerText = role === 'dosen' ? '(Materi Saya)' : '(Semua Materi)';

    // 1. FETCH MATERI UNTUK DROPDOWN
    fetchMateriDropdown();
    async function fetchMateriDropdown() {
        // Jika dosen, ambil dari matkulnya. Jika admin, ambil semua materi.
        let urlMateri = role === 'dosen' 
            ? `http://127.0.0.1:8000/api/courses/${user.course_id}/materials`
            : `http://127.0.0.1:8000/api/materials`; // Pastikan rute ini ada di backend utk Admin

        try {
            const res = await fetch(urlMateri, { headers: { 'Authorization': 'Bearer '+token, 'Accept': 'application/json' } });
            const data = await res.json();
            const materials = data.data || data;

            const select = document.getElementById('selectMateri');
            select.innerHTML = '<option value="">-- Pilih Materi --</option>';

            materials.forEach(m => {
                // Tampilkan semua materi agar dosen/admin bisa nimpa AR lama atau pasang baru
                let label = m.has_ar ? `✅ [Ada AR] ${m.title}` : `❌ [Tanpa AR] ${m.title}`;
                select.innerHTML += `<option value="${m.id || m._id}">${label}</option>`;
            });
        } catch (error) {
            document.getElementById('selectMateri').innerHTML = '<option value="">Gagal memuat materi</option>';
        }
    }

    // 2. FETCH GALERI AR
    fetchGaleriAR();
    async function fetchGaleriAR() {
        try {
            const res = await fetch(`http://127.0.0.1:8000/api/ar-gallery`, {
                headers: { 'Authorization': 'Bearer '+token, 'Accept': 'application/json' }
            });
            const data = await res.json();
            const arList = data.data || [];

            const tbody = document.getElementById('tabelAR');
            tbody.innerHTML = '';

            if(arList.length === 0) {
                tbody.innerHTML = '<tr><td colspan="3" style="text-align:center;">Belum ada aset AR yang diupload.</td></tr>';
                return;
            }

            arList.forEach((ar, idx) => {
                tbody.innerHTML += `
                    <tr>
                        <td>${idx + 1}</td>
                        <td><strong>${ar.title}</strong></td>
                        <td><span class="badge-ar">✨ Tersedia</span></td>
                    </tr>
                `;
            });
        } catch(e) {
            document.getElementById('tabelAR').innerHTML = '<tr><td colspan="3" style="text-align:center; color:red;">Gagal memuat galeri.</td></tr>';
        }
    }

    // 3. PROSES UPLOAD AR
    document.getElementById('formTambahAR').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const materialId = document.getElementById('selectMateri').value;
        const fileInput = document.getElementById('fileAR');
        const btnUpload = document.getElementById('btnUpload');

        if(!materialId || fileInput.files.length === 0) {
            alert("Pilih materi dan file AR terlebih dahulu!"); return;
        }

        let formData = new FormData();
        formData.append('model_3d', fileInput.files[0]);
        // Laravel method spoofing untuk form-data
        formData.append('_method', 'POST'); 

        btnUpload.innerText = "Mengupload...";
        btnUpload.disabled = true;

        try {
            const res = await fetch(`http://127.0.0.1:8000/api/materials/${materialId}/ar`, {
                method: 'POST',
                headers: { 'Authorization': 'Bearer '+token, 'Accept': 'application/json' },
                body: formData
            });

            if(res.ok) {
                alert("Aset AR Berhasil Terpasang!");
                document.getElementById('formTambahAR').reset();
                fetchMateriDropdown(); // Refresh dropdown
                fetchGaleriAR();       // Refresh tabel
            } else {
                const err = await res.json();
                alert("Gagal upload: " + (err.message || 'Cek koneksi server'));
            }
        } catch(e) {
            alert("Terjadi kesalahan jaringan.");
        } finally {
            btnUpload.innerText = "Upload & Pasang AR";
            btnUpload.disabled = false;
        }
    });

})();
</script>
@endpush