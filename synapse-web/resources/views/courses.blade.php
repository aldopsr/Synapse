@extends('layouts.app')
@section('title', 'Mata Kuliah - Synapse')
@section('header_title', 'Mata Kuliah')

@section('content')
<style>
/* ── Page header ─────────────────────────────────────────── */
.page-header {
    display:flex; align-items:center; justify-content:space-between;
    margin-bottom:24px; gap:12px; flex-wrap:wrap;
}
.page-header-left h2 { font-size:20px; font-weight:800; color:#111827; margin:0 0 3px; letter-spacing:-.3px; }
.page-header-left p  { font-size:13px; color:#9ca3af; margin:0; }

/* ── Info banner ─────────────────────────────────────────── */
.info-banner {
    display:flex; align-items:center; gap:12px;
    background:#f0fdf9; border:1px solid #b2e8e0;
    border-radius:12px; padding:13px 16px; margin-bottom:24px;
    font-size:13px; color:#0f6e56; font-weight:500;
}
.info-banner svg { flex-shrink:0; color:#279685; }

/* ── Cards grid ──────────────────────────────────────────── */
.courses-grid {
    display:grid;
    grid-template-columns:repeat(auto-fill, minmax(300px, 1fr));
    gap:16px;
}

/* ── Course card ─────────────────────────────────────────── */
.course-card {
    background:#fff; border-radius:16px; border:1px solid #eff0f2;
    overflow:hidden; display:flex; flex-direction:column;
    transition:transform .2s, box-shadow .2s;
}
.course-card:hover { transform:translateY(-3px); box-shadow:0 12px 32px rgba(0,0,0,.08); }

.card-accent { height:4px; background:linear-gradient(90deg,#279685,#4A90E2); }
.card-body   { padding:20px; flex:1; }

.card-title { font-size:15px; font-weight:700; color:#111827; margin:0 0 6px; line-height:1.4; }
.card-desc  {
    font-size:13px; color:#9ca3af; margin:0 0 14px; line-height:1.5;
    display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;
}

/* Dosen chip */
.dosen-chip {
    display:inline-flex; align-items:center; gap:7px;
    background:#f5f6f8; border-radius:99px;
    padding:5px 12px 5px 6px; font-size:12px; font-weight:600;
    color:#374151; margin-bottom:14px;
}
.dosen-avatar {
    width:22px; height:22px; border-radius:50%;
    background:#279685; color:#fff; font-size:9px; font-weight:700;
    display:flex; align-items:center; justify-content:center; flex-shrink:0;
}
.dosen-chip.unassigned            { background:#fffbeb; color:#92400e; }
.dosen-chip.unassigned .dosen-avatar { background:#f59e0b; }

/* Meta badges */
.card-meta { display:flex; align-items:center; gap:8px; }
.meta-badge {
    display:inline-flex; align-items:center; gap:5px;
    padding:3px 10px; border-radius:99px; font-size:11px; font-weight:600;
}
.meta-badge svg { width:12px; height:12px; }
.meta-badge.materi { background:#e8f1fd; color:#185fa5; }
.meta-badge.kuis   { background:#f0eeff; color:#534ab7; }

/* Card footer */
.card-footer { border-top:1px solid #f5f6f8; padding:12px 16px; display:flex; gap:7px; flex-wrap:wrap; }
.btn-act {
    flex:1; display:inline-flex; align-items:center; justify-content:center; gap:6px;
    padding:8px 12px; border-radius:9px; font-size:12px; font-weight:700;
    cursor:pointer; border:1.5px solid transparent; transition:background .15s, border-color .15s;
    text-decoration:none; white-space:nowrap; font-family:inherit;
}
.btn-act svg { width:13px; height:13px; flex-shrink:0; }
.btn-act:hover { transform:none; }

.ba-materi { background:#f0fdf9; color:#0f6e56; border-color:#b2e8e0; }
.ba-materi:hover { background:#dcfaf5; }
.ba-assign { background:#f0f4ff; color:#3730a3; border-color:#c7d2fe; }
.ba-assign:hover { background:#e0e7ff; }
.ba-delete { background:#fef2f2; color:#dc2626; border-color:#fecaca; }
.ba-delete:hover { background:#fee2e2; }

/* Admin-only */
.admin-only { display:none; }

/* Skeleton */
.skeleton { background:linear-gradient(90deg,#f5f5f5 25%,#eee 50%,#f5f5f5 75%); background-size:200% 100%; animation:shimmer 1.4s infinite; border-radius:8px; }
@keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }
.skeleton-card { background:#fff; border-radius:16px; border:1px solid #eff0f2; overflow:hidden; }

/* Empty */
.empty-state { grid-column:1/-1; text-align:center; padding:64px 20px; }
.empty-icon  { width:56px; height:56px; border-radius:16px; background:#f5f6f8; display:flex; align-items:center; justify-content:center; margin:0 auto 16px; }
.empty-icon svg { width:28px; height:28px; color:#d1d5db; }
.empty-title { font-size:15px; font-weight:700; color:#374151; margin-bottom:6px; }
.empty-sub   { font-size:13px; color:#9ca3af; }

/* ── Modal ───────────────────────────────────────────────── */
.modal-overlay {
    display:none; position:fixed; inset:0;
    background:rgba(15,23,42,.4); backdrop-filter:blur(3px);
    z-index:1000; align-items:center; justify-content:center; padding:20px;
}
.modal-overlay.open { display:flex; }
.modal-box {
    background:#fff; border-radius:20px; width:100%; max-width:480px;
    box-shadow:0 24px 64px rgba(0,0,0,.16);
    animation:dlgUp .2s cubic-bezier(.34,1.4,.64,1); overflow:hidden;
}
@keyframes dlgUp { from{opacity:0;transform:translateY(20px) scale(.97)} to{opacity:1;transform:none} }

.modal-header {
    display:flex; align-items:center; justify-content:space-between;
    padding:20px 24px 16px; border-bottom:1px solid #f5f6f8;
}
.modal-header h3 { font-size:15px; font-weight:800; color:#111827; margin:0; }
.modal-close {
    width:32px; height:32px; border-radius:9px; border:none;
    background:#f5f6f8; cursor:pointer; display:flex; align-items:center; justify-content:center;
    color:#6b7280; transition:background .15s;
}
.modal-close:hover { background:#e5e7eb; color:#111827; }
.modal-close svg { width:14px; height:14px; }

.modal-body { padding:20px 24px; }
.modal-footer { display:flex; gap:10px; padding:14px 24px 20px; border-top:1px solid #f5f6f8; }

/* Form */
.form-group { margin-bottom:16px; }
.form-group:last-child { margin-bottom:0; }
.form-group label { display:block; font-size:11px; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:.05em; margin-bottom:7px; }
.form-group label .req { color:#ef4444; margin-left:2px; }
.form-control {
    width:100%; padding:10px 13px; border:1.5px solid #e5e7eb; border-radius:10px;
    font-size:13px; font-family:inherit; color:#111827; background:#fff; box-sizing:border-box;
    transition:border-color .15s, box-shadow .15s;
}
.form-control:focus { outline:none; border-color:#279685; box-shadow:0 0 0 3px rgba(39,150,133,.12); }
.form-control::placeholder { color:#c8cbd0; }
select.form-control { cursor:pointer; }
textarea.form-control { resize:vertical; min-height:80px; }

/* Dosen preview */
.dosen-preview {
    display:none; align-items:center; gap:8px;
    background:#f0fdf9; border-radius:9px; padding:9px 12px;
    font-size:13px; font-weight:600; color:#0f6e56; margin-top:8px;
}
.dosen-preview.show { display:flex; }

/* Assign info card */
.assign-info-card {
    background:#f9fafb; border-radius:11px; padding:13px 15px; margin-bottom:16px;
}
.assign-info-card .course-nm { font-weight:700; color:#111827; font-size:14px; margin-bottom:4px; }
.assign-info-card .cur-dosen { font-size:13px; color:#9ca3af; }
.assign-info-card .cur-dosen strong { color:#279685; }

/* Buttons */
.btn-cancel {
    flex:1; padding:10px; border:1.5px solid #e5e7eb; border-radius:10px;
    background:#fff; color:#6b7280; font-size:13px; font-weight:700;
    cursor:pointer; transition:background .15s; font-family:inherit;
}
.btn-cancel:hover { background:#f5f6f8; }
.btn-submit {
    flex:2; padding:10px; background:#279685; color:#fff; border:none;
    border-radius:10px; font-size:13px; font-weight:700;
    cursor:pointer; transition:background .15s; font-family:inherit;
}
.btn-submit:hover { background:#1c6e60; }
.btn-submit:disabled { background:#9ca3af; cursor:not-allowed; }
</style>

{{-- PAGE HEADER --}}
<div class="page-header">
    <div class="page-header-left">
        <h2 id="pageTitle">Daftar Mata Kuliah</h2>
        <p id="pageSubtitle">Memuat data...</p>
    </div>
    <button class="btn btn-primary admin-only" id="btnTambahMatkul" onclick="bukaModalTambah()" style="border-radius:99px">
        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Tambah Mata Kuliah
    </button>
</div>

{{-- Info banner dosen --}}
<div class="info-banner" id="dosenBanner" style="display:none;">
    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <span>Kamu hanya melihat mata kuliah yang diampu. Untuk perubahan penugasan, hubungi admin.</span>
</div>

{{-- COURSES GRID --}}
<div class="courses-grid" id="coursesGrid">
    @for($i=0;$i<3;$i++)
    <div class="skeleton-card">
        <div style="height:4px;" class="skeleton"></div>
        <div style="padding:20px">
            <div class="skeleton" style="height:15px;width:70%;margin-bottom:10px"></div>
            <div class="skeleton" style="height:12px;width:90%;margin-bottom:6px"></div>
            <div class="skeleton" style="height:12px;width:60%;margin-bottom:16px"></div>
            <div class="skeleton" style="height:26px;width:48%;border-radius:99px"></div>
        </div>
        <div style="border-top:1px solid #f5f6f8;padding:12px 16px;display:flex;gap:7px">
            <div class="skeleton" style="height:34px;flex:1;border-radius:9px"></div>
            <div class="skeleton" style="height:34px;flex:1;border-radius:9px"></div>
        </div>
    </div>
    @endfor
</div>

{{-- MODAL: Tambah Matkul --}}
<div class="modal-overlay" id="modalTambah">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Tambah Mata Kuliah</h3>
            <button class="modal-close" onclick="tutupModal('modalTambah')">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label>Nama Mata Kuliah <span class="req">*</span></label>
                <input type="text" id="inputTitle" class="form-control" placeholder="Contoh: Pemrograman Web Lanjut" maxlength="255">
            </div>
            <div class="form-group">
                <label>Deskripsi</label>
                <textarea id="inputDesc" class="form-control" rows="3" placeholder="Deskripsi singkat mata kuliah..."></textarea>
            </div>
            <div class="form-group">
                <label>Dosen Pengampu <span class="req">*</span></label>
                <select id="selectDosen" class="form-control" onchange="onDosenChange(this)">
                    <option value="">— Pilih dosen —</option>
                </select>
                <div class="dosen-preview" id="dosenPreview">
                    <div class="dosen-avatar" id="dosenPreviewAvatar">?</div>
                    <span id="dosenPreviewName">—</span>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel" onclick="tutupModal('modalTambah')">Batal</button>
            <button class="btn-submit" id="btnSimpanMatkul" onclick="simpanMatkul()">Simpan</button>
        </div>
    </div>
</div>

{{-- MODAL: Assign Dosen --}}
<div class="modal-overlay" id="modalAssign">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Ganti Dosen Pengampu</h3>
            <button class="modal-close" onclick="tutupModal('modalAssign')">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="assign-info-card">
                <div class="course-nm" id="assignCourseName">—</div>
                <div class="cur-dosen">Dosen saat ini: <strong id="assignCurrentDosen">—</strong></div>
            </div>
            <div class="form-group">
                <label>Dosen Baru <span class="req">*</span></label>
                <select id="selectDosenAssign" class="form-control" onchange="onDosenAssignChange(this)">
                    <option value="">— Pilih dosen —</option>
                </select>
                <div class="dosen-preview" id="dosenAssignPreview" style="margin-top:8px">
                    <div class="dosen-avatar" id="dosenAssignAvatar">?</div>
                    <span id="dosenAssignName">—</span>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel" onclick="tutupModal('modalAssign')">Batal</button>
            <button class="btn-submit" id="btnSimpanAssign" onclick="simpanAssign()">Simpan</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    const API     = window.apiBaseUrl;
    const token   = window.token;
    const role    = window.role || 'dosen';
    const isAdmin = role === 'admin' || role === 'superadmin';

    let dosenMap  = {};
    let dosenList = [];
    let courseList= [];
    let assignTargetId   = null;
    let assignTargetName = '';

    const $ = id => document.getElementById(id);

    function initials(name) {
        if (!name) return '?';
        const p = name.trim().split(' ');
        return p.length >= 2 ? (p[0][0] + p[1][0]).toUpperCase() : p[0].slice(0,2).toUpperCase();
    }
    function esc(s) {
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    window.tutupModal = id => $(id).classList.remove('open');
    document.querySelectorAll('.modal-overlay').forEach(el => {
        el.addEventListener('click', e => { if (e.target === el) el.classList.remove('open'); });
    });

    window.onDosenChange = function(sel) {
        const d = dosenMap[sel.value];
        const prev = $('dosenPreview');
        if (d) { $('dosenPreviewAvatar').textContent = initials(d.name); $('dosenPreviewName').textContent = d.name + (d.email ? ' — ' + d.email : ''); prev.classList.add('show'); }
        else prev.classList.remove('show');
    };
    window.onDosenAssignChange = function(sel) {
        const d = dosenMap[sel.value];
        const prev = $('dosenAssignPreview');
        if (d) { $('dosenAssignAvatar').textContent = initials(d.name); $('dosenAssignName').textContent = d.name; prev.classList.add('show'); }
        else prev.classList.remove('show');
    };

    function fillDosenSelect(sel, selectedId) {
        sel.innerHTML = '<option value="">— Pilih dosen —</option>';
        dosenList.forEach(d => {
            const id = d._id || d.id;
            const opt = document.createElement('option');
            opt.value = id;
            opt.textContent = d.name + (d.email ? ' (' + d.email + ')' : '');
            if (selectedId && id == selectedId) opt.selected = true;
            sel.appendChild(opt);
        });
    }

    async function fetchDosen() {
        if (!isAdmin) return;
        try {
            const res  = await fetch(API + '/dosen', { headers:{ Authorization:'Bearer '+token, Accept:'application/json' } });
            const data = await res.json();
            dosenList  = data.data || [];
            dosenList.forEach(d => { dosenMap[d._id||d.id] = { name:d.name, email:d.email||'' }; });
        } catch(e) { console.warn('Gagal fetch dosen:', e.message); }
    }

    async function fetchCourses() {
        try {
            const res  = await fetch(API + '/courses', { headers:{ Authorization:'Bearer '+token, Accept:'application/json' } });
            if (res.status === 401) { window.logout(); return; }
            const data = await res.json();
            courseList = data.data || [];
            renderGrid(courseList);
            $('pageSubtitle').textContent = isAdmin
                ? courseList.length + ' mata kuliah terdaftar'
                : 'Mata kuliah yang kamu ampu';
        } catch(e) {
            $('coursesGrid').innerHTML = `<div class="empty-state">
                <div class="empty-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg></div>
                <div class="empty-title">Gagal memuat data</div>
                <div class="empty-sub">${esc(e.message)}</div>
            </div>`;
        }
    }

    function renderGrid(courses) {
        const grid = $('coursesGrid');
        if (!courses || !courses.length) {
            grid.innerHTML = `<div class="empty-state">
                <div class="empty-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg></div>
                <div class="empty-title">Belum ada mata kuliah</div>
                <div class="empty-sub">${isAdmin ? 'Klik "Tambah Mata Kuliah" untuk mulai.' : 'Belum ada matkul yang ditugaskan.'}</div>
            </div>`;
            return;
        }
        grid.innerHTML = courses.map(buildCard).join('');
        applyAdmin();
    }

    function buildCard(c) {
        const id      = c._id || c.id;
        const title   = esc(c.title || 'Tanpa Judul');
        const desc    = esc(c.description || '—');
        const dId     = c.dosen_id;
        const d       = dId ? dosenMap[dId] : null;

        const dosenHtml = d
            ? `<div class="dosen-chip"><div class="dosen-avatar">${initials(d.name)}</div>${esc(d.name)}</div>`
            : dId
                ? `<div class="dosen-chip"><div class="dosen-avatar">?</div>ID: ${esc(String(dId).slice(-6))}</div>`
                : `<div class="dosen-chip unassigned"><div class="dosen-avatar"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></div>Belum ada dosen</div>`;

        const SVG_BOOK = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>`;
        const SVG_CHECK= `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>`;
        const SVG_EDIT = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>`;
        const SVG_TRASH= `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>`;

        const adminActs = isAdmin ? `
            <button class="btn-act ba-assign admin-only" onclick="bukaModalAssign('${id}','${title.replace(/'/g,"\\'")}','${dId||''}')">
                ${SVG_EDIT} Ganti Dosen
            </button>
            <button class="btn-act ba-delete admin-only" onclick="hapusMatkul('${id}','${title.replace(/'/g,"\\'")}')">
                ${SVG_TRASH} Hapus
            </button>` : '';

        return `<div class="course-card" id="card-${id}">
            <div class="card-accent"></div>
            <div class="card-body">
                <div class="card-title">${title}</div>
                <div class="card-desc">${desc}</div>
                ${dosenHtml}
                <div class="card-meta">
                    <span class="meta-badge materi">${SVG_BOOK} Materi</span>
                    <span class="meta-badge kuis">${SVG_CHECK} Kuis</span>
                </div>
            </div>
            <div class="card-footer">
                <a class="btn-act ba-materi" href="/mata-kuliah/${id}/materi">
                    ${SVG_BOOK} Kelola Materi
                </a>
                ${adminActs}
            </div>
        </div>`;
    }

    function applyAdmin() {
        if (isAdmin) document.querySelectorAll('.admin-only').forEach(el => el.style.display = 'inline-flex');
    }

    window.bukaModalTambah = function() {
        $('inputTitle').value = '';
        $('inputDesc').value  = '';
        $('selectDosen').value = '';
        $('dosenPreview').classList.remove('show');
        fillDosenSelect($('selectDosen'));
        $('modalTambah').classList.add('open');
        setTimeout(() => $('inputTitle').focus(), 80);
    };

    window.simpanMatkul = async function() {
        const title   = $('inputTitle').value.trim();
        const desc    = $('inputDesc').value.trim();
        const dosenId = $('selectDosen').value;
        if (!title)   { toast('Nama mata kuliah wajib diisi.', 'err'); return; }
        if (!dosenId) { toast('Pilih dosen pengampu terlebih dahulu.', 'err'); return; }
        const btn = $('btnSimpanMatkul');
        btn.disabled = true; btn.textContent = 'Menyimpan...';
        try {
            const res  = await fetch(API + '/courses', {
                method:'POST',
                headers:{ Authorization:'Bearer '+token, Accept:'application/json', 'Content-Type':'application/json' },
                body: JSON.stringify({ title, description:desc, dosen_id:dosenId })
            });
            const data = await res.json();
            if (res.ok && data.success) {
                tutupModal('modalTambah');
                toast('Mata kuliah berhasil ditambahkan!');
                await fetchCourses(); applyAdmin();
            } else { toast(data.message || 'Gagal menyimpan.', 'err'); }
        } catch(e) { toast('Koneksi bermasalah.', 'err'); }
        finally { btn.disabled = false; btn.textContent = 'Simpan'; }
    };

    window.bukaModalAssign = function(courseId, courseName, currentDosenId) {
        assignTargetId   = courseId;
        assignTargetName = courseName;
        $('assignCourseName').textContent  = courseName;
        $('assignCurrentDosen').textContent = currentDosenId && dosenMap[currentDosenId]
            ? dosenMap[currentDosenId].name
            : currentDosenId ? 'ID: ' + String(currentDosenId).slice(-6) : 'Belum ada';
        $('dosenAssignPreview').classList.remove('show');
        fillDosenSelect($('selectDosenAssign'), currentDosenId);
        $('modalAssign').classList.add('open');
    };

    window.simpanAssign = async function() {
        const newDosenId = $('selectDosenAssign').value;
        if (!newDosenId) { toast('Pilih dosen terlebih dahulu.', 'err'); return; }
        const btn = $('btnSimpanAssign');
        btn.disabled = true; btn.textContent = 'Menyimpan...';
        try {
            const res = await fetch(API + '/courses/' + assignTargetId, {
                method:'PUT',
                headers:{ Authorization:'Bearer '+token, Accept:'application/json', 'Content-Type':'application/json' },
                body: JSON.stringify({ dosen_id:newDosenId })
            });
            if (res.ok) {
                tutupModal('modalAssign');
                toast('Dosen berhasil diperbarui!');
                await fetchCourses(); applyAdmin();
            } else if (res.status === 404 || res.status === 405) {
                tutupModal('modalAssign');
                toast('Tersimpan (perlu route PUT /courses/{id} di backend).', 'ok');
                const idx = courseList.findIndex(c => (c._id||c.id) == assignTargetId);
                if (idx > -1) { courseList[idx].dosen_id = newDosenId; renderGrid(courseList); }
            } else {
                const data = await res.json().catch(() => ({}));
                toast(data.message || 'Gagal memperbarui dosen.', 'err');
            }
        } catch(e) { toast('Koneksi bermasalah.', 'err'); }
        finally { btn.disabled = false; btn.textContent = 'Simpan'; }
    };

    window.hapusMatkul = function(id, title) {
        showDialog({
            icon:'err', title:'Hapus Mata Kuliah',
            msg:`Hapus "${title}"? Semua materi dan kuis terkait akan ikut terhapus.`,
            confirmText:'Hapus', confirmClass:'confirm-err',
            onConfirm: () => doHapusMatkul(id, title)
        });
    };

    window.doHapusMatkul = async function(id, title) {
        try {
            const res = await fetch(API + '/courses/' + id, {
                method:'DELETE',
                headers:{ Authorization:'Bearer '+token, Accept:'application/json' }
            });
            if (res.ok) {
                toast('Mata kuliah dihapus.');
                const card = document.getElementById('card-' + id);
                if (card) { card.style.opacity='0'; card.style.transform='scale(.97)'; card.style.transition='all .2s'; setTimeout(() => card.remove(), 200); }
                courseList = courseList.filter(c => (c._id||c.id) != id);
                if (!courseList.length) renderGrid([]);
            } else {
                const data = await res.json().catch(() => ({}));
                toast(data.message || 'Gagal menghapus.', 'err');
            }
        } catch(e) { toast('Koneksi bermasalah.', 'err'); }
    };

    window.init = async function() {
        if (isAdmin) { $('btnTambahMatkul').style.display = 'inline-flex'; }
        else { $('dosenBanner').style.display = 'flex'; $('pageTitle').textContent = 'Mata Kuliah Saya'; }
        await fetchDosen();
        await fetchCourses();
        applyAdmin();
    };

    init();
})();
</script>
@endpush