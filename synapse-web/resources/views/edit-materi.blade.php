@extends('layouts.app')

@section('title', 'Edit Materi - Synapse')
@section('header_title', 'Edit Materi')

@section('content')
<style>
.back-link {
    display: inline-flex; align-items: center; gap: 7px;
    color: #888; font-size: 13px; font-weight: 600;
    text-decoration: none; margin-bottom: 22px; transition: color .15s;
}
.back-link:hover { color: #279685; }
.back-link svg { transition: transform .15s; }
.back-link:hover svg { transform: translateX(-3px); }

.form-layout {
    display: grid; grid-template-columns: 1fr 300px;
    gap: 20px; align-items: start;
}
@media (max-width: 820px) { .form-layout { grid-template-columns: 1fr; } }

.form-card {
    background: #fff; border-radius: 16px;
    border: 1px solid #eee; overflow: hidden; margin-bottom: 16px;
}
.form-card:last-child { margin-bottom: 0; }

.card-header {
    display: flex; align-items: center; gap: 10px;
    padding: 18px 22px 14px; border-bottom: 1px solid #f0f0f0;
}
.ch-icon {
    width: 34px; height: 34px; border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    font-size: 16px; flex-shrink: 0;
}
.ch-icon.teal   { background: #e3faf8; }
.ch-icon.amber  { background: #fef3c7; }
.ch-icon.purple { background: #f0eeff; }
.ch-icon.green  { background: #dcfce7; }
.card-header h3 { font-size: 14px; font-weight: 700; color: #1a1a1a; margin: 0 0 2px; }
.card-header p  { font-size: 11px; color: #aaa; margin: 0; }
.card-body { padding: 18px 22px; }

.fg { margin-bottom: 16px; }
.fg:last-child { margin-bottom: 0; }
.fg > label {
    display: flex; align-items: center; gap: 5px;
    font-size: 11px; font-weight: 700; color: #555;
    text-transform: uppercase; letter-spacing: .04em; margin-bottom: 6px;
}
.req { color: #ef4444; font-size: 13px; }

.fc {
    width: 100%; padding: 10px 12px;
    border: 1px solid #e5e7eb; border-radius: 9px;
    font-size: 13px; font-family: inherit; color: #1a1a1a;
    background: #fff; box-sizing: border-box;
    transition: border-color .15s, box-shadow .15s;
}
.fc:focus  { outline: none; border-color: #279685; box-shadow: 0 0 0 3px rgba(39,150,133,.1); }
.fc::placeholder { color: #ccc; }

/* Thumbnail */
.thumb-zone {
    border: 2px dashed #e5e7eb; border-radius: 12px;
    padding: 20px 16px; text-align: center; cursor: pointer;
    background: #fafafa; transition: all .2s; position: relative;
    min-height: 120px; display: flex; align-items: center;
    justify-content: center; flex-direction: column;
}
.thumb-zone:hover     { border-color: #279685; background: #f0fdfb; }
.thumb-zone.has-image { padding: 0; border-style: solid; border-color: #279685; background: #fff; min-height: auto; }
.thumb-zone input { display: none; }
.thumb-zone-icon  { font-size: 30px; margin-bottom: 6px; }
.thumb-zone-label { font-size: 13px; font-weight: 700; color: #279685; margin-bottom: 3px; }
.thumb-zone-hint  { font-size: 11px; color: #aaa; }
.thumb-preview-img { width: 100%; max-height: 220px; object-fit: cover; border-radius: 10px; display: block; }
.thumb-change-hint { font-size: 11px; color: #aaa; margin: 6px 0 0; text-align: center; }
.thumb-remove-btn {
    position: absolute; top: 8px; right: 8px;
    background: rgba(0,0,0,.6); color: #fff; border: none;
    padding: 5px 10px; border-radius: 6px; font-size: 11px;
    font-weight: 700; cursor: pointer; font-family: inherit;
}
.thumb-remove-btn:hover { background: rgba(220,38,38,.85); }

/* Visibility */
.vis-box {
    display: flex; align-items: center; justify-content: space-between;
    padding: 13px 14px; border: 2px solid #e5e7eb;
    border-radius: 10px; background: #f9fafb; cursor: pointer;
    transition: border-color .2s, background .2s; user-select: none;
}
.vis-box:hover   { border-color: #9ca3af; }
.vis-box.is-umum { border-color: #279685; background: #f0fdfb; }
.vis-box.is-umum .vis-track { background: #279685; }
.vis-box.is-umum .vis-knob  { left: 22px; }
.vis-info   { display: flex; align-items: center; gap: 10px; }
.vis-icon   { font-size: 18px; }
.vis-main   { font-size: 13px; font-weight: 700; color: #1a1a1a; }
.vis-sub    { font-size: 11px; color: #888; margin-top: 1px; }
.vis-track  { width: 44px; height: 24px; background: #e5e7eb;
    border-radius: 12px; position: relative; transition: background .2s; flex-shrink: 0; }
.vis-knob   { position: absolute; top: 2px; left: 2px;
    width: 20px; height: 20px; background: white; border-radius: 50%;
    transition: left .2s; box-shadow: 0 1px 3px rgba(0,0,0,.25); }
.vis-hint   { font-size: 11px; color: #aaa; margin: 6px 0 0; }

.ck-editor__editable_inline { min-height: 380px; font-size: 14px; }

.action-bar {
    display: flex; align-items: center; justify-content: flex-end;
    gap: 10px; padding: 16px 22px; border-top: 1px solid #f0f0f0;
}
.btn-batal {
    padding: 10px 20px; border-radius: 9px; font-size: 13px; font-weight: 600;
    border: 1px solid #e5e7eb; background: #fff; color: #555;
    text-decoration: none; cursor: pointer; transition: background .15s;
}
.btn-batal:hover { background: #f9fafb; }
.btn-update {
    padding: 10px 24px; border-radius: 9px; font-size: 13px; font-weight: 700;
    border: none; background: #f59e0b; color: #fff;
    cursor: pointer; transition: background .15s, opacity .15s;
}
.btn-update:hover    { background: #d97706; }
.btn-update:disabled { opacity: .5; cursor: not-allowed; }

/* Skeleton */
.skeleton {
    background: linear-gradient(90deg, #f0f0f0 25%, #e8e8e8 50%, #f0f0f0 75%);
    background-size: 200% 100%; animation: shimmer 1.2s infinite; border-radius: 6px;
}
@keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }

.toast {
    position: fixed; bottom: 24px; left: 50%;
    transform: translateX(-50%) translateY(60px);
    background: #1a1a1a; color: #fff; padding: 10px 20px;
    border-radius: 10px; font-size: 13px; font-weight: 600;
    opacity: 0; transition: all .3s; z-index: 999; white-space: nowrap;
}
.toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
.toast.err  { background: #dc2626; }

/* Loading overlay */
.page-overlay {
    position: fixed; inset: 0; background: rgba(255,255,255,.8);
    display: flex; align-items: center; justify-content: center;
    z-index: 500; font-size: 14px; font-weight: 600; color: #279685;
    gap: 12px;
}
.page-overlay.hidden { display: none; }
.spin {
    width: 20px; height: 20px; border: 2.5px solid #e5e7eb;
    border-top-color: #279685; border-radius: 50%;
    animation: spin .7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* ── AI Deskripsi ───────────────────────────────── */
.ai-desc-wrap { position: relative; }
.btn-ai-desc {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 5px 12px; border: none; border-radius: 7px;
    background: linear-gradient(135deg, #279685, #1a6b5e);
    color: #fff; font-size: 11px; font-weight: 700;
    cursor: pointer; font-family: inherit;
    transition: opacity .15s;
    position: absolute; right: 0; top: -30px;
}
.btn-ai-desc:hover   { opacity: .85; }
.btn-ai-desc:disabled { opacity: .6; cursor: not-allowed; }

.ai-desc-panel {
    background: #f0fdfb; border: 1.5px solid #a7f3d0;
    border-radius: 10px; padding: 14px 16px; margin-top: 8px;
    display: none;
}
.ai-desc-panel.open { display: block; }
.ai-desc-panel p {
    font-size: 11px; font-weight: 700; color: #065f46;
    margin: 0 0 8px; text-transform: uppercase; letter-spacing: .05em;
}
.ai-desc-option {
    background: #fff; border: 1px solid #d1fae5;
    border-radius: 8px; padding: 10px 12px; margin-bottom: 8px;
    cursor: pointer; font-size: 13px; color: #1a1a1a;
    transition: border-color .15s, background .15s;
    display: flex; align-items: flex-start; gap: 8px;
}
.ai-desc-option:hover { border-color: #279685; background: #f0fdfb; }
.ai-desc-option.selected { border-color: #279685; background: #e6f4f2; }
.ai-desc-option-label {
    font-size: 10px; font-weight: 700; color: #279685;
    white-space: nowrap; margin-top: 2px;
}
.btn-use-desc {
    padding: 7px 16px; border: none; border-radius: 8px;
    background: #279685; color: #fff; font-size: 12px;
    font-weight: 700; cursor: pointer; font-family: inherit;
    margin-top: 4px;
}
.btn-use-desc:disabled { background: #9ca3af; cursor: not-allowed; }
</style>

{{-- Loading overlay saat ambil data --}}
<div class="page-overlay" id="loadingOverlay">
    <div class="spin"></div> Memuat data materi...
</div>

<a href="/mata-kuliah/{{ $course_id }}/materi" class="back-link">
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
        stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>
    </svg>
    Kembali ke daftar materi
</a>

<div class="form-layout">

    {{-- Kolom kiri: Form --}}
    <div>
        <div class="form-card">
            <div class="card-header">
                <div class="ch-icon teal">📝</div>
                <div>
                    <h3>Informasi Materi</h3>
                    <p>Edit judul dan deskripsi materi</p>
                </div>
            </div>
            <div class="card-body">
                <div class="fg">
                    <label>Judul Materi <span class="req">*</span></label>
                    <input type="text" id="title" class="fc" placeholder="Judul materi" required>
                </div>
                <div class="fg">
                    <label>Deskripsi Singkat <span class="req">*</span></label>
                    <div class="ai-desc-wrap" style="position:relative;padding-top:30px;">
                        <button class="btn-ai-desc" id="btnAiDesc" onclick="generateDesc()" type="button">
                            ✨ Generate dengan AI
                        </button>
                        <input type="text" id="description" class="fc"
                            placeholder="Ringkasan singkat yang muncul di card materi" required>
                    </div>
                    <div class="ai-desc-panel" id="aiDescPanel">
                        <p>Pilih versi deskripsi:</p>
                        <div id="aiDescOptions"></div>
                        <button class="btn-use-desc" id="btnUseDesc"
                            onclick="useSelectedDesc()" disabled>
                            ✓ Gunakan Deskripsi Ini
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-card">
            <div class="card-header">
                <div class="ch-icon purple">📄</div>
                <div>
                    <h3>Isi E-Modul</h3>
                    <p>Edit konten materi</p>
                </div>
            </div>
            <div class="card-body">
                <textarea id="editor"></textarea>
            </div>
        </div>
    </div>

    {{-- Kolom kanan --}}
    <div>

        {{-- Thumbnail --}}
        <div class="form-card">
            <div class="card-header">
                <div class="ch-icon amber">🖼️</div>
                <div>
                    <h3>Gambar Cover</h3>
                    <p>Biarkan kosong untuk tidak mengubah</p>
                </div>
            </div>
            <div class="card-body">
                <div class="thumb-zone" id="thumbZone"
                    onclick="document.getElementById('imageInput').click()"
                    ondragover="event.preventDefault()"
                    ondrop="handleThumbDrop(event)">
                    <input type="file" id="imageInput" accept="image/jpeg,image/png,image/jpg,image/webp">
                    <div id="thumbPlaceholder">
                        <div class="thumb-zone-icon">🖼️</div>
                        <div class="thumb-zone-label">Klik untuk ganti gambar</div>
                        <div class="thumb-zone-hint">PNG, JPG, WEBP — maks 2MB</div>
                    </div>
                    <img id="thumbPreviewImg" class="thumb-preview-img" style="display:none;" alt="preview">
                    <button type="button" id="thumbRemoveBtn" class="thumb-remove-btn"
                        style="display:none;" onclick="event.stopPropagation(); removeThumbnail()">
                        🗑 Hapus
                    </button>
                </div>
                <p class="thumb-change-hint">Gambar lama tetap digunakan jika tidak diganti</p>
            </div>
        </div>

        {{-- Visibility --}}
        <div class="form-card">
            <div class="card-header">
                <div class="ch-icon green">🔐</div>
                <div>
                    <h3>Hak Akses</h3>
                    <p>Siapa yang bisa melihat materi ini</p>
                </div>
            </div>
            <div class="card-body">
                <div class="vis-box" id="visBox" onclick="toggleVisibility()">
                    <div class="vis-info">
                        <span class="vis-icon" id="visIcon">🔒</span>
                        <div>
                            <div class="vis-main" id="visMain">Hanya Mahasiswa IPB</div>
                            <div class="vis-sub"  id="visSub">Tersembunyi untuk tamu & umum</div>
                        </div>
                    </div>
                    <div class="vis-track">
                        <div class="vis-knob" id="visKnob"></div>
                    </div>
                </div>
                <input type="hidden" id="visibility" value="mahasiswa">
                <p class="vis-hint">Pilih <strong>Umum</strong> agar dapat diakses pengguna non-mahasiswa IPB</p>
            </div>
        </div>

        {{-- Action --}}
        <div class="form-card">
            <div class="action-bar">
                <a href="/mata-kuliah/{{ $course_id }}/materi" class="btn-batal">Batal</a>
                <button class="btn-update" id="btnUpdate" onclick="submitUpdate()">
                    ✏️ Simpan Perubahan
                </button>
            </div>
        </div>

    </div>
</div>

<div class="toast" id="toast"></div>
@endsection

@push('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
(function () {
    const token    = window.token || localStorage.getItem('token');
    const courseId = "{{ $course_id }}";
    const materiId = "{{ $materi_id ?? '' }}";
    const API      = window.apiBaseUrl;
    const $        = id => document.getElementById(id);
    let   myEditor = null;

    // ── Toast ─────────────────────────────────────────────────
    function toast(msg, type = 'ok') {
        const el = $('toast');
        el.textContent = (type === 'ok' ? '✓  ' : '✕  ') + msg;
        el.className   = 'toast ' + type + ' show';
        clearTimeout(el._t);
        el._t = setTimeout(() => el.classList.remove('show'), 3200);
    }

    // ── Visibility ────────────────────────────────────────────
    function setVisibility(val) {
        const input = $('visibility');
        const box   = $('visBox');
        const icon  = $('visIcon');
        const main  = $('visMain');
        const sub   = $('visSub');
        input.value = val || 'mahasiswa';
        if (val === 'umum') {
            box.classList.add('is-umum');
            icon.textContent = '🌐';
            main.textContent = 'Umum — Semua Pengguna';
            sub.textContent  = 'Dapat diakses semua pengguna yang sudah login';
        } else {
            box.classList.remove('is-umum');
            icon.textContent = '🔒';
            main.textContent = 'Hanya Mahasiswa IPB';
            sub.textContent  = 'Tersembunyi untuk tamu & pengguna umum';
        }
    }

    window.toggleVisibility = function() {
        setVisibility($('visibility').value === 'mahasiswa' ? 'umum' : 'mahasiswa');
    };

    // ── Thumbnail ─────────────────────────────────────────────
    $('imageInput').addEventListener('change', e => processThumb(e.target.files[0]));

    window.handleThumbDrop = function(e) {
        e.preventDefault();
        processThumb(e.dataTransfer.files[0]);
    };

    function processThumb(file) {
        if (!file) return;
        if (file.size > 2 * 1024 * 1024) { toast('Gambar maks 2MB!', 'err'); return; }
        const reader = new FileReader();
        reader.onload = ev => {
            $('thumbPreviewImg').src = ev.target.result;
            $('thumbPreviewImg').style.display = 'block';
            $('thumbPlaceholder').style.display = 'none';
            $('thumbRemoveBtn').style.display   = 'block';
            $('thumbZone').classList.add('has-image');
        };
        reader.readAsDataURL(file);
    }

    window.removeThumbnail = function() {
        $('imageInput').value = '';
        $('thumbPreviewImg').src = '';
        $('thumbPreviewImg').style.display  = 'none';
        $('thumbPlaceholder').style.display = 'flex';
        $('thumbRemoveBtn').style.display   = 'none';
        $('thumbZone').classList.remove('has-image');
    };

    // ── CKEditor ──────────────────────────────────────────────
    class UploadAdapter {
        constructor(loader) { this.loader = loader; }
        upload() {
            return this.loader.file.then(file => new Promise((res, rej) => {
                const fd = new FormData();
                fd.append('upload', file);
                fetch(API + '/upload-image', {
                    method: 'POST', headers: { 'Authorization': 'Bearer ' + token }, body: fd
                }).then(r => r.json()).then(d => res({ default: d.url })).catch(rej);
            }));
        }
        abort() {}
    }
    function uploadPlugin(editor) {
        editor.plugins.get('FileRepository').createUploadAdapter = l => new UploadAdapter(l);
    }

    // ── Load data lama ────────────────────────────────────────
    async function loadData() {
        try {
            const res  = await fetch(`${API}/materials/${materiId}`, {
                headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
            });
            const data = await res.json();
            const d    = data.data || data;

            $('title').value       = d.title       || '';
            $('description').value = d.description || '';

            // Thumbnail dari server
            if (d.image) {
                $('thumbPreviewImg').src = d.image;
                $('thumbPreviewImg').style.display = 'block';
                $('thumbPlaceholder').style.display = 'none';
                $('thumbRemoveBtn').style.display   = 'block';
                $('thumbZone').classList.add('has-image');
            }

            // Visibility
            setVisibility(d.visibility || 'mahasiswa');

            // Inisialisasi CKEditor dengan konten lama
            ClassicEditor.create($('editor'), { extraPlugins: [uploadPlugin] })
                .then(editor => {
                    myEditor = editor;
                    editor.setData(d.content || '');
                })
                .catch(console.error);

        } catch (e) {
            toast('Gagal memuat data materi: ' + e.message, 'err');
            ClassicEditor.create($('editor'), { extraPlugins: [uploadPlugin] })
                .then(editor => { myEditor = editor; })
                .catch(console.error);
        } finally {
            $('loadingOverlay').classList.add('hidden');
        }
    }

    // ── Submit update ─────────────────────────────────────────
    window.submitUpdate = async function() {
        const title   = $('title').value.trim();
        const desc    = $('description').value.trim();
        const content = myEditor ? myEditor.getData() : '';

        if (!title)   { toast('Judul materi wajib diisi!', 'err'); return; }
        if (!desc)    { toast('Deskripsi singkat wajib diisi!', 'err'); return; }
        if (!content || content.replace(/<[^>]*>/g,'').trim().length < 5) {
            toast('Isi e-modul tidak boleh kosong!', 'err'); return;
        }

        const btn = $('btnUpdate');
        btn.disabled   = true;
        btn.textContent = '⏳ Menyimpan...';

        const imgInput  = $('imageInput');
        const hasNewImg = imgInput.files.length > 0;

        let res;
        if (hasNewImg) {
            // Pakai FormData karena ada file
            const fd = new FormData();
            fd.append('title',       title);
            fd.append('description', desc);
            fd.append('content',     content);
            fd.append('visibility',  $('visibility').value);
            fd.append('image',       imgInput.files[0]);
            fd.append('_method',     'PUT');
            res = await fetch(`${API}/materials/${materiId}`, {
                method: 'POST',
                headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' },
                body: fd,
            });
        } else {
            // JSON biasa
            res = await fetch(`${API}/materials/${materiId}`, {
                method: 'PUT',
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    title, description: desc, content,
                    visibility: $('visibility').value,
                }),
            });
        }

        if (res.ok) {
            toast('Materi berhasil diperbarui!');
            setTimeout(() => { window.location.href = `/mata-kuliah/${courseId}/materi`; }, 800);
        } else {
            const err = await res.json().catch(() => ({}));
            let msg = err.message || 'Gagal menyimpan perubahan.';
            if (err.errors) msg = Object.values(err.errors).flat()[0] || msg;
            toast(msg, 'err');
            btn.disabled   = false;
            btn.textContent = '✏️ Simpan Perubahan';
        }
    };

    /* ── AI Generate Deskripsi ───────────────────────────── */
    let selectedDescIdx = -1;
    let aiDescriptions  = [];

    window.generateDesc = async function() {
        const title   = $('title').value.trim();
        if (!title) { toast('Isi judul materi terlebih dahulu.', 'err'); return; }

        const btn = $('btnAiDesc');
        btn.disabled    = true;
        btn.textContent = '⏳ Generating...';

        // Coba ambil konten editor kalau ada
        let content = '';
        try {
            if (typeof myEditor !== 'undefined' && myEditor.getData) {
                content = myEditor.getData();
            }
        } catch (e) {}

        try {
            const res  = await fetch(`${window.apiBaseUrl}/ai/generate-description`, {
                method: 'POST',
                headers: {
                    Authorization:  'Bearer ' + window.token,
                    Accept:         'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ title, content }),
            });
            const data = await res.json();

            if (!res.ok) {
                toast(data.message || 'Gagal generate deskripsi.', 'err'); return;
            }

            const d = data.descriptions;
            aiDescriptions = [
                { label: 'Singkat (1 kalimat)',  text: d.short  || '' },
                { label: 'Menengah (2-3 kalimat)', text: d.medium || '' },
                { label: 'Panjang (4-5 kalimat)', text: d.long   || '' },
            ].filter(x => x.text);

            renderDescOptions();
            $('aiDescPanel').classList.add('open');

        } catch (e) {
            toast('Koneksi bermasalah.', 'err');
        } finally {
            btn.disabled    = false;
            btn.textContent = '✨ Generate dengan AI';
        }
    };

    function renderDescOptions() {
        selectedDescIdx = -1;
        $('btnUseDesc').disabled = true;
        $('aiDescOptions').innerHTML = aiDescriptions.map((d, i) => `
            <div class="ai-desc-option" id="descOpt-${i}" onclick="selectDesc(${i})">
                <span class="ai-desc-option-label">${d.label}</span>
                <span>${esc(d.text)}</span>
            </div>
        `).join('');
    }

    window.selectDesc = function(i) {
        document.querySelectorAll('.ai-desc-option')
            .forEach(el => el.classList.remove('selected'));
        document.getElementById(`descOpt-${i}`).classList.add('selected');
        selectedDescIdx = i;
        $('btnUseDesc').disabled = false;
    };

    window.useSelectedDesc = function() {
        if (selectedDescIdx < 0) return;
        const text = aiDescriptions[selectedDescIdx].text;
        $('description').value = text;
        $('aiDescPanel').classList.remove('open');
        selectedDescIdx = -1;
        toast('Deskripsi diterapkan! Kamu bisa edit manual.');
    };

    // Helper esc kalau belum ada
    if (typeof esc === 'undefined') {
        window.esc = s => String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    // ── Init ─────────────────────────────────────────────────
    if (!materiId) {
        toast('ID materi tidak ditemukan!', 'err');
        $('loadingOverlay').classList.add('hidden');
        return;
    }
    loadData();
})();
</script>
@endpush