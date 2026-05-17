@extends('layouts.app')

@section('title', 'Kelola Aset AR - Synapse')
@section('header_title', 'Kelola Aset Augmented Reality (AR)')

@section('content')
<style>
    .layout-container { display: flex; gap: 25px; flex-wrap: wrap; align-items: flex-start; }
    .card { background: white; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #eee; padding: 25px; box-sizing: border-box; }
    .col-kiri { flex: 1; min-width: 320px; max-width: 420px; }
    .col-kanan { flex: 2; min-width: 400px; }

    .card-title { font-size: 16px; font-weight: bold; color: #333; margin-top: 0; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px; }

    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; margin-bottom: 6px; font-weight: 600; font-size: 13px; color: #555; }
    .form-control { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 8px; box-sizing: border-box; font-family: 'Inter', sans-serif; font-size: 14px; }
    textarea.form-control { resize: vertical; min-height: 70px; }

    .btn-simpan { background-color: #4A148C; color: white; border: none; padding: 12px 20px; border-radius: 8px; cursor: pointer; font-weight: bold; width: 100%; transition: 0.2s; font-size: 14px; }
    .btn-simpan:hover { background-color: #380b6e; }
    .btn-simpan:disabled { background-color: #999; cursor: not-allowed; }

    .btn-aksi { padding: 6px 12px; border: none; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: 600; margin-right: 5px; }
    .btn-hapus { background: #EF5350; color: white; }

    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 10px 12px; text-align: left; border-bottom: 1px solid #eee; vertical-align: middle; }
    th { background: #F3E5F5; color: #4A148C; font-size: 13px; }
    .thumb-mini { width: 50px; height: 50px; border-radius: 8px; object-fit: cover; background: #f5f5f5; border: 1px solid #eee; }

    .thumbnail-preview-box {
        border: 2px dashed #ccc;
        border-radius: 8px;
        padding: 10px;
        text-align: center;
        background: #fafafa;
        margin-top: 8px;
        min-height: 120px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
    }
    .thumbnail-preview-box img { max-width: 100%; max-height: 180px; border-radius: 6px; }
    .thumbnail-preview-box .placeholder-text { color: #999; font-size: 12px; }

    .model-viewer-visible {
        width: 100%;
        height: 250px;
        background-color: #f5f5f5;
        border-radius: 8px;
        margin-top: 8px;
    }

    .status-msg {
        font-size: 12px;
        color: #4A148C;
        margin-top: 5px;
        font-weight: 600;
    }
    .status-msg.success { color: #2E7D32; }
    .status-msg.error { color: #C62828; }

    
</style>

<div class="layout-container">

    <div class="card col-kiri">
        <h3 class="card-title">✨ Tambah Aset AR Baru</h3>
        <form id="formTambahAR">
            <div class="form-group">
                <label>Pilih Materi</label>
                <select id="selectMateri" class="form-control" required>
                    <option value="">-- Memuat Materi... --</option>
                </select>
            </div>

            <div class="form-group">
                <label>Judul Aset AR</label>
                <input type="text" id="titleAR" class="form-control" placeholder="cth: Motherboard Komputer" required maxlength="255">
            </div>

            <div class="form-group">
                <label>Deskripsi (opsional)</label>
                <textarea id="descAR" class="form-control" placeholder="Penjelasan singkat..."></textarea>
            </div>

            <div class="form-group">
                <label>Upload File 3D (.glb / .gltf)</label>
                <input type="file" id="fileAR" class="form-control" accept=".glb,.gltf" required>
                <small style="color:#888; font-size:11px;">Maksimal 20MB.</small>
            </div>

            <div class="form-group">
                <label>🎬 Preview Model 3D (Live)</label>
                <model-viewer
                    id="liveModelViewer"
                    class="model-viewer-visible"
                    camera-controls
                    auto-rotate
                    shadow-intensity="1"
                    exposure="1"
                    environment-image="neutral">
                    <div slot="poster" style="display:flex;align-items:center;justify-content:center;height:100%;color:#999;">Belum ada model dipilih</div>
                </model-viewer>
            </div>

            <div class="form-group">
                <label>🖼️ Thumbnail Preview (Auto-Generated)</label>
                <div class="thumbnail-preview-box" id="thumbnailPreviewBox">
                    <span class="placeholder-text">Pilih file 3D dulu...</span>
                </div>
                <div class="status-msg" id="thumbnailStatus"></div>
            </div>

            

            <button type="submit" class="btn-simpan" id="btnUpload" disabled>Upload & Pasang AR</button>
        </form>
    </div>

    <div class="card col-kanan">
        <h3 class="card-title">🖼️ Galeri Aset AR <span id="labelTampilan" style="font-weight:normal; font-size:13px; color:#888;"></span></h3>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Thumbnail</th>
                    <th>Judul AR</th>
                    <th>Materi</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody id="tabelAR">
                <tr><td colspan="5" style="text-align:center;">Loading data...</td></tr>
            </tbody>
        </table>
    </div>

</div>

@endsection

@push('scripts')
<script type="module" src="https://ajax.googleapis.com/ajax/libs/model-viewer/3.5.0/model-viewer.min.js"></script>

<script>
(function() {
    const token = window.token || localStorage.getItem('token');
    const role = window.role || localStorage.getItem('role');
    const API_BASE = window.apiBaseUrl;
    let user = null;
    try { user = JSON.parse(localStorage.getItem('user')); } catch(e) {}

    if (!token) { window.location.href = '/'; return; }

    let generatedThumbnailBlob = null;

    document.getElementById('labelTampilan').innerText = role === 'dosen' ? '(Materi Saya)' : '(Semua Materi)';

    const debugBox = document.getElementById('debugBox');
    function log(msg, type = 'info') {
        console.log(`[${type.toUpperCase()}] ${msg}`);
    }

    function waitForModelViewer() {
        return new Promise((resolve, reject) => {
            let attempts = 0;
            const check = () => {
                if (customElements.get('model-viewer')) {
                    resolve();
                } else if (attempts++ > 50) {
                    reject(new Error('model-viewer library tidak ke-load (timeout 5 detik)'));
                } else {
                    setTimeout(check, 100);
                }
            };
            check();
        });
    }

    fetchMateriDropdown();
    async function fetchMateriDropdown() {
        let urlMateri = role === 'dosen' && user && user.course_id
            ? `${API_BASE}/courses/${user.course_id}/materials`
            : `${API_BASE}/materials`;

        try {
            const res = await fetch(urlMateri, {
                headers: { 'Authorization': 'Bearer '+token, 'Accept': 'application/json' }
            });
            const data = await res.json();
            const materials = data.data || data;

            const select = document.getElementById('selectMateri');
            select.innerHTML = '<option value="">-- Pilih Materi --</option>';

            materials.forEach(m => {
                select.innerHTML += `<option value="${m.id || m._id}">${m.title}</option>`;
            });
            log(`📚 ${materials.length} materi dimuat`, 'info');
        } catch (error) {
            document.getElementById('selectMateri').innerHTML = '<option value="">Gagal memuat materi</option>';
            log('❌ Gagal load materi: ' + error.message, 'error');
        }
    }

    fetchGaleriAR();
    async function fetchGaleriAR() {
        try {
            const res = await fetch(`${API_BASE}/ar-assets`, {
                headers: { 'Authorization': 'Bearer '+token, 'Accept': 'application/json' }
            });
            const data = await res.json();
            const arList = data.data || [];

            const tbody = document.getElementById('tabelAR');
            tbody.innerHTML = '';

            if(arList.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; color:#888;">Belum ada aset AR. Upload yang pertama! 🚀</td></tr>';
                return;
            }

            arList.forEach((ar, idx) => {
                const thumbHtml = ar.image_url
                    ? `<img src="${ar.image_url}" class="thumb-mini" alt="thumb">`
                    : `<div class="thumb-mini" style="display:flex;align-items:center;justify-content:center;color:#bbb;font-size:20px;">📦</div>`;
                const materiTitle = (ar.material && ar.material.title) ? ar.material.title : '-';
                tbody.innerHTML += `
                    <tr>
                        <td>${idx + 1}</td>
                        <td>${thumbHtml}</td>
                        <td><strong>${ar.title}</strong></td>
                        <td>${materiTitle}</td>
                        <td>
                            <button class="btn-aksi btn-hapus" onclick="hapusAR('${ar.id || ar._id}', '${(ar.title||'').replace(/'/g, "\\'")}')">🗑️ Hapus</button>
                        </td>
                    </tr>
                `;
            });
        } catch(e) {
            console.error('Gagal load galeri:', e);
            document.getElementById('tabelAR').innerHTML = '<tr><td colspan="5" style="text-align:center; color:red;">Gagal memuat galeri.</td></tr>';
        }
    }

    const fileARInput = document.getElementById('fileAR');
    const previewBox = document.getElementById('thumbnailPreviewBox');
    const statusMsg = document.getElementById('thumbnailStatus');
    const btnUpload = document.getElementById('btnUpload');
    const liveViewer = document.getElementById('liveModelViewer');

    fileARInput.addEventListener('change', async function(e) {
        const file = e.target.files[0];
        if (!file) return;

        generatedThumbnailBlob = null;
        btnUpload.disabled = true;
        previewBox.innerHTML = '<span class="placeholder-text">⏳ Memuat model 3D...</span>';
        statusMsg.className = 'status-msg';
        statusMsg.innerText = 'Menyiapkan render...';

        log(`📁 File: ${file.name} (${(file.size/1024/1024).toFixed(2)} MB)`, 'info');
        log(`📄 Type: ${file.type || 'unknown'}`, 'info');

        try {
            await waitForModelViewer();
            log('✅ Library model-viewer ready', 'success');

            const fileUrl = URL.createObjectURL(file);
            log(`🔗 Blob URL dibuat`, 'info');

            liveViewer.src = fileUrl;
            log('🎬 Set src ke model-viewer...', 'info');

            await new Promise((resolve, reject) => {
                let resolved = false;
                const onLoad = () => {
                    if (resolved) return;
                    resolved = true;
                    log('✅ Model loaded!', 'success');
                    cleanup();
                    resolve();
                };
                const onError = (err) => {
                    if (resolved) return;
                    resolved = true;
                    const errMsg = err.detail?.sourceError?.message || err.detail?.message || err.message || 'unknown';
                    log('❌ Model load error: ' + errMsg, 'error');
                    cleanup();
                    reject(new Error('Gagal load model: ' + errMsg));
                };
                const cleanup = () => {
                    liveViewer.removeEventListener('load', onLoad);
                    liveViewer.removeEventListener('error', onError);
                };
                liveViewer.addEventListener('load', onLoad);
                liveViewer.addEventListener('error', onError);

                setTimeout(() => {
                    if (!resolved) {
                        resolved = true;
                        cleanup();
                        reject(new Error('Timeout 30 detik'));
                    }
                }, 30000);
            });

            statusMsg.innerText = '🎨 Render thumbnail...';
            log('⏳ Tunggu 1.5 detik supaya kamera stabil...', 'info');
            await new Promise(r => setTimeout(r, 1500));

            log('📸 Mencoba capture thumbnail...', 'info');

            let blob = null;

            // METODE 1: toBlob()
            try {
                log('🔄 Method 1: toBlob() ...', 'info');
                blob = await liveViewer.toBlob({
                    idealAspect: true,
                    mimeType: 'image/png'
                });
                if (blob && blob.size > 0) {
                    log(`✅ Method 1 BERHASIL! Size: ${(blob.size/1024).toFixed(1)} KB`, 'success');
                } else {
                    throw new Error('Blob kosong');
                }
            } catch (err1) {
                log('⚠️ Method 1 gagal: ' + err1.message, 'warn');
                blob = null;

                // METODE 2: toDataURL()
                try {
                    log('🔄 Method 2: toDataURL() ...', 'info');
                    const dataUrl = liveViewer.toDataURL ? liveViewer.toDataURL('image/png') : null;
                    if (dataUrl && dataUrl.length > 1000) {
                        const res = await fetch(dataUrl);
                        blob = await res.blob();
                        log(`✅ Method 2 BERHASIL! Size: ${(blob.size/1024).toFixed(1)} KB`, 'success');
                    } else {
                        throw new Error('toDataURL tidak tersedia atau kosong');
                    }
                } catch (err2) {
                    log('⚠️ Method 2 gagal: ' + err2.message, 'warn');
                    blob = null;

                    // METODE 3: Manual canvas screenshot
                    try {
                        log('🔄 Method 3: Canvas screenshot manual...', 'info');
                        const canvas = liveViewer.shadowRoot
                            ? liveViewer.shadowRoot.querySelector('canvas')
                            : liveViewer.querySelector('canvas');

                        if (!canvas) throw new Error('Canvas tidak ditemukan');

                        log(`📐 Canvas size: ${canvas.width}x${canvas.height}`, 'info');

                        blob = await new Promise((resolve, reject) => {
                            canvas.toBlob((b) => {
                                if (b && b.size > 0) resolve(b);
                                else reject(new Error('Canvas toBlob kosong'));
                            }, 'image/png');
                        });
                        log(`✅ Method 3 BERHASIL! Size: ${(blob.size/1024).toFixed(1)} KB`, 'success');
                    } catch (err3) {
                        log('❌ Method 3 gagal: ' + err3.message, 'error');
                        throw new Error('Semua method gagal: ' + err3.message);
                    }
                }
            }

            generatedThumbnailBlob = blob;

            const previewUrl = URL.createObjectURL(blob);
            previewBox.innerHTML = `<img src="${previewUrl}" alt="thumbnail preview">`;

            statusMsg.className = 'status-msg success';
            statusMsg.innerText = '✅ Thumbnail siap! Klik tombol upload.';
            log('🎉 Thumbnail siap!', 'success');

            btnUpload.disabled = false;

        } catch (err) {
            console.error('Error generate thumbnail:', err);
            log('❌ FATAL: ' + err.message, 'error');
            log('💡 Buka Console (F12) lihat detail', 'warn');

            previewBox.innerHTML = '<span class="placeholder-text" style="color:#C62828;">❌ Gagal render</span>';
            statusMsg.className = 'status-msg error';
            statusMsg.innerText = '⚠️ ' + err.message;

            generatedThumbnailBlob = null;
            btnUpload.disabled = false;
        }
    });

    document.getElementById('formTambahAR').addEventListener('submit', async function(e) {
        e.preventDefault();

        const materialId = document.getElementById('selectMateri').value;
        const title = document.getElementById('titleAR').value.trim();
        const description = document.getElementById('descAR').value.trim();
        const file = fileARInput.files[0];

        if (!materialId || !title || !file) {
            alert("Pilih materi, isi judul, dan pilih file 3D!");
            return;
        }

        const formData = new FormData();
        formData.append('title', title);
        formData.append('description', description);
        formData.append('model_3d', file);

        if (generatedThumbnailBlob) {
            formData.append('thumbnail', generatedThumbnailBlob, 'thumbnail.png');
            log('📤 Upload dengan thumbnail', 'info');
        } else {
            log('📤 Upload tanpa thumbnail', 'warn');
        }

        btnUpload.innerText = "⏳ Mengupload...";
        btnUpload.disabled = true;

        try {
            const res = await fetch(`${API_BASE}/materials/${materialId}/ar-assets`, {
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer '+token,
                    'Accept': 'application/json'
                },
                body: formData
            });

            if (res.ok) {
                alert("✅ Aset AR Berhasil Diupload!");
                document.getElementById('formTambahAR').reset();
                previewBox.innerHTML = '<span class="placeholder-text">Pilih file 3D dulu...</span>';
                statusMsg.innerText = '';
                liveViewer.src = '';
                generatedThumbnailBlob = null;
                fetchGaleriAR();
            } else {
                const err = await res.json();
                let msg = err.message || 'Cek koneksi server';
                if (err.errors) {
                    msg += '\n' + Object.values(err.errors).flat().join('\n');
                }
                alert("❌ Gagal upload:\n" + msg);
                log('❌ Upload error: ' + msg, 'error');
            }
        } catch(e) {
            console.error(e);
            alert("⚠️ Terjadi kesalahan jaringan: " + e.message);
            log('❌ Network error: ' + e.message, 'error');
        } finally {
            btnUpload.innerText = "Upload & Pasang AR";
            btnUpload.disabled = false;
        }
    });

    window.hapusAR = async function(id, title) {
        if (!confirm(`Yakin mau hapus AR "${title}"?\nFile akan terhapus permanen.`)) return;

        try {
            const res = await fetch(`${API_BASE}/ar-assets/${id}`, {
                method: 'DELETE',
                headers: { 'Authorization': 'Bearer '+token, 'Accept': 'application/json' }
            });

            if (res.ok) {
                alert('✅ AR berhasil dihapus.');
                fetchGaleriAR();
            } else {
                const err = await res.json();
                alert('❌ Gagal hapus: ' + (err.message || 'Cek koneksi'));
            }
        } catch(e) {
            alert('⚠️ Error jaringan: ' + e.message);
        }
    };

    waitForModelViewer().then(() => {
        log('🚀 Halaman siap!', 'success');
    }).catch(err => {
        log('❌ ' + err.message, 'error');
    });

})();
</script>
@endpush