@extends('layouts.app')
@section('title','Kelola Aset AR - Synapse')
@section('header_title','Aset 3D')

@section('content')
<style>
.ar-layout { display:grid; grid-template-columns:380px 1fr; gap:20px; align-items:start; }
@media(max-width:900px) { .ar-layout { grid-template-columns:1fr; } }

/* Cards */
.card { background:#fff; border-radius:16px; border:1px solid #eff0f2; overflow:hidden; }
.card-header { display:flex; align-items:center; gap:12px; padding:18px 22px 14px; border-bottom:1px solid #f5f6f8; }
.ch-icon { width:36px; height:36px; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.ch-icon svg { width:18px; height:18px; }
.ch-icon.purple { background:rgba(124,58,237,.1); color:#7c3aed; }
.ch-icon.teal   { background:rgba(39,150,133,.1);  color:#279685; }
.card-header h3 { font-size:14px; font-weight:700; color:#111827; margin:0 0 2px; }
.card-header p  { font-size:11px; color:#9ca3af; margin:0; }
.card-body { padding:18px 22px; }

/* Form */
.fg { margin-bottom:15px; }
.fg:last-child { margin-bottom:0; }
.fg label { display:block; font-size:11px; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:.05em; margin-bottom:6px; }
.req { color:#ef4444; margin-left:2px; }
.fc { width:100%; padding:9px 12px; border:1.5px solid #e5e7eb; border-radius:9px; font-size:13px; font-family:inherit; color:#111827; background:#fff; box-sizing:border-box; transition:border-color .15s,box-shadow .15s; }
.fc:focus { outline:none; border-color:#279685; box-shadow:0 0 0 3px rgba(39,150,133,.12); }
.fc::placeholder { color:#d1d5db; }
textarea.fc { resize:vertical; min-height:68px; }
select.fc { cursor:pointer; }
.fc.err { border-color:#ef4444; }

/* Dropzone */
.dropzone { border:2px dashed #e5e7eb; border-radius:11px; padding:20px; text-align:center; cursor:pointer; transition:border-color .15s,background .15s; background:#fafafa; position:relative; }
.dropzone:hover, .dropzone.drag { border-color:#279685; background:#f0fdf9; }
.dropzone input[type=file] { display:none; }
.dz-ico { width:44px; height:44px; border-radius:12px; background:rgba(39,150,133,.1); color:#279685; display:flex; align-items:center; justify-content:center; margin:0 auto 10px; }
.dz-ico svg { width:22px; height:22px; }
.dz-label { font-size:12px; font-weight:700; color:#374151; margin-bottom:3px; }
.dz-hint  { font-size:11px; color:#9ca3af; }
.dz-file  { font-size:12px; font-weight:700; color:#279685; margin-top:7px; display:flex; align-items:center; justify-content:center; gap:5px; }
.dz-file svg { width:13px; height:13px; }

/* Model viewer */
.model-wrap { border-radius:10px; overflow:hidden; background:#f8fafa; border:1px solid #eff0f2; height:200px; display:none; align-items:center; justify-content:center; position:relative; }
.model-wrap.show { display:flex; }
model-viewer { width:100%; height:100%; }

/* Thumbnail */
.thumb-preview { border-radius:9px; overflow:hidden; border:1.5px dashed #e5e7eb; height:90px; background:#fafafa; display:flex; align-items:center; justify-content:center; font-size:11px; color:#9ca3af; text-align:center; transition:all .2s; }
.thumb-preview img { width:100%; height:100%; object-fit:cover; }
.thumb-preview.generating { border-color:#279685; color:#279685; }

/* Progress */
.progress-bar { height:4px; background:#e5e7eb; border-radius:99px; overflow:hidden; margin-top:8px; display:none; }
.progress-fill { height:100%; background:linear-gradient(90deg,#279685,#4A90E2); border-radius:99px; width:70%; animation:pshim 1.5s infinite; background-size:200% 100%; }
@keyframes pshim { 0%{background-position:200% 0} 100%{background-position:-200% 0} }
.progress-bar.show { display:block; }

/* Upload button */
.btn-upload { width:100%; padding:11px; background:linear-gradient(135deg,#279685,#4A90E2); color:#fff; border:none; border-radius:10px; font-size:13px; font-weight:700; cursor:pointer; font-family:inherit; display:flex; align-items:center; justify-content:center; gap:7px; transition:opacity .18s,transform .18s; }
.btn-upload svg { width:15px; height:15px; }
.btn-upload:hover:not(:disabled) { opacity:.92; transform:translateY(-1px); }
.btn-upload:disabled { background:#9ca3af; cursor:not-allowed; }

/* Gallery toolbar */
.gallery-toolbar { display:flex; align-items:center; gap:10px; padding:14px 22px; border-bottom:1px solid #f5f6f8; flex-wrap:wrap; }
.search-wrap { position:relative; flex:1; min-width:160px; max-width:260px; }
.search-wrap svg { position:absolute; left:10px; top:50%; transform:translateY(-50%); color:#c4c8d0; pointer-events:none; width:14px; height:14px; }
.search-input { width:100%; padding:8px 10px 8px 32px; border:1.5px solid #e8eaed; border-radius:9px; font-size:12px; font-family:inherit; background:#fff; box-sizing:border-box; transition:border-color .15s; }
.search-input:focus { outline:none; border-color:#279685; }
.search-input::placeholder { color:#d1d5db; }
.filter-select { padding:8px 12px; border:1.5px solid #e8eaed; border-radius:9px; font-size:12px; font-family:inherit; background:#fff; color:#374151; cursor:pointer; max-width:200px; }
.filter-select:focus { outline:none; border-color:#279685; }
.count-badge { font-size:12px; font-weight:600; color:#9ca3af; margin-left:auto; white-space:nowrap; }
.count-badge span { color:#279685; font-weight:700; }

/* AR grid */
.ar-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(170px,1fr)); gap:14px; padding:18px 22px; }
.ar-card { background:#fff; border-radius:12px; border:1px solid #eff0f2; overflow:hidden; transition:transform .2s,box-shadow .2s; position:relative; }
.ar-card:hover { transform:translateY(-3px); box-shadow:0 10px 24px rgba(0,0,0,.08); }
.ar-card.is-new::before { content:'Baru'; position:absolute; top:8px; left:8px; background:#279685; color:#fff; font-size:9px; font-weight:700; padding:2px 7px; border-radius:99px; z-index:1; }
.ar-thumb { width:100%; aspect-ratio:4/3; background:#f5f6f8; overflow:hidden; display:flex; align-items:center; justify-content:center; position:relative; }
.ar-thumb img { width:100%; height:100%; object-fit:cover; transition:transform .3s; }
.ar-card:hover .ar-thumb img { transform:scale(1.05); }
.no-thumb { display:flex; flex-direction:column; align-items:center; gap:5px; color:#d1d5db; }
.no-thumb svg { width:32px; height:32px; }
.no-thumb span { font-size:10px; }
.ar-info { padding:10px 12px 8px; }
.ar-title { font-size:12px; font-weight:700; color:#111827; line-height:1.35; margin-bottom:4px; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
.ar-materi { font-size:10px; color:#9ca3af; display:flex; align-items:center; gap:4px; margin-bottom:8px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.ar-materi svg { width:11px; height:11px; flex-shrink:0; }
.ar-actions { display:flex; gap:5px; padding:0 12px 10px; }
.btn-view, .btn-del { display:flex; align-items:center; justify-content:center; gap:4px; padding:5px 8px; border-radius:7px; font-size:11px; font-weight:700; border:none; cursor:pointer; font-family:inherit; transition:background .15s; }
.btn-view svg, .btn-del svg { width:12px; height:12px; flex-shrink:0; }
.btn-view { flex:1; background:#f0fdf9; color:#0f6e56; }
.btn-view:hover { background:#dcfaf5; }
.btn-del  { background:#fef2f2; color:#dc2626; flex:none; width:30px; }
.btn-del:hover  { background:#fee2e2; }

/* Empty */
.ar-empty { grid-column:1/-1; text-align:center; padding:52px 20px; }
.empty-ico { width:56px; height:56px; border-radius:16px; background:#f5f6f8; display:flex; align-items:center; justify-content:center; margin:0 auto 14px; }
.empty-ico svg { width:28px; height:28px; color:#d1d5db; }
.empty-el { font-size:14px; font-weight:700; color:#374151; margin-bottom:5px; }
.empty-es { font-size:12px; color:#9ca3af; }

/* Skeleton */
.skeleton { background:linear-gradient(90deg,#f5f5f5 25%,#eee 50%,#f5f5f5 75%); background-size:200% 100%; animation:shimmer 1.4s infinite; }
@keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }

/* Modal */
.ar-modal { display:none; position:fixed; inset:0; background:rgba(0,0,0,.75); z-index:2000; align-items:center; justify-content:center; padding:20px; }
.ar-modal.open { display:flex; }
.ar-modal-box { background:#111; border-radius:16px; width:100%; max-width:560px; overflow:hidden; position:relative; animation:dlgUp .22s ease; }
@keyframes dlgUp { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:none} }
.ar-modal-top { display:flex; align-items:center; justify-content:space-between; padding:14px 18px; background:rgba(255,255,255,.06); border-bottom:1px solid rgba(255,255,255,.08); }
.ar-modal-top h4 { font-size:14px; font-weight:700; color:#fff; margin:0; }
.ar-modal-close { width:32px; height:32px; border-radius:8px; border:none; background:rgba(255,255,255,.12); color:#fff; cursor:pointer; display:flex; align-items:center; justify-content:center; }
.ar-modal-close:hover { background:rgba(255,255,255,.2); }
.ar-modal-close svg { width:14px; height:14px; }
.ar-modal-viewer { width:100%; height:360px; background:#1e293b; }

@media (max-width: 768px) {
    .ar-layout { flex-direction:column; }
    .ar-modal { padding:0; align-items:flex-end; }
    .ar-modal-box { border-radius:20px 20px 0 0; max-width:100%; }
    .ar-modal-viewer { height:260px; }
}
</style>

<div class="ar-layout">

    {{-- KOLOM KIRI: FORM UPLOAD --}}
    <div>
        <div class="card">
            <div class="card-header">
                <div class="ch-icon purple">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l9 4.9V17L12 22 3 17V6.9L12 2z"/><polyline points="3 7 12 12 21 7"/><line x1="12" y1="12" x2="12" y2="22"/></svg>
                </div>
                <div><h3>Upload Aset 3D Baru</h3><p>File .glb atau .gltf, maks. 100MB</p></div>
            </div>
            <div class="card-body">
                <div class="fg">
                    <label>Materi Terkait <span class="req">*</span></label>
                    <select id="selectMateri" class="fc"><option value="">— Memuat materi... —</option></select>
                </div>
                <div class="fg">
                    <label>Judul Aset 3D <span class="req">*</span></label>
                    <input type="text" id="titleAR" class="fc" placeholder="Contoh: Motherboard ATX 3D" maxlength="255">
                </div>
                <div class="fg">
                    <label>Deskripsi <span style="font-weight:400;color:#c4c8d0;font-size:10px">(opsional)</span></label>
                    <textarea id="descAR" class="fc" rows="2" placeholder="Penjelasan singkat aset ini..."></textarea>
                </div>
                <div class="fg">
                    <label>File 3D (.glb / .gltf) <span class="req">*</span></label>
                    <div class="dropzone" id="dropzone" onclick="document.getElementById('fileAR').click()"
                        ondragover="event.preventDefault();this.classList.add('drag')"
                        ondragleave="this.classList.remove('drag')"
                        ondrop="handleDrop(event)">
                        <input type="file" id="fileAR" accept=".glb,.gltf" onchange="onFileSelected(this.files[0])">
                        <div class="dz-ico">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                        </div>
                        <div class="dz-label">Klik atau seret file ke sini</div>
                        <div class="dz-hint">.glb / .gltf &bull; maks 100MB</div>
                        <div class="dz-file" id="fileLabel"></div>
                    </div>
                </div>

                <div class="fg" id="viewerWrap">
                    <label>Preview 3D Live</label>
                    <div class="model-wrap" id="modelWrap">
                        <model-viewer id="modelViewer" camera-controls auto-rotate shadow-intensity="1" exposure="1" environment-image="neutral" style="width:100%;height:100%"></model-viewer>
                    </div>
                    <div class="progress-bar" id="thumbProgress"><div class="progress-fill" id="thumbFill"></div></div>
                </div>

                <div class="fg" id="thumbWrap" style="display:none">
                    <label>Thumbnail (auto-generated)</label>
                    <div class="thumb-preview" id="thumbPreview"><span>Mengenerate thumbnail...</span></div>
                </div>

                <div class="fg">
                    <button class="btn-upload" id="btnUpload" type="button" onclick="submitUpload()" disabled>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                        <span id="uploadLabel">Upload Aset 3D</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- KOLOM KANAN: GALERI --}}
    <div class="card">
        <div class="card-header">
            <div class="ch-icon teal">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
            </div>
            <div><h3>Galeri Aset 3D</h3><p id="gallerySubtitle">Memuat aset...</p></div>
        </div>
        <div class="gallery-toolbar">
            <div class="search-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                <input type="text" class="search-input" id="searchInput" placeholder="Cari judul aset..." oninput="applyFilter()">
            </div>
            <select class="filter-select" id="filterMateri" onchange="applyFilter()">
                <option value="">Semua Materi</option>
            </select>
            <span class="count-badge" id="countBadge">Memuat...</span>
        </div>
        <div class="ar-grid" id="arGrid">
            @for($i=0;$i<6;$i++)
            <div class="ar-card">
                <div class="ar-thumb skeleton" style="aspect-ratio:4/3"></div>
                <div style="padding:10px 12px 10px">
                    <div class="skeleton" style="height:12px;width:80%;border-radius:5px;margin-bottom:5px"></div>
                    <div class="skeleton" style="height:10px;width:55%;border-radius:5px"></div>
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
            <button class="ar-modal-close" onclick="closeModal()">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <model-viewer id="modalViewer" class="ar-modal-viewer" camera-controls auto-rotate shadow-intensity="1" exposure="1" environment-image="neutral"></model-viewer>
    </div>
</div>
@endsection

@push('scripts')
<script type="module" src="https://ajax.googleapis.com/ajax/libs/model-viewer/3.5.0/model-viewer.min.js"></script>
<script>
(function(){
    const API=window.apiBaseUrl, token=window.token, role=window.role||'dosen';
    const isAdmin=role==='admin'||role==='superadmin';
    const $=id=>document.getElementById(id);

    let allAR=[], materiMap={}, selectedFile=null, thumbBlob=null, newlyUploaded=new Set();
    let dosenMaterialIds=new Set();

    function esc(s){return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}

    function waitMV(){return new Promise((resolve,reject)=>{let t=0;const c=()=>{if(customElements.get('model-viewer'))return resolve();if(++t>60)return reject(new Error('timeout'));setTimeout(c,100);};c();});}

    async function loadMateriDropdown(){
        const sel=$('selectMateri');
        sel.innerHTML='<option value="">— Memuat... —</option>';
        try {
            const rc=await fetch(API+'/courses',{headers:{Authorization:'Bearer '+token,Accept:'application/json'}});
            const courses=(await rc.json()).data||[];
            if(!courses.length){sel.innerHTML='<option value="">— Tidak ada mata kuliah —</option>';return;}
            const allMaterials=[];
            await Promise.all(courses.map(async c=>{
                const cid=c._id||c.id;
                try {
                    const rm=await fetch(`${API}/courses/${cid}/materials`,{headers:{Authorization:'Bearer '+token,Accept:'application/json'}});
                    const mj=await rm.json();
                    const list=Array.isArray(mj)?mj:(mj.data||[]);
                    list.forEach(m=>{const mid=m._id||m.id;materiMap[mid]=m.title;allMaterials.push({id:mid,title:m.title,courseTitle:c.title});});
                } catch(_){}
            }));
            if(!allMaterials.length){sel.innerHTML='<option value="">— Belum ada materi —</option>';return;}
            dosenMaterialIds=new Set(allMaterials.map(m=>String(m.id)));
            sel.innerHTML='<option value="">— Pilih Materi —</option>';
            allMaterials.forEach(m=>{const o=document.createElement('option');o.value=m.id;o.textContent=m.title+(isAdmin?` (${m.courseTitle})`:'');sel.appendChild(o);});
            const fs=$('filterMateri');fs.innerHTML='<option value="">Semua Materi</option>';
            allMaterials.forEach(m=>{const o=document.createElement('option');o.value=m.id;o.textContent=m.title;fs.appendChild(o);});
        } catch(e){sel.innerHTML='<option value="">— Gagal memuat, coba refresh —</option>';console.error('[AR]',e.message);}
    }

    async function fetchGaleriAR(){
        try {
            const res=await fetch(API+'/ar-assets',{headers:{Authorization:'Bearer '+token,Accept:'application/json'}});
            if(res.status===401){window.logout();return;}
            const rawAR=(await res.json()).data||[];
            allAR=isAdmin?rawAR:rawAR.filter(ar=>dosenMaterialIds.has(String(ar.material_id)));
            $('gallerySubtitle').textContent=`${allAR.length} aset di ${isAdmin?'semua materi':'materi kamu'}`;
            applyFilter();
        } catch(e){
            $('arGrid').innerHTML=`<div class="ar-empty"><div class="empty-ico"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg></div><div class="empty-el">Gagal memuat galeri</div><div class="empty-es">${esc(e.message)}</div><button onclick="fetchGaleriAR()" style="margin-top:12px;padding:7px 16px;background:#279685;color:#fff;border:none;border-radius:8px;cursor:pointer;font-weight:700;font-size:12px">Coba lagi</button></div>`;
        }
    }

    window.applyFilter=function(){
        const q=$('searchInput').value.trim().toLowerCase();
        const mid=$('filterMateri').value;
        const filtered=allAR.filter(ar=>{
            const mq=!q||(ar.title||'').toLowerCase().includes(q);
            const mm=!mid||String(ar.material_id)===String(mid);
            return mq&&mm;
        });
        $('countBadge').innerHTML=`<span>${filtered.length}</span> / ${allAR.length} aset`;
        renderGrid(filtered);
    };

    const SVG_CUBE  =`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l9 4.9V17L12 22 3 17V6.9L12 2z"/><polyline points="3 7 12 12 21 7"/><line x1="12" y1="12" x2="12" y2="22"/></svg>`;
    const SVG_BOOK  =`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>`;
    const SVG_EYE   =`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>`;
    const SVG_TRASH =`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>`;
    const SVG_INBOX =`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>`;

    function renderGrid(list){
        const grid=$('arGrid');
        if(!list||!list.length){
            grid.innerHTML=`<div class="ar-empty"><div class="empty-ico">${SVG_INBOX}</div><div class="empty-el">Belum ada aset 3D</div><div class="empty-es">${$('searchInput').value||$('filterMateri').value?'Coba ubah filter.':'Upload aset pertamamu di form kiri!'}</div></div>`;
            return;
        }
        grid.innerHTML=list.map(buildCard).join('');
    }

    function buildCard(ar){
        const id=ar._id||ar.id,title=ar.title||'Tanpa Judul';
        const matTitle=ar.material?.title||(ar.material_id&&materiMap[ar.material_id])||'—';
        const thumbUrl=ar.image_url||'',modelUrl=ar.model_3d_url||'';
        const isNew=newlyUploaded.has(String(id));
        const thumbHtml=thumbUrl?`<img src="${esc(thumbUrl)}" alt="${esc(title)}" loading="lazy">`:`<div class="no-thumb">${SVG_CUBE}<span>No thumb</span></div>`;
        return`<div class="ar-card${isNew?' is-new':''}" id="arcard-${id}">
            <div class="ar-thumb">${thumbHtml}</div>
            <div class="ar-info">
                <div class="ar-title">${esc(title)}</div>
                <div class="ar-materi">${SVG_BOOK} ${esc(matTitle)}</div>
            </div>
            <div class="ar-actions">
                ${modelUrl?`<button class="btn-view" onclick="previewAR('${esc(modelUrl)}','${esc(title)}')">${SVG_EYE} Preview</button>`:`<button class="btn-view" disabled style="opacity:.4;cursor:default">${SVG_EYE} Preview</button>`}
                <button class="btn-del" title="Hapus" onclick="hapusAR('${id}','${esc(title).replace(/'/g,"\\'")}')">
                    ${SVG_TRASH}
                </button>
            </div>
        </div>`;
    }

    window.handleDrop=function(e){e.preventDefault();$('dropzone').classList.remove('drag');const f=e.dataTransfer.files[0];if(f)onFileSelected(f);};

    window.onFileSelected=async function(file){
        if(!file)return;
        const ext=file.name.split('.').pop().toLowerCase();
        if(!['glb','gltf'].includes(ext)){toast('Hanya file .glb atau .gltf yang didukung.','err');return;}
        if(file.size>100*1024*1024){toast('File terlalu besar. Maks 100MB.','err');return;}
        selectedFile=file;thumbBlob=null;
        const fl=$('fileLabel');
        fl.innerHTML=`<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg> ${esc(file.name)} (${(file.size/1024/1024).toFixed(1)} MB)`;
        $('btnUpload').disabled=true;
        $('thumbWrap').style.display='block';
        $('thumbPreview').className='thumb-preview generating';
        $('thumbPreview').innerHTML='<span>Mengenerate thumbnail...</span>';
        $('thumbProgress').classList.add('show');
        try {
            await waitMV();
            const blob=URL.createObjectURL(file),mv=$('modelViewer');
            $('modelWrap').classList.add('show');mv.src=blob;
            await new Promise((resolve,reject)=>{
                let done=false;
                const onLoad=async()=>{if(done)return;done=true;
                    try{
                        await new Promise(r=>setTimeout(r,800));
                        const dataUrl=await mv.toBlob({mimeType:'image/png',qualityArgument:.92,idealAspect:true});
                        thumbBlob=dataUrl;
                        const url=URL.createObjectURL(dataUrl);
                        $('thumbPreview').className='thumb-preview';
                        $('thumbPreview').innerHTML=`<img src="${url}" alt="thumbnail">`;
                        $('thumbProgress').classList.remove('show');
                        $('btnUpload').disabled=false;resolve();
                    } catch(err){
                        $('thumbPreview').className='thumb-preview';
                        $('thumbPreview').innerHTML='<span>Thumbnail tidak tersedia, upload tetap bisa dilanjutkan</span>';
                        $('thumbProgress').classList.remove('show');$('btnUpload').disabled=false;resolve();
                    }
                };
                const onErr=e=>{if(!done){done=true;reject(e);}};
                mv.addEventListener('load',onLoad,{once:true});
                mv.addEventListener('error',onErr,{once:true});
                setTimeout(()=>{if(!done){done=true;resolve();}},10000);
            });
        } catch(e){$('thumbProgress').classList.remove('show');$('thumbPreview').innerHTML='<span>Preview tidak tersedia</span>';$('btnUpload').disabled=false;}
    };

    window.submitUpload=async function(){
        const materialId=$('selectMateri').value,title=$('titleAR').value.trim(),desc=$('descAR').value.trim();
        if(!materialId){toast('Pilih materi terlebih dahulu.','err');$('selectMateri').classList.add('err');return;}
        if(!title){toast('Judul aset wajib diisi.','err');$('titleAR').classList.add('err');return;}
        if(!selectedFile){toast('Pilih file 3D terlebih dahulu.','err');return;}
        $('selectMateri').classList.remove('err');$('titleAR').classList.remove('err');
        const btn=$('btnUpload'),lbl=$('uploadLabel');
        btn.disabled=true;lbl.textContent='Mengupload...';$('thumbProgress').classList.add('show');
        const fd=new FormData();
        fd.append('title',title);fd.append('description',desc||'');fd.append('model_3d',selectedFile);
        if(thumbBlob)fd.append('thumbnail',thumbBlob,'thumbnail.png');
        try {
            const res=await fetch(`${API}/materials/${materialId}/ar-assets`,{method:'POST',headers:{Authorization:'Bearer '+token,Accept:'application/json'},body:fd});
            const data=await res.json();
            if(res.ok){
                const newAR=data.data,newId=newAR._id||newAR.id;
                if(!newAR.material&&materiMap[materialId])newAR.material={title:materiMap[materialId]};
                if(!newAR.material_id)newAR.material_id=materialId;
                allAR.unshift(newAR);newlyUploaded.add(String(newId));
                $('gallerySubtitle').textContent=`${allAR.length} aset di ${isAdmin?'semua materi':'materi kamu'}`;
                applyFilter();resetForm();toast('Aset 3D berhasil diupload!');
                setTimeout(()=>{newlyUploaded.delete(String(newId));const c=document.getElementById(`arcard-${newId}`);if(c)c.classList.remove('is-new');},10000);
            } else {
                let msg=data.message||'Gagal upload.';if(data.errors)msg=Object.values(data.errors).flat()[0]||msg;
                toast(msg,'err');btn.disabled=false;lbl.textContent='Upload Aset 3D';
            }
        } catch(e){toast('Koneksi bermasalah: '+e.message,'err');btn.disabled=false;lbl.textContent='Upload Aset 3D';}
        finally{$('thumbProgress').classList.remove('show');}
    };

    function resetForm(){
        $('selectMateri').value='';$('titleAR').value='';$('descAR').value='';
        $('fileAR').value='';$('fileLabel').innerHTML='';
        $('modelWrap').classList.remove('show');$('modelViewer').src='';
        $('thumbWrap').style.display='none';$('thumbPreview').className='thumb-preview';$('thumbPreview').innerHTML='';
        $('btnUpload').disabled=true;$('uploadLabel').textContent='Upload Aset 3D';
        selectedFile=null;thumbBlob=null;
    }

    window.hapusAR=function(id,title){
        showDialog({
            icon:'err', title:'Hapus Aset 3D',
            msg:`Hapus "${title}"? File 3D akan terhapus permanen dari server.`,
            confirmText:'Hapus', confirmClass:'confirm-err',
            onConfirm:()=>doHapusAR(id)
        });
    };

    window.doHapusAR=async function(id){
        const card=document.getElementById(`arcard-${id}`);
        if(card){card.style.transition='opacity .25s,transform .25s';card.style.opacity='0';card.style.transform='scale(.93)';}
        try {
            const res=await fetch(`${API}/ar-assets/${id}`,{method:'DELETE',headers:{Authorization:'Bearer '+token,Accept:'application/json'}});
            if(res.ok){
                allAR=allAR.filter(ar=>(ar._id||ar.id)!=id);
                applyFilter();$('gallerySubtitle').textContent=`${allAR.length} aset di ${isAdmin?'semua materi':'materi kamu'}`;
                toast('Aset 3D berhasil dihapus.');
            } else {
                if(card){card.style.opacity='1';card.style.transform='';}
                const err=await res.json().catch(()=>({}));toast(err.message||'Gagal menghapus.','err');
            }
        } catch(_){if(card){card.style.opacity='1';card.style.transform='';}toast('Koneksi bermasalah.','err');}
    };

    window.previewAR=function(url,title){
        $('modalViewer').src='';
        $('modalTitle').textContent=title;
        $('arModal').classList.add('open');
        // Tunggu modal visible sebelum set src, agar WebGL context bisa dibuat
        requestAnimationFrame(()=>requestAnimationFrame(()=>{ $('modalViewer').src=url; }));
    };
    window.closeModal=function(){$('arModal').classList.remove('open');$('modalViewer').src='';};
    $('arModal').addEventListener('click',e=>{if(e.target===$('arModal'))closeModal();});

    async function init(){await loadMateriDropdown();await fetchGaleriAR();}
    init();
    window.fetchGaleriAR=fetchGaleriAR;
})();
</script>
@endpush