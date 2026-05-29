@extends('layouts.app')

@section('title', 'Tambah Materi - Synapse')
@section('header_title', 'Tambah Materi Baru')

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
    display: grid;
    grid-template-columns: 1fr 300px;
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
.opt { font-size: 10px; color: #bbb; font-weight: 400;
       text-transform: none; letter-spacing: 0; }

.fc {
    width: 100%; padding: 10px 12px;
    border: 1px solid #e5e7eb; border-radius: 9px;
    font-size: 13px; font-family: inherit; color: #1a1a1a;
    background: #fff; box-sizing: border-box;
    transition: border-color .15s, box-shadow .15s;
}
.fc:focus  { outline: none; border-color: #279685; box-shadow: 0 0 0 3px rgba(39,150,133,.1); }
.fc::placeholder { color: #ccc; }
.fc.err { border-color: #ef4444; }

/* Thumbnail uploader */
.thumb-zone {
    border: 2px dashed #e5e7eb; border-radius: 12px;
    padding: 24px 16px; text-align: center; cursor: pointer;
    background: #fafafa; transition: all .2s; position: relative;
    min-height: 140px; display: flex; align-items: center;
    justify-content: center; flex-direction: column;
}
.thumb-zone:hover     { border-color: #279685; background: #f0fdfb; }
.thumb-zone.has-image { padding: 0; border-style: solid; border-color: #279685; background: #fff; min-height: auto; }
.thumb-zone input     { display: none; }
.thumb-zone-icon      { font-size: 36px; margin-bottom: 8px; }
.thumb-zone-label     { font-size: 13px; font-weight: 700; color: #279685; margin-bottom: 4px; }
.thumb-zone-hint      { font-size: 11px; color: #aaa; }
.thumb-preview-img    { width: 100%; max-height: 240px; object-fit: cover; border-radius: 10px; display: block; }
.thumb-remove-btn {
    position: absolute; top: 8px; right: 8px;
    background: rgba(0,0,0,.6); color: #fff; border: none;
    padding: 5px 10px; border-radius: 6px; font-size: 11px;
    font-weight: 700; cursor: pointer; font-family: inherit;
}
.thumb-remove-btn:hover { background: rgba(220,38,38,.85); }

/* Visibility toggle */
.vis-box {
    display: flex; align-items: center; justify-content: space-between;
    padding: 13px 14px; border: 2px solid #e5e7eb;
    border-radius: 10px; background: #f9fafb; cursor: pointer;
    transition: border-color .2s, background .2s; user-select: none;
}
.vis-box:hover     { border-color: #9ca3af; }
.vis-box.is-umum   { border-color: #279685; background: #f0fdfb; }
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

/* CKEditor */
.ck-editor__editable_inline { min-height: 380px; font-size: 14px; }

/* Action bar */
.action-bar {
    display: flex; align-items: center; justify-content: flex-end;
    gap: 10px; padding: 16px 22px;
    border-top: 1px solid #f0f0f0;
}
.btn-batal {
    padding: 10px 20px; border-radius: 9px; font-size: 13px; font-weight: 600;
    border: 1px solid #e5e7eb; background: #fff; color: #555;
    text-decoration: none; cursor: pointer; transition: background .15s;
}
.btn-batal:hover { background: #f9fafb; }
.btn-simpan {
    padding: 10px 24px; border-radius: 9px; font-size: 13px; font-weight: 700;
    border: none; background: #279685; color: #fff;
    cursor: pointer; transition: background .15s, opacity .15s;
}
.btn-simpan:hover    { background: #1f7a6d; }
.btn-simpan:disabled { opacity: .5; cursor: not-allowed; }

/* Checklist sidebar */
.checklist { list-style: none; padding: 0; margin: 0; }
.checklist li {
    display: flex; align-items: flex-start; gap: 10px;
    font-size: 12px; color: #888; padding: 8px 0;
    border-bottom: 1px solid #f5f5f5; line-height: 1.4;
}
.checklist li:last-child { border-bottom: none; }
.checklist li .ck-dot {
    width: 18px; height: 18px; border-radius: 50%; flex-shrink: 0;
    border: 2px solid #e5e7eb; margin-top: 1px; transition: all .2s;
    display: flex; align-items: center; justify-content: center; font-size: 10px;
}
.checklist li.done .ck-dot { background: #279685; border-color: #279685; color: #fff; }
.checklist li.done { color: #1a1a1a; }

.toast {
    position: fixed; bottom: 24px; left: 50%;
    transform: translateX(-50%) translateY(60px);
    background: #1a1a1a; color: #fff; padding: 10px 20px;
    border-radius: 10px; font-size: 13px; font-weight: 600;
    opacity: 0; transition: all .3s; z-index: 999; white-space: nowrap;
}
.toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
.toast.err  { background: #dc2626; }

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

<a href="/mata-kuliah/{{ $course_id }}/materi" class="back-link">
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
        stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>
    </svg>
    Kembali ke daftar materi
</a>

<div class="form-layout">

    {{-- ── KOLOM KIRI: Form utama ── --}}
    <div>

        {{-- Card: Info Dasar --}}
        <div class="form-card">
            <div class="card-header">
                <div class="ch-icon teal">📝</div>
                <div>
                    <h3>Informasi Materi</h3>
                    <p>Judul dan deskripsi yang muncul di aplikasi mahasiswa</p>
                </div>
            </div>
            <div class="card-body">
                <div class="fg">
                    <label>Judul Materi <span class="req">*</span></label>
                    <input type="text" id="title" class="fc"
                        placeholder="Contoh: Pengenalan Struktur Data" required>
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

        {{-- Card: Isi Materi --}}
        <div class="form-card">
            <div class="card-header">
                <div class="ch-icon purple">📄</div>
                <div>
                    <h3>Isi E-Modul</h3>
                    <p>Konten lengkap materi dengan rich text editor</p>
                </div>
            </div>
            <div class="card-body">
                <textarea id="editor"></textarea>
            </div>
        </div>

    </div>

    {{-- ── KOLOM KANAN: Thumbnail, Akses, Checklist ── --}}
    <div>

        {{-- Card: Thumbnail --}}
        <div class="form-card">
            <div class="card-header">
                <div class="ch-icon amber">🖼️</div>
                <div>
                    <h3>Gambar Cover</h3>
                    <p>Opsional — tampil sebagai thumbnail di app</p>
                </div>
            </div>
            <div class="card-body">
                <div class="thumb-zone" id="thumbZone"
                    onclick="document.getElementById('imageInput').click()"
                    ondragover="event.preventDefault(); this.style.borderColor='#279685'"
                    ondragleave="this.style.borderColor=''"
                    ondrop="handleThumbDrop(event)">
                    <input type="file" id="imageInput" accept="image/jpeg,image/png,image/jpg,image/webp">
                    <div id="thumbPlaceholder">
                        <div class="thumb-zone-icon">🖼️</div>
                        <div class="thumb-zone-label">Klik atau drag & drop</div>
                        <div class="thumb-zone-hint">PNG, JPG, WEBP — maks 2MB</div>
                    </div>
                    <img id="thumbPreviewImg" class="thumb-preview-img" style="display:none;" alt="preview">
                    <button type="button" id="thumbRemoveBtn" class="thumb-remove-btn"
                        style="display:none;" onclick="event.stopPropagation(); removeThumbnail()">
                        🗑 Hapus
                    </button>
                </div>
            </div>
        </div>

        {{-- Card: Akses (Visibility) --}}
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
                    <div class="vis-track" id="visTrack">
                        <div class="vis-knob" id="visKnob"></div>
                    </div>
                </div>
                <input type="hidden" id="visibility" value="mahasiswa">
                <p class="vis-hint">Pilih <strong>Umum</strong> agar dapat diakses pengguna non-mahasiswa IPB yang sudah login</p>
            </div>
        </div>

        {{-- Card: Checklist --}}
        <div class="form-card">
            <div class="card-header">
                <div class="ch-icon teal">✅</div>
                <div><h3>Kelengkapan</h3></div>
            </div>
            <div class="card-body" style="padding: 12px 22px;">
                <ul class="checklist" id="checklist">
                    <li id="ck-title">
                        <div class="ck-dot"></div>
                        <span>Judul materi diisi</span>
                    </li>
                    <li id="ck-desc">
                        <div class="ck-dot"></div>
                        <span>Deskripsi singkat diisi</span>
                    </li>
                    <li id="ck-content">
                        <div class="ck-dot"></div>
                        <span>Isi e-modul ditulis</span>
                    </li>
                </ul>
            </div>
            <div class="action-bar">
                <a href="/mata-kuliah/{{ $course_id }}/materi" class="btn-batal">Batal</a>
                <button class="btn-simpan" id="btnSimpan" onclick="submitMateri()">
                    💾 Simpan Materi
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

    // ── Checklist ─────────────────────────────────────────────
    function updateChecklist() {
        const title   = $('title').value.trim();
        const desc    = $('description').value.trim();
        const content = myEditor ? myEditor.getData().replace(/<[^>]*>/g,'').trim() : '';

        setDone('ck-title',   title.length > 0);
        setDone('ck-desc',    desc.length > 0);
        setDone('ck-content', content.length > 10);
    }
    function setDone(id, done) {
        const el = $(id); if (!el) return;
        el.classList.toggle('done', done);
        el.querySelector('.ck-dot').textContent = done ? '✓' : '';
    }

    $('title').addEventListener('input', updateChecklist);
    $('description').addEventListener('input', updateChecklist);

    // ── CKEditor ──────────────────────────────────────────────
    class UploadAdapter {
        constructor(loader) { this.loader = loader; }
        upload() {
            return this.loader.file.then(file => new Promise((res, rej) => {
                const fd = new FormData();
                fd.append('upload', file);
                fetch(API + '/upload-image', {
                    method: 'POST',
                    headers: { 'Authorization': 'Bearer ' + token },
                    body: fd
                }).then(r => r.json())
                  .then(d => res({ default: d.url }))
                  .catch(rej);
            }));
        }
        abort() {}
    }
    function uploadPlugin(editor) {
        editor.plugins.get('FileRepository').createUploadAdapter = l => new UploadAdapter(l);
    }

    ClassicEditor.create($('editor'), { extraPlugins: [uploadPlugin] })
        .then(editor => {
            myEditor = editor;
            editor.model.document.on('change:data', updateChecklist);
        })
        .catch(console.error);

    // ── Thumbnail ─────────────────────────────────────────────
    $('imageInput').addEventListener('change', function(e) {
        processThumb(e.target.files[0]);
    });

    window.handleThumbDrop = function(e) {
        e.preventDefault();
        $('thumbZone').style.borderColor = '';
        processThumb(e.dataTransfer.files[0]);
    };

    function processThumb(file) {
        if (!file) return;
        if (file.size > 2 * 1024 * 1024) {
            toast('Ukuran thumbnail maksimal 2MB!', 'err'); return;
        }
        const reader = new FileReader();
        reader.onload = ev => {
            $('thumbPreviewImg').src   = ev.target.result;
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

    // ── Visibility toggle ─────────────────────────────────────
    window.toggleVisibility = function() {
        const input = $('visibility');
        const box   = $('visBox');
        const icon  = $('visIcon');
        const main  = $('visMain');
        const sub   = $('visSub');

        if (input.value === 'mahasiswa') {
            input.value = 'umum';
            box.classList.add('is-umum');
            icon.textContent = '🌐';
            main.textContent = 'Umum — Semua Pengguna';
            sub.textContent  = 'Dapat diakses semua pengguna yang sudah login';
        } else {
            input.value = 'mahasiswa';
            box.classList.remove('is-umum');
            icon.textContent = '🔒';
            main.textContent = 'Hanya Mahasiswa IPB';
            sub.textContent  = 'Tersembunyi untuk tamu & pengguna umum';
        }
    };

    // ── Submit ────────────────────────────────────────────────
    window.submitMateri = async function() {
        const title   = $('title').value.trim();
        const desc    = $('description').value.trim();
        const content = myEditor ? myEditor.getData() : '';

        if (!title)   { toast('Judul materi wajib diisi!', 'err'); $('title').focus(); return; }
        if (!desc)    { toast('Deskripsi singkat wajib diisi!', 'err'); $('description').focus(); return; }
        if (!content || content.replace(/<[^>]*>/g,'').trim().length < 10) {
            toast('Isi e-modul tidak boleh kosong!', 'err'); return;
        }

        const btn = $('btnSimpan');
        btn.disabled   = true;
        btn.textContent = '⏳ Menyimpan...';

        const formData = new FormData();
        formData.append('title',       title);
        formData.append('description', desc);
        formData.append('content',     content);
        formData.append('visibility',  $('visibility').value);

        const imgInput = $('imageInput');
        if (imgInput.files.length > 0) {
            formData.append('image', imgInput.files[0]);
        }

        try {
            const res = await fetch(`${API}/courses/${courseId}/materials`, {
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Accept': 'application/json',
                },
                body: formData,
            });

            if (res.ok) {
                toast('Materi berhasil disimpan!');
                setTimeout(() => {
                    window.location.href = `/mata-kuliah/${courseId}/materi`;
                }, 800);
            } else {
                const err = await res.json();
                let msg = err.message || 'Gagal menyimpan materi.';
                if (err.errors) msg = Object.values(err.errors).flat()[0] || msg;
                toast(msg, 'err');
                btn.disabled   = false;
                btn.textContent = '💾 Simpan Materi';
            }
        } catch (e) {
            toast('Koneksi bermasalah: ' + e.message, 'err');
            btn.disabled   = false;
            btn.textContent = '💾 Simpan Materi';
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

            // Langsung isi field deskripsi
            $('description').value = data.description || '';
            $('description').dispatchEvent(new Event('input')); // trigger checklist update
            toast('Deskripsi berhasil di-generate! Kamu bisa edit manual.');

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

    // Helper escape HTML
    function esc(s) {
        return String(s||'')
            .replace(/&/g,'&amp;')
            .replace(/</g,'&lt;')
            .replace(/>/g,'&gt;')
            .replace(/"/g,'&quot;');
    }

    // ── Init ─────────────────────────────────────────────────
    updateChecklist();
})();
</script>
@endpush