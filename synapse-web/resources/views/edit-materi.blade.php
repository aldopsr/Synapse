<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Materi E-Modul</title>
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
        .btn-simpan { background-color: #F39C12; color: white; border: none; padding: 12px 25px; border-radius: 5px; cursor: pointer; font-weight: bold; }
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
            <h2 style="margin-top: 0;">✏️ Edit Materi E-Modul</h2>
            <hr style="margin-bottom: 20px; border: 0; border-top: 1px solid #eee;">

            <form id="formEditMateri">
                <div class="form-group">
                    <label>Judul Materi</label>
                    <input type="text" id="title" required>
                </div>

                <div class="form-group">
                    <label>Deskripsi Singkat</label>
                    <input type="text" id="description" required>
                </div>

                <div class="form-group">
                    <label>Isi Materi (E-Modul)</label>
                    <textarea id="editor"></textarea>
                </div>

                <div style="margin-top: 20px; text-align: right;">
                    <a href="/mata-kuliah/{{ $course_id }}/materi" class="btn-batal">Batal</a>
                    <button type="submit" class="btn-simpan">🔄 Update Materi</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
    <script>
        const token = localStorage.getItem('token');
        const courseId = "{{ $course_id }}";
        const materiId = "{{ $materi_id ?? request()->route('materi_id') }}"; // Ambil ID materi dari rute
        let myEditor;

        // Custom Adapter Upload Gambar
        class MyUploadAdapter {
            constructor(loader) { this.loader = loader; }
            upload() {
                return this.loader.file.then(file => new Promise((resolve, reject) => {
                    const data = new FormData();
                    data.append('upload', file);
                    fetch(window.apiBaseUrl + '/upload-image', {
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

        // Inisialisasi CKEditor & Ambil Data Lama
        ClassicEditor
            .create(document.querySelector('#editor'), { extraPlugins: [MyCustomUploadAdapterPlugin] })
            .then(editor => { 
                myEditor = editor; 
                loadDataLama(); // Panggil data lama setelah editor siap
            })
            .catch(err => console.error(err));

        // Fungsi Mengambil Data Lama dari Backend
        async function loadDataLama() {
            try {
                const response = await fetch(`${window.apiBaseUrl}/materials/${materiId}`, {
                    headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
                });
                const result = await response.json();
                const data = result.data || result;

                if(response.ok) {
                    document.getElementById('title').value = data.title || '';
                    document.getElementById('description').value = data.description || '';
                    myEditor.setData(data.content || ''); // Masukkan isi modul ke editor
                } else {
                    alert("Gagal menarik data lama materi.");
                }
            } catch(e) {
                console.error("Error loading data", e);
            }
        }

        // Submit Data Update ke API menggunakan PUT
        document.getElementById('formEditMateri').addEventListener('submit', async function(e) {
            e.preventDefault();
            const payload = {
                title: document.getElementById('title').value,
                description: document.getElementById('description').value,
                content: myEditor.getData() 
            };

            try {
                const response = await fetch(`${window.apiBaseUrl}/materials/${materiId}`, {
                    method: 'PUT', // Menggunakan PUT untuk Update
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                if (response.ok) {
                    alert("Materi Berhasil Diupdate!");
                    window.location.href = `/mata-kuliah/${courseId}/materi`;
                } else {
                    const errorData = await response.json();
                    alert("Gagal update: " + (errorData.message || 'Cek rute PUT di Laravel'));
                }
            } catch (error) {
                alert("Terjadi kesalahan saat mengupdate.");
            }
        });
    </script>
</body>
</html>