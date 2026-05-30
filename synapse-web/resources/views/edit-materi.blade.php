@extends('layouts.app')
@section('title', 'Edit Materi - Synapse')
@section('header_title', 'Edit Materi')

@section('content')
<style>
/* ── Semua style sama persis dengan tambah_materi.blade, kecuali btn-update ── */
.back-link { display:inline-flex; align-items:center; gap:7px; color:#9ca3af; font-size:13px; font-weight:600; text-decoration:none; margin-bottom:22px; transition:color .15s; }
.back-link:hover { color:#279685; }
.back-link svg { width:15px; height:15px; transition:transform .15s; }
.back-link:hover svg { transform:translateX(-3px); }

.form-layout { display:grid; grid-template-columns:1fr 300px; gap:20px; align-items:start; }
@media(max-width:820px) { .form-layout { grid-template-columns:1fr; } }

.form-card { background:#fff; border-radius:16px; border:1px solid #eff0f2; overflow:hidden; margin-bottom:16px; }
.form-card:last-child { margin-bottom:0; }

.card-header { display:flex; align-items:center; gap:12px; padding:18px 22px 14px; border-bottom:1px solid #f5f6f8; }
.ch-icon { width:36px; height:36px; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.ch-icon svg { width:18px; height:18px; }
.ch-icon.teal   { background:rgba(39,150,133,.1);  color:#279685; }
.ch-icon.amber  { background:rgba(245,158,11,.1);  color:#d97706; }
.ch-icon.purple { background:rgba(124,58,237,.1);  color:#7c3aed; }
.ch-icon.green  { background:rgba(16,185,129,.1);  color:#059669; }
.card-header h3 { font-size:14px; font-weight:700; color:#111827; margin:0 0 2px; }
.card-header p  { font-size:11px; color:#9ca3af; margin:0; }
.card-body { padding:18px 22px; }

.fg { margin-bottom:16px; }
.fg:last-child { margin-bottom:0; }
.fg > label { display:flex; align-items:center; gap:5px; font-size:11px; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:.05em; margin-bottom:7px; }
.req { color:#ef4444; }

.fc { width:100%; padding:10px 13px; border:1.5px solid #e5e7eb; border-radius:10px; font-size:13px; font-family:inherit; color:#111827; background:#fff; box-sizing:border-box; transition:border-color .15s, box-shadow .15s; }
.fc:focus { outline:none; border-color:#279685; box-shadow:0 0 0 3px rgba(39,150,133,.12); }
.fc::placeholder { color:#d1d5db; }

.thumb-zone { border:2px dashed #e5e7eb; border-radius:12px; padding:28px 16px; text-align:center; cursor:pointer; background:#fafafa; transition:all .2s; position:relative; min-height:120px; display:flex; align-items:center; justify-content:center; flex-direction:column; gap:8px; }
.thumb-zone:hover     { border-color:#279685; background:#f0fdf9; }
.thumb-zone.has-image { padding:0; border-style:solid; border-color:#279685; min-height:auto; }
.thumb-zone input { display:none; }
.thumb-ico { width:44px; height:44px; border-radius:12px; background:#f0fdf9; display:flex; align-items:center; justify-content:center; color:#279685; margin:0 auto; }
.thumb-ico svg { width:22px; height:22px; }
.thumb-lbl  { font-size:13px; font-weight:700; color:#279685; }
.thumb-hint { font-size:11px; color:#9ca3af; }
.thumb-preview-img { width:100%; max-height:220px; object-fit:cover; border-radius:10px; display:block; }
.thumb-change-hint { font-size:11px; color:#9ca3af; margin:8px 0 0; text-align:center; }
.thumb-remove-btn { position:absolute; top:8px; right:8px; background:rgba(0,0,0,.6); color:#fff; border:none; padding:5px 10px; border-radius:7px; font-size:11px; font-weight:700; cursor:pointer; font-family:inherit; display:flex; align-items:center; gap:4px; }
.thumb-remove-btn:hover { background:rgba(220,38,38,.85); }
.thumb-remove-btn svg { width:12px; height:12px; }

.vis-box { display:flex; align-items:center; justify-content:space-between; padding:13px 14px; border:2px solid #e5e7eb; border-radius:11px; background:#f9fafb; cursor:pointer; transition:border-color .2s, background .2s; user-select:none; }
.vis-box:hover   { border-color:#9ca3af; }
.vis-box.is-umum { border-color:#279685; background:#f0fdf9; }
.vis-box.is-umum .vis-track { background:#279685; }
.vis-box.is-umum .vis-knob  { left:22px; }
.vis-info  { display:flex; align-items:center; gap:10px; }
.vis-icon  { width:34px; height:34px; border-radius:9px; display:flex; align-items:center; justify-content:center; background:#f3f4f6; flex-shrink:0; }
.vis-icon svg { width:17px; height:17px; color:#6b7280; }
.vis-box.is-umum .vis-icon { background:rgba(39,150,133,.1); }
.vis-box.is-umum .vis-icon svg { color:#279685; }
.vis-main { font-size:13px; font-weight:700; color:#111827; }
.vis-sub  { font-size:11px; color:#9ca3af; margin-top:1px; }
.vis-track { width:44px; height:24px; background:#e5e7eb; border-radius:12px; position:relative; transition:background .2s; flex-shrink:0; }
.vis-knob  { position:absolute; top:2px; left:2px; width:20px; height:20px; background:#fff; border-radius:50%; transition:left .2s; box-shadow:0 1px 3px rgba(0,0,0,.25); }
.vis-hint  { font-size:11px; color:#9ca3af; margin:8px 0 0; }

.ck-editor__editable_inline { min-height:380px; font-size:14px; }

.action-bar { display:flex; align-items:center; justify-content:flex-end; gap:10px; padding:16px 22px; border-top:1px solid #f5f6f8; }
.btn-batal { padding:10px 20px; border-radius:10px; font-size:13px; font-weight:600; border:1.5px solid #e5e7eb; background:#fff; color:#6b7280; text-decoration:none; cursor:pointer; transition:background .15s; }
.btn-batal:hover { background:#f5f6f8; }
.btn-update {
    display:inline-flex; align-items:center; gap:7px;
    padding:10px 24px; border-radius:10px; font-size:13px; font-weight:700;
    border:none; background:#f59e0b; color:#fff;
    cursor:pointer; transition:background .15s; font-family:inherit;
}
.btn-update:hover    { background:#d97706; }
.btn-update:disabled { opacity:.5; cursor:not-allowed; }
.btn-update svg { width:15px; height:15px; }

/* Loading overlay */
.page-overlay { position:fixed; inset:0; background:rgba(255,255,255,.85); display:flex; align-items:center; justify-content:center; z-index:500; gap:12px; font-size:14px; font-weight:600; color:#279685; }
.page-overlay.hidden { display:none; }
.spin { width:20px; height:20px; border:2.5px solid #e5e7eb; border-top-color:#279685; border-radius:50%; animation:spin .7s linear infinite; }
@keyframes spin { to{transform:rotate(360deg)} }

/* Skeleton */
.skeleton { background:linear-gradient(90deg,#f5f5f5 25%,#eee 50%,#f5f5f5 75%); background-size:200% 100%; animation:shimmer 1.2s infinite; border-radius:6px; }
@keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }

/* AI */
.ai-desc-wrap { position:relative; padding-top:32px; }
.btn-ai-desc { display:inline-flex; align-items:center; gap:5px; padding:5px 12px; border:none; border-radius:99px; background:linear-gradient(135deg,#279685,#1a6b5e); color:#fff; font-size:11px; font-weight:700; cursor:pointer; font-family:inherit; transition:opacity .15s; position:absolute; right:0; top:0; }
.btn-ai-desc svg { width:13px; height:13px; }
.btn-ai-desc:hover { opacity:.85; }
.btn-ai-desc:disabled { opacity:.6; cursor:not-allowed; }
.ai-desc-panel { background:#f0fdf9; border:1.5px solid #a7f3d0; border-radius:11px; padding:14px 16px; margin-top:8px; display:none; }
.ai-desc-panel.open { display:block; }
.ai-desc-panel p { font-size:11px; font-weight:700; color:#065f46; margin:0 0 8px; text-transform:uppercase; letter-spacing:.05em; }
.ai-desc-option { background:#fff; border:1.5px solid #d1fae5; border-radius:9px; padding:10px 12px; margin-bottom:8px; cursor:pointer; font-size:13px; color:#111827; transition:border-color .15s, background .15s; }
.ai-desc-option:hover { border-color:#279685; background:#f0fdf9; }
.ai-desc-option.selected { border-color:#279685; background:#e6f4f2; }
.ai-desc-option-label { font-size:10px; font-weight:700; color:#279685; display:block; margin-bottom:4px; }
.btn-use-desc { padding:7px 16px; border:none; border-radius:8px; background:#279685; color:#fff; font-size:12px; font-weight:700; cursor:pointer; font-family:inherit; margin-top:4px; }
.btn-use-desc:disabled { background:#9ca3af; cursor:not-allowed; }
</style>

<div class="page-overlay" id="loadingOverlay">
    <div class="spin"></div> Memuat data materi...
</div>

<a href="/mata-kuliah/{{ $course_id }}/materi" class="back-link">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>
    </svg>
    Kembali ke daftar materi
</a>

<div class="form-layout">
    {{-- KOLOM KIRI --}}
    <div>
        <div class="form-card">
            <div class="card-header">
                <div class="ch-icon teal">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </div>
                <div>
                    <h3>Informasi Materi</h3>
                    <p>Edit judul dan deskripsi materi</p>
                </div>
            </div>
            <div class="card-body">
                <div class="fg">
                    <label>Judul Materi <span class="req">*</span></label>
                    <input type="text" id="title" class="fc" placeholder="Judul materi">
                </div>
                <div class="fg">
                    <label>Deskripsi Singkat <span class="req">*</span></label>
                    <div class="ai-desc-wrap">
                        <button class="btn-ai-desc" id="btnAiDesc" onclick="generateDesc()" type="button">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                            Generate AI
                        </button>
                        <input type="text" id="description" class="fc" placeholder="Ringkasan singkat">
                    </div>
                    <div class="ai-desc-panel" id="aiDescPanel">
                        <p>Pilih versi deskripsi:</p>
                        <div id="aiDescOptions"></div>
                        <button class="btn-use-desc" id="btnUseDesc" onclick="useSelectedDesc()" disabled>Gunakan ini</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-card">
            <div class="card-header">
                <div class="ch-icon purple">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                </div>
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

    {{-- KOLOM KANAN --}}
    <div>
        <div class="form-card">
            <div class="card-header">
                <div class="ch-icon amber">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                </div>
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
                        <div class="thumb-ico">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                        </div>
                        <div class="thumb-lbl">Klik untuk ganti gambar</div>
                        <div class="thumb-hint">PNG, JPG, WEBP — maks 2MB</div>
                    </div>
                    <img id="thumbPreviewImg" class="thumb-preview-img" style="display:none" alt="preview">
                    <button type="button" id="thumbRemoveBtn" class="thumb-remove-btn" style="display:none"
                        onclick="event.stopPropagation(); removeThumbnail()">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/></svg>
                        Hapus
                    </button>
                </div>
                <p class="thumb-change-hint">Gambar lama tetap digunakan jika tidak diganti</p>
            </div>
        </div>

        <div class="form-card">
            <div class="card-header">
                <div class="ch-icon green">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                </div>
                <div>
                    <h3>Hak Akses</h3>
                    <p>Siapa yang bisa melihat materi ini</p>
                </div>
            </div>
            <div class="card-body">
                <div class="vis-box" id="visBox" onclick="toggleVisibility()">
                    <div class="vis-info">
                        <div class="vis-icon" id="visIconBox">
                            <svg id="visIconSvg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        </div>
                        <div>
                            <div class="vis-main" id="visMain">Hanya Mahasiswa</div>
                            <div class="vis-sub"  id="visSub">Tersembunyi untuk tamu & umum</div>
                        </div>
                    </div>
                    <div class="vis-track"><div class="vis-knob"></div></div>
                </div>
                <input type="hidden" id="visibility" value="mahasiswa">
                <p class="vis-hint">Aktifkan untuk mengizinkan semua pengguna yang sudah login</p>
            </div>
        </div>

        <div class="form-card">
            <div class="action-bar">
                <a href="/mata-kuliah/{{ $course_id }}/materi" class="btn-batal">Batal</a>
                <button class="btn-update" id="btnUpdate" onclick="submitUpdate()">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    Simpan Perubahan
                </button>
            </div>
        </div>
    </div>
</div>
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

    const lockSVG  = `<svg id="visIconSvg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>`;
    const globeSVG = `<svg id="visIconSvg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>`;

    function setVisibility(val) {
        $('visibility').value = val || 'mahasiswa';
        const box = $('visBox');
        if (val === 'umum') {
            box.classList.add('is-umum');
            $('visMain').textContent = 'Umum — Semua Pengguna';
            $('visSub').textContent  = 'Dapat diakses semua pengguna yang sudah login';
            $('visIconSvg').outerHTML = globeSVG;
        } else {
            box.classList.remove('is-umum');
            $('visMain').textContent = 'Hanya Mahasiswa';
            $('visSub').textContent  = 'Tersembunyi untuk tamu & umum';
            $('visIconSvg').outerHTML = lockSVG;
        }
    }
    window.toggleVisibility = function() {
        setVisibility($('visibility').value === 'mahasiswa' ? 'umum' : 'mahasiswa');
    };

    $('imageInput').addEventListener('change', e => processThumb(e.target.files[0]));
    window.handleThumbDrop = function(e) { e.preventDefault(); processThumb(e.dataTransfer.files[0]); };
    function processThumb(file) {
        if (!file) return;
        if (file.size > 2*1024*1024) { toast('Gambar maks 2MB!','err'); return; }
        const r = new FileReader();
        r.onload = ev => {
            $('thumbPreviewImg').src = ev.target.result;
            $('thumbPreviewImg').style.display = 'block';
            $('thumbPlaceholder').style.display = 'none';
            $('thumbRemoveBtn').style.display   = 'flex';
            $('thumbZone').classList.add('has-image');
        };
        r.readAsDataURL(file);
    }
    window.removeThumbnail = function() {
        $('imageInput').value = '';
        $('thumbPreviewImg').src = '';
        $('thumbPreviewImg').style.display = 'none';
        $('thumbPlaceholder').style.display = 'flex';
        $('thumbRemoveBtn').style.display   = 'none';
        $('thumbZone').classList.remove('has-image');
    };

    class UploadAdapter {
        constructor(loader) { this.loader = loader; }
        upload() {
            return this.loader.file.then(file => new Promise((res,rej) => {
                const fd = new FormData();
                fd.append('upload', file);
                fetch(API+'/upload-image',{method:'POST',headers:{'Authorization':'Bearer '+token},body:fd})
                    .then(r=>r.json()).then(d=>res({default:d.url})).catch(rej);
            }));
        }
        abort() {}
    }
    function uploadPlugin(editor) { editor.plugins.get('FileRepository').createUploadAdapter = l => new UploadAdapter(l); }

    async function loadData() {
        try {
            const res  = await fetch(`${API}/materials/${materiId}`, { headers:{'Authorization':'Bearer '+token,'Accept':'application/json'} });
            const data = await res.json();
            const d    = data.data || data;
            $('title').value       = d.title       || '';
            $('description').value = d.description || '';
            if (d.image) {
                $('thumbPreviewImg').src = d.image;
                $('thumbPreviewImg').style.display = 'block';
                $('thumbPlaceholder').style.display = 'none';
                $('thumbRemoveBtn').style.display   = 'flex';
                $('thumbZone').classList.add('has-image');
            }
            setVisibility(d.visibility || 'mahasiswa');
            ClassicEditor.create($('editor'), { extraPlugins:[uploadPlugin] })
                .then(editor => { myEditor = editor; editor.setData(d.content || ''); })
                .catch(console.error);
        } catch(e) {
            toast('Gagal memuat data: '+e.message,'err');
            ClassicEditor.create($('editor'), { extraPlugins:[uploadPlugin] })
                .then(editor => { myEditor = editor; }).catch(console.error);
        } finally { $('loadingOverlay').classList.add('hidden'); }
    }

    window.submitUpdate = async function() {
        const title   = $('title').value.trim();
        const desc    = $('description').value.trim();
        const content = myEditor ? myEditor.getData() : '';
        if (!title)   { toast('Judul wajib diisi!','err'); return; }
        if (!desc)    { toast('Deskripsi wajib diisi!','err'); return; }
        if (!content || content.replace(/<[^>]*>/g,'').trim().length < 5) { toast('Isi e-modul tidak boleh kosong!','err'); return; }

        const btn = $('btnUpdate');
        btn.disabled = true;

        const img = $('imageInput');
        let res;
        if (img.files.length > 0) {
            const fd = new FormData();
            fd.append('title',desc); fd.append('title',title);
            fd.append('description',desc); fd.append('content',content);
            fd.append('visibility',$('visibility').value);
            fd.append('image',img.files[0]); fd.append('_method','PUT');
            res = await fetch(`${API}/materials/${materiId}`,{method:'POST',headers:{'Authorization':'Bearer '+token,'Accept':'application/json'},body:fd});
        } else {
            res = await fetch(`${API}/materials/${materiId}`,{method:'PUT',headers:{'Authorization':'Bearer '+token,'Accept':'application/json','Content-Type':'application/json'},body:JSON.stringify({title,description:desc,content,visibility:$('visibility').value})});
        }

        if (res.ok) {
            toast('Materi berhasil diperbarui!');
            setTimeout(() => { window.location.href=`/mata-kuliah/${courseId}/materi`; }, 800);
        } else {
            const err = await res.json().catch(()=>({}));
            let msg = err.message||'Gagal menyimpan.';
            if (err.errors) msg = Object.values(err.errors).flat()[0]||msg;
            toast(msg,'err'); btn.disabled = false;
        }
    };

    let selectedDescIdx=-1, aiDescriptions=[];
    window.generateDesc = async function() {
        const title = $('title').value.trim();
        if (!title) { toast('Isi judul terlebih dahulu.','err'); return; }
        const btn = $('btnAiDesc'); btn.disabled=true;
        let content=''; try{ if(myEditor?.getData) content=myEditor.getData(); }catch(_){}
        try {
            const res  = await fetch(`${API}/ai/generate-description`,{method:'POST',headers:{Authorization:'Bearer '+token,Accept:'application/json','Content-Type':'application/json'},body:JSON.stringify({title,content})});
            const data = await res.json();
            if (!res.ok) { toast(data.message||'Gagal generate.','err'); return; }
            $('description').value = data.description||'';
            $('description').dispatchEvent(new Event('input'));
            toast('Deskripsi berhasil di-generate!');
        } catch(_) { toast('Koneksi bermasalah.','err'); }
        finally { btn.disabled=false; }
    };

    function esc(s){return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');}
    window.selectDesc = function(i) {
        document.querySelectorAll('.ai-desc-option').forEach(el=>el.classList.remove('selected'));
        document.getElementById(`descOpt-${i}`).classList.add('selected');
        selectedDescIdx=i; $('btnUseDesc').disabled=false;
    };
    window.useSelectedDesc = function() {
        if(selectedDescIdx<0) return;
        $('description').value=aiDescriptions[selectedDescIdx].text;
        $('aiDescPanel').classList.remove('open');
        selectedDescIdx=-1; toast('Deskripsi diterapkan!');
    };

    if (!materiId) { toast('ID materi tidak ditemukan!','err'); $('loadingOverlay').classList.add('hidden'); return; }
    loadData();
})();
</script>
@endpush