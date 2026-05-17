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
        .btn-simpan:disabled { background-color: #aaa; cursor: not-allowed; }
        .btn-batal { background-color: #ccc; color: black; text-decoration: none; padding: 12px 25px; border-radius: 5px; margin-right: 10px; display: inline-block; }

        /* 🌟 STYLE BARU: Drag-drop thumbnail uploader */
        .thumbnail-uploader {
            border: 2px dashed #279685;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            background: #F0FDFB;
            cursor: pointer;
            transition: all 0.2s;
            position: relative;
            min-height: 180px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
        }
        .thumbnail-uploader:hover {
            background: #E0F8F4;
            border-color: #1f7a6e;
        }
        .thumbnail-uploader.has-preview {
            padding: 0;
            border-style: solid;
            border-color: #279685;
            background: #fff;
            min-height: auto;
        }
        .thumbnail-uploader input[type="file"] {
            display: none;
        }
        .thumb-preview {
            width: 100%;
            max-height: 280px;
            object-fit: cover;
            border-radius: 8px;
            display: block;
        }
        .thumb-overlay {
            position: absolute;
            top: 8px;
            right: 8px;
            background: rgba(0,0,0,0.7);
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            cursor: pointer;
            font-weight: 600;
        }
        .thumb-overlay:hover { background: rgba(220,53,69,0.9); }
        .thumb-icon { font-size: 48px; margin-bottom: 10px; }
        .thumb-text { color: #279685; font-weight: 600; margin-bottom: 5px; }
        .thumb-hint { color: #777; font-size: 12px; }
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

                <!-- 🌟 BARU: Upload Thumbnail -->
                <div class="form-group">
                    <label>Gambar Thumbnail (Opsional)</label>
                    <div class="thumbnail-uploader" id="thumbUploader" onclick="document.getElementById('imageInput').click()">
                        <input type="file" id="imageInput" accept="image/jpeg,image/png,image/jpg,image/webp">
                        <div id="thumbPlaceholder">
                            <div class="thumb-icon">🖼️</div>
                            <div class="thumb-text">Klik untuk upload thumbnail</div>
                            <div class="thumb-hint">PNG, JPG, WEBP (Max 2MB)<br>Akan tampil sebagai cover materi di aplikasi mahasiswa</div>
                        </div>
                        <img id="thumbPreviewImg" class="thumb-preview" style="display:none;" alt="preview">
                        <button type="button" id="thumbRemoveBtn" class="thumb-overlay" style="display:none;" onclick="event.stopPropagation(); removeThumbnail();">🗑️ Hapus</button>
                    </div>
                </div>

                <div class="form-group">
                    <label>Isi Materi (E-Modul)</label>
                    <textarea id="editor"></textarea>
                </div>

                <div style="margin-top: 20px; text-align: right;">
                    <a href="/mata-kuliah/{{ $course_id }}/materi" class="btn-batal">Batal</a>
                    <button type="submit" class="btn-simpan" id="btnSimpan">💾 Simpan Materi</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
    <script>
        const token = localStorage.getItem('token');
        const courseId = "{{ $course_id }}";
        let myEditor;

        // ============================================
        // 🌟 THUMBNAIL UPLOADER LOGIC
        // ============================================
        const imageInput = document.getElementById('imageInput');
        const thumbUploader = document.getElementById('thumbUploader');
        const thumbPlaceholder = document.getElementById('thumbPlaceholder');
        const thumbPreviewImg = document.getElementById('thumbPreviewImg');
        const thumbRemoveBtn = document.getElementById('thumbRemoveBtn');

        imageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;

            // Validasi ukuran
            if (file.size > 2 * 1024 * 1024) {
                alert('⚠️ Ukuran thumbnail maksimal 2MB!');
                imageInput.value = '';
                return;
            }

            // Tampilkan preview
            const reader = new FileReader();
            reader.onload = function(ev) {
                thumbPreviewImg.src = ev.target.result;
                thumbPreviewImg.style.display = 'block';
                thumbPlaceholder.style.display = 'none';
                thumbRemoveBtn.style.display = 'block';
                thumbUploader.classList.add('has-preview');
            };
            reader.readAsDataURL(file);
        });

        function removeThumbnail() {
            imageInput.value = '';
            thumbPreviewImg.src = '';
            thumbPreviewImg.style.display = 'none';
            thumbPlaceholder.style.display = 'block';
            thumbRemoveBtn.style.display = 'none';
            thumbUploader.classList.remove('has-preview');
        }

        // ============================================
        // CKEDITOR + UPLOAD ADAPTER
        // ============================================
        class MyUploadAdapter {
            constructor(loader) { this.loader = loader; }
            upload() {
                return this.loader.file.then(file => new Promise((resolve, reject) => {
                    const data = new FormData();
                    data.append('upload', file);
                    fetch(window.apiBaseUrl + '/api/upload-image', {
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

        // ============================================
        // SUBMIT FORM
        // ============================================
        document.getElementById('formMateriBaru').addEventListener('submit', async function(e) {
            e.preventDefault();

            const btnSimpan = document.getElementById('btnSimpan');
            btnSimpan.disabled = true;
            btnSimpan.innerText = '⏳ Menyimpan...';

            // 1. Inisialisasi FormData untuk membungkus Teks + File
            let formData = new FormData();
            formData.append('title', document.getElementById('title').value);
            formData.append('description', document.getElementById('description').value);
            formData.append('content', myEditor.getData());

            // 🌟 BARU: Sertakan file thumbnail kalau ada
            if (imageInput.files.length > 0) {
                formData.append('image', imageInput.files[0]);
            }

            try {
                const response = await fetch(`${window.apiBaseUrl}/api/courses/${courseId}/materials`, {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Accept': 'application/json'
                        // Note: JANGAN set Content-Type, biarkan browser auto-set ke multipart/form-data
                    },
                    body: formData
                });

                if (response.ok) {
                    alert("✅ Materi Berhasil Disimpan!");
                    window.location.href = `/mata-kuliah/${courseId}/materi`;
                } else {
                    const errorData = await response.json();
                    let msg = errorData.message || "Cek koneksi atau validasi data.";
                    if (errorData.errors) {
                        msg += '\n\n' + Object.values(errorData.errors).flat().join('\n');
                    }
                    alert("❌ Gagal menyimpan:\n" + msg);
                    console.error("Detail Error:", errorData);
                }
            } catch (error) {
                console.error(error);
                alert("⚠️ Terjadi kesalahan sistem saat menyimpan.");
            } finally {
                btnSimpan.disabled = false;
                btnSimpan.innerText = '💾 Simpan Materi';
            }
        });
    </script>
</body>
</html>