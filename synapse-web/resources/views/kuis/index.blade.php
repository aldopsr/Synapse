@extends('layouts.app')

@section('title', 'Kelola Kuis - Synapse')
@section('header_title', 'Kelola Kuis')

@section('content')
<style>
/* =====================================================
   KELOLA KUIS — modernized
   ===================================================== */

/* --- Page header --- */
.page-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 24px;
    flex-wrap: wrap;
}
.page-header-left h2 { font-size: 18px; font-weight: 700; color: #1a1a1a; margin: 0 0 4px; }
.page-header-left p  { font-size: 13px; color: #888; margin: 0; }

.btn-primary {
    display: inline-flex; align-items: center; gap: 7px;
    background: #279685; color: #fff; border: none;
    padding: 10px 18px; border-radius: 10px;
    font-size: 13px; font-weight: 700; cursor: pointer;
    transition: background .18s, transform .18s;
    white-space: nowrap; font-family: inherit;
    text-decoration: none;
}
.btn-primary:hover { background: #1f7a6c; transform: translateY(-2px); }

/* --- Toolbar --- */
.toolbar {
    display: flex; align-items: center; gap: 10px;
    margin-bottom: 16px; flex-wrap: wrap;
}
.search-wrap {
    position: relative; flex: 1;
    min-width: 200px; max-width: 320px;
}
.search-wrap svg {
    position: absolute; left: 11px; top: 50%;
    transform: translateY(-50%); color: #bbb; pointer-events: none;
}
.search-input {
    width: 100%; padding: 9px 12px 9px 36px;
    border: 1px solid #e5e7eb; border-radius: 10px;
    font-size: 13px; font-family: inherit; background: #fff;
    color: #1a1a1a; box-sizing: border-box;
    transition: border-color .15s, box-shadow .15s;
}
.search-input:focus {
    outline: none; border-color: #279685;
    box-shadow: 0 0 0 3px rgba(39,150,133,.1);
}
.search-input::placeholder { color: #ccc; }

.filter-select {
    padding: 9px 13px;
    border: 1px solid #e5e7eb; border-radius: 10px;
    font-size: 13px; font-family: inherit;
    background: #fff; color: #444; cursor: pointer;
}
.filter-select:focus { outline: none; border-color: #279685; }

/* Status pills */
.pills { display: flex; gap: 6px; flex-wrap: wrap; align-items: center; }
.pill {
    padding: 6px 14px; border-radius: 99px; font-size: 12px;
    font-weight: 700; cursor: pointer; border: 1.5px solid #e5e7eb;
    background: #fff; color: #666;
    transition: all .15s; white-space: nowrap;
    font-family: inherit;
}
.pill:hover { border-color: #279685; color: #279685; }
.pill.active { background: #279685; color: #fff; border-color: #279685; }
.pill.active.p-nonaktif   { background: #ef4444; border-color: #ef4444; }
.pill.active.p-belum_mulai{ background: #f59e0b; border-color: #f59e0b; }
.pill.active.p-sudah_selesai{ background: #6b7280; border-color: #6b7280; }
.pill.active.p-aktif      { background: #279685; border-color: #279685; }

.count-badge {
    font-size: 12px; font-weight: 700; color: #888;
    white-space: nowrap; margin-left: auto;
}
.count-badge span { color: #279685; font-weight: 700; }

/* --- Cards grid --- */
.kuis-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 16px;
}

/* --- Quiz card --- */
.quiz-card {
    background: #fff;
    border-radius: 16px;
    border: 1px solid #eee;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    transition: transform .2s, box-shadow .2s, border-color .2s;
    position: relative;
}
.quiz-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 28px rgba(0,0,0,.07);
}
.quiz-card.is-inactive {
    opacity: .75;
    border-color: #f0f0f0;
}

/* Status accent strip */
.card-strip {
    height: 4px;
    transition: background .3s;
}
.strip-aktif          { background: linear-gradient(90deg, #279685, #4A90E2); }
.strip-nonaktif       { background: #e5e7eb; }
.strip-belum_mulai    { background: linear-gradient(90deg, #f59e0b, #ef4444); }
.strip-sudah_selesai  { background: #9ca3af; }

.card-body { padding: 18px 18px 14px; flex: 1; }

/* Card title row */
.card-title-row {
    display: flex; align-items: flex-start;
    justify-content: space-between; gap: 10px; margin-bottom: 6px;
}
.card-title {
    font-size: 14px; font-weight: 700; color: #1a1a1a;
    line-height: 1.4; flex: 1;
}
.card-title a { color: inherit; text-decoration: none; }
.card-title a:hover { color: #279685; }

/* Status badge */
.status-badge {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 3px 9px; border-radius: 99px;
    font-size: 10px; font-weight: 700;
    white-space: nowrap; flex-shrink: 0;
}
.s-aktif        { background: #d1fae5; color: #065f46; }
.s-nonaktif     { background: #fee2e2; color: #991b1b; }
.s-belum_mulai  { background: #fef3c7; color: #92400e; }
.s-sudah_selesai{ background: #e5e7eb; color: #374151; }

/* Card desc */
.card-desc {
    font-size: 12px; color: #999; margin: 0 0 12px;
    line-height: 1.5;
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Meta chips row */
.card-meta {
    display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 14px;
}
.meta-chip {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 4px 9px; border-radius: 8px;
    font-size: 11px; font-weight: 600;
}
.chip-matkul  { background: #e3faf8; color: #0f6e56; }
.chip-soal    { background: #e8f1fd; color: #185fa5; }
.chip-soal.zero { background: #fef3c7; color: #92400e; }
.chip-durasi  { background: #f0eeff; color: #534ab7; }
.chip-jadwal  { background: #fef3c7; color: #92400e; }

/* Toggle row */
.card-toggle-row {
    display: flex; align-items: center; justify-content: space-between;
    padding: 10px 0 0;
    border-top: 1px solid #f5f5f5;
    margin-top: 2px;
}
.toggle-label {
    font-size: 12px; font-weight: 600;
    color: #888;
}
.toggle-label.on { color: #279685; }

/* Toggle switch (modern pill) */
.toggle-wrap {
    position: relative; width: 42px; height: 24px;
    cursor: pointer; flex-shrink: 0;
}
.toggle-wrap input { opacity: 0; width: 0; height: 0; position: absolute; }
.toggle-track {
    position: absolute; inset: 0;
    background: #e5e7eb; border-radius: 99px;
    transition: background .25s;
}
.toggle-wrap input:checked + .toggle-track { background: #279685; }
.toggle-thumb {
    position: absolute;
    top: 3px; left: 3px;
    width: 18px; height: 18px;
    background: #fff; border-radius: 50%;
    transition: transform .25s;
    box-shadow: 0 1px 3px rgba(0,0,0,.2);
    pointer-events: none;
}
.toggle-wrap input:checked ~ .toggle-thumb { transform: translateX(18px); }

/* Spinner untuk toggle loading */
.toggle-wrap.loading .toggle-track { background: #d1d5db; }
.toggle-wrap.loading .toggle-thumb {
    background: #9ca3af;
    animation: spin .6s linear infinite;
}
@keyframes spin {
    0%   { transform: rotate(0deg) translateX(9px); }
    100% { transform: rotate(360deg) translateX(9px); }
}

/* --- Card footer actions --- */
.card-footer {
    border-top: 1px solid #f0f0f0;
    padding: 11px 18px;
    display: flex; gap: 6px; flex-wrap: wrap;
    background: #fafafa;
}
.btn-action {
    flex: 1; min-width: 70px;
    display: inline-flex; align-items: center; justify-content: center; gap: 5px;
    padding: 7px 10px; border-radius: 8px;
    font-size: 11px; font-weight: 700;
    border: none; cursor: pointer;
    transition: background .15s, transform .12s;
    font-family: inherit; text-decoration: none;
    white-space: nowrap;
}
.btn-action:hover { transform: translateY(-1px); }
.btn-soal   { background: #e3faf8; color: #0f6e56; }
.btn-soal:hover   { background: #c0ede8; }
.btn-edit   { background: #fef3c7; color: #92400e; }
.btn-edit:hover   { background: #fde68a; }
.btn-delete { background: #fee2e2; color: #991b1b; }
.btn-delete:hover { background: #fecaca; }

/* --- Skeleton --- */
.skeleton {
    background: linear-gradient(90deg,#f0f0f0 25%,#e8e8e8 50%,#f0f0f0 75%);
    background-size: 200% 100%;
    animation: shimmer 1.4s infinite;
    border-radius: 8px;
}
@keyframes shimmer {
    0%   { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}
.skeleton-card {
    background: #fff; border-radius: 16px;
    border: 1px solid #eee; overflow: hidden;
}

/* --- Empty state --- */
.empty-state {
    grid-column: 1 / -1;
    text-align: center; padding: 60px 20px; color: #bbb;
}
.empty-state .ei  { font-size: 48px; margin-bottom: 12px; }
.empty-state .el  { font-size: 15px; font-weight: 700; color: #888; margin-bottom: 6px; }
.empty-state .es  { font-size: 12px; }

/* --- Toast --- */
.toast {
    position: fixed; bottom: 24px; right: 24px;
    padding: 11px 16px; border-radius: 10px;
    font-size: 13px; font-weight: 600; z-index: 9999;
    transform: translateY(80px); opacity: 0;
    transition: all .28s cubic-bezier(.34,1.56,.64,1);
    display: flex; align-items: center; gap: 8px;
    max-width: 300px; box-shadow: 0 8px 24px rgba(0,0,0,.18);
    color: #fff; pointer-events: none;
}
.toast.show { transform: translateY(0); opacity: 1; }
.toast.ok  { background: #279685; }
.toast.err { background: #ef4444; }
</style>

{{-- ========== PAGE HEADER ========== --}}
<div class="page-header">
    <div class="page-header-left">
        <h2>Daftar Kuis</h2>
        <p id="pageSubtitle">Memuat data...</p>
    </div>
    <a href="/kuis/buat" class="btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="currentColor">
            <path fill-rule="evenodd" d="M12 3.75a.75.75 0 0 1 .75.75v6.75h6.75a.75.75 0 0 1 0 1.5h-6.75v6.75a.75.75 0 0 1-1.5 0v-6.75H4.5a.75.75 0 0 1 0-1.5h6.75V4.5a.75.75 0 0 1 .75-.75Z" clip-rule="evenodd"/>
        </svg>
        Buat Kuis Baru
    </a>
</div>

{{-- ========== TOOLBAR ========== --}}
<div class="toolbar">
    {{-- Search --}}
    <div class="search-wrap">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
        </svg>
        <input type="text" class="search-input" id="searchInput"
            placeholder="Cari judul kuis..." oninput="applyFilters()">
    </div>

    {{-- Filter matkul (admin only, hidden untuk dosen) --}}
    <select class="filter-select" id="filterMatkul" onchange="applyFilters()" style="display:none;">
        <option value="">Semua Matkul</option>
    </select>

    {{-- Status pills --}}
    <div class="pills">
        <button class="pill active" data-status="">Semua</button>
        <button class="pill p-aktif"         data-status="aktif">🟢 Aktif</button>
        <button class="pill p-belum_mulai"   data-status="belum_mulai">🟡 Belum Mulai</button>
        <button class="pill p-sudah_selesai" data-status="sudah_selesai">⚪ Selesai</button>
        <button class="pill p-nonaktif"      data-status="nonaktif">🔴 Nonaktif</button>
    </div>

    <span class="count-badge" id="countBadge">Memuat...</span>
</div>

{{-- ========== QUIZ GRID ========== --}}
<div class="kuis-grid" id="kuisGrid">
    {{-- Skeleton --}}
    @for ($i = 0; $i < 6; $i++)
    <div class="skeleton-card">
        <div style="height:4px;" class="skeleton"></div>
        <div style="padding:18px;">
            <div class="skeleton" style="height:14px;width:75%;margin-bottom:8px;"></div>
            <div class="skeleton" style="height:12px;width:90%;margin-bottom:4px;"></div>
            <div class="skeleton" style="height:12px;width:60%;margin-bottom:14px;"></div>
            <div style="display:flex;gap:6px;">
                <div class="skeleton" style="height:24px;width:90px;border-radius:8px;"></div>
                <div class="skeleton" style="height:24px;width:70px;border-radius:8px;"></div>
                <div class="skeleton" style="height:24px;width:80px;border-radius:8px;"></div>
            </div>
        </div>
        <div style="border-top:1px solid #f0f0f0;padding:11px 18px;display:flex;gap:6px;background:#fafafa;">
            <div class="skeleton" style="height:32px;flex:1;border-radius:8px;"></div>
            <div class="skeleton" style="height:32px;flex:1;border-radius:8px;"></div>
            <div class="skeleton" style="height:32px;width:40px;border-radius:8px;"></div>
        </div>
    </div>
    @endfor
</div>

{{-- Toast --}}
<div class="toast" id="toast"></div>

@endsection

@push('scripts')
<script>
(function () {
    /* ── globals ────────────────────────────────────────────── */
    const API   = window.apiBaseUrl;
    const token = window.token;
    const role  = window.role || 'dosen';
    const user  = window.user;
    const isAdmin = role === 'admin' || role === 'superadmin';

    const $  = id => document.getElementById(id);
    let allQuizzes = [];
    let courseMap  = {};   // id → title (untuk dropdown filter admin)
    let activeStatus = '';  // filter status pill
    let toggleInFlight = new Set(); // IDs yang sedang di-toggle

    /* ── toast ──────────────────────────────────────────────── */
    function toast(msg, type = 'ok') {
        const el = $('toast');
        el.textContent = (type === 'ok' ? '✓  ' : '✕  ') + msg;
        el.className   = 'toast ' + type + ' show';
        clearTimeout(el._t);
        el._t = setTimeout(() => el.classList.remove('show'), 3200);
    }

    /* ── helpers ────────────────────────────────────────────── */
    function esc(s) {
        return String(s||'')
            .replace(/&/g,'&amp;').replace(/</g,'&lt;')
            .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
    function escJs(s) {
        return String(s||'').replace(/\\/g,'\\\\').replace(/'/g,"\\'").replace(/"/g,'\\"');
    }
    function fmtDate(d) {
        if (!d) return '—';
        const dt = new Date(d);
        if (isNaN(dt)) return d;
        return dt.toLocaleString('id-ID', { day:'2-digit', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit' });
    }
    function statusInfo(s) {
        return {
            aktif:         { cls:'s-aktif',         strip:'strip-aktif',         label:'🟢 Aktif' },
            nonaktif:      { cls:'s-nonaktif',       strip:'strip-nonaktif',      label:'🔴 Nonaktif' },
            belum_mulai:   { cls:'s-belum_mulai',    strip:'strip-belum_mulai',   label:'🟡 Belum Mulai' },
            sudah_selesai: { cls:'s-sudah_selesai',  strip:'strip-sudah_selesai', label:'⚪ Selesai' },
        }[s] || { cls:'s-nonaktif', strip:'strip-nonaktif', label: s };
    }

    /* ── fetch courses (admin only, untuk filter) ───────────── */
    async function fetchCourses() {
        if (!isAdmin) return;
        try {
            const res  = await fetch(API + '/courses', {
                headers: { Authorization: 'Bearer ' + token, Accept: 'application/json' }
            });
            const data = await res.json();
            const list = data.data || [];
            list.forEach(c => { courseMap[c._id || c.id] = c.title; });

            const sel = $('filterMatkul');
            sel.style.display = '';
            list.forEach(c => {
                const opt = document.createElement('option');
                opt.value = c._id || c.id;
                opt.textContent = c.title;
                sel.appendChild(opt);
            });
        } catch (e) { console.warn('[Kuis] courses:', e.message); }
    }

    /* ── fetch quizzes ──────────────────────────────────────── */
    async function fetchQuizzes() {
        try {
            // Untuk dosen: backend sudah filter by course_id via middleware
            // Untuk admin: bisa tambah ?course_id= dari filter dropdown
            const courseFilter = isAdmin ? ($('filterMatkul').value || '') : '';
            const url = API + '/admin/quizzes' + (courseFilter ? `?course_id=${courseFilter}` : '');

            const res  = await fetch(url, {
                headers: { Authorization: 'Bearer ' + token, Accept: 'application/json' }
            });
            if (res.status === 401) { window.logout(); return; }
            const data = await res.json();
            allQuizzes = data.data || [];

            // Update subtitle
            const matkulCtx = isAdmin ? 'semua matkul' : (user?.course_id ? 'matkul kamu' : 'sistem');
            $('pageSubtitle').textContent = allQuizzes.length + ' kuis tersedia di ' + matkulCtx;

            applyFilters();
        } catch (e) {
            $('kuisGrid').innerHTML = `<div class="empty-state">
                <div class="ei">⚠️</div>
                <div class="el">Gagal memuat data</div>
                <div class="es">${esc(e.message)}</div>
                <button onclick="fetchQuizzes()" style="margin-top:14px;padding:8px 18px;background:#279685;color:#fff;border:none;border-radius:8px;cursor:pointer;font-weight:700;">Coba lagi</button>
            </div>`;
        }
    }

    /* ── apply filters + search + render ───────────────────── */
    window.applyFilters = function() {
        const q = $('searchInput').value.trim().toLowerCase();

        let filtered = allQuizzes.filter(qz => {
            // Search
            const matchQ = !q || (qz.title || '').toLowerCase().includes(q)
                || (qz.description || '').toLowerCase().includes(q);
            // Status pill
            const matchStatus = !activeStatus || (qz.status || '') === activeStatus;
            return matchQ && matchStatus;
        });

        $('countBadge').innerHTML = `<span>${filtered.length}</span> dari ${allQuizzes.length} kuis`;
        renderGrid(filtered);
    };

    /* ── render grid ────────────────────────────────────────── */
    function renderGrid(list) {
        const grid = $('kuisGrid');

        if (!list || list.length === 0) {
            grid.innerHTML = `<div class="empty-state">
                <div class="ei">📝</div>
                <div class="el">Belum ada kuis</div>
                <div class="es">${activeStatus || $('searchInput').value
                    ? 'Coba ubah filter atau pencarian.'
                    : 'Klik "Buat Kuis Baru" untuk mulai.'}</div>
            </div>`;
            return;
        }

        grid.innerHTML = list.map(q => buildCard(q)).join('');
    }

    /* ── build card HTML ────────────────────────────────────── */
    function buildCard(q) {
        const id     = q._id || q.id;
        const status = q.status || (q.is_active ? 'aktif' : 'nonaktif');
        const si     = statusInfo(status);

        const title    = q.title || 'Tanpa Judul';
        const desc     = q.description || '';
        const nSoal    = q.questions_count ?? 0;
        const durasi   = q.duration_minutes ?? 0;
        const isActive = !!q.is_active;

        // Matkul
        const courseId    = q.course_id;
        const courseTitle = q.course?.title
            || (courseId && courseMap[courseId])
            || null;
        const matkulChip  = courseTitle
            ? `<span class="meta-chip chip-matkul">📚 ${esc(courseTitle)}</span>`
            : '';

        // Soal chip (kuning kalau 0)
        const soalChip = `<span class="meta-chip chip-soal${nSoal === 0 ? ' zero' : ''}">
            ${nSoal === 0 ? '⚠' : '📋'} ${nSoal} soal
        </span>`;

        // Durasi chip
        const durasiChip = `<span class="meta-chip chip-durasi">⏱ ${durasi} mnt</span>`;

        // Jadwal chip
        let jadwalChip = '';
        if (q.start_at || q.end_at) {
            const label = q.start_at
                ? fmtDate(q.start_at).split(',')[0]   // cuma tanggalnya
                : 'Sekarang';
            jadwalChip = `<span class="meta-chip chip-jadwal">📅 ${esc(label)}</span>`;
        }

        // Toggle state
        const toggleChecked = isActive ? 'checked' : '';
        const toggleLabelTxt = isActive ? 'Aktif' : 'Nonaktif';
        const toggleLabelCls = isActive ? 'on' : '';

        return `
        <div class="quiz-card${isActive ? '' : ' is-inactive'}" id="qcard-${id}">
            <div class="card-strip ${si.strip}" id="strip-${id}"></div>
            <div class="card-body">
                <div class="card-title-row">
                    <div class="card-title">
                        <a href="/kuis/${id}/edit">${esc(title)}</a>
                    </div>
                    <span class="status-badge ${si.cls}" id="sbadge-${id}">${si.label}</span>
                </div>
                ${desc ? `<div class="card-desc">${esc(desc)}</div>` : '<div style="margin-bottom:12px;"></div>'}
                <div class="card-meta">
                    ${matkulChip}
                    ${soalChip}
                    ${durasiChip}
                    ${jadwalChip}
                </div>
                <div class="card-toggle-row">
                    <span class="toggle-label ${toggleLabelCls}" id="tlabel-${id}">
                        ${isActive ? '✓ Aktif — mahasiswa bisa akses' : '✗ Nonaktif — tersembunyi'}
                    </span>
                    <label class="toggle-wrap" id="twrap-${id}" title="Toggle aktif/nonaktif">
                        <input type="checkbox" ${toggleChecked}
                            onchange="toggleQuiz('${id}', this)">
                        <div class="toggle-track"></div>
                        <div class="toggle-thumb"></div>
                    </label>
                </div>
            </div>
            <div class="card-footer">
                <a class="btn-action btn-soal" href="/kuis/${id}/soal">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M7.502 6h7.128A3.375 3.375 0 0 1 18 9.375v9.375a3 3 0 0 0 3-3V6.108c0-1.505-1.125-2.811-2.664-2.94a48.972 48.972 0 0 0-.673-.05A3 3 0 0 0 15 1.5h-1.5a3 3 0 0 0-2.663 1.618c-.225.015-.45.032-.673.05C8.662 3.295 7.554 4.542 7.502 6ZM13.5 3A1.5 1.5 0 0 0 12 4.5h4.5A1.5 1.5 0 0 0 15 3h-1.5Z" clip-rule="evenodd"/><path fill-rule="evenodd" d="M3 9.375C3 8.339 3.84 7.5 4.875 7.5h9.75c1.036 0 1.875.84 1.875 1.875v11.25c0 1.035-.84 1.875-1.875 1.875h-9.75A1.875 1.875 0 0 1 3 20.625V9.375Zm9.586 4.594a.75.75 0 0 0-1.172-.938l-2.476 3.096-.908-.907a.75.75 0 0 0-1.06 1.06l1.5 1.5a.75.75 0 0 0 1.116-.062l3-3.75Z" clip-rule="evenodd"/></svg>
                    Kelola Soal
                    ${nSoal > 0 ? `<span style="background:rgba(15,110,86,.18);padding:1px 6px;border-radius:4px;font-size:10px;">${nSoal}</span>` : ''}
                </a>
                <a class="btn-action btn-edit" href="/kuis/${id}/edit">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-8.4 8.4a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32l8.4-8.4Z"/><path d="M5.25 5.25a3 3 0 0 0-3 3v10.5a3 3 0 0 0 3 3h10.5a3 3 0 0 0 3-3V13.5a.75.75 0 0 0-1.5 0v5.25a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5V8.25a1.5 1.5 0 0 1 1.5-1.5h5.25a.75.75 0 0 0 0-1.5H5.25Z"/></svg>
                    Edit
                </a>
                <a href="/kuis/${id}/leaderboard"
                style="display:inline-flex;align-items:center;gap:5px;padding:6px 12px;
                        border-radius:8px;font-size:12px;font-weight:600;
                        background:#f0fdfb;color:#279685;border:1px solid #ccfbf1;
                        text-decoration:none;transition:background .15s;"
                onmouseover="this.style.background='#e3faf8'"
                onmouseout="this.style.background='#f0fdfb'">
                    🏆 Leaderboard
                </a>
                <button class="btn-action btn-delete"
                    onclick="hapusQuiz('${id}','${escJs(title)}')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M16.5 4.478v.227a48.816 48.816 0 0 1 3.878.512.75.75 0 1 1-.256 1.478l-.209-.035-1.005 13.07a3 3 0 0 1-2.991 2.77H8.084a3 3 0 0 1-2.991-2.77L4.087 6.66l-.209.035a.75.75 0 0 1-.256-1.478A48.567 48.567 0 0 1 7.5 4.705v-.227c0-1.564 1.213-2.9 2.816-2.951a52.662 52.662 0 0 1 3.369 0c1.603.051 2.815 1.387 2.815 2.951Zm-6.136-1.452a51.196 51.196 0 0 1 3.273 0C14.39 3.05 15 3.684 15 4.478v.113a49.488 49.488 0 0 0-6 0v-.113c0-.794.609-1.428 1.364-1.452Zm-.355 5.945a.75.75 0 1 0-1.5.058l.347 9a.75.75 0 1 0 1.499-.058l-.346-9Zm5.48.058a.75.75 0 1 0-1.498-.058l-.347 9a.75.75 0 0 0 1.5.058l.345-9Z" clip-rule="evenodd"/></svg>
                </button>
            </div>
        </div>`;
    }

    /* ── TOGGLE — optimistic update ─────────────────────────── */
    window.toggleQuiz = async function(id, checkbox) {
        if (toggleInFlight.has(id)) {
            // Batalkan perubahan visual kalau sedang in-flight
            checkbox.checked = !checkbox.checked;
            return;
        }

        const newActive = checkbox.checked;
        toggleInFlight.add(id);

        // 1. Langsung update UI (optimistic)
        updateCardVisual(id, newActive);

        // 2. Tambah loading state ke toggle
        const wrap = $(`twrap-${id}`);
        if (wrap) wrap.classList.add('loading');

        try {
            const res = await fetch(`${API}/admin/quizzes/${id}/toggle`, {
                method: 'POST',
                headers: { Authorization: 'Bearer ' + token, Accept: 'application/json' }
            });

            if (res.ok) {
                const data   = await res.json();
                // Sinkronisasi dengan nilai yang dikembalikan server
                const actual = data.data?.is_active ?? newActive;

                // Update data lokal
                const idx = allQuizzes.findIndex(q => (q._id||q.id) == id);
                if (idx > -1) {
                    allQuizzes[idx].is_active = actual;
                    allQuizzes[idx].status    = actual
                        ? 'aktif'
                        : (allQuizzes[idx].status === 'aktif' ? 'nonaktif' : allQuizzes[idx].status);
                }

                // Kalau server kembalikan nilai berbeda dari yang kita prediksi, perbaiki
                if (actual !== newActive) {
                    updateCardVisual(id, actual);
                    if (checkbox) checkbox.checked = actual;
                }

                toast(actual ? 'Kuis diaktifkan' : 'Kuis dinonaktifkan');
            } else {
                // Rollback
                updateCardVisual(id, !newActive);
                if (checkbox) checkbox.checked = !newActive;
                toast('Gagal mengubah status kuis.', 'err');
            }
        } catch (e) {
            // Rollback
            updateCardVisual(id, !newActive);
            if (checkbox) checkbox.checked = !newActive;
            toast('Koneksi bermasalah.', 'err');
        } finally {
            toggleInFlight.delete(id);
            const wrap = $(`twrap-${id}`);
            if (wrap) wrap.classList.remove('loading');
        }
    };

    /* Update visual elemen di card tanpa re-render seluruh grid */
    function updateCardVisual(id, isActive) {
        const card   = $(`qcard-${id}`);
        const strip  = $(`strip-${id}`);
        const badge  = $(`sbadge-${id}`);
        const label  = $(`tlabel-${id}`);

        if (!card) return;

        const newStatus = isActive ? 'aktif' : 'nonaktif';
        const si        = statusInfo(newStatus);

        card.classList.toggle('is-inactive', !isActive);
        if (strip) {
            strip.className = 'card-strip ' + si.strip;
        }
        if (badge) {
            badge.className   = 'status-badge ' + si.cls;
            badge.textContent = si.label;
        }
        if (label) {
            label.className   = 'toggle-label ' + (isActive ? 'on' : '');
            label.textContent = isActive
                ? '✓ Aktif — mahasiswa bisa akses'
                : '✗ Nonaktif — tersembunyi';
        }
    }

    /* ── HAPUS QUIZ ─────────────────────────────────────────── */
    window.hapusQuiz = async function(id, title) {
        if (!confirm(`Hapus kuis "${title}"?\n\nSemua soal di dalamnya juga akan terhapus. Tindakan ini tidak bisa dibatalkan.`)) return;

        try {
            const res = await fetch(`${API}/admin/quizzes/${id}`, {
                method: 'DELETE',
                headers: { Authorization: 'Bearer ' + token, Accept: 'application/json' }
            });
            if (res.ok) {
                // Optimistic: hilangkan card
                const card = $(`qcard-${id}`);
                if (card) {
                    card.style.transition = 'opacity .25s, transform .25s';
                    card.style.opacity = '0';
                    card.style.transform = 'scale(.95)';
                    setTimeout(() => {
                        allQuizzes = allQuizzes.filter(q => (q._id||q.id) != id);
                        applyFilters();
                    }, 260);
                }
                toast('Kuis berhasil dihapus.');
            } else {
                const err = await res.json().catch(() => ({}));
                toast(err.message || 'Gagal menghapus kuis.', 'err');
            }
        } catch (e) { toast('Koneksi bermasalah.', 'err'); }
    };

    /* ── Status pill clicks ─────────────────────────────────── */
    document.querySelectorAll('.pill').forEach(pill => {
        pill.addEventListener('click', () => {
            document.querySelectorAll('.pill').forEach(p => p.classList.remove('active'));
            pill.classList.add('active');
            activeStatus = pill.dataset.status;
            applyFilters();
        });
    });

    /* ── Filter matkul admin: re-fetch dari server ──────────── */
    if (isAdmin) {
        $('filterMatkul').addEventListener('change', fetchQuizzes);
    }

    /* ── Init ───────────────────────────────────────────────── */
    async function init() {
        await fetchCourses();   // courses dulu untuk dropdown + courseMap
        await fetchQuizzes();
    }
    init();

    // Expose untuk tombol coba lagi
    window.fetchQuizzes = fetchQuizzes;
})();
</script>
@endpush