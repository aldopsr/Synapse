@extends('layouts.app')
@section('title') Daftar Mahasiswa - Synapse @endsection
@section('header_title') Daftar Mahasiswa @endsection

@section('content')
<style>
.page-top { display:flex; align-items:center; gap:12px; margin-bottom:24px; flex-wrap:wrap; }
.page-top h2 { font-size:20px; font-weight:800; color:#111827; margin:0; letter-spacing:-.3px; }
.page-top p  { font-size:13px; color:#9ca3af; margin:4px 0 0; }

.filter-bar { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:20px; align-items:center; }
.filter-bar input, .filter-bar select { padding:9px 14px; border:1.5px solid #e8eaed; border-radius:9px; font-size:13px; font-family:inherit; color:#111827; background:#fff; outline:none; transition:border-color .15s; }
.filter-bar input:focus, .filter-bar select:focus { border-color:#279685; }
.filter-bar input { flex:1; min-width:200px; }

/* Search icon wrapper */
.search-wrap { position:relative; flex:1; min-width:200px; }
.search-wrap svg { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#c4c8d0; pointer-events:none; width:14px; height:14px; }
.search-wrap input { padding-left:36px; width:100%; }

.stats-row { display:grid; grid-template-columns:repeat(auto-fit,minmax(140px,1fr)); gap:12px; margin-bottom:24px; }
.stat-card { background:#fff; border:1px solid #eff0f2; border-radius:14px; padding:16px 18px; }
.stat-card .num { font-size:26px; font-weight:800; color:#279685; }
.stat-card .lbl { font-size:11px; color:#9ca3af; margin-top:4px; }

.table-wrap { background:#fff; border:1px solid #eff0f2; border-radius:14px; overflow:hidden; }
table { width:100%; border-collapse:collapse; }
thead tr { background:#fafafa; }
thead th { padding:11px 16px; font-size:11px; font-weight:700; color:#9ca3af; text-transform:uppercase; letter-spacing:.05em; text-align:left; border-bottom:1px solid #eff0f2; }
tbody tr { border-bottom:1px solid #f5f6f8; transition:background .1s; }
tbody tr:last-child { border-bottom:none; }
tbody tr:hover { background:#f8fffe; }
tbody td { padding:12px 16px; font-size:13px; color:#111827; vertical-align:middle; }

.nim-badge { display:inline-flex; align-items:center; gap:5px; padding:3px 9px; border-radius:6px; font-size:11px; font-weight:600; font-family:monospace; background:#f0fdf9; color:#0f766e; border:1px solid #ccfbf1; }
.chip-angkatan { display:inline-block; padding:2px 9px; border-radius:99px; font-size:11px; font-weight:600; background:#fef3c7; color:#92400e; border:1px solid #fde68a; }
.score-pill { display:inline-block; padding:3px 10px; border-radius:8px; font-size:12px; font-weight:700; }
.score-pass { background:#d1fae5; color:#15803d; }
.score-fail { background:#fee2e2; color:#b91c1c; }
.score-none { background:#f5f6f8; color:#9ca3af; }

/* Action buttons */
.action-btns { display:flex; gap:5px; align-items:center; flex-wrap:wrap; }
.btn-act { display:inline-flex; align-items:center; gap:4px; padding:5px 10px; border-radius:7px; font-size:11px; font-weight:700; border:1.5px solid var(--b,#e5e7eb); background:var(--bg,#fff); color:var(--c,#374151); cursor:pointer; transition:background .12s,border-color .12s; font-family:inherit; white-space:nowrap; }
.btn-act svg { width:12px; height:12px; flex-shrink:0; }
.btn-act:hover { background:var(--bgh,#f5f6f8); border-color:var(--bh,#d1d5db); }
.ba-detail { --bg:#fff; --c:#374151; --b:#e5e7eb; --bgh:#f5f6f8; --bh:#279685; }
.ba-detail:hover { color:#279685; }
.ba-detail.open { --bg:#f0fdf9; --c:#0f6e56; --b:#279685; }
.ba-export { --bg:#f0fdf9; --c:#0f6e56; --b:#b2e8e0; --bgh:#dcfaf5; --bh:#279685; }
.ba-edit   { --bg:#fffbeb; --c:#854d0e; --b:#fde68a; --bgh:#fef9c3; --bh:#fcd34d; }
.ba-del    { --bg:#fef2f2; --c:#dc2626; --b:#fecaca; --bgh:#fee2e2; --bh:#fca5a5; }

/* Detail row */
.detail-row { display:none; }
.detail-row.open { display:table-row; }
.detail-cell { padding:0 16px 16px 40px; background:#fafafa; }
.attempt-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(180px,1fr)); gap:10px; padding-top:12px; }
.attempt-card { background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:12px 14px; }
.attempt-card .ac-title { font-size:12px; font-weight:700; color:#111827; margin-bottom:6px; }
.attempt-card .ac-score { font-size:20px; font-weight:700; margin-bottom:4px; }
.attempt-card .ac-meta  { font-size:11px; color:#9ca3af; }

/* Group header */
.group-header td { background:#f5f6f8; font-size:11px; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:.05em; padding:8px 16px; border-top:1px solid #e5e7eb; }

/* Empty */
.empty-state { text-align:center; padding:60px 20px; }
.empty-ico { width:56px; height:56px; border-radius:16px; background:#f5f6f8; display:flex; align-items:center; justify-content:center; margin:0 auto 14px; }
.empty-ico svg { width:28px; height:28px; color:#d1d5db; }
.empty-el { font-size:14px; font-weight:700; color:#374151; margin-bottom:5px; }
.empty-es { font-size:12px; color:#9ca3af; }

/* Skeleton */
.skeleton { background:linear-gradient(90deg,#f5f5f5 25%,#eee 50%,#f5f5f5 75%); background-size:200% 100%; animation:shimmer 1.2s infinite; border-radius:6px; }
@keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }

/* Modal */
.modal-overlay-mhs { display:none; position:fixed; inset:0; background:rgba(15,23,42,.4); backdrop-filter:blur(3px); z-index:1000; align-items:center; justify-content:center; }
.modal-overlay-mhs.open { display:flex; }
.modal-box-mhs { background:#fff; border-radius:20px; width:100%; max-width:460px; margin:20px; overflow:hidden; box-shadow:0 24px 64px rgba(0,0,0,.16); animation:dlgUp .2s cubic-bezier(.34,1.4,.64,1); }
@keyframes dlgUp { from{opacity:0;transform:translateY(20px) scale(.97)} to{opacity:1;transform:none} }
.modal-head-mhs { padding:20px 24px 16px; border-bottom:1px solid #f5f6f8; display:flex; align-items:center; justify-content:space-between; }
.modal-head-mhs h3 { font-size:15px; font-weight:800; color:#111827; margin:0; }
.modal-close-mhs { width:32px; height:32px; border:none; background:#f5f6f8; border-radius:9px; cursor:pointer; display:flex; align-items:center; justify-content:center; color:#6b7280; }
.modal-close-mhs:hover { background:#e5e7eb; color:#111827; }
.modal-close-mhs svg { width:14px; height:14px; }
.modal-body-mhs { padding:20px 24px; }
.fg-mhs { margin-bottom:15px; }
.fg-mhs label { display:block; font-size:11px; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:.05em; margin-bottom:6px; }
.fg-mhs input { width:100%; padding:10px 12px; border:1.5px solid #e5e7eb; border-radius:9px; font-size:13px; font-family:inherit; box-sizing:border-box; outline:none; transition:border-color .15s; }
.fg-mhs input:focus { border-color:#279685; box-shadow:0 0 0 3px rgba(39,150,133,.1); }
.modal-foot-mhs { padding:14px 24px; border-top:1px solid #f5f6f8; display:flex; justify-content:flex-end; gap:10px; background:#fafafa; }
.btn-cancel-mhs { padding:9px 18px; border:1.5px solid #e5e7eb; border-radius:9px; background:#fff; font-size:13px; font-weight:600; cursor:pointer; font-family:inherit; }
.btn-cancel-mhs:hover { background:#f5f6f8; }
.btn-save-mhs   { padding:9px 18px; border:none; border-radius:9px; background:#279685; color:#fff; font-size:13px; font-weight:700; cursor:pointer; font-family:inherit; }
.btn-save-mhs:hover { background:#1c6e60; }
.btn-save-mhs:disabled { background:#9ca3af; cursor:not-allowed; }

@media (max-width: 768px) {
    .page-top { flex-direction:column; align-items:flex-start; gap:6px; }
    .filter-bar { flex-direction:column; }
    .filter-bar input, .filter-bar select { width:100%; }
    .search-wrap { width:100%; min-width:0; }
    .table-wrap { overflow-x:auto; -webkit-overflow-scrolling:touch; }
    table { min-width:580px; }
    .stats-row { grid-template-columns:repeat(2,1fr); }
}
</style>

<div class="page-top">
    <div>
        <h2>Data Mahasiswa</h2>
        <p id="pageSubtitle">Memuat data...</p>
    </div>
    <div style="margin-left:auto;display:flex;gap:8px">
        <button onclick="exportAllCSV()" class="btn-act ba-export">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Export Semua
        </button>
    </div>
</div>

<div class="stats-row" id="statsRow">
    <div class="stat-card"><div class="skeleton" style="height:26px;width:60px;margin-bottom:6px"></div><div class="lbl">Total Peserta</div></div>
    <div class="stat-card"><div class="skeleton" style="height:26px;width:60px;margin-bottom:6px"></div><div class="lbl">Rata-rata Skor</div></div>
    <div class="stat-card"><div class="skeleton" style="height:26px;width:60px;margin-bottom:6px"></div><div class="lbl">Rata-rata ≥ 70</div></div>
    <div class="stat-card"><div class="skeleton" style="height:26px;width:60px;margin-bottom:6px"></div><div class="lbl">Angkatan</div></div>
</div>

<div class="filter-bar">
    <div class="search-wrap">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        <input type="text" id="searchInput" placeholder="Cari nama, NIM, atau email..." oninput="applyFilter()" class="fc" style="border:1.5px solid #e8eaed;border-radius:9px;padding:9px 14px 9px 36px">
    </div>
    <select id="filterAngkatan" onchange="applyFilter()" style="padding:9px 14px;border:1.5px solid #e8eaed;border-radius:9px;font-size:13px;font-family:inherit;color:#111827;background:#fff;outline:none">
        <option value="">Semua Angkatan</option>
    </select>
    <select id="filterFakultas" onchange="applyFilter()" style="padding:9px 14px;border:1.5px solid #e8eaed;border-radius:9px;font-size:13px;font-family:inherit;color:#111827;background:#fff;outline:none">
        <option value="">Semua Fakultas</option>
    </select>
</div>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Mahasiswa</th>
                <th>NIM</th>
                <th>Angkatan</th>
                <th>Kelas</th>
                <th>Quiz</th>
                <th>Avg Skor</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody id="tableBody">
            <tr><td colspan="8" style="text-align:center;padding:40px">
                <div class="skeleton" style="height:14px;width:60%;margin:0 auto 10px"></div>
                <div class="skeleton" style="height:14px;width:40%;margin:0 auto"></div>
            </td></tr>
        </tbody>
    </table>
</div>

{{-- Modal Edit --}}
<div class="modal-overlay-mhs" id="modalEditMhs">
    <div class="modal-box-mhs">
        <div class="modal-head-mhs">
            <h3>Edit Data Mahasiswa</h3>
            <button class="modal-close-mhs" onclick="tutupModalMhs()">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body-mhs">
            <input type="hidden" id="editMhsId">
            <div class="fg-mhs"><label>Nama Lengkap</label><input type="text" id="editMhsName" placeholder="Nama mahasiswa"></div>
            <div class="fg-mhs"><label>Email</label><input type="email" id="editMhsEmail" placeholder="email@example.com"></div>
            <div class="fg-mhs"><label>NIM</label><input type="text" id="editMhsNim" placeholder="NIM"></div>
            <div class="fg-mhs"><label>Kelas</label><input type="text" id="editMhsKelas" placeholder="Kelas"></div>
            <div class="fg-mhs">
                <label>Password Baru <span style="color:#9ca3af;font-weight:400">(kosongkan jika tidak diubah)</span></label>
                <input type="password" id="editMhsPassword" placeholder="Min. 8 karakter">
            </div>
        </div>
        <div class="modal-foot-mhs">
            <button class="btn-cancel-mhs" onclick="tutupModalMhs()">Batal</button>
            <button class="btn-save-mhs" id="btnSaveMhs" onclick="simpanEditMhs()">Simpan</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
    const API=window.apiBaseUrl, token=window.token, role=window.role||'dosen';
    const isAdmin=role==='admin'||role==='superadmin';
    const $=id=>document.getElementById(id);
    let allData=[], filtered=[];

    function esc(s){return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');}

    const SVG={
        chart:`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>`,
        chartX:`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/><line x1="2" y1="2" x2="22" y2="22"/></svg>`,
        dl:`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>`,
        edit:`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>`,
        trash:`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>`,
        inbox:`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>`,
        check:`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>`,
        x:`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>`,
    };

    async function fetchData(){
        const endpoint=isAdmin?`${API}/student-data/all`:`${API}/student-data/quiz-participants`;
        try {
            const res=await fetch(endpoint,{headers:{Authorization:'Bearer '+token,Accept:'application/json'}});
            if(res.status===401){window.logout();return;}
            const data=await res.json();
            allData=data.data||[];
            buildFilterOptions(allData);renderStats(allData);
            $('pageSubtitle').textContent=`${allData.length} mahasiswa ${isAdmin?'terdaftar':'mengerjakan quiz'}`;
            applyFilter();
        } catch(e){
            $('tableBody').innerHTML=`<tr><td colspan="8"><div class="empty-state"><div class="empty-ico">${SVG.inbox}</div><div class="empty-el">Gagal memuat data</div><p><button onclick="fetchData()" style="color:#279685;background:none;border:none;cursor:pointer;font-weight:700;font-family:inherit">Coba lagi</button></p></div></td></tr>`;
        }
    }

    function buildFilterOptions(data){
        const aSet=new Set(),fSet=new Set();
        data.forEach(s=>{if(s.nim_info?.angkatan)aSet.add(s.nim_info.angkatan);if(s.nim_info?.sekolah)fSet.add(s.nim_info.sekolah);});
        const ae=$('filterAngkatan'),fe=$('filterFakultas');
        ae.innerHTML='<option value="">Semua Angkatan</option>';fe.innerHTML='<option value="">Semua Fakultas</option>';
        [...aSet].sort().forEach(a=>ae.innerHTML+=`<option value="${a}">${a}</option>`);
        [...fSet].sort().forEach(f=>fe.innerHTML+=`<option value="${esc(f)}">${esc(f)}</option>`);
    }

    function renderStats(data){
        const total=data.length,avgArr=data.map(d=>d.avg_score).filter(s=>s!==null&&s!==undefined);
        const avg=avgArr.length>0?(avgArr.reduce((a,b)=>a+b,0)/avgArr.length).toFixed(1):'-';
        const lulus=data.filter(d=>d.avg_score!==null&&d.avg_score>=70).length;
        const angkSz=new Set(data.map(d=>d.nim_info?.angkatan).filter(Boolean)).size;
        $('statsRow').innerHTML=`
            <div class="stat-card"><div class="num">${total}</div><div class="lbl">Total Peserta</div></div>
            <div class="stat-card"><div class="num">${avg}</div><div class="lbl">Rata-rata Skor</div></div>
            <div class="stat-card"><div class="num">${lulus}</div><div class="lbl">Rata-rata ≥ 70</div></div>
            <div class="stat-card"><div class="num">${angkSz}</div><div class="lbl">Angkatan</div></div>`;
    }

    window.applyFilter=function(){
        const q=$('searchInput').value.trim().toLowerCase(),angk=$('filterAngkatan').value,fak=$('filterFakultas').value;
        filtered=allData.filter(s=>{
            const mQ=!q||(s.name||'').toLowerCase().includes(q)||(s.nim||'').toLowerCase().includes(q)||(s.email||'').toLowerCase().includes(q);
            const mA=!angk||s.nim_info?.angkatan===angk;
            const mF=!fak||s.nim_info?.sekolah===fak;
            return mQ&&mA&&mF;
        });
        renderTable(filtered);
    };

    let _pagMhs = null;
    let _allMhsData = [];

    function renderTable(data){
        _allMhsData = data || [];
        const tbody=$('tableBody');
        if(!data.length){
            tbody.innerHTML=`<tr><td colspan="8"><div class="empty-state"><div class="empty-ico">${SVG.inbox}</div><div class="empty-label">Belum ada data mahasiswa</div></div></td></tr>`;
            const p=document.getElementById('pag-tableBody'); if(p) p.style.display='none';
            return;
        }

        if(window.Paginator){
            if(!_pagMhs) _pagMhs=window.Paginator('tableBody', data, 25, renderMhsPage);
            else _pagMhs.setData(data);
        } else {
            renderMhsPage(data);
        }
    }

    function renderMhsPage(slice){
        const tbody=$('tableBody');
        const grouped={};
        slice.forEach(s=>{const k=s.nim_info?.angkatan||'Tidak Diketahui';if(!grouped[k])grouped[k]=[];grouped[k].push(s);});
        let html='',counter=1;
        Object.keys(grouped).sort().reverse().forEach(angkatan=>{
            html+=`<tr class="group-header"><td colspan="8">Angkatan ${esc(angkatan)} — ${grouped[angkatan].length} mahasiswa</td></tr>`;
            grouped[angkatan].forEach(s=>{
                const uid=s.user_id,avg=s.avg_score!==null&&s.avg_score!==undefined?s.avg_score:null;
                const scoreEl=avg!==null?`<span class="score-pill ${avg>=70?'score-pass':'score-fail'}">${avg}</span>`:`<span class="score-pill score-none">—</span>`;
                window[`_student_${uid}`]=s;
                if(s.attempts) window[`_attempts_${uid}`]=s.attempts;
                html+=`<tr>
                    <td style="color:#d1d5db;font-size:12px;font-weight:500">${counter++}</td>
                    <td><div style="font-weight:600">${esc(s.name)}</div><div style="font-size:11px;color:#9ca3af">${esc(s.email)}</div></td>
                    <td>${s.nim?`<span class="nim-badge">${esc(s.nim)}</span>`:'<span style="color:#d1d5db">—</span>'}<div style="font-size:10px;color:#9ca3af;margin-top:2px">${esc(s.nim_info?.sekolah||'')}</div></td>
                    <td>${s.nim_info?.angkatan?`<span class="chip-angkatan">${esc(s.nim_info.angkatan)}</span>`:'<span style="color:#d1d5db">—</span>'}</td>
                    <td>${esc(s.kelas||'—')}</td>
                    <td style="font-weight:700">${s.total_quiz_taken??0}</td>
                    <td>${scoreEl}</td>
                    <td><div class="action-btns">
                        <button class="btn-act ba-detail" id="btnDetail-${uid}" onclick="toggleDetail('${uid}')">${SVG.chart} Detail</button>
                        <button class="btn-act ba-export" onclick="exportStudentCSV('${uid}')" title="Export CSV">${SVG.dl}</button>
                        ${isAdmin?`<button class="btn-act ba-edit" onclick="bukaEditMhs('${uid}')">${SVG.edit}</button><button class="btn-act ba-del" onclick="hapusMhs('${uid}','${esc(s.name||'').replace(/'/g,"\'")}')">${SVG.trash}</button>`:''}
                    </div></td>
                </tr>
                <tr class="detail-row" id="detail-${uid}">
                    <td colspan="8" class="detail-cell" id="detailCell-${uid}"></td>
                </tr>`;
            });
        });
        tbody.innerHTML=html;
    }


    window.toggleDetail=function(uid){
        const row=$(`detail-${uid}`),cell=$(`detailCell-${uid}`),btn=$(`btnDetail-${uid}`);
        if(row.classList.toggle('open')){
            btn.classList.add('open');btn.innerHTML=SVG.chartX+' Tutup';
            loadDetailCell(uid,cell);
        } else {
            btn.classList.remove('open');btn.innerHTML=SVG.chart+' Detail';
        }
    };

    async function loadDetailCell(uid,cell){
        // Kalau attempts sudah pernah di-fetch, langsung render
        if(window[`_attempts_fetched_${uid}`]){
            renderDetailCell(uid,cell); return;
        }
        cell.innerHTML='<div style="color:#9ca3af;font-size:12px;padding:12px 0;display:flex;align-items:center;gap:8px"><div style="width:14px;height:14px;border:2px solid #e5e7eb;border-top-color:#279685;border-radius:50%;animation:spin .7s linear infinite;flex-shrink:0"></div>Memuat riwayat...</div>';
        try {
            const res=await fetch(`${API}/student-data/${uid}/detail`,{headers:{Authorization:'Bearer '+token,Accept:'application/json'}});
            if(res.ok){
                const data=await res.json();
                const s=data.data||data;
                // Merge ke object student & simpan attempts
                if(window[`_student_${uid}`]) Object.assign(window[`_student_${uid}`],s);
                window[`_attempts_${uid}`]=s.attempts||[];
                window[`_attempts_fetched_${uid}`]=true;
            } else {
                // Fallback: coba pakai attempts yang mungkin sudah ada di allData
                window[`_attempts_fetched_${uid}`]=true;
            }
        } catch(_){
            window[`_attempts_fetched_${uid}`]=true;
        }
        renderDetailCell(uid,cell);
    }

    function renderDetailCell(uid,cell){
        const s=window[`_student_${uid}`]||{},attempts=window[`_attempts_${uid}`]||[];
        if(!attempts.length){cell.innerHTML='<div style="color:#9ca3af;font-size:12px;padding:12px 0">Belum ada quiz yang dikerjakan.</div>';return;}
        cell.innerHTML=`
            <div style="display:flex;gap:16px;flex-wrap:wrap;padding:12px 0 8px">
                <div><div style="font-size:10px;color:#9ca3af;margin-bottom:3px;text-transform:uppercase;letter-spacing:.05em">Sekolah/Fakultas</div><div style="font-size:12px;font-weight:600">${esc(s.nim_info?.sekolah||'—')}</div><div style="font-size:11px;color:#9ca3af">${esc(s.nim_info?.jenjang||'')}</div></div>
                <div><div style="font-size:10px;color:#9ca3af;margin-bottom:3px;text-transform:uppercase;letter-spacing:.05em">Total Quiz</div><div style="font-size:20px;font-weight:800;color:#279685">${s.total_quiz_taken??0}</div></div>
                <div><div style="font-size:10px;color:#9ca3af;margin-bottom:3px;text-transform:uppercase;letter-spacing:.05em">Rata-rata Skor</div><div style="font-size:20px;font-weight:800;color:${(s.avg_score||0)>=70?'#15803d':'#b91c1c'}">${s.avg_score??'—'}</div></div>
            </div>
            <div class="attempt-grid">
                ${attempts.map(a=>`<div class="attempt-card">
                    <div class="ac-title">${esc(a.quiz_title||'Quiz')}</div>
                    <div class="ac-score" style="color:${(a.score||0)>=70?'#15803d':'#b91c1c'}">${a.score??'—'}</div>
                    <div class="ac-meta">
                        ${a.is_passed?`<span style="color:#15803d;font-weight:700">Lulus</span>`:`<span style="color:#b91c1c;font-weight:700">Tidak Lulus</span>`}
                        &nbsp;·&nbsp;
                        ${a.created_at?new Date(a.created_at).toLocaleDateString('id-ID',{day:'numeric',month:'short',year:'numeric'}):'—'}
                    </div>
                </div>`).join('')}
            </div>`;
    }

    window.exportStudentCSV=function(uid){
        const s=window[`_student_${uid}`],attempts=window[`_attempts_${uid}`]||[];
        if(!s)return;
        const rows=[['=== DATA MAHASISWA ==='],['Nama',s.name],['Email',s.email],['NIM',s.nim||''],['Kelas',s.kelas||''],['Angkatan',s.nim_info?.angkatan||''],['Fakultas',s.nim_info?.sekolah||''],['Jenjang',s.nim_info?.jenjang||''],['Total Quiz',s.total_quiz_taken??0],['Rata-rata Skor',s.avg_score??''],[],...[['=== RIWAYAT QUIZ ==='],['No','Nama Quiz','Skor','Status','Tanggal'],...attempts.map((a,i)=>[i+1,a.quiz_title||'',a.score??'',a.is_passed?'Lulus':'Tidak Lulus',a.created_at?new Date(a.created_at).toLocaleDateString('id-ID'):''])]];
        const csv=rows.map(r=>r.map(c=>`"${String(c).replace(/"/g,'""')}"`).join(',')).join('\n');
        const link=document.createElement('a');
        link.href=URL.createObjectURL(new Blob(['\uFEFF'+csv],{type:'text/csv;charset=utf-8;'}));
        link.download=`riwayat_${(s.name||'mahasiswa').replace(/\s+/g,'_').toLowerCase()}_${new Date().toISOString().split('T')[0]}.csv`;
        link.click();toast(`Berhasil export data ${s.name}`);
    };

    window.exportAllCSV=function(){
        const data=filtered.length>0?filtered:allData;if(!data.length)return;
        const rows=[['Nama','Email','NIM','Kelas','Angkatan','Fakultas','Total Quiz','Rata-rata Skor'],...data.map(s=>[s.name,s.email,s.nim||'',s.kelas||'',s.nim_info?.angkatan||'',s.nim_info?.sekolah||'',s.total_quiz_taken??0,s.avg_score??''])];
        const csv=rows.map(r=>r.map(c=>`"${String(c).replace(/"/g,'""')}"`).join(',')).join('\n');
        const link=document.createElement('a');
        link.href=URL.createObjectURL(new Blob(['\uFEFF'+csv],{type:'text/csv;charset=utf-8;'}));
        link.download=`data_mahasiswa_${new Date().toISOString().split('T')[0]}.csv`;link.click();toast('Export berhasil!');
    };

    window.bukaEditMhs=function(uid){
        const s=allData.find(d=>d.user_id===uid);if(!s)return;
        $('editMhsId').value=uid;$('editMhsName').value=s.name||'';$('editMhsEmail').value=s.email||'';
        $('editMhsNim').value=s.nim||'';$('editMhsKelas').value=s.kelas||'';$('editMhsPassword').value='';
        $('modalEditMhs').classList.add('open');
    };

    window.tutupModalMhs=function(){$('modalEditMhs').classList.remove('open');};

    window.simpanEditMhs=async function(){
        const uid=$('editMhsId').value,name=$('editMhsName').value.trim(),email=$('editMhsEmail').value.trim();
        const nim=$('editMhsNim').value.trim(),kelas=$('editMhsKelas').value.trim(),pwd=$('editMhsPassword').value;
        if(!name||!email){toast('Nama dan email wajib diisi.');return;}
        if(pwd&&pwd.length<8){toast('Password minimal 8 karakter.');return;}
        const body={name,email,nim,kelas};if(pwd)body.password=pwd;
        const btn=$('btnSaveMhs');btn.disabled=true;btn.textContent='Menyimpan...';
        try {
            const res=await fetch(`${API}/student-data/${uid}`,{method:'PUT',headers:{Authorization:'Bearer '+token,Accept:'application/json','Content-Type':'application/json'},body:JSON.stringify(body)});
            const data=await res.json();
            if(res.ok){tutupModalMhs();toast('Data mahasiswa berhasil diperbarui!');await fetchData();}
            else toast(data.message||'Gagal menyimpan.','err');
        } catch(_){toast('Koneksi bermasalah.','err');}
        finally{btn.disabled=false;btn.textContent='Simpan';}
    };

    window.hapusMhs=function(uid,name){
        showDialog({
            icon:'err', title:'Hapus Akun Mahasiswa',
            msg:`Hapus akun "${name}"? Semua data riwayat kuis dan duel juga akan terhapus. Tindakan ini tidak bisa dibatalkan.`,
            confirmText:'Hapus', confirmClass:'confirm-err',
            onConfirm:()=>doHapusMhs(uid,name)
        });
    };

    window.doHapusMhs=async function(uid,name){
        try {
            const res=await fetch(`${API}/student-data/${uid}`,{method:'DELETE',headers:{Authorization:'Bearer '+token,Accept:'application/json'}});
            if(res.ok){toast(`Akun ${name} berhasil dihapus.`);allData=allData.filter(d=>d.user_id!==uid);applyFilter();}
            else{const data=await res.json().catch(()=>({}));toast(data.message||'Gagal menghapus.','err');}
        } catch(_){toast('Koneksi bermasalah.','err');}
    };

    $('modalEditMhs').addEventListener('click',function(e){if(e.target===this)tutupModalMhs();});

    fetchData();
})();
</script>
@endpush