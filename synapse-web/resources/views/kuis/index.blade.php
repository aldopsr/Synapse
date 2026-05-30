@extends('layouts.app')
@section('title', 'Kelola Kuis - Synapse')
@section('header_title', 'Kelola Kuis')

@section('content')
<style>
.page-header { display:flex; align-items:flex-start; justify-content:space-between; gap:16px; margin-bottom:24px; flex-wrap:wrap; }
.page-header-left h2 { font-size:20px; font-weight:800; color:#111827; margin:0 0 4px; letter-spacing:-.3px; }
.page-header-left p  { font-size:13px; color:#9ca3af; margin:0; }

/* Toolbar */
.toolbar { display:flex; align-items:center; gap:10px; margin-bottom:16px; flex-wrap:wrap; }
.search-wrap { position:relative; flex:1; min-width:200px; max-width:320px; }
.search-wrap svg { position:absolute; left:11px; top:50%; transform:translateY(-50%); color:#c4c8d0; pointer-events:none; width:15px; height:15px; }
.search-input { width:100%; padding:9px 12px 9px 36px; border:1.5px solid #e8eaed; border-radius:99px; font-size:13px; font-family:inherit; background:#fff; color:#111827; box-sizing:border-box; transition:border-color .15s; }
.search-input:focus { outline:none; border-color:#279685; }
.search-input::placeholder { color:#d1d5db; }
.filter-select { padding:9px 13px; border:1.5px solid #e8eaed; border-radius:99px; font-size:13px; font-family:inherit; background:#fff; color:#374151; cursor:pointer; }
.filter-select:focus { outline:none; border-color:#279685; }

.pills { display:flex; gap:6px; flex-wrap:wrap; align-items:center; }
.pill { padding:6px 14px; border-radius:99px; font-size:12px; font-weight:700; cursor:pointer; border:1.5px solid #e8eaed; background:#fff; color:#6b7280; transition:all .15s; font-family:inherit; }
.pill:hover { border-color:#279685; color:#279685; }
.pill.active { background:#279685; color:#fff; border-color:#279685; }
.pill.active.p-nonaktif    { background:#ef4444; border-color:#ef4444; }
.pill.active.p-belum_mulai { background:#f59e0b; border-color:#f59e0b; }
.pill.active.p-sudah_selesai { background:#6b7280; border-color:#6b7280; }

.count-badge { font-size:12px; font-weight:600; color:#9ca3af; white-space:nowrap; margin-left:auto; }
.count-badge span { color:#279685; font-weight:700; }

/* Grid */
.kuis-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(320px,1fr)); gap:16px; }

/* Quiz card */
.quiz-card { background:#fff; border-radius:16px; border:1px solid #eff0f2; overflow:hidden; display:flex; flex-direction:column; transition:transform .2s,box-shadow .2s; }
.quiz-card:hover { transform:translateY(-3px); box-shadow:0 12px 28px rgba(0,0,0,.07); }
.quiz-card.is-inactive { opacity:.7; }

.card-strip { height:4px; }
.strip-aktif         { background:linear-gradient(90deg,#279685,#4A90E2); }
.strip-nonaktif      { background:#e5e7eb; }
.strip-belum_mulai   { background:linear-gradient(90deg,#f59e0b,#ef4444); }
.strip-sudah_selesai { background:#9ca3af; }

.card-body { padding:18px; flex:1; }

.card-title-row { display:flex; align-items:flex-start; justify-content:space-between; gap:10px; margin-bottom:6px; }
.card-title { font-size:14px; font-weight:700; color:#111827; line-height:1.4; flex:1; }
.card-title a { color:inherit; text-decoration:none; }
.card-title a:hover { color:#279685; }

/* Status badge — no emoji */
.status-badge { display:inline-flex; align-items:center; gap:5px; padding:3px 10px; border-radius:99px; font-size:11px; font-weight:700; white-space:nowrap; flex-shrink:0; }
.status-badge svg { width:8px; height:8px; }
.s-aktif        { background:#d1fae5; color:#065f46; }
.s-nonaktif     { background:#fee2e2; color:#991b1b; }
.s-belum_mulai  { background:#fef3c7; color:#92400e; }
.s-sudah_selesai{ background:#e5e7eb; color:#374151; }

.card-desc { font-size:12px; color:#9ca3af; margin:0 0 12px; line-height:1.5; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }

/* Meta chips — SVG icons */
.card-meta { display:flex; flex-wrap:wrap; gap:6px; margin-bottom:14px; }
.meta-chip { display:inline-flex; align-items:center; gap:5px; padding:4px 10px; border-radius:8px; font-size:11px; font-weight:600; }
.meta-chip svg { width:12px; height:12px; }
.chip-matkul { background:#f0fdf9; color:#0f6e56; }
.chip-soal   { background:#e8f1fd; color:#185fa5; }
.chip-soal.zero { background:#fef3c7; color:#92400e; }
.chip-durasi { background:#f5f3ff; color:#534ab7; }
.chip-jadwal { background:#fffbeb; color:#92400e; }

/* Toggle row */
.card-toggle-row { display:flex; align-items:center; justify-content:space-between; padding:10px 0 0; border-top:1px solid #f5f6f8; margin-top:4px; }
.toggle-label { font-size:12px; font-weight:600; color:#9ca3af; }
.toggle-label.on { color:#279685; }

.toggle-wrap { position:relative; width:42px; height:24px; cursor:pointer; flex-shrink:0; }
.toggle-wrap input { opacity:0; width:0; height:0; position:absolute; }
.toggle-track { position:absolute; inset:0; background:#e5e7eb; border-radius:99px; transition:background .25s; }
.toggle-wrap input:checked + .toggle-track { background:#279685; }
.toggle-thumb { position:absolute; top:3px; left:3px; width:18px; height:18px; background:#fff; border-radius:50%; transition:transform .25s; box-shadow:0 1px 3px rgba(0,0,0,.2); pointer-events:none; }
.toggle-wrap input:checked ~ .toggle-thumb { transform:translateX(18px); }

/* Duel toggle */
.card-duel-row { display:flex; align-items:center; justify-content:space-between; padding:8px 0 0; }
.duel-toggle-wrap { position:relative; width:38px; height:22px; cursor:pointer; flex-shrink:0; }
.duel-toggle-wrap input { opacity:0; width:0; height:0; position:absolute; }
.duel-track { position:absolute; inset:0; background:#e5e7eb; border-radius:99px; transition:background .25s; }
.duel-toggle-wrap input:checked + .duel-track { background:#279685; }
.duel-thumb { position:absolute; top:3px; left:3px; width:16px; height:16px; background:#fff; border-radius:50%; transition:transform .25s; box-shadow:0 1px 3px rgba(0,0,0,.2); pointer-events:none; }
.duel-toggle-wrap input:checked ~ .duel-thumb { transform:translateX(16px); }

/* Card footer */
.card-footer { border-top:1px solid #f5f6f8; padding:11px 18px; display:flex; gap:6px; flex-wrap:wrap; background:#fafafa; }
.btn-act { flex:1; min-width:70px; display:inline-flex; align-items:center; justify-content:center; gap:5px; padding:7px 10px; border-radius:8px; font-size:11px; font-weight:700; border:1.5px solid transparent; cursor:pointer; transition:background .15s; font-family:inherit; text-decoration:none; white-space:nowrap; overflow:hidden; }
.btn-act svg { width:12px; height:12px; flex-shrink:0; }
.ba-soal   { background:#f0fdf9; color:#0f6e56; border-color:#b2e8e0; }
.ba-soal:hover { background:#dcfaf5; }
.ba-edit   { background:#fffbeb; color:#854d0e; border-color:#fde68a; }
.ba-edit:hover { background:#fef9c3; }
.ba-lb     { background:#f5f3ff; color:#6d28d9; border-color:#ddd6fe; }
.ba-lb:hover { background:#ede9fe; }
.ba-del    { background:#fef2f2; color:#dc2626; border-color:#fecaca; }
.ba-del:hover { background:#fee2e2; }

/* Skeleton */
.skeleton { background:linear-gradient(90deg,#f5f5f5 25%,#eee 50%,#f5f5f5 75%); background-size:200% 100%; animation:shimmer 1.4s infinite; border-radius:8px; }
@keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }
.skeleton-card { background:#fff; border-radius:16px; border:1px solid #eff0f2; overflow:hidden; }

/* Empty */
.empty-state { grid-column:1/-1; text-align:center; padding:64px 20px; }
.empty-icon  { width:60px; height:60px; border-radius:18px; background:#f5f6f8; display:flex; align-items:center; justify-content:center; margin:0 auto 16px; }
.empty-icon svg { width:30px; height:30px; color:#d1d5db; }
.empty-title { font-size:15px; font-weight:700; color:#374151; margin-bottom:6px; }
.empty-sub   { font-size:13px; color:#9ca3af; }

/* Tab nav */
.tab-nav { display:flex; border-bottom:2px solid #e8eaed; margin-bottom:20px; }
.tab-btn { padding:10px 20px; border:none; background:none; cursor:pointer; font-weight:600; font-size:14px; color:#9ca3af; border-bottom:2px solid transparent; margin-bottom:-2px; transition:color .15s,border-color .15s; font-family:inherit; display:inline-flex; align-items:center; gap:7px; }
.tab-btn svg { width:16px; height:16px; }
.tab-btn.active { color:#279685; border-bottom-color:#279685; font-weight:700; }
.tab-btn:hover:not(.active) { color:#374151; }

/* Duel table */
.duel-table { width:100%; border-collapse:collapse; font-size:13px; background:#fff; border-radius:14px; overflow:hidden; border:1px solid #e8eaed; }
.duel-table th { padding:12px 16px; text-align:left; font-weight:700; color:#6b7280; font-size:11px; text-transform:uppercase; letter-spacing:.05em; background:#f9fafb; border-bottom:1px solid #e8eaed; }
.duel-table td { padding:12px 16px; border-bottom:1px solid #f5f6f8; color:#111827; }
.duel-table tr:last-child td { border-bottom:none; }
.duel-table tr:hover td { background:#f8fffe; }
.d-pill { display:inline-block; padding:2px 9px; border-radius:7px; font-size:11px; font-weight:700; }
</style>

<div class="page-header">
    <div class="page-header-left">
        <h2>Daftar Kuis</h2>
        <p id="pageSubtitle">Memuat data...</p>
    </div>
    <a href="/kuis/buat" class="btn btn-primary" style="border-radius:99px">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Buat Kuis Baru
    </a>
</div>

<div class="tab-nav">
    <button class="tab-btn active" id="tab-kuis" onclick="switchTab('kuis')">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
        Daftar Kuis
    </button>
    <button class="tab-btn" id="tab-duel" onclick="switchTab('duel')">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
        Histori Duel
    </button>
</div>

<div id="toolbarPanel">
    <div class="toolbar">
        <div class="search-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            <input type="text" class="search-input" id="searchInput" placeholder="Cari judul kuis..." oninput="applyFilters()">
        </div>
        <select class="filter-select" id="filterMatkul" onchange="applyFilters()" style="display:none">
            <option value="">Semua Matkul</option>
        </select>
        <div class="pills">
            <button class="pill active" data-status="">Semua</button>
            <button class="pill p-aktif"          data-status="aktif">Aktif</button>
            <button class="pill p-belum_mulai"    data-status="belum_mulai">Belum Mulai</button>
            <button class="pill p-sudah_selesai"  data-status="sudah_selesai">Selesai</button>
            <button class="pill p-nonaktif"       data-status="nonaktif">Nonaktif</button>
        </div>
        <span class="count-badge" id="countBadge">Memuat...</span>
    </div>
</div>

<div id="kuisPanel">
    <div class="kuis-grid" id="kuisGrid">
        @for($i=0;$i<6;$i++)
        <div class="skeleton-card">
            <div style="height:4px" class="skeleton"></div>
            <div style="padding:18px">
                <div class="skeleton" style="height:14px;width:75%;margin-bottom:8px"></div>
                <div class="skeleton" style="height:12px;width:90%;margin-bottom:4px"></div>
                <div class="skeleton" style="height:12px;width:60%;margin-bottom:14px"></div>
                <div style="display:flex;gap:6px">
                    <div class="skeleton" style="height:24px;width:90px;border-radius:8px"></div>
                    <div class="skeleton" style="height:24px;width:70px;border-radius:8px"></div>
                </div>
            </div>
            <div style="border-top:1px solid #f5f6f8;padding:11px 18px;display:flex;gap:6px;background:#fafafa">
                <div class="skeleton" style="height:32px;flex:1;border-radius:8px"></div>
                <div class="skeleton" style="height:32px;flex:1;border-radius:8px"></div>
                <div class="skeleton" style="height:32px;width:36px;border-radius:8px"></div>
            </div>
        </div>
        @endfor
    </div>
</div>

<div id="duelPanel" style="display:none">
    <div id="duelHistoryTable">
        <div style="padding:40px;text-align:center;color:#9ca3af">Memuat histori duel...</div>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    const API   = window.apiBaseUrl;
    const token = window.token;
    const role  = window.role || 'dosen';
    const user  = window.user;
    const isAdmin = role === 'admin' || role === 'superadmin';

    const $ = id => document.getElementById(id);
    let allQuizzes = [], courseMap = {}, activeStatus = '';
    let toggleInFlight = new Set(), duelInFlight = new Set();
    let duelHistoryLoaded = false;

    function esc(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
    function escJs(s){ return String(s||'').replace(/\\/g,'\\\\').replace(/'/g,"\\'").replace(/"/g,'\\"'); }
    function fmtDate(d){ if(!d)return'—'; const dt=new Date(d); if(isNaN(dt))return d; return dt.toLocaleString('id-ID',{day:'2-digit',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'}); }

    // SVG icons inline
    const ICO = {
        book:  `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>`,
        check: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>`,
        clock: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>`,
        cal:   `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>`,
        warn:  `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.46 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>`,
        inbox: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>`,
        edit:  `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>`,
        lb:    `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>`,
        trash: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>`,
        dot:   `<svg viewBox="0 0 8 8"><circle cx="4" cy="4" r="3" fill="currentColor"/></svg>`,
    };

    function statusInfo(s) {
        return {
            aktif:         { cls:'s-aktif',         strip:'strip-aktif',         label:'Aktif' },
            nonaktif:      { cls:'s-nonaktif',       strip:'strip-nonaktif',      label:'Nonaktif' },
            belum_mulai:   { cls:'s-belum_mulai',    strip:'strip-belum_mulai',   label:'Belum Mulai' },
            sudah_selesai: { cls:'s-sudah_selesai',  strip:'strip-sudah_selesai', label:'Selesai' },
        }[s] || { cls:'s-nonaktif', strip:'strip-nonaktif', label:s };
    }

    window.switchTab = function(tab) {
        const kp=$('kuisPanel'),dp=$('duelPanel'),tp=$('toolbarPanel');
        const tk=$('tab-kuis'),td=$('tab-duel');
        if (tab==='kuis') {
            kp.style.display=''; dp.style.display='none'; tp.style.display='';
            tk.classList.add('active'); td.classList.remove('active');
        } else {
            kp.style.display='none'; dp.style.display=''; tp.style.display='none';
            td.classList.add('active'); tk.classList.remove('active');
            fetchDuelHistory();
        }
    };

    async function fetchDuelHistory() {
        if (duelHistoryLoaded) return;
        const el = $('duelHistoryTable');
        try {
            const res  = await fetch(API+'/admin/duels/history',{headers:{Authorization:'Bearer '+token,Accept:'application/json'}});
            const data = await res.json();
            const list = data.data||[];
            duelHistoryLoaded = true;
            if (!list.length) {
                el.innerHTML=`<div style="padding:60px;text-align:center;background:#fff;border-radius:14px;border:1px solid #eff0f2">
                    <div style="width:56px;height:56px;border-radius:16px;background:#f5f6f8;display:flex;align-items:center;justify-content:center;margin:0 auto 14px">${ICO.inbox}</div>
                    <div style="font-weight:700;font-size:15px;color:#374151;margin-bottom:6px">Belum ada histori duel</div>
                    <div style="font-size:13px;color:#9ca3af">Aktifkan kuis untuk duel agar mahasiswa bisa bertanding</div>
                </div>`;
                return;
            }
            const pill=(s)=>{
                const map={pending:'background:#fef3c7;color:#d97706',active:'background:#dbeafe;color:#2563eb',completed:'background:#d1fae5;color:#059669',expired:'background:#f1f5f9;color:#64748b',declined:'background:#fee2e2;color:#dc2626',cancelled:'background:#f1f5f9;color:#64748b'};
                const lbl={pending:'Menunggu',active:'Berlangsung',completed:'Selesai',expired:'Kedaluwarsa',declined:'Ditolak',cancelled:'Dibatalkan'};
                return `<span class="d-pill" style="${map[s]||'background:#f1f5f9;color:#64748b'}">${lbl[s]||s}</span>`;
            };
            el.innerHTML=`<table class="duel-table">
                <thead><tr><th>Quiz</th><th>Penantang</th><th>Lawan</th><th style="text-align:center">Skor</th><th>Pemenang</th><th style="text-align:center">Status</th><th>Tanggal</th></tr></thead>
                <tbody>${list.map(d=>`<tr>
                    <td style="font-weight:600">${esc(d.quiz_title)}</td>
                    <td><div style="font-weight:600">${esc(d.challenger_name)}</div><div style="font-size:11px;color:#9ca3af">${esc(d.challenger_nim)}</div></td>
                    <td><div style="font-weight:600">${esc(d.opponent_name)}</div><div style="font-size:11px;color:#9ca3af">${esc(d.opponent_nim)}</div></td>
                    <td style="text-align:center">${d.challenger_score!==null&&d.opponent_score!==null?`<b style="color:#279685">${d.challenger_score}</b><span style="color:#9ca3af;margin:0 4px">vs</span><b style="color:#279685">${d.opponent_score}</b>`:'<span style="color:#9ca3af">—</span>'}</td>
                    <td style="font-weight:600;color:#059669">${d.winner_name?esc(d.winner_name):'<span style="color:#9ca3af">—</span>'}</td>
                    <td style="text-align:center">${pill(d.status)}</td>
                    <td style="color:#9ca3af;font-size:12px">${d.created_at?new Date(d.created_at).toLocaleDateString('id-ID',{day:'2-digit',month:'short',year:'numeric'}):'—'}</td>
                </tr>`).join('')}</tbody>
            </table>`;
        } catch(e) {
            el.innerHTML=`<div style="padding:20px;text-align:center;color:#ef4444;background:#fff;border-radius:14px;border:1px solid #eff0f2">Gagal memuat histori. <button onclick="duelHistoryLoaded=false;fetchDuelHistory()" style="background:#279685;color:#fff;border:none;padding:4px 12px;border-radius:7px;cursor:pointer;font-weight:700;margin-left:8px">Coba lagi</button></div>`;
        }
    }

    window.toggleDuel = async function(id, cb) {
        if (duelInFlight.has(id)){cb.checked=!cb.checked;return;}
        duelInFlight.add(id);
        const v=cb.checked, lbl=$('duel-label-'+id);
        if(lbl) lbl.textContent=v?'Duel Aktif':'Duel Nonaktif';
        try {
            const res=await fetch(`${API}/admin/quizzes/${id}/toggle-duel`,{method:'POST',headers:{Authorization:'Bearer '+token,Accept:'application/json'}});
            if(res.ok){
                const data=await res.json(),actual=data.is_duel_enabled??v;
                cb.checked=actual; if(lbl) lbl.textContent=actual?'Duel Aktif':'Duel Nonaktif';
                const idx=allQuizzes.findIndex(q=>(q._id||q.id)==id);
                if(idx>-1) allQuizzes[idx].is_duel_enabled=actual;
                duelHistoryLoaded=false;
                toast(actual?'Kuis diaktifkan untuk duel':'Kuis dinonaktifkan dari duel');
            } else {
                cb.checked=!v; if(lbl) lbl.textContent=!v?'Duel Aktif':'Duel Nonaktif';
                toast('Gagal mengubah status duel.','err');
            }
        } catch(_){ cb.checked=!v; if(lbl) lbl.textContent=!v?'Duel Aktif':'Duel Nonaktif'; toast('Koneksi bermasalah.','err'); }
        finally { duelInFlight.delete(id); }
    };

    async function fetchCourses() {
        if (!isAdmin) return;
        try {
            const res=await fetch(API+'/courses',{headers:{Authorization:'Bearer '+token,Accept:'application/json'}});
            const list=(await res.json()).data||[];
            list.forEach(c=>{courseMap[c._id||c.id]=c.title;});
            const sel=$('filterMatkul'); sel.style.display='';
            list.forEach(c=>{const o=document.createElement('option');o.value=c._id||c.id;o.textContent=c.title;sel.appendChild(o);});
        } catch(_){}
    }

    async function fetchQuizzes() {
        try {
            const cf=isAdmin?($('filterMatkul').value||''):'';
            const url=API+'/admin/quizzes'+(cf?`?course_id=${cf}`:'');
            const res=await fetch(url,{headers:{Authorization:'Bearer '+token,Accept:'application/json'}});
            if(res.status===401){window.logout();return;}
            const data=await res.json();
            allQuizzes=data.data||[];
            $('pageSubtitle').textContent=allQuizzes.length+' kuis tersedia';
            applyFilters();
        } catch(e) {
            $('kuisGrid').innerHTML=`<div class="empty-state"><div class="empty-icon">${ICO.warn}</div><div class="empty-title">Gagal memuat data</div><div class="empty-sub">${esc(e.message)}</div></div>`;
        }
    }

    window.applyFilters = function() {
        const q=$('searchInput').value.trim().toLowerCase();
        const filtered=allQuizzes.filter(qz=>{
            const mQ=!q||(qz.title||'').toLowerCase().includes(q)||(qz.description||'').toLowerCase().includes(q);
            const mS=!activeStatus||(qz.status||'')===activeStatus;
            return mQ&&mS;
        });
        $('countBadge').innerHTML=`<span>${filtered.length}</span> dari ${allQuizzes.length} kuis`;
        renderGrid(filtered);
    };

    function renderGrid(list) {
        const grid=$('kuisGrid');
        if(!list||!list.length){
            grid.innerHTML=`<div class="empty-state"><div class="empty-icon">${ICO.inbox}</div><div class="empty-title">Belum ada kuis</div><div class="empty-sub">${activeStatus||$('searchInput').value?'Coba ubah filter.':'Klik "Buat Kuis Baru" untuk mulai.'}</div></div>`;
            return;
        }
        grid.innerHTML=list.map(buildCard).join('');
    }

    function buildCard(q) {
        const id=q._id||q.id, status=q.status||(q.is_active?'aktif':'nonaktif'), si=statusInfo(status);
        const title=q.title||'Tanpa Judul', desc=q.description||'', nSoal=q.questions_count??0, durasi=q.duration_minutes??0;
        const isActive=!!q.is_active, isDuel=!!q.is_duel_enabled;
        const courseTitle=q.course?.title||(q.course_id&&courseMap[q.course_id])||null;
        const matkulChip=courseTitle?`<span class="meta-chip chip-matkul">${ICO.book} ${esc(courseTitle)}</span>`:'';
        const soalChip=`<span class="meta-chip chip-soal${nSoal===0?' zero':''}">${ICO.check} ${nSoal} soal</span>`;
        const durasiChip=`<span class="meta-chip chip-durasi">${ICO.clock} ${durasi} mnt</span>`;
        const jadwalChip=(q.start_at||q.end_at)?`<span class="meta-chip chip-jadwal">${ICO.cal} ${esc(fmtDate(q.start_at).split(',')[0])}</span>`:'';
        const duelLbl=isDuel?'Duel Aktif':'Duel Nonaktif';

        return `<div class="quiz-card${isActive?'':' is-inactive'}" id="qcard-${id}">
            <div class="card-strip ${si.strip}" id="strip-${id}"></div>
            <div class="card-body">
                <div class="card-title-row">
                    <div class="card-title"><a href="/kuis/${id}/edit">${esc(title)}</a></div>
                    <span class="status-badge ${si.cls}" id="sbadge-${id}">${ICO.dot} ${si.label}</span>
                </div>
                ${desc?`<div class="card-desc">${esc(desc)}</div>`:'<div style="margin-bottom:12px"></div>'}
                <div class="card-meta">${matkulChip}${soalChip}${durasiChip}${jadwalChip}</div>
                <div class="card-toggle-row">
                    <span class="toggle-label${isActive?' on':''}" id="tlabel-${id}">${isActive?'Aktif — mahasiswa bisa akses':'Nonaktif — tersembunyi'}</span>
                    <label class="toggle-wrap" id="twrap-${id}">
                        <input type="checkbox" ${isActive?'checked':''} onchange="toggleQuiz('${id}',this)">
                        <div class="toggle-track"></div><div class="toggle-thumb"></div>
                    </label>
                </div>
                <div class="card-duel-row">
                    <span style="font-size:12px;font-weight:600;color:${isDuel?'#279685':'#9ca3af'}" id="duel-label-${id}">${duelLbl}</span>
                    <label class="duel-toggle-wrap">
                        <input type="checkbox" ${isDuel?'checked':''} onchange="toggleDuel('${id}',this)">
                        <div class="duel-track"></div><div class="duel-thumb"></div>
                    </label>
                </div>
            </div>
            <div class="card-footer">
                <a class="btn-act ba-soal" href="/kuis/${id}/soal">
                    ${ICO.check} Kelola Soal
                </a>
                <a class="btn-act ba-edit" href="/kuis/${id}/edit">${ICO.edit} Edit</a>
                <a class="btn-act ba-lb" href="/kuis/${id}/leaderboard">${ICO.lb}</a>
                <button class="btn-act ba-del" onclick="hapusQuiz('${id}','${escJs(title)}')">${ICO.trash}</button>
            </div>
        </div>`;
    }

    window.toggleQuiz = async function(id, cb) {
        if(toggleInFlight.has(id)){cb.checked=!cb.checked;return;}
        const newActive=cb.checked; toggleInFlight.add(id);
        updateCardVisual(id,newActive);
        const wrap=$(`twrap-${id}`); if(wrap) wrap.classList.add('loading');
        try {
            const res=await fetch(`${API}/admin/quizzes/${id}/toggle`,{method:'POST',headers:{Authorization:'Bearer '+token,Accept:'application/json'}});
            if(res.ok){
                const data=await res.json(),actual=data.data?.is_active??newActive;
                const idx=allQuizzes.findIndex(q=>(q._id||q.id)==id);
                if(idx>-1){allQuizzes[idx].is_active=actual;allQuizzes[idx].status=actual?'aktif':(allQuizzes[idx].status==='aktif'?'nonaktif':allQuizzes[idx].status);}
                if(actual!==newActive){updateCardVisual(id,actual);cb.checked=actual;}
                toast(actual?'Kuis diaktifkan':'Kuis dinonaktifkan');
            } else { updateCardVisual(id,!newActive); cb.checked=!newActive; toast('Gagal mengubah status.','err'); }
        } catch(_){ updateCardVisual(id,!newActive); cb.checked=!newActive; toast('Koneksi bermasalah.','err'); }
        finally { toggleInFlight.delete(id); if(wrap) wrap.classList.remove('loading'); }
    };

    function updateCardVisual(id,isActive) {
        const card=$(`qcard-${id}`),strip=$(`strip-${id}`),badge=$(`sbadge-${id}`),lbl=$(`tlabel-${id}`);
        if(!card)return;
        const si=statusInfo(isActive?'aktif':'nonaktif');
        card.classList.toggle('is-inactive',!isActive);
        if(strip) strip.className='card-strip '+si.strip;
        if(badge){badge.className='status-badge '+si.cls;badge.innerHTML=ICO.dot+' '+si.label;}
        if(lbl){lbl.className='toggle-label'+(isActive?' on':'');lbl.textContent=isActive?'Aktif — mahasiswa bisa akses':'Nonaktif — tersembunyi';}
    }

    window.hapusQuiz = function(id, title) {
        showDialog({
            icon:'err', title:'Hapus Kuis',
            msg:`Hapus "${title}"? Semua soal di dalamnya juga akan terhapus.`,
            confirmText:'Hapus', confirmClass:'confirm-err',
            onConfirm: () => doHapusQuiz(id)
        });
    };
    window.doHapusQuiz = async function(id) {
        try {
            const res=await fetch(`${API}/admin/quizzes/${id}`,{method:'DELETE',headers:{Authorization:'Bearer '+token,Accept:'application/json'}});
            if(res.ok){
                const card=$(`qcard-${id}`);
                if(card){card.style.transition='opacity .25s,transform .25s';card.style.opacity='0';card.style.transform='scale(.95)';setTimeout(()=>{allQuizzes=allQuizzes.filter(q=>(q._id||q.id)!=id);applyFilters();},260);}
                toast('Kuis berhasil dihapus.');
            } else { const err=await res.json().catch(()=>({})); toast(err.message||'Gagal menghapus.','err'); }
        } catch(_){ toast('Koneksi bermasalah.','err'); }
    };

    document.querySelectorAll('.pill').forEach(p=>{
        p.addEventListener('click',()=>{
            document.querySelectorAll('.pill').forEach(x=>x.classList.remove('active'));
            p.classList.add('active'); activeStatus=p.dataset.status; applyFilters();
        });
    });

    if(isAdmin) $('filterMatkul').addEventListener('change',fetchQuizzes);

    async function init(){ await fetchCourses(); await fetchQuizzes(); }
    init();
    window.fetchQuizzes=fetchQuizzes;
})();
</script>
@endpush