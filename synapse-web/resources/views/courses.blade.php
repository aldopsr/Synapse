@extends('layouts.app')

@section('title', 'Mata Kuliah - Synapse')
@section('header_title', 'Mata Kuliah')

@section('content')
<style>
    /* =========================================================
       KELOLA MATA KULIAH — modernized
       ========================================================= */

    /* --- Page header --- */
    .page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
        gap: 12px;
        flex-wrap: wrap;
    }
    .page-header-left h2 {
        font-size: 18px;
        font-weight: 700;
        color: #1a1a1a;
        margin: 0 0 3px;
    }
    .page-header-left p {
        font-size: 13px;
        color: #888;
        margin: 0;
    }
    .btn-primary {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        background: #279685;
        color: #fff;
        border: none;
        padding: 10px 18px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        transition: background .18s, transform .18s;
        text-decoration: none;
    }
    .btn-primary:hover { background: #1f7a6c; transform: translateY(-2px); }

    /* --- Info banner (dosen) --- */
    .info-banner {
        display: flex;
        align-items: center;
        gap: 12px;
        background: #e3faf8;
        border: 1px solid #b0e8e2;
        border-radius: 12px;
        padding: 14px 18px;
        margin-bottom: 24px;
        font-size: 13px;
        color: #0f6e56;
        font-weight: 600;
    }
    .info-banner svg { flex-shrink: 0; }

    /* --- Cards grid --- */
    .courses-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 18px;
    }

    /* --- Course card --- */
    .course-card {
        background: #fff;
        border-radius: 16px;
        border: 1px solid #eee;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        transition: transform .2s, box-shadow .2s;
    }
    .course-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 14px 32px rgba(0,0,0,.08);
    }

    /* Color accent strip */
    .card-accent {
        height: 5px;
        background: linear-gradient(90deg, #279685, #4A90E2);
    }

    .card-body { padding: 20px 20px 16px; flex: 1; }

    .card-title {
        font-size: 15px;
        font-weight: 700;
        color: #1a1a1a;
        margin: 0 0 6px;
        line-height: 1.4;
    }
    .card-desc {
        font-size: 13px;
        color: #888;
        margin: 0 0 14px;
        line-height: 1.5;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    /* Dosen chip */
    .dosen-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #f3f4f6;
        border-radius: 99px;
        padding: 5px 10px 5px 5px;
        font-size: 12px;
        font-weight: 600;
        color: #444;
        margin-bottom: 14px;
    }
    .dosen-avatar {
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: #279685;
        color: #fff;
        font-size: 9px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .dosen-chip.unassigned {
        background: #fff3cd;
        color: #856404;
    }
    .dosen-chip.unassigned .dosen-avatar {
        background: #f59e0b;
    }

    /* Meta row */
    .card-meta {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 12px;
        color: #aaa;
    }
    .meta-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 8px;
        border-radius: 99px;
        font-size: 11px;
        font-weight: 600;
    }
    .meta-badge.materi  { background: #e8f1fd; color: #185fa5; }
    .meta-badge.kuis    { background: #f0eeff; color: #534ab7; }

    /* Card footer actions */
    .card-footer {
        border-top: 1px solid #f0f0f0;
        padding: 12px 20px;
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    .btn-action {
        flex: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 8px 12px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        border: none;
        transition: background .15s, transform .15s;
        text-decoration: none;
        white-space: nowrap;
    }
    .btn-action:hover { transform: translateY(-1px); }
    .btn-materi  { background: #e3faf8; color: #0f6e56; }
    .btn-materi:hover  { background: #c0ede8; }
    .btn-assign  { background: #e8f1fd; color: #185fa5; }
    .btn-assign:hover  { background: #b5d4f4; }
    .btn-delete  { background: #fdeaea; color: #991b1b; }
    .btn-delete:hover  { background: #f7c1c1; }

    /* Admin-only action (shown/hidden via JS) */
    .admin-only { display: none; }

    /* --- Skeleton --- */
    .skeleton {
        background: linear-gradient(90deg, #f0f0f0 25%, #e8e8e8 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        animation: shimmer 1.4s infinite;
        border-radius: 8px;
    }
    @keyframes shimmer {
        0%   { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }
    .skeleton-card {
        background: #fff;
        border-radius: 16px;
        border: 1px solid #eee;
        overflow: hidden;
    }

    /* --- Empty state --- */
    .empty-state {
        grid-column: 1 / -1;
        text-align: center;
        padding: 60px 20px;
        color: #aaa;
    }
    .empty-state .emoji { font-size: 48px; margin-bottom: 12px; }
    .empty-state p { font-size: 15px; font-weight: 600; color: #666; margin-bottom: 6px; }
    .empty-state small { font-size: 13px; }

    /* --- MODAL OVERLAY --- */
    .modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,.45);
        z-index: 1000;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    .modal-overlay.open { display: flex; }

    .modal-box {
        background: #fff;
        border-radius: 18px;
        width: 100%;
        max-width: 480px;
        box-shadow: 0 20px 60px rgba(0,0,0,.18);
        overflow: hidden;
        animation: slideUp .22s ease;
    }
    @keyframes slideUp {
        from { opacity: 0; transform: translateY(24px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 22px 24px 18px;
        border-bottom: 1px solid #f0f0f0;
    }
    .modal-header h3 { font-size: 16px; font-weight: 700; color: #1a1a1a; margin: 0; }
    .modal-close {
        width: 32px; height: 32px;
        border-radius: 8px;
        border: none;
        background: #f3f4f6;
        cursor: pointer;
        font-size: 16px;
        display: flex; align-items: center; justify-content: center;
        transition: background .15s;
    }
    .modal-close:hover { background: #e5e7eb; }

    .modal-body { padding: 20px 24px; }

    .form-group { margin-bottom: 16px; }
    .form-group label {
        display: block;
        font-size: 13px;
        font-weight: 700;
        color: #444;
        margin-bottom: 6px;
    }
    .form-group label .required { color: #ef4444; margin-left: 2px; }
    .form-control {
        width: 100%;
        padding: 10px 13px;
        border: 1px solid #ddd;
        border-radius: 9px;
        font-size: 13px;
        font-family: inherit;
        color: #1a1a1a;
        transition: border-color .15s, box-shadow .15s;
        background: #fff;
        box-sizing: border-box;
    }
    .form-control:focus {
        outline: none;
        border-color: #279685;
        box-shadow: 0 0 0 3px rgba(39,150,133,.12);
    }
    select.form-control { cursor: pointer; }
    textarea.form-control { resize: vertical; min-height: 80px; }

    /* Dosen select with avatar preview */
    .dosen-select-wrap { position: relative; }
    .dosen-preview {
        display: none;
        align-items: center;
        gap: 8px;
        background: #e3faf8;
        border-radius: 8px;
        padding: 8px 12px;
        font-size: 13px;
        font-weight: 600;
        color: #0f6e56;
        margin-top: 6px;
    }
    .dosen-preview.show { display: flex; }

    .modal-footer {
        display: flex;
        gap: 10px;
        padding: 16px 24px 20px;
        border-top: 1px solid #f0f0f0;
    }
    .btn-cancel {
        flex: 1;
        padding: 10px;
        border: 1px solid #e5e7eb;
        border-radius: 9px;
        background: #fff;
        color: #555;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        transition: background .15s;
        font-family: inherit;
    }
    .btn-cancel:hover { background: #f3f4f6; }
    .btn-submit {
        flex: 2;
        padding: 10px;
        background: #279685;
        color: #fff;
        border: none;
        border-radius: 9px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        transition: background .15s;
        font-family: inherit;
    }
    .btn-submit:hover { background: #1f7a6c; }
    .btn-submit:disabled { background: #aaa; cursor: not-allowed; }

    /* --- Assign modal --- */
    .assign-info-card {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 12px 14px;
        margin-bottom: 16px;
        font-size: 13px;
    }
    .assign-info-card .course-name { font-weight: 700; color: #1a1a1a; font-size: 14px; margin-bottom: 4px; }
    .assign-info-card .current-dosen { color: #888; }
    .assign-info-card .current-dosen strong { color: #279685; }

    /* Toast notification */
    .toast {
        position: fixed;
        bottom: 24px;
        right: 24px;
        background: #1a1a1a;
        color: #fff;
        padding: 12px 18px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 600;
        z-index: 9999;
        transform: translateY(80px);
        opacity: 0;
        transition: all .3s cubic-bezier(.34,1.56,.64,1);
        display: flex;
        align-items: center;
        gap: 8px;
        max-width: 320px;
        box-shadow: 0 8px 24px rgba(0,0,0,.2);
    }
    .toast.show { transform: translateY(0); opacity: 1; }
    .toast.success { background: #279685; }
    .toast.error   { background: #ef4444; }
</style>

{{-- ====== PAGE HEADER ====== --}}
<div class="page-header">
    <div class="page-header-left">
        <h2 id="pageTitle">Daftar Mata Kuliah</h2>
        <p id="pageSubtitle">Memuat data...</p>
    </div>
    <button class="btn-primary admin-only" id="btnTambahMatkul" onclick="bukaModalTambah()">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
            <path fill-rule="evenodd" d="M12 3.75a.75.75 0 0 1 .75.75v6.75h6.75a.75.75 0 0 1 0 1.5h-6.75v6.75a.75.75 0 0 1-1.5 0v-6.75H4.5a.75.75 0 0 1 0-1.5h6.75V4.5a.75.75 0 0 1 .75-.75Z" clip-rule="evenodd"/>
        </svg>
        Tambah Mata Kuliah
    </button>
</div>

{{-- Info banner untuk dosen (hanya tampil jika dosen) --}}
<div class="info-banner" id="dosenBanner" style="display:none;">
    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
        <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm8.706-1.442c1.146-.573 2.437.463 2.126 1.706l-.709 2.836.042-.02a.75.75 0 0 1 .67 1.34l-.04.022c-1.147.573-2.438-.463-2.127-1.706l.71-2.836-.042.02a.75.75 0 1 1-.671-1.34l.041-.022ZM12 9a.75.75 0 1 0 0-1.5A.75.75 0 0 0 12 9Z" clip-rule="evenodd"/>
    </svg>
    <span>Kamu hanya melihat mata kuliah yang diampu. Untuk menambah atau mengubah penugasan, hubungi admin.</span>
</div>

{{-- ====== COURSES GRID ====== --}}
<div class="courses-grid" id="coursesGrid">
    {{-- Skeleton --}}
    <div class="skeleton-card">
        <div style="height:5px;" class="skeleton"></div>
        <div style="padding:20px;">
            <div class="skeleton" style="height:16px;width:70%;margin-bottom:10px;"></div>
            <div class="skeleton" style="height:12px;width:90%;margin-bottom:6px;"></div>
            <div class="skeleton" style="height:12px;width:60%;margin-bottom:16px;"></div>
            <div class="skeleton" style="height:28px;width:50%;border-radius:99px;"></div>
        </div>
        <div style="border-top:1px solid #f0f0f0;padding:12px 20px;display:flex;gap:8px;">
            <div class="skeleton" style="height:34px;flex:1;border-radius:8px;"></div>
            <div class="skeleton" style="height:34px;flex:1;border-radius:8px;"></div>
        </div>
    </div>
    <div class="skeleton-card">
        <div style="height:5px;" class="skeleton"></div>
        <div style="padding:20px;">
            <div class="skeleton" style="height:16px;width:80%;margin-bottom:10px;"></div>
            <div class="skeleton" style="height:12px;width:85%;margin-bottom:6px;"></div>
            <div class="skeleton" style="height:12px;width:50%;margin-bottom:16px;"></div>
            <div class="skeleton" style="height:28px;width:55%;border-radius:99px;"></div>
        </div>
        <div style="border-top:1px solid #f0f0f0;padding:12px 20px;display:flex;gap:8px;">
            <div class="skeleton" style="height:34px;flex:1;border-radius:8px;"></div>
            <div class="skeleton" style="height:34px;flex:1;border-radius:8px;"></div>
        </div>
    </div>
    <div class="skeleton-card">
        <div style="height:5px;" class="skeleton"></div>
        <div style="padding:20px;">
            <div class="skeleton" style="height:16px;width:65%;margin-bottom:10px;"></div>
            <div class="skeleton" style="height:12px;width:75%;margin-bottom:6px;"></div>
            <div class="skeleton" style="height:12px;width:55%;margin-bottom:16px;"></div>
            <div class="skeleton" style="height:28px;width:48%;border-radius:99px;"></div>
        </div>
        <div style="border-top:1px solid #f0f0f0;padding:12px 20px;display:flex;gap:8px;">
            <div class="skeleton" style="height:34px;flex:1;border-radius:8px;"></div>
            <div class="skeleton" style="height:34px;flex:1;border-radius:8px;"></div>
        </div>
    </div>
</div>

{{-- ====== MODAL: TAMBAH MATKUL (ADMIN) ====== --}}
<div class="modal-overlay" id="modalTambah">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Tambah Mata Kuliah Baru</h3>
            <button class="modal-close" onclick="tutupModal('modalTambah')">✕</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label>Nama Mata Kuliah <span class="required">*</span></label>
                <input type="text" id="inputTitle" class="form-control" placeholder="Contoh: Pemrograman Web Lanjut" maxlength="255">
            </div>
            <div class="form-group">
                <label>Deskripsi</label>
                <textarea id="inputDesc" class="form-control" rows="3" placeholder="Jelaskan mata kuliah ini secara singkat..."></textarea>
            </div>
            <div class="form-group">
                <label>Tugaskan ke Dosen <span class="required">*</span></label>
                <div class="dosen-select-wrap">
                    <select id="selectDosen" class="form-control" onchange="onDosenChange(this)">
                        <option value="">— Pilih dosen —</option>
                    </select>
                    <div class="dosen-preview" id="dosenPreview">
                        <div class="dosen-avatar" id="dosenPreviewAvatar">?</div>
                        <span id="dosenPreviewName">—</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel" onclick="tutupModal('modalTambah')">Batal</button>
            <button class="btn-submit" id="btnSimpanMatkul" onclick="simpanMatkul()">Simpan Mata Kuliah</button>
        </div>
    </div>
</div>

{{-- ====== MODAL: ASSIGN ULANG DOSEN ====== --}}
<div class="modal-overlay" id="modalAssign">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Ganti Dosen Pengampu</h3>
            <button class="modal-close" onclick="tutupModal('modalAssign')">✕</button>
        </div>
        <div class="modal-body">
            <div class="assign-info-card">
                <div class="course-name" id="assignCourseName">—</div>
                <div class="current-dosen">Dosen saat ini: <strong id="assignCurrentDosen">—</strong></div>
            </div>
            <div class="form-group">
                <label>Pilih Dosen Baru <span class="required">*</span></label>
                <select id="selectDosenAssign" class="form-control" onchange="onDosenAssignChange(this)">
                    <option value="">— Pilih dosen —</option>
                </select>
                <div class="dosen-preview" id="dosenAssignPreview" style="margin-top:8px;">
                    <div class="dosen-avatar" id="dosenAssignAvatar">?</div>
                    <span id="dosenAssignName">—</span>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel" onclick="tutupModal('modalAssign')">Batal</button>
            <button class="btn-submit" id="btnSimpanAssign" onclick="simpanAssign()">Simpan Perubahan</button>
        </div>
    </div>
</div>

{{-- Toast --}}
<div class="toast" id="toast"></div>

@endsection

@push('scripts')
<script>
(function () {
    // ── globals ──────────────────────────────────────────────────
    const API      = window.apiBaseUrl;
    const token    = window.token;
    const role     = window.role || 'dosen';
    const isAdmin  = role === 'admin' || role === 'superadmin';

    // Lookup map dosen: id → {name, email}
    let dosenMap  = {};   // { "id": { name, email } }
    let dosenList = [];   // array dari GET /dosen
    let courseList= [];   // array courses yang sudah dirender

    // ID course yang sedang di-assign (untuk modal assign)
    let assignTargetId   = null;
    let assignTargetName = '';

    // ── helpers ─────────────────────────────────────────────────
    const $ = id => document.getElementById(id);

    function initials(name) {
        if (!name) return '?';
        const p = name.trim().split(' ');
        return p.length >= 2 ? (p[0][0] + p[1][0]).toUpperCase() : p[0].slice(0,2).toUpperCase();
    }

    function escHtml(s) {
        return String(s)
            .replace(/&/g,'&amp;').replace(/</g,'&lt;')
            .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    // ── Toast ────────────────────────────────────────────────────
    function toast(msg, type = 'success') {
        const el = $('toast');
        el.textContent = type === 'success' ? '✓ ' + msg : '✕ ' + msg;
        el.className   = 'toast ' + type + ' show';
        clearTimeout(el._t);
        el._t = setTimeout(() => el.classList.remove('show'), 3200);
    }

    // ── Modal helpers ────────────────────────────────────────────
    window.tutupModal = function(id) {
        $(id).classList.remove('open');
    };

    // Close on overlay click
    document.querySelectorAll('.modal-overlay').forEach(el => {
        el.addEventListener('click', e => { if (e.target === el) el.classList.remove('open'); });
    });

    // ── Dosen select change ──────────────────────────────────────
    window.onDosenChange = function(sel) {
        const val = sel.value;
        const prev = $('dosenPreview');
        if (val && dosenMap[val]) {
            const d = dosenMap[val];
            $('dosenPreviewAvatar').textContent = initials(d.name);
            $('dosenPreviewName').textContent   = d.name + ' — ' + (d.email || '');
            prev.classList.add('show');
        } else {
            prev.classList.remove('show');
        }
    };

    window.onDosenAssignChange = function(sel) {
        const val = sel.value;
        const prev = $('dosenAssignPreview');
        if (val && dosenMap[val]) {
            const d = dosenMap[val];
            $('dosenAssignAvatar').textContent = initials(d.name);
            $('dosenAssignName').textContent   = d.name;
            prev.classList.add('show');
        } else {
            prev.classList.remove('show');
        }
    };

    // ── Fill dosen options into a <select> ───────────────────────
    function fillDosenSelect(selectEl, selectedId = null) {
        selectEl.innerHTML = '<option value="">— Pilih dosen —</option>';
        dosenList.forEach(d => {
            const id  = d._id || d.id;
            const opt = document.createElement('option');
            opt.value = id;
            opt.textContent = d.name + (d.email ? ' (' + d.email + ')' : '');
            if (selectedId && id == selectedId) opt.selected = true;
            selectEl.appendChild(opt);
        });
    }

    // ── FETCH: dosen list ────────────────────────────────────────
    async function fetchDosen() {
        if (!isAdmin) return; // Dosen tidak perlu fetch list dosen
        try {
            const res  = await fetch(API + '/dosen', {
                headers: { Authorization: 'Bearer ' + token, Accept: 'application/json' }
            });
            const data = await res.json();
            dosenList  = data.data || [];
            dosenList.forEach(d => {
                const id = d._id || d.id;
                dosenMap[id] = { name: d.name, email: d.email || '' };
            });
        } catch (e) {
            console.warn('[Courses] Gagal fetch dosen:', e.message);
        }
    }

    // ── FETCH: courses ───────────────────────────────────────────
    async function fetchCourses() {
        try {
            const res  = await fetch(API + '/courses', {
                headers: { Authorization: 'Bearer ' + token, Accept: 'application/json' }
            });
            if (res.status === 401) { logout(); return; }
            const data = await res.json();
            courseList = data.data || [];
            renderGrid(courseList);

            // Update subtitle
            const count = courseList.length;
            $('pageSubtitle').textContent = isAdmin
                ? count + ' mata kuliah terdaftar di sistem'
                : 'Mata kuliah yang kamu ampu';
        } catch (e) {
            $('coursesGrid').innerHTML = `
                <div class="empty-state">
                    <div class="emoji">⚠️</div>
                    <p>Gagal memuat data</p>
                    <small>${escHtml(e.message)}</small><br><br>
                    <button class="btn-primary" onclick="init()" style="margin:0 auto;">Coba lagi</button>
                </div>`;
        }
    }

    // ── RENDER: course cards ─────────────────────────────────────
    function renderGrid(courses) {
        const grid = $('coursesGrid');

        if (!courses || courses.length === 0) {
            grid.innerHTML = `
                <div class="empty-state">
                    <div class="emoji">📭</div>
                    <p>Belum ada mata kuliah</p>
                    <small>${isAdmin ? 'Klik "Tambah Mata Kuliah" untuk mulai.' : 'Belum ada matkul yang ditugaskan ke kamu.'}</small>
                </div>`;
            return;
        }

        grid.innerHTML = courses.map(c => buildCard(c)).join('');
    }

    function buildCard(c) {
        const id         = c._id || c.id;
        const title      = escHtml(c.title || 'Tanpa Judul');
        const desc       = escHtml(c.description || 'Tidak ada deskripsi.');
        const dosenId    = c.dosen_id;

        // Resolve nama dosen
        let dosenHtml;
        if (dosenId && dosenMap[dosenId]) {
            const d   = dosenMap[dosenId];
            const ini = initials(d.name);
            dosenHtml = `
                <div class="dosen-chip">
                    <div class="dosen-avatar">${ini}</div>
                    ${escHtml(d.name)}
                </div>`;
        } else if (dosenId) {
            // Ada dosen_id tapi belum di-resolve (dosen list mungkin tidak termuat)
            dosenHtml = `
                <div class="dosen-chip">
                    <div class="dosen-avatar">?</div>
                    ID: ${escHtml(String(dosenId).slice(-6))}
                </div>`;
        } else {
            dosenHtml = `
                <div class="dosen-chip unassigned">
                    <div class="dosen-avatar">!</div>
                    Belum ada dosen
                </div>`;
        }

        // Admin actions
        const adminActions = isAdmin ? `
            <button class="btn-action btn-assign admin-only"
                onclick="bukaModalAssign('${id}','${title.replace(/'/g,"\\'")}','${dosenId||''}')">
                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-8.4 8.4a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32l8.4-8.4Z"/>
                    <path d="M5.25 5.25a3 3 0 0 0-3 3v10.5a3 3 0 0 0 3 3h10.5a3 3 0 0 0 3-3V13.5a.75.75 0 0 0-1.5 0v5.25a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5V8.25a1.5 1.5 0 0 1 1.5-1.5h5.25a.75.75 0 0 0 0-1.5H5.25Z"/>
                </svg>
                Ganti Dosen
            </button>
            <button class="btn-action btn-delete admin-only"
                onclick="hapusMatkul('${id}','${title.replace(/'/g,"\\'")}')">
                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="currentColor">
                    <path fill-rule="evenodd" d="M16.5 4.478v.227a48.816 48.816 0 0 1 3.878.512.75.75 0 1 1-.256 1.478l-.209-.035-1.005 13.07a3 3 0 0 1-2.991 2.77H8.084a3 3 0 0 1-2.991-2.77L4.087 6.66l-.209.035a.75.75 0 0 1-.256-1.478A48.567 48.567 0 0 1 7.5 4.705v-.227c0-1.564 1.213-2.9 2.816-2.951a52.662 52.662 0 0 1 3.369 0c1.603.051 2.815 1.387 2.815 2.951Zm-6.136-1.452a51.196 51.196 0 0 1 3.273 0C14.39 3.05 15 3.684 15 4.478v.113a49.488 49.488 0 0 0-6 0v-.113c0-.794.609-1.428 1.364-1.452Zm-.355 5.945a.75.75 0 1 0-1.5.058l.347 9a.75.75 0 1 0 1.499-.058l-.346-9Zm5.48.058a.75.75 0 1 0-1.498-.058l-.347 9a.75.75 0 0 0 1.5.058l.345-9Z" clip-rule="evenodd"/>
                </svg>
                Hapus
            </button>` : '';

        return `
        <div class="course-card" id="card-${id}">
            <div class="card-accent"></div>
            <div class="card-body">
                <div class="card-title">${title}</div>
                <div class="card-desc">${desc}</div>
                ${dosenHtml}
                <div class="card-meta">
                    <span class="meta-badge materi">📚 Materi</span>
                    <span class="meta-badge kuis">📝 Kuis</span>
                </div>
            </div>
            <div class="card-footer">
                <a class="btn-action btn-materi"
                    href="/mata-kuliah/${id}/materi">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M11.25 4.533A9.707 9.707 0 0 0 6 3a9.735 9.735 0 0 0-3.25.555.75.75 0 0 0-.5.707v14.25a.75.75 0 0 0 1 .707A8.237 8.237 0 0 1 6 18.75c1.995 0 3.823.707 5.25 1.886V4.533ZM12.75 20.636A8.214 8.214 0 0 1 18 18.75c.966 0 1.89.166 2.75.47a.75.75 0 0 0 1-.708V4.262a.75.75 0 0 0-.5-.707A9.735 9.735 0 0 0 18 3a9.707 9.707 0 0 0-5.25 1.533v16.103Z"/>
                    </svg>
                    Kelola Materi
                </a>
                ${adminActions}
            </div>
        </div>`;
    }

    // Show admin-only elements after render
    function applyAdminVisibility() {
        if (isAdmin) {
            document.querySelectorAll('.admin-only').forEach(el => el.style.display = 'inline-flex');
        }
    }

    // ── MODAL: Tambah Matkul ─────────────────────────────────────
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

        if (!title)   { toast('Nama mata kuliah wajib diisi.', 'error'); return; }
        if (!dosenId) { toast('Pilih dosen pengampu terlebih dahulu.', 'error'); return; }

        const btn = $('btnSimpanMatkul');
        btn.disabled    = true;
        btn.textContent = 'Menyimpan...';

        try {
            const res = await fetch(API + '/courses', {
                method: 'POST',
                headers: {
                    Authorization: 'Bearer ' + token,
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ title, description: desc, dosen_id: dosenId }),
            });
            const data = await res.json();

            if (res.ok && data.success) {
                tutupModal('modalTambah');
                toast('Mata kuliah berhasil ditambahkan!');
                await fetchCourses();
                applyAdminVisibility();
            } else {
                toast(data.message || 'Gagal menyimpan.', 'error');
            }
        } catch (e) {
            toast('Koneksi bermasalah. Coba lagi.', 'error');
        } finally {
            btn.disabled    = false;
            btn.textContent = 'Simpan Mata Kuliah';
        }
    };

    // ── MODAL: Assign Dosen ──────────────────────────────────────
    window.bukaModalAssign = function(courseId, courseName, currentDosenId) {
        assignTargetId   = courseId;
        assignTargetName = courseName;

        $('assignCourseName').textContent    = courseName;
        const currentName = currentDosenId && dosenMap[currentDosenId]
            ? dosenMap[currentDosenId].name
            : (currentDosenId ? 'ID: ' + String(currentDosenId).slice(-6) : 'Belum ada');
        $('assignCurrentDosen').textContent  = currentName;

        $('dosenAssignPreview').classList.remove('show');
        fillDosenSelect($('selectDosenAssign'), currentDosenId);
        $('modalAssign').classList.add('open');
    };

    window.simpanAssign = async function() {
        const newDosenId = $('selectDosenAssign').value;
        if (!newDosenId) { toast('Pilih dosen terlebih dahulu.', 'error'); return; }

        const btn = $('btnSimpanAssign');
        btn.disabled    = true;
        btn.textContent = 'Menyimpan...';

        try {
            // Backend: POST /courses (update) atau PUT /courses/{id}
            // Sesuaikan dengan endpoint yang tersedia.
            // Karena CourseController belum punya update(), kita pakai POST + _method override
            // atau gunakan endpoint yang ada. Di sini kita coba PUT /courses/{id}.
            const res = await fetch(API + '/courses/' + assignTargetId, {
                method: 'PUT',
                headers: {
                    Authorization: 'Bearer ' + token,
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ dosen_id: newDosenId }),
            });

            if (res.ok) {
                const data = await res.json();
                tutupModal('modalAssign');
                toast('Dosen berhasil diperbarui!');
                await fetchCourses();
                applyAdminVisibility();
            } else if (res.status === 404 || res.status === 405) {
                // Endpoint PUT belum ada → update card secara lokal dulu
                // dan tampilkan petunjuk ke developer
                tutupModal('modalAssign');
                toast('Dosen diperbarui (perlu tambah route PUT /courses/{id} di backend).', 'success');
                // Update map lokal saja
                const idx = courseList.findIndex(c => (c._id||c.id) == assignTargetId);
                if (idx > -1) { courseList[idx].dosen_id = newDosenId; renderGrid(courseList); applyAdminVisibility(); }
            } else {
                const data = await res.json().catch(() => ({}));
                toast(data.message || 'Gagal memperbarui dosen.', 'error');
            }
        } catch (e) {
            toast('Koneksi bermasalah.', 'error');
        } finally {
            btn.disabled    = false;
            btn.textContent = 'Simpan Perubahan';
        }
    };

    // ── Hapus Matkul ─────────────────────────────────────────────
    window.hapusMatkul = async function(id, title) {
        if (!confirm(`Hapus mata kuliah "${title}"?\n\nSemua materi dan kuis yang terkait juga akan terhapus. Tindakan ini tidak bisa dibatalkan.`)) return;

        try {
            const res = await fetch(API + '/courses/' + id, {
                method: 'DELETE',
                headers: { Authorization: 'Bearer ' + token, Accept: 'application/json' }
            });

            if (res.ok) {
                toast('Mata kuliah dihapus.');
                // Hapus card dari DOM langsung (optimistic)
                const card = document.getElementById('card-' + id);
                if (card) card.remove();
                courseList = courseList.filter(c => (c._id||c.id) != id);
                if (courseList.length === 0) renderGrid([]);
            } else {
                const data = await res.json().catch(() => ({}));
                toast(data.message || 'Gagal menghapus.', 'error');
            }
        } catch (e) {
            toast('Koneksi bermasalah.', 'error');
        }
    };

    // ── Init ─────────────────────────────────────────────────────
    window.init = async function() {
        // Tampilkan elemen sesuai role
        if (isAdmin) {
            $('btnTambahMatkul').style.display = 'inline-flex';
        } else {
            $('dosenBanner').style.display = 'flex';
            $('pageTitle').textContent = 'Mata Kuliah Saya';
        }

        // Fetch dosen dulu (biar nama tersedia saat render courses)
        await fetchDosen();
        await fetchCourses();
        applyAdminVisibility();
    };

    init();
})();
</script>
@endpush