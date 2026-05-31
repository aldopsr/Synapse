@extends('layouts.app')
@section('title','Laporan Kuis - Synapse')
@section('header_title','Laporan Kuis')

@section('content')
<style>
.back-link { display:inline-flex; align-items:center; gap:7px; color:#9ca3af; font-size:13px; font-weight:600; text-decoration:none; margin-bottom:20px; transition:color .15s; }
.back-link:hover { color:#279685; }
.back-link svg { width:15px; height:15px; transition:transform .15s; }
.back-link:hover svg { transform:translateX(-3px); }

/* Banner */
.quiz-banner { background:linear-gradient(135deg,#279685,#1a6b5e); border-radius:16px; padding:20px 28px; margin-bottom:24px; display:flex; align-items:center; justify-content:space-between; gap:16px; flex-wrap:wrap; }
.qb-title { font-size:18px; font-weight:800; color:#fff; margin:0 0 8px; letter-spacing:-.3px; }
.qb-chips { display:flex; gap:7px; flex-wrap:wrap; }
.qb-chip  { padding:4px 12px; border-radius:99px; font-size:11px; font-weight:600; background:rgba(255,255,255,.18); color:#fff; }
.qb-stats { display:flex; gap:20px; flex-shrink:0; }
.qb-stat  { text-align:center; }
.qb-stat .num { font-size:28px; font-weight:800; color:#fff; line-height:1; }
.qb-stat .lbl { font-size:11px; color:rgba(255,255,255,.65); margin-top:3px; }

/* Summary cards */
.summary-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(140px,1fr)); gap:12px; margin-bottom:24px; }
.sum-card { background:#fff; border:1px solid #eff0f2; border-radius:14px; padding:16px 18px; }
.sum-card .num { font-size:24px; font-weight:800; margin-bottom:4px; }
.sum-card .lbl { font-size:11px; color:#9ca3af; }
.num-teal   { color:#279685; }
.num-blue   { color:#4A90E2; }
.num-amber  { color:#d97706; }
.num-red    { color:#dc2626; }
.num-purple { color:#7c3aed; }

/* Toolbar */
.toolbar { display:flex; align-items:center; gap:10px; margin-bottom:16px; flex-wrap:wrap; }
.search-wrap { position:relative; flex:1; min-width:200px; max-width:320px; }
.search-wrap svg { position:absolute; left:11px; top:50%; transform:translateY(-50%); color:#c4c8d0; pointer-events:none; width:14px; height:14px; }
.search-input { width:100%; padding:9px 12px 9px 35px; border:1.5px solid #e8eaed; border-radius:99px; font-size:13px; font-family:inherit; background:#fff; box-sizing:border-box; transition:border-color .15s; }
.search-input:focus { outline:none; border-color:#279685; }
.search-input::placeholder { color:#d1d5db; }

.filter-select { padding:9px 14px; border:1.5px solid #e8eaed; border-radius:99px; font-size:13px; font-family:inherit; background:#fff; color:#374151; cursor:pointer; }

.btn-export { display:inline-flex; align-items:center; gap:6px; padding:9px 16px; border-radius:99px; font-size:13px; font-weight:700; border:1.5px solid #b2e8e0; background:#f0fdf9; color:#0f6e56; cursor:pointer; transition:background .15s; font-family:inherit; }
.btn-export svg { width:14px; height:14px; }
.btn-export:hover { background:#dcfaf5; }

.count-badge { font-size:12px; font-weight:600; color:#9ca3af; margin-left:auto; white-space:nowrap; }
.count-badge span { color:#279685; font-weight:700; }

/* Tab bar */
.tab-bar { display:flex; gap:4px; background:#f5f6f8; border-radius:12px; padding:4px; margin-bottom:20px; width:fit-content; }
.tab-btn { padding:8px 20px; border-radius:9px; font-size:13px; font-weight:600; border:none; background:transparent; color:#9ca3af; cursor:pointer; transition:all .2s; font-family:inherit; display:inline-flex; align-items:center; gap:6px; }
.tab-btn svg { width:14px; height:14px; }
.tab-btn.active { background:#fff; color:#279685; box-shadow:0 2px 8px rgba(0,0,0,.08); }

/* Table */
.table-wrap { background:#fff; border-radius:16px; border:1px solid #eff0f2; overflow:hidden; }
table { width:100%; border-collapse:collapse; }
thead tr { background:#fafafa; }
thead th { padding:11px 16px; font-size:11px; font-weight:700; color:#9ca3af; text-transform:uppercase; letter-spacing:.05em; text-align:left; border-bottom:1px solid #eff0f2; cursor:pointer; white-space:nowrap; }
thead th:hover { color:#279685; }
thead th svg { width:11px; height:11px; margin-left:3px; opacity:.4; }
thead th.sorted svg { opacity:1; color:#279685; }
tbody tr { border-bottom:1px solid #f5f6f8; transition:background .1s; }
tbody tr:last-child { border-bottom:none; }
tbody tr:hover { background:#f8fffe; }
tbody td { padding:12px 16px; font-size:13px; color:#111827; vertical-align:middle; }

.rank-badge { display:inline-flex; align-items:center; justify-content:center; width:28px; height:28px; border-radius:50%; font-size:12px; font-weight:700; }
.r1 { background:#fef3c7; } .r2 { background:#f3f4f6; } .r3 { background:#fff7ed; } .rn { background:#f9fafb; color:#9ca3af; font-size:11px; }

.score-pill { display:inline-block; padding:3px 10px; border-radius:99px; font-size:12px; font-weight:700; }
.sp-pass { background:#d1fae5; color:#15803d; }
.sp-fail { background:#fee2e2; color:#b91c1c; }

.progress-bar-wrap { display:flex; align-items:center; gap:8px; }
.progress-bar { height:6px; background:#f5f6f8; border-radius:99px; flex:1; overflow:hidden; min-width:60px; }
.progress-fill { height:100%; border-radius:99px; background:#279685; transition:width .3s; }
.progress-fill.fail { background:#ef4444; }

/* Empty */
.empty-state { text-align:center; padding:64px 20px; }
.empty-ico { width:60px; height:60px; border-radius:18px; background:#f5f6f8; display:flex; align-items:center; justify-content:center; margin:0 auto 16px; }
.empty-ico svg { width:30px; height:30px; color:#d1d5db; }
.empty-et { font-size:15px; font-weight:700; color:#111827; margin-bottom:8px; }
.empty-es { font-size:13px; color:#9ca3af; }

/* Skeleton */
.skeleton { background:linear-gradient(90deg,#f5f5f5 25%,#eee 50%,#f5f5f5 75%); background-size:200% 100%; animation:shimmer 1.2s infinite; border-radius:6px; }
@keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }

/* Loading */
.lb-loading { padding:48px; text-align:center; color:#9ca3af; }
.lb-spinner { display:inline-block; width:28px; height:28px; border:3px solid #e5e7eb; border-top-color:#279685; border-radius:50%; animation:spin .7s linear infinite; margin-bottom:12px; }
@keyframes spin { to{transform:rotate(360deg)} }
</style>

<a href="/kuis" class="back-link">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
    Kembali ke Daftar Kuis
</a>

{{-- Banner --}}
<div class="quiz-banner">
    <div>
        <div class="qb-title" id="quizTitle">
            <div class="skeleton" style="height:20px;width:220px;background:rgba(255,255,255,.25);border-radius:8px"></div>
        </div>
        <div class="qb-chips" id="quizChips"></div>
    </div>
    <div class="qb-stats">
        <div class="qb-stat"><div class="num" id="statTotal">—</div><div class="lbl">Peserta</div></div>
        <div class="qb-stat"><div class="num" id="statLulus">—</div><div class="lbl">Lulus</div></div>
        <div class="qb-stat"><div class="num" id="statAvg">—</div><div class="lbl">Rata-rata</div></div>
    </div>
</div>

{{-- Summary cards --}}
<div class="summary-grid" id="summaryGrid">
    @for($i=0;$i<5;$i++)
    <div class="sum-card">
        <div class="skeleton" style="height:24px;width:60px;margin-bottom:6px"></div>
        <div class="lbl">Memuat...</div>
    </div>
    @endfor
</div>

{{-- Tab bar --}}
<div class="tab-bar">
    <button class="tab-btn active" id="tabMhs" onclick="switchTab('mahasiswa')">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
        Mahasiswa
    </button>
    <button class="tab-btn" id="tabUmum" onclick="switchTab('umum')">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
        Pengguna Umum
    </button>
</div>

{{-- Toolbar --}}
<div class="toolbar">
    <div class="search-wrap">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        <input type="text" class="search-input" id="searchInput" placeholder="Cari nama atau NIM..." oninput="applyFilter()">
    </div>
    <select class="filter-select" id="filterStatus" onchange="applyFilter()">
        <option value="">Semua Status</option>
        <option value="pass">Lulus</option>
        <option value="fail">Tidak Lulus</option>
    </select>
    <button class="btn-export" onclick="exportCSV()">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
        Export CSV
    </button>
    <span class="count-badge" id="countBadge">Memuat...</span>
</div>

{{-- Table --}}
<div class="table-wrap">
    <div class="lb-loading" id="lbLoading">
        <div class="lb-spinner"></div>
        <div>Memuat data...</div>
    </div>
    <table id="lbTable" style="display:none">
        <thead>
            <tr>
                <th width="56">Rank</th>
                <th onclick="doSort('name')">Nama <svg id="si-name" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"/></svg></th>
                <th>NIM / Info</th>
                <th onclick="doSort('score')">Skor <svg id="si-score" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"/></svg></th>
                <th>Benar / Total</th>
                <th onclick="doSort('time')">Waktu <svg id="si-time" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"/></svg></th>
                <th onclick="doSort('date')">Tanggal <svg id="si-date" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"/></svg></th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody id="lbBody"></tbody>
    </table>
    <div id="lbEmpty" style="display:none"></div>
</div>
@endsection

@push('scripts')
<script>
(function(){
    const API=window.apiBaseUrl, token=window.token;
    const QUIZ='{{ $quiz_id ?? "" }}';
    const $=id=>document.getElementById(id);
    if(!QUIZ){window.location.href='/kuis';return;}

    let allData=[], filtered=[], quizInfo={}, currentTab='mahasiswa';
    let sortCol='score', sortAsc=false; // default: skor tertinggi dulu

    function esc(s){return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');}
    function fmt(s){if(!s)return'—';const m=Math.floor(s/60),sc=s%60;return`${m}:${String(sc).padStart(2,'0')}`;}
    function fmtDate(d){if(!d)return'—';return new Date(d).toLocaleDateString('id-ID',{day:'numeric',month:'short',year:'numeric'});}

    const SVG_INBOX=`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>`;

    const MEDALS=['🥇','🥈','🥉'];

    async function loadQuizInfo(){
        try {
            const res=await fetch(`${API}/admin/quizzes/${QUIZ}`,{headers:{Authorization:'Bearer '+token,Accept:'application/json'}});
            if(!res.ok)return;
            const q=(await res.json()).data;if(!q)return;
            quizInfo=q;
            $('quizTitle').textContent=q.title||'Kuis';
            const chips=[
                q.course?.title?esc(q.course.title):null,
                q.duration_minutes?`${q.duration_minutes} mnt`:null,
                `KKM: ${q.passing_score||70}`,
                q.status?({aktif:'Aktif',nonaktif:'Nonaktif',belum_mulai:'Belum Mulai',sudah_selesai:'Selesai'}[q.status]||q.status):null,
            ].filter(Boolean);
            $('quizChips').innerHTML=chips.map(c=>`<span class="qb-chip">${c}</span>`).join('');
        } catch(_){$('quizTitle').textContent='Kuis';}
    }

    async function loadData(tab){
        $('lbLoading').style.display='block';
        $('lbTable').style.display='none';
        $('lbEmpty').style.display='none';
        try {
            const res=await fetch(`${API}/quizzes/${QUIZ}/leaderboard?tab=${tab}`,{headers:{Authorization:'Bearer '+token,Accept:'application/json'}});
            if(res.status===401){window.logout();return;}
            allData=(await res.json()).data||[];
            $('lbLoading').style.display='none';
            renderSummary(allData);
            applyFilter();
        } catch(e){
            $('lbLoading').style.display='none';
            $('lbEmpty').style.display='block';
            $('lbEmpty').innerHTML=`<div class="empty-state"><div class="empty-ico">${SVG_INBOX}</div><div class="empty-et">Gagal memuat data</div><div class="empty-es">${esc(e.message)}</div><button onclick="loadData('${tab}')" style="margin-top:14px;padding:9px 20px;background:#279685;color:#fff;border:none;border-radius:9px;cursor:pointer;font-weight:600;font-family:inherit">Coba Lagi</button></div>`;
        }
    }

    function renderSummary(data){
        const total=data.length;
        const lulus=data.filter(d=>d.is_passed).length;
        const scores=data.map(d=>d.score??0);
        const avg=total>0?(scores.reduce((a,b)=>a+b,0)/total).toFixed(1):0;
        const highest=total>0?Math.max(...scores):0;
        const lulusPct=total>0?Math.round(lulus/total*100):0;

        $('statTotal').textContent=total;
        $('statLulus').textContent=lulus;
        $('statAvg').textContent=avg;

        $('summaryGrid').innerHTML=`
            <div class="sum-card"><div class="num num-teal">${total}</div><div class="lbl">Total Peserta</div></div>
            <div class="sum-card"><div class="num num-blue">${avg}</div><div class="lbl">Rata-rata Skor</div></div>
            <div class="sum-card"><div class="num num-teal">${lulus}</div><div class="lbl">Lulus</div></div>
            <div class="sum-card"><div class="num num-red">${total-lulus}</div><div class="lbl">Tidak Lulus</div></div>
            <div class="sum-card"><div class="num num-amber">${lulusPct}%</div><div class="lbl">Tingkat Kelulusan</div></div>`;
    }

    window.applyFilter=function(){
        const q=$('searchInput').value.trim().toLowerCase();
        const status=$('filterStatus').value;
        filtered=allData.filter(d=>{
            const user=d.user||{};
            const mQ=!q||(user.name||'').toLowerCase().includes(q)||(user.nim||'').toLowerCase().includes(q);
            const mS=!status||(status==='pass'&&d.is_passed)||(status==='fail'&&!d.is_passed);
            return mQ&&mS;
        });
        filtered=sortData(filtered);
        $('countBadge').innerHTML=`<span>${filtered.length}</span> dari ${allData.length} peserta`;
        renderTable(filtered);
    };

    function sortData(data){
        return[...data].sort((a,b)=>{
            let va,vb;
            if(sortCol==='name'){va=(a.user?.name||'').toLowerCase();vb=(b.user?.name||'').toLowerCase();}
            else if(sortCol==='time'){va=a.time_taken_seconds??9999;vb=b.time_taken_seconds??9999;}
            else if(sortCol==='date'){va=new Date(a.created_at||0).getTime();vb=new Date(b.created_at||0).getTime();}
            else{va=a.score??0;vb=b.score??0;}
            return sortAsc?(va>vb?1:va<vb?-1:0):(va<vb?1:va>vb?-1:0);
        });
    }

    window.doSort=function(col){
        if(sortCol===col)sortAsc=!sortAsc;else{sortCol=col;sortAsc=col==='name';}
        document.querySelectorAll('thead th svg').forEach(el=>{el.style.opacity='.3';el.style.transform='';});
        const si=$('si-'+col);
        if(si){si.style.opacity='1';si.style.color='#279685';si.style.transform=sortAsc?'rotate(180deg)':'';}
        document.querySelectorAll('thead th').forEach(el=>el.classList.remove('sorted'));
        applyFilter();
    };

    function renderTable(list){
        const tbody=$('lbBody');
        if(!list.length){
            $('lbTable').style.display='none';
            $('lbEmpty').style.display='block';
            $('lbEmpty').innerHTML=`<div class="empty-state"><div class="empty-ico">${SVG_INBOX}</div><div class="empty-et">Tidak ada data</div><div class="empty-es">Coba ubah filter pencarian.</div></div>`;
            return;
        }
        $('lbTable').style.display='table';
        $('lbEmpty').style.display='none';
        const total=quizInfo.questions_count??0;
        function _doRender(pg){
            tbody.innerHTML=pg.map((d,idx)=>{
            const rank=idx+1;
            const user=d.user||{};
            const score=d.score??0;
            const correct=d.correct_count??0;
            const totalQ=d.total_questions??total??0;
            const pct=totalQ>0?Math.round(correct/totalQ*100):0;
            const rankHtml=rank<=3?`<span class="rank-badge r${rank}" style="font-size:16px">${MEDALS[rank-1]}</span>`:`<span class="rank-badge rn">${rank}</span>`;
            const nimCell=currentTab==='mahasiswa'
                ?`<div style="font-family:monospace;font-size:12px;color:#6b7280">${esc(user.nim||'—')}</div>${user.kelas?`<div style="font-size:10px;color:#9ca3af">Kelas ${esc(user.kelas)}</div>`:''}`
                :`<div style="font-size:11px;color:#9ca3af">Pengguna Umum</div>`;
            return`<tr>
                <td>${rankHtml}</td>
                <td style="font-weight:600">${esc(user.name||'—')}</td>
                <td>${nimCell}</td>
                <td><span class="score-pill ${d.is_passed?'sp-pass':'sp-fail'}">${score}</span></td>
                <td>
                    <div class="progress-bar-wrap">
                        <div class="progress-bar"><div class="progress-fill ${d.is_passed?'':'fail'}" style="width:${pct}%"></div></div>
                        <span style="font-size:12px;color:#6b7280;white-space:nowrap">${correct}/${totalQ}</span>
                    </div>
                </td>
                <td style="font-size:12px;color:#9ca3af;font-family:monospace">${fmt(d.time_taken_seconds)}</td>
                <td style="font-size:12px;color:#9ca3af">${fmtDate(d.created_at)}</td>
                <td><span class="score-pill ${d.is_passed?'sp-pass':'sp-fail'}">${d.is_passed?'Lulus':'Tidak Lulus'}</span></td>
            </tr>`;
        }).join('');

        // Paginator
        let _lapPag = window._lapPag || null;
        if(window.Paginator){
            if(!_lapPag){ window._lapPag=window.Paginator("lbBody",list,25,_doRender); }
            else { window._lapPag.setData(list); }
        } else { _doRender(list); }
    }
    window.switchTab=function(tab){
        currentTab=tab;
        $('tabMhs').classList.toggle('active',tab==='mahasiswa');
        $('tabUmum').classList.toggle('active',tab==='umum');
        $('searchInput').value='';$('filterStatus').value='';
        loadData(tab);
    };

    window.exportCSV=function(){
        const data=filtered.length>0?filtered:allData;
        if(!data.length){toast('Tidak ada data untuk diekspor.','err');return;}

        const quizTitle=quizInfo.title||'Kuis';
        const rows=[
            ['=== LAPORAN KUIS ==='],
            ['Judul Kuis', quizTitle],
            ['Mata Kuliah', quizInfo.course?.title||''],
            ['Durasi', (quizInfo.duration_minutes||0)+' menit'],
            ['KKM', quizInfo.passing_score||70],
            ['Tanggal Export', new Date().toLocaleDateString('id-ID',{day:'numeric',month:'long',year:'numeric'})],
            [],
            ['=== RINGKASAN ==='],
            ['Total Peserta', allData.length],
            ['Peserta Lulus', allData.filter(d=>d.is_passed).length],
            ['Peserta Tidak Lulus', allData.filter(d=>!d.is_passed).length],
            ['Rata-rata Skor', allData.length>0?(allData.reduce((a,d)=>a+(d.score??0),0)/allData.length).toFixed(1):'—'],
            ['Tingkat Kelulusan', allData.length>0?Math.round(allData.filter(d=>d.is_passed).length/allData.length*100)+'%':'—'],
            [],
            ['=== DATA PESERTA ==='],
            currentTab==='mahasiswa'
                ?['Rank','Nama','NIM','Kelas','Skor','Benar','Total Soal','Waktu (menit)','Status','Tanggal']
                :['Rank','Nama','Skor','Benar','Total Soal','Waktu (menit)','Status','Tanggal'],
            ...data.map((d,i)=>{
                const user=d.user||{};
                const mins=d.time_taken_seconds?Math.ceil(d.time_taken_seconds/60):0;
                const base=[i+1,user.name||'',d.score??'',d.correct_count??'',d.total_questions??'',mins,d.is_passed?'Lulus':'Tidak Lulus',d.created_at?new Date(d.created_at).toLocaleDateString('id-ID'):''];
                return currentTab==='mahasiswa'?[i+1,user.name||'',user.nim||'',user.kelas||'',d.score??'',d.correct_count??'',d.total_questions??'',mins,d.is_passed?'Lulus':'Tidak Lulus',d.created_at?new Date(d.created_at).toLocaleDateString('id-ID'):'']:base;
            })
        ];

        const csv=rows.map(r=>r.map(c=>`"${String(c).replace(/"/g,'""')}"`).join(',')).join('\n');
        const link=document.createElement('a');
        link.href=URL.createObjectURL(new Blob(['\uFEFF'+csv],{type:'text/csv;charset=utf-8;'}));
        link.download=`laporan_${(quizTitle).replace(/\s+/g,'_').toLowerCase()}_${currentTab}_${new Date().toISOString().split('T')[0]}.csv`;
        link.click();
        toast(`Export berhasil — ${data.length} baris data`);
    };

    loadQuizInfo();
    loadData('mahasiswa');
})();
</script>
@endpush