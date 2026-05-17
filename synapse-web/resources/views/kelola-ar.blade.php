@extends('layouts.app')

@section('title', 'Kelola Aset AR - Synapse')
@section('header_title', 'Aset AR')

@section('content')
<style>
/* ======================================================
   KELOLA ASET AR — modernized
   ====================================================== */

/* --- Page layout --- */
.ar-layout {
    display: grid;
    grid-template-columns: 380px 1fr;
    gap: 20px;
    align-items: start;
}
@media (max-width: 900px) { .ar-layout { grid-template-columns: 1fr; } }

/* --- Cards --- */
.card {
    background: #fff;
    border-radius: 16px;
    border: 1px solid #eee;
    overflow: hidden;
}
.card-header {
    display: flex; align-items: center; gap: 10px;
    padding: 18px 22px 14px;
    border-bottom: 1px solid #f0f0f0;
}
.ch-icon {
    width: 34px; height: 34px; border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    font-size: 16px; flex-shrink: 0;
}
.ch-icon.purple { background: #f0eeff; }
.ch-icon.teal   { background: #e3faf8; }
.card-header h3 { font-size: 14px; font-weight: 700; color: #1a1a1a; margin: 0 0 2px; }
.card-header p  { font-size: 11px; color: #aaa; margin: 0; }
.card-body { padding: 18px 22px; }

/* --- Form groups --- */
.fg { margin-bottom: 15px; }
.fg:last-child { margin-bottom: 0; }
.fg label {
    display: block; font-size: 11px; font-weight: 700;
    color: #555; text-transform: uppercase; letter-spacing: .04em;
    margin-bottom: 6px;
}
.fg label .req { color: #ef4444; margin-left: 2px; }
.fc {
    width: 100%; padding: 9px 12px;
    border: 1px solid #e5e7eb; border-radius: 9px;
    font-size: 13px; font-family: inherit; color: #1a1a1a;
    background: #fff; box-sizing: border-box;
    transition: border-color .15s, box-shadow .15s;
}
.fc:focus { outline: none; border-color: #279685; box-shadow: 0 0 0 3px rgba(39,150,133,.1); }
.fc::placeholder { color: #ccc; }
textarea.fc { resize: vertical; min-height: 68px; }
select.fc { cursor: pointer; }
.fc.err { border-color: #ef4444; }

/* --- File dropzone --- */
.dropzone {
    border: 2px dashed #e5e7eb; border-radius: 10px;
    padding: 18px; text-align: center; cursor: pointer;
    transition: border-color .15s, background .15s;
    background: #fafafa; position: relative;
}
.dropzone:hover, .dropzone.drag { border-color: #279685; background: #f0fdfb; }
.dropzone input[type=file] { display: none; }
.dropzone-icon { font-size: 28px; margin-bottom: 6px; }
.dropzone-label { font-size: 12px; font-weight: 600; color: #555; margin-bottom: 3px; }
.dropzone-hint  { font-size: 11px; color: #aaa; }
.dropzone-file  { font-size: 12px; font-weight: 700; color: #279685; margin-top: 6px; }

/* --- Model-viewer --- */
.model-wrap {
    border-radius: 10px; overflow: hidden;
    background: #f8fafa; border: 1px solid #eee;
    height: 200px; display: none;
    align-items: center; justify-content: center;
    position: relative;
}
.model-wrap.show { display: flex; }
model-viewer {
    width: 100%; height: 100%;
}

/* --- Thumbnail preview --- */
.thumb-preview {
    border-radius: 9px; overflow: hidden;
    border: 1px dashed #e5e7eb;
    height: 90px; background: #fafafa;
    display: flex; align-items: center; justify-content: center;
    font-size: 11px; color: #bbb; text-align: center;
    transition: all .2s;
}
.thumb-preview img { width: 100%; height: 100%; object-fit: cover; }
.thumb-preview.generating { border-color: #279685; color: #279685; }

/* --- Progress bar --- */
.progress-bar {
    height: 4px; background: #e5e7eb; border-radius: 99px;
    overflow: hidden; margin-top: 8px; display: none;
}
.progress-fill {
    height: 100%; background: linear-gradient(90deg, #279685, #4A90E2);
    border-radius: 99px; width: 0%; transition: width .3s;
    animation: progress-shimmer 1.5s infinite;
    background-size: 200% 100%;
}
@keyframes progress-shimmer {
    0%   { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}
.progress-bar.show { display: block; }

/* --- Submit button --- */
.btn-upload {
    width: 100%; padding: 11px;
    background: linear-gradient(135deg, #279685, #4A90E2);
    color: #fff; border: none; border-radius: 10px;
    font-size: 13px; font-weight: 700; cursor: pointer;
    font-family: inherit;
    display: flex; align-items: center; justify-content: center; gap: 7px;
    transition: opacity .18s, transform .18s;
}
.btn-upload:hover:not(:disabled) { opacity: .92; transform: translateY(-1px); }
.btn-upload:disabled { background: #9ca3af; cursor: not-allowed; }

/* ========================
   GALERI (kanan)
   ======================== */

/* Toolbar */
.gallery-toolbar {
    display: flex; align-items: center; gap: 10px;
    padding: 14px 22px; border-bottom: 1px solid #f0f0f0;
    flex-wrap: wrap;
}
.search-wrap {
    position: relative; flex: 1; min-width: 160px; max-width: 260px;
}
.search-wrap svg {
    position: absolute; left: 10px; top: 50%;
    transform: translateY(-50%); color: #bbb; pointer-events: none;
}
.search-input {
    width: 100%; padding: 8px 10px 8px 32px;
    border: 1px solid #e5e7eb; border-radius: 9px;
    font-size: 12px; font-family: inherit; background: #fff;
    box-sizing: border-box; transition: border-color .15s;
}
.search-input:focus { outline: none; border-color: #279685; }
.search-input::placeholder { color: #ccc; }
.filter-select {
    padding: 8px 12px; border: 1px solid #e5e7eb;
    border-radius: 9px; font-size: 12px; font-family: inherit;
    background: #fff; color: #444; cursor: pointer;
    max-width: 200px;
}
.filter-select:focus { outline: none; border-color: #279685; }
.count-badge {
    font-size: 12px; font-weight: 700; color: #888;
    margin-left: auto; white-space: nowrap;
}
.count-badge span { color: #279685; }

/* AR Cards grid */
.ar-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
    gap: 14px;
    padding: 18px 22px;
}

/* Single AR card */
.ar-card {
    background: #fff; border-radius: 12px;
    border: 1px solid #eee; overflow: hidden;
    transition: transform .2s, box-shadow .2s;
    position: relative;
}
.ar-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 24px rgba(0,0,0,.08);
}
/* NEW chip — slide in */
.ar-card.is-new::before {
    content: 'Baru';
    position: absolute; top: 8px; left: 8px;
    background: #279685; color: #fff;
    font-size: 9px; font-weight: 700;
    padding: 2px 7px; border-radius: 99px;
    z-index: 1;
}

/* Thumbnail area */
.ar-thumb {
    width: 100%; aspect-ratio: 4/3;
    background: #f3f4f6; overflow: hidden;
    display: flex; align-items: center; justify-content: center;
    position: relative;
}
.ar-thumb img {
    width: 100%; height: 100%; object-fit: cover;
    transition: transform .3s;
}
.ar-card:hover .ar-thumb img { transform: scale(1.05); }
.ar-thumb .no-thumb {
    font-size: 32px; color: #ccc;
    display: flex; flex-direction: column;
    align-items: center; gap: 4px;
}
.ar-thumb .no-thumb span { font-size: 10px; color: #ccc; }

/* Card info */
.ar-info { padding: 10px 12px 8px; }
.ar-title {
    font-size: 12px; font-weight: 700; color: #1a1a1a;
    line-height: 1.35; margin-bottom: 4px;
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
    overflow: hidden;
}
.ar-materi {
    font-size: 10px; color: #888;
    display: flex; align-items: center; gap: 3px;
    margin-bottom: 8px;
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}

/* Card actions */
.ar-actions {
    display: flex; gap: 5px;
    padding: 0 12px 10px;
}
.btn-view, .btn-del {
    flex: 1; display: flex; align-items: center; justify-content: center; gap: 4px;
    padding: 5px 8px; border-radius: 7px;
    font-size: 11px; font-weight: 700; border: none; cursor: pointer;
    font-family: inherit; transition: background .15s;
}
.btn-view { background: #e3faf8; color: #0f6e56; }
.btn-view:hover { background: #c0ede8; }
.btn-del  { background: #fee2e2; color: #991b1b; flex: none; width: 30px; }
.btn-del:hover  { background: #fecaca; }

/* Empty state */
.ar-empty {
    grid-column: 1 / -1; text-align: center;
    padding: 48px 20px; color: #bbb;
}
.ar-empty .ei { font-size: 44px; margin-bottom: 10px; }
.ar-empty .el { font-size: 14px; font-weight: 700; color: #888; margin-bottom: 5px; }
.ar-empty .es { font-size: 12px; }

/* Skeleton */
.skeleton {
    background: linear-gradient(90deg,#f0f0f0 25%,#e8e8e8 50%,#f0f0f0 75%);
    background-size: 200% 100%;
    animation: shimmer 1.4s infinite;
}
@keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }

/* AR viewer modal */
.ar-modal {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,.7); z-index: 2000;
    align-items: center; justify-content: center;
    padding: 20px;
}
.ar-modal.open { display: flex; }
.ar-modal-box {
    background: #111; border-radius: 16px;
    width: 100%; max-width: 560px;
    overflow: hidden; position: relative;
    animation: slideUp .22s ease;
}
@keyframes slideUp { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:translateY(0)} }
.ar-modal-top {
    display: flex; align-items: center; justify-content: space-between;
    padding: 14px 18px;
    background: rgba(255,255,255,.06);
    border-bottom: 1px solid rgba(255,255,255,.08);
}
.ar-modal-top h4 { font-size: 14px; font-weight: 700; color: #fff; margin: 0; }
.ar-modal-close {
    width: 30px; height: 30px; border-radius: 7px;
    border: none; background: rgba(255,255,255,.12);
    color: #fff; cursor: pointer; font-size: 16px;
    display: flex; align-items: center; justify-content: center;
}
.ar-modal-viewer {
    width: 100%; height: 360px;
}

/* Toast */
.toast {
    position: fixed; bottom: 24px; right: 24px;
    padding: 11px 16px; border-radius: 10px;
    font-size: 13px; font-weight: 600; z-index: 9999;
    transform: translateY(80px); opacity: 0;
    transition: all .28s cubic-bezier(.34,1.56,.64,1);
    color: #fff; pointer-events: none; max-width: 300px;
    box-shadow: 0 8px 24px rgba(0,0,0,.18);
}
.toast.show { transform: translateY(0); opacity: 1; }
.toast.ok  { background: #279685; }
.toast.err { background: #ef4444; }
</style>

{{-- ============ LAYOUT ============ --}}
<div class="ar-layout">

    {{-- ===== KOLOM KIRI: FORM UPLOAD ===== --}}
    <div>
        <div class="card">
            <div class="card-header">
                <div class="ch-icon purple">🌐</div>
                <div>
                    <h3>Upload Aset AR Baru</h3>
                    <p>File .glb atau .gltf, maks. 100MB</p>
                </div>
            </div>
            <div class="card-body">

                {{-- Pilih Materi --}}
                <div class="fg">
                    <label>Materi Terkait <span class="req">*</span></label>
                    <select id="selectMateri" class="fc">
                        <option value="">— Memuat materi... —</option>
                    </select>
                </div>

                {{-- Judul --}}
                <div class="fg">
                    <label>Judul Aset AR <span class="req">*</span></label>
                    <input type="text" id="titleAR" class="fc"
                        placeholder="Contoh: Motherboard ATX 3D" maxlength="255">
                </div>

                {{-- Deskripsi --}}
                <div class="fg">
                    <label>Deskripsi <span style="font-weight:400;color:#bbb;font-size:10px;">(opsional)</span></label>
                    <textarea id="descAR" class="fc" rows="2" placeholder="Penjelasan singkat aset ini..."></textarea>
                </div>

                {{-- File dropzone --}}
                <div class="fg">
                    <label>File 3D (.glb / .gltf) <span class="req">*</span></label>
                    <div class="dropzone" id="dropzone" onclick="document.getElementById('fileAR').click()"
                        ondragover="event.preventDefault();this.classList.add('drag')"
                        ondragleave="this.classList.remove('drag')"
                        ondrop="handleDrop(event)">
                        <input type="file" id="fileAR" accept=".glb,.gltf" onchange="onFileSelected(this.files[0])">
                        <div class="dropzone-icon">📂</div>
                        <div class="dropzone-label">Klik atau seret file ke sini</div>
                        <div class="dropzone-hint">.glb / .gltf • maks 100MB</div>
                        <div class="dropzone-file" id="fileLabel"></div>
                    </div>
                </div>

                {{-- Model Viewer (muncul setelah file dipilih) --}}
                <div class="fg" id="viewerWrap">
                    <label>Preview 3D Live</label>
                    <div class="model-wrap" id="modelWrap">
                        <model-viewer
                            id="modelViewer"
                            camera-controls auto-rotate
                            shadow-intensity="1" exposure="1"
                            environment-image="neutral"
                            style="width:100%;height:100%;">
                        </model-viewer>
                    </div>
                    <div class="progress-bar" id="thumbProgress">
                        <div class="progress-fill" id="thumbFill" style="background:linear-gradient(90deg,#279685,#4A90E2);background-size:200%;animation:progress-shimmer 1.5s infinite;width:70%;"></div>
                    </div>
                </div>

                {{-- Thumbnail preview --}}
                <div class="fg" id="thumbWrap" style="display:none;">
                    <label>Thumbnail (auto-generated)</label>
                    <div class="thumb-preview" id="thumbPreview">
                        <span>Mengenerate thumbnail dari model...</span>
                    </div>
                </div>

                {{-- Submit --}}
                <div class="fg">
                    <button class="btn-upload" id="btnUpload" type="button"
                        onclick="submitUpload()" disabled>
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M11.47 2.47a.75.75 0 0 1 1.06 0l4.5 4.5a.75.75 0 0 1-1.06 1.06l-3.22-3.22V16.5a.75.75 0 0 1-1.5 0V4.81L8.03 8.03a.75.75 0 0 1-1.06-1.06l4.5-4.5ZM3 15.75a.75.75 0 0 1 .75.75v2.25a1.5 1.5 0 0 0 1.5 1.5h13.5a1.5 1.5 0 0 0 1.5-1.5V16.5a.75.75 0 0 1 1.5 0v2.25a3 3 0 0 1-3 3H5.25a3 3 0 0 1-3-3V16.5a.75.75 0 0 1 .75-.75Z" clip-rule="evenodd"/></svg>
                        <span id="uploadLabel">Upload Aset AR</span>
                    </button>
                </div>

            </div>
        </div>
    </div>

    {{-- ===== KOLOM KANAN: GALERI ===== --}}
    <div class="card">
        <div class="card-header">
            <div class="ch-icon teal">🖼️</div>
            <div>
                <h3>Galeri Aset AR</h3>
                <p id="gallerySubtitle">Memuat aset...</p>
            </div>
        </div>

        {{-- Toolbar: search + filter materi --}}
        <div class="gallery-toolbar">
            <div class="search-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                </svg>
                <input type="text" class="search-input" id="searchInput"
                    placeholder="Cari judul aset..." oninput="applyFilter()">
            </div>
            <select class="filter-select" id="filterMateri" onchange="applyFilter()">
                <option value="">Semua Materi</option>
            </select>
            <span class="count-badge" id="countBadge">Memuat...</span>
        </div>

        {{-- Grid --}}
        <div class="ar-grid" id="arGrid">
            {{-- Skeleton --}}
            @for ($i = 0; $i < 6; $i++)
            <div class="ar-card">
                <div class="ar-thumb skeleton" style="aspect-ratio:4/3;"></div>
                <div style="padding:10px 12px 10px;">
                    <div class="skeleton" style="height:12px;width:80%;border-radius:5px;margin-bottom:5px;"></div>
                    <div class="skeleton" style="height:10px;width:55%;border-radius:5px;"></div>
                </div>
            </div>
            @endfor
        </div>
    </div>

</div>

{{-- AR Viewer Modal --}}
<div class="ar-modal" id="arModal">
    <div class="ar-modal-box">
        <div class="ar-modal-top">
            <h4 id="modalTitle">Preview AR</h4>
            <button class="ar-modal-close" onclick="closeModal()">✕</button>
        </div>
        <model-viewer id="modalViewer" class="ar-modal-viewer"
            camera-controls auto-rotate shadow-intensity="1"
            environment-image="neutral">
        </model-viewer>
    </div>
</div>

<div class="toast" id="toast"></div>

@endsection

@push('scripts')
{{-- Model-viewer --}}
<script type="module" src="https://ajax.googleapis.com/ajax/libs/model-viewer/3.5.0/model-viewer.min.js"></script>

<script>
(function () {
    /* ── globals ─────────────────────────────────────────────── */
    const API   = window.apiBaseUrl;
    const token = window.token;
    const role  = window.role || 'dosen';
    const user  = window.user;
    const isAdmin = role === 'admin' || role === 'superadmin';

    const $ = id => document.getElementById(id);

    let allAR        = [];     // raw data dari server
    let materiMap    = {};     // materialId → title (untuk filter dropdown galeri)
    let selectedFile = null;   // File 3D yang dipilih
    let thumbBlob    = null;   // Blob thumbnail auto-generated
    let newlyUploaded= new Set(); // ID AR yang baru diupload (buat chip "Baru")

    /* ── Toast ───────────────────────────────────────────────── */
    function toast(msg, type = 'ok') {
        const el = $('toast');
        el.textContent = (type === 'ok' ? '✓  ' : '✕  ') + msg;
        el.className   = 'toast ' + type + ' show';
        clearTimeout(el._t);
        el._t = setTimeout(() => el.classList.remove('show'), 3400);
    }

    /* ── Helpers ─────────────────────────────────────────────── */
    function esc(s) {
        return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    /* ── Wait for model-viewer custom element ────────────────── */
    function waitMV() {
        return new Promise((resolve, reject) => {
            let tries = 0;
            const check = () => {
                if (customElements.get('model-viewer')) return resolve();
                if (++tries > 60) return reject(new Error('model-viewer timeout'));
                setTimeout(check, 100);
            };
            check();
        });
    }

    /* ═══════════════════════════════════════════════════════════
       MATERI DROPDOWN
       ═══════════════════════════════════════════════════════════
       FIX: Kode lama pakai `user.course_id` dari localStorage
       langsung — race condition kalau user baru login dan
       localStorage belum terupdate. Solusi: selalu GET /courses,
       lalu untuk setiap course GET /courses/{id}/materials.
       Backend sudah filter courses berdasarkan role.
    ═══════════════════════════════════════════════════════════ */
    async function loadMateriDropdown() {
        const sel = $('selectMateri');
        sel.innerHTML = '<option value="">— Memuat... —</option>';

        try {
            // Step 1: ambil daftar course (sudah difilter per role di backend)
            const resC = await fetch(API + '/courses', {
                headers: { Authorization: 'Bearer ' + token, Accept: 'application/json' }
            });
            const courses = (await resC.json()).data || [];

            if (courses.length === 0) {
                sel.innerHTML = '<option value="">— Tidak ada mata kuliah —</option>';
                return;
            }

            // Step 2: ambil materi dari semua course sekaligus (parallel)
            const allMaterials = [];
            await Promise.all(courses.map(async c => {
                const cid = c._id || c.id;
                try {
                    const resM = await fetch(`${API}/courses/${cid}/materials`, {
                        headers: { Authorization: 'Bearer ' + token, Accept: 'application/json' }
                    });
                    const mList = await resM.json();
                    // Response bisa array langsung atau { data: [...] }
                    const list = Array.isArray(mList) ? mList : (mList.data || []);
                    list.forEach(m => {
                        const mid = m._id || m.id;
                        materiMap[mid] = m.title;
                        allMaterials.push({ id: mid, title: m.title, courseTitle: c.title });
                    });
                } catch (e) { /* ignore individual course errors */ }
            }));

            if (allMaterials.length === 0) {
                sel.innerHTML = '<option value="">— Belum ada materi —</option>';
                return;
            }

            // Isi dropdown form upload
            sel.innerHTML = '<option value="">— Pilih Materi —</option>';
            allMaterials.forEach(m => {
                const opt = document.createElement('option');
                opt.value = m.id;
                opt.textContent = m.title + (isAdmin ? ` (${m.courseTitle})` : '');
                sel.appendChild(opt);
            });

            // Isi dropdown filter galeri
            const filtSel = $('filterMateri');
            filtSel.innerHTML = '<option value="">Semua Materi</option>';
            allMaterials.forEach(m => {
                const opt = document.createElement('option');
                opt.value = m.id;
                opt.textContent = m.title;
                filtSel.appendChild(opt);
            });

        } catch (e) {
            sel.innerHTML = '<option value="">— Gagal memuat, coba refresh —</option>';
            console.error('[AR] loadMateri:', e.message);
        }
    }

    /* ═══════════════════════════════════════════════════════════
       GALERI AR
       ═══════════════════════════════════════════════════════════
       FIX: Kode lama memanggil fetchGaleriAR() setelah upload
       sukses — tapi respons GET /ar-assets tidak menyertakan
       aset yang baru diupload kalau index di MongoDB belum flush.
       Solusi: setelah upload sukses, tambahkan data respons
       langsung ke allAR[] (prepend) lalu re-render tanpa fetch.
       Kalau fetch tetap ingin dilakukan, ada cooldown 500ms.
    ═══════════════════════════════════════════════════════════ */
    async function fetchGaleriAR() {
        try {
            const res  = await fetch(API + '/ar-assets', {
                headers: { Authorization: 'Bearer ' + token, Accept: 'application/json' }
            });
            if (res.status === 401) { window.logout(); return; }
            const data = await res.json();
            allAR = data.data || [];

            $('gallerySubtitle').textContent = isAdmin
                ? `${allAR.length} aset di semua materi`
                : `${allAR.length} aset di materi kamu`;

            applyFilter();
        } catch (e) {
            $('arGrid').innerHTML = `<div class="ar-empty">
                <div class="ei">⚠️</div>
                <div class="el">Gagal memuat galeri</div>
                <div class="es">${esc(e.message)}</div>
                <button onclick="fetchGaleriAR()" style="margin-top:12px;padding:7px 16px;background:#279685;color:#fff;border:none;border-radius:8px;cursor:pointer;font-weight:700;font-size:12px;">
                    Coba lagi
                </button>
            </div>`;
        }
    }

    /* ── Apply filter & search ───────────────────────────────── */
    window.applyFilter = function () {
        const q   = $('searchInput').value.trim().toLowerCase();
        const mid = $('filterMateri').value;

        const filtered = allAR.filter(ar => {
            const matchQ   = !q   || (ar.title||'').toLowerCase().includes(q);
            const matchMat = !mid || String(ar.material_id) === String(mid);
            return matchQ && matchMat;
        });

        $('countBadge').innerHTML = `<span>${filtered.length}</span> / ${allAR.length} aset`;
        renderGrid(filtered);
    };

    /* ── Render grid ─────────────────────────────────────────── */
    function renderGrid(list) {
        const grid = $('arGrid');
        if (!list || list.length === 0) {
            grid.innerHTML = `<div class="ar-empty">
                <div class="ei">📭</div>
                <div class="el">Belum ada aset AR</div>
                <div class="es">${$('searchInput').value || $('filterMateri').value
                    ? 'Coba ubah filter.' : 'Upload aset pertamamu di form kiri!'}</div>
            </div>`;
            return;
        }

        grid.innerHTML = list.map(ar => buildCard(ar)).join('');
    }

    /* ── Build single AR card ────────────────────────────────── */
    function buildCard(ar) {
        const id         = ar._id || ar.id;
        const title      = ar.title || 'Tanpa Judul';
        const matTitle   = ar.material?.title
            || (ar.material_id && materiMap[ar.material_id])
            || '—';
        const thumbUrl   = ar.image_url || '';
        const modelUrl   = ar.model_3d_url || '';
        const isNew      = newlyUploaded.has(String(id));

        const thumbHtml = thumbUrl
            ? `<img src="${esc(thumbUrl)}" alt="${esc(title)}" loading="lazy">`
            : `<div class="no-thumb">🌐<span>No thumb</span></div>`;

        return `
        <div class="ar-card${isNew ? ' is-new' : ''}" id="arcard-${id}">
            <div class="ar-thumb">${thumbHtml}</div>
            <div class="ar-info">
                <div class="ar-title">${esc(title)}</div>
                <div class="ar-materi">📚 ${esc(matTitle)}</div>
            </div>
            <div class="ar-actions">
                ${modelUrl ? `
                <button class="btn-view" onclick="previewAR('${esc(modelUrl)}','${esc(title)}')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="currentColor"><path d="M12.378 1.602a.75.75 0 0 0-.756 0L3 6.632l9 5.25 9-5.25-8.622-5.03ZM21.75 7.93l-9 5.25v9l8.628-5.032a.75.75 0 0 0 .372-.648V7.93ZM11.25 22.18v-9l-9-5.25v8.57a.75.75 0 0 0 .372.648l8.628 5.033Z"/></svg>
                    Preview
                </button>` : `<button class="btn-view" disabled style="opacity:.4;cursor:default;">Preview</button>`}
                <button class="btn-del" title="Hapus"
                    onclick="hapusAR('${id}','${esc(title).replace(/'/g,"\\'")}')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M16.5 4.478v.227a48.816 48.816 0 0 1 3.878.512.75.75 0 1 1-.256 1.478l-.209-.035-1.005 13.07a3 3 0 0 1-2.991 2.77H8.084a3 3 0 0 1-2.991-2.77L4.087 6.66l-.209.035a.75.75 0 0 1-.256-1.478A48.567 48.567 0 0 1 7.5 4.705v-.227c0-1.564 1.213-2.9 2.816-2.951a52.662 52.662 0 0 1 3.369 0c1.603.051 2.815 1.387 2.815 2.951Zm-6.136-1.452a51.196 51.196 0 0 1 3.273 0C14.39 3.05 15 3.684 15 4.478v.113a49.488 49.488 0 0 0-6 0v-.113c0-.794.609-1.428 1.364-1.452Zm-.355 5.945a.75.75 0 1 0-1.5.058l.347 9a.75.75 0 1 0 1.499-.058l-.346-9Zm5.48.058a.75.75 0 1 0-1.498-.058l-.347 9a.75.75 0 0 0 1.5.058l.345-9Z" clip-rule="evenodd"/></svg>
                </button>
            </div>
        </div>`;
    }

    /* ═══════════════════════════════════════════════════════════
       FILE SELECTION & THUMBNAIL GENERATION
    ═══════════════════════════════════════════════════════════ */
    window.handleDrop = function (e) {
        e.preventDefault();
        $('dropzone').classList.remove('drag');
        const file = e.dataTransfer.files[0];
        if (file) onFileSelected(file);
    };

    window.onFileSelected = async function (file) {
        if (!file) return;

        const ext = file.name.split('.').pop().toLowerCase();
        if (!['glb','gltf'].includes(ext)) {
            toast('Hanya file .glb atau .gltf yang didukung.', 'err'); return;
        }
        if (file.size > 100 * 1024 * 1024) {
            toast('File terlalu besar. Maks 100MB.', 'err'); return;
        }

        selectedFile = file;
        thumbBlob    = null;

        $('fileLabel').textContent  = `📁 ${file.name} (${(file.size/1024/1024).toFixed(1)} MB)`;
        $('btnUpload').disabled     = true;
        $('thumbWrap').style.display = 'block';
        $('thumbPreview').className  = 'thumb-preview generating';
        $('thumbPreview').innerHTML  = '<span>⏳ Mengenerate thumbnail...</span>';
        $('thumbProgress').classList.add('show');

        try {
            await waitMV();
            const blob = URL.createObjectURL(file);
            const mv   = $('modelViewer');
            $('modelWrap').classList.add('show');
            mv.src = blob;

            await new Promise((resolve, reject) => {
                let done = false;
                const onLoad = async () => {
                    if (done) return; done = true;
                    try {
                        // Tunggu sebentar agar model render
                        await new Promise(r => setTimeout(r, 800));
                        const dataUrl = await mv.toBlob({ mimeType: 'image/png', qualityArgument: 0.92, idealAspect: true });
                        thumbBlob = dataUrl;

                        // Tampilkan preview thumbnail
                        const url = URL.createObjectURL(dataUrl);
                        $('thumbPreview').className = 'thumb-preview';
                        $('thumbPreview').innerHTML = `<img src="${url}" alt="thumbnail">`;
                        $('thumbProgress').classList.remove('show');
                        $('btnUpload').disabled = false;
                        resolve();
                    } catch (err) {
                        // Thumbnail gagal tapi upload tetap bisa jalan
                        $('thumbPreview').className = 'thumb-preview';
                        $('thumbPreview').innerHTML = '<span>⚠ Thumbnail tidak tersedia, upload tetap bisa dilanjutkan</span>';
                        $('thumbProgress').classList.remove('show');
                        $('btnUpload').disabled = false;
                        resolve();
                    }
                };
                const onErr = (e) => { if (!done) { done = true; reject(e); } };
                mv.addEventListener('load', onLoad, { once: true });
                mv.addEventListener('error', onErr, { once: true });
                setTimeout(() => { if (!done) { done = true; resolve(); } }, 10000);
            });
        } catch (e) {
            $('thumbProgress').classList.remove('show');
            $('thumbPreview').innerHTML = '<span>⚠ Preview tidak tersedia</span>';
            $('btnUpload').disabled = false;
        }
    };

    /* ═══════════════════════════════════════════════════════════
       UPLOAD SUBMIT
       FIX: Setelah upload sukses, prepend data ke allAR[]
       langsung (tidak tunggu re-fetch) → galeri update instan.
    ═══════════════════════════════════════════════════════════ */
    window.submitUpload = async function () {
        const materialId = $('selectMateri').value;
        const title      = $('titleAR').value.trim();
        const desc       = $('descAR').value.trim();

        if (!materialId) { toast('Pilih materi terlebih dahulu.', 'err'); $('selectMateri').classList.add('err'); return; }
        if (!title)      { toast('Judul aset wajib diisi.', 'err'); $('titleAR').classList.add('err'); return; }
        if (!selectedFile) { toast('Pilih file 3D terlebih dahulu.', 'err'); return; }

        $('selectMateri').classList.remove('err');
        $('titleAR').classList.remove('err');

        const btn = $('btnUpload'), lbl = $('uploadLabel');
        btn.disabled   = true;
        lbl.textContent = 'Mengupload...';
        $('thumbProgress').classList.add('show');

        const fd = new FormData();
        fd.append('title', title);
        fd.append('description', desc || '');
        fd.append('model_3d', selectedFile);
        if (thumbBlob) fd.append('thumbnail', thumbBlob, 'thumbnail.png');

        try {
            const res  = await fetch(`${API}/materials/${materialId}/ar-assets`, {
                method: 'POST',
                headers: { Authorization: 'Bearer ' + token, Accept: 'application/json' },
                body: fd,
            });
            const data = await res.json();

            if (res.ok) {
                const newAR = data.data;
                const newId = newAR._id || newAR.id;

                // ── FIX: Inject ke allAR[] langsung (prepend) ──
                // Backend mungkin belum return material relasi, inject manual
                if (!newAR.material && materiMap[materialId]) {
                    newAR.material = { title: materiMap[materialId] };
                }
                if (!newAR.material_id) newAR.material_id = materialId;

                allAR.unshift(newAR);
                newlyUploaded.add(String(newId));

                // Update subtitle count
                $('gallerySubtitle').textContent = `${allAR.length} aset di ${isAdmin ? 'semua materi' : 'materi kamu'}`;

                // Re-render dengan filter yang aktif
                applyFilter();

                // Reset form
                resetForm();
                toast('Aset AR berhasil diupload!');

                // Hapus chip "Baru" setelah 10 detik
                setTimeout(() => {
                    newlyUploaded.delete(String(newId));
                    const card = document.getElementById(`arcard-${newId}`);
                    if (card) card.classList.remove('is-new');
                }, 10000);

            } else {
                let msg = data.message || 'Gagal upload.';
                if (data.errors) msg = Object.values(data.errors).flat()[0] || msg;
                toast(msg, 'err');
                btn.disabled   = false;
                lbl.textContent = 'Upload Aset AR';
            }
        } catch (e) {
            toast('Koneksi bermasalah: ' + e.message, 'err');
            btn.disabled   = false;
            lbl.textContent = 'Upload Aset AR';
        } finally {
            $('thumbProgress').classList.remove('show');
        }
    };

    /* ── Reset form setelah upload ───────────────────────────── */
    function resetForm() {
        $('selectMateri').value   = '';
        $('titleAR').value        = '';
        $('descAR').value         = '';
        $('fileAR').value         = '';
        $('fileLabel').textContent = '';
        $('modelWrap').classList.remove('show');
        $('modelViewer').src      = '';
        $('thumbWrap').style.display = 'none';
        $('thumbPreview').className  = 'thumb-preview';
        $('thumbPreview').innerHTML  = '';
        $('btnUpload').disabled   = true;
        $('uploadLabel').textContent = 'Upload Aset AR';
        selectedFile = null;
        thumbBlob    = null;
    }

    /* ═══════════════════════════════════════════════════════════
       HAPUS AR — optimistic
    ═══════════════════════════════════════════════════════════ */
    window.hapusAR = async function (id, title) {
        if (!confirm(`Hapus aset AR "${title}"?\n\nFile 3D akan terhapus permanen dari server.`)) return;

        // Optimistic: fade out card
        const card = document.getElementById(`arcard-${id}`);
        if (card) {
            card.style.transition = 'opacity .25s, transform .25s';
            card.style.opacity = '0'; card.style.transform = 'scale(.93)';
        }

        try {
            const res = await fetch(`${API}/ar-assets/${id}`, {
                method: 'DELETE',
                headers: { Authorization: 'Bearer ' + token, Accept: 'application/json' }
            });

            if (res.ok) {
                allAR = allAR.filter(ar => (ar._id||ar.id) != id);
                applyFilter();
                $('gallerySubtitle').textContent = `${allAR.length} aset di ${isAdmin ? 'semua materi' : 'materi kamu'}`;
                toast('Aset AR berhasil dihapus.');
            } else {
                // Rollback visual
                if (card) { card.style.opacity = '1'; card.style.transform = ''; }
                const err = await res.json().catch(() => ({}));
                toast(err.message || 'Gagal menghapus.', 'err');
            }
        } catch (e) {
            if (card) { card.style.opacity = '1'; card.style.transform = ''; }
            toast('Koneksi bermasalah.', 'err');
        }
    };

    /* ═══════════════════════════════════════════════════════════
       PREVIEW AR MODAL
    ═══════════════════════════════════════════════════════════ */
    window.previewAR = function (url, title) {
        $('modalViewer').src  = url;
        $('modalTitle').textContent = title;
        $('arModal').classList.add('open');
    };

    window.closeModal = function () {
        $('arModal').classList.remove('open');
        $('modalViewer').src = '';
    };

    $('arModal').addEventListener('click', e => {
        if (e.target === $('arModal')) closeModal();
    });

    /* ── Init ────────────────────────────────────────────────── */
    async function init() {
        await loadMateriDropdown();
        await fetchGaleriAR();
    }
    init();

    // Expose for retry button
    window.fetchGaleriAR = fetchGaleriAR;
})();
</script>
@endpush