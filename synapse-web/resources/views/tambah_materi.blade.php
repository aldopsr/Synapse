<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tulis Materi Baru</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background-color: #F4F7F6; display: flex; }
        .sidebar { width: 250px; background-color: #279685; color: white; height: 100vh; padding: 20px; box-sizing: border-box; }
        .sidebar a { color: white; text-decoration: none; display: block; padding: 10px 0; }
        .main-content { flex: 1; padding: 30px; }
        .card { background: white; padding: 25px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; color: #333; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; }
        .ck-editor__editable_inline { min-height: 300px; }
        .btn-simpan { background-color: #279685; color: white; border: none; padding: 12px 25px; border-radius: 5px; cursor: pointer; font-weight: bold; }
        .btn-batal { background-color: #ccc; color: black; text-decoration: none; padding: 12px 25px; border-radius: 5px; margin-right: 10px; display: inline-block; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>SYNAPSE</h2>
        <a href="/dashboard">🏠 Dashboard</a>
        <a href="/mata-kuliah">📚 Mata Kuliah</a>
    </div>

    <div class="main-content">
        <div class="card">
            <h2 style="margin-top: 0;">📝 Tulis Materi E-Modul</h2>
            <hr style="margin-bottom: 20px; border: 0; border-top: 1px solid #eee;">

            <form id="formMateriBaru">
                <div class="form-group">
                    <label>Judul Materi</label>
                    <input type="text" id="title" placeholder="Contoh: Pengenalan Anatomi Dasar" required>
                </div>

                <div class="form-group">
                    <label>Deskripsi Singkat</label>
                    <input type="text" id="description" placeholder="Akan muncul di ringkasan aplikasi mobile" required>
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
    <label style="display: block; font-weight: 600; margin-bottom: 6px;">
        ✨ Upload Aset AR (3D Model) 
        <span style="color: #888; font-weight: normal; font-size: 12px;">*Opsional (Format: .glb/.gltf, Max 20MB)</span>
    </label>
    <input type="file" id="model_3d" name="model_3d" class="form-control" accept=".glb,.gltf">
</div>

                <div class="form-group">
                    <label>Isi Materi (E-Modul)</label>
                    <textarea id="editor"></textarea>
                </div>

                <div style="margin-top: 20px; text-align: right;">
                    <a href="/mata-kuliah/{{ $course_id }}/materi" class="btn-batal">Batal</a>
                    <button type="submit" class="btn-simpan">💾 Simpan Materi</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
    <script>
        const token = localStorage.getItem('token');
        const courseId = "{{ $course_id }}";
        let myEditor;

        // Custom Adapter untuk Upload Gambar ke Backend API (Port 8000)
        class MyUploadAdapter {
            constructor(loader) { this.loader = loader; }
            upload() {
                return this.loader.file.then(file => new Promise((resolve, reject) => {
                    const data = new FormData();
                    data.append('upload', file);
                    fetch('http://127.0.0.1:8000/api/upload-image', {
                        method: 'POST',
                        headers: { 'Authorization': 'Bearer ' + token },
                        body: data
                    })
                    .then(res => res.json())
                    .then(result => resolve({ default: result.url }))
                    .catch(err => reject(err));
                }));
            }
            abort() {}
        }

        function MyCustomUploadAdapterPlugin(editor) {
            editor.plugins.get('FileRepository').createUploadAdapter = (loader) => new MyUploadAdapter(loader);
        }

        ClassicEditor
            .create(document.querySelector('#editor'), { extraPlugins: [MyCustomUploadAdapterPlugin] })
            .then(editor => { myEditor = editor; })
            .catch(err => console.error(err));

        // Submit Data ke API
        document.getElementById('formMateriBaru').addEventListener('submit', async function(e) {
    e.preventDefault();

    // 1. Inisialisasi FormData untuk membungkus Teks + File
    let formData = new FormData();
    formData.append('title', document.getElementById('title').value);
    formData.append('description', document.getElementById('description').value);
    formData.append('content', myEditor.getData()); // Mengambil hasil dari CKEditor

    // 2. Cek dan masukkan file AR (jika Kapten menguploadnya)
    // Pastikan di HTML Kapten ada <input type="file" id="model_3d" ...>
    const arFileInput = document.getElementById('model_3d');
    if (arFileInput && arFileInput.files.length > 0) {
        formData.append('model_3d', arFileInput.files[0]);
    }

    try {
        const response = await fetch(`http://127.0.0.1:8000/api/courses/${courseId}/materials`, {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + token,
                'Accept': 'application/json'
                // 🚨 PENTING KAPTEN: 
                // Hapus baris 'Content-Type': 'application/json'!
                // Jika pakai FormData, biarkan browser yang otomatis menyetelnya ke 'multipart/form-data'
            },
            body: formData // Kirim variabel formData, BUKAN JSON.stringify
        });

        if (response.ok) {
            alert("Materi & Aset AR Berhasil Disimpan!");
            window.location.href = `/mata-kuliah/${courseId}/materi`;
        } else {
            const errorData = await response.json();
            alert("Gagal menyimpan: " + (errorData.message || "Cek koneksi atau validasi data."));
            console.error("Detail Error:", errorData);
        }
    } catch (error) {
        console.error(error);
        alert("Terjadi kesalahan sistem saat menyimpan.");
    }
});
    </script>
</body>
</html>