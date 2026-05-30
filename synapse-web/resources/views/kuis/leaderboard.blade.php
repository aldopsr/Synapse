@extends('layouts.app')
@section('title','Leaderboard - Synapse')
@section('header_title','Leaderboard Kuis')

@section('content')
<style>
.back-link { display:inline-flex; align-items:center; gap:7px; color:#9ca3af; font-size:13px; font-weight:600; text-decoration:none; margin-bottom:22px; transition:color .15s; }
.back-link:hover { color:#279685; }
.back-link svg { width:15px; height:15px; transition:transform .15s; }
.back-link:hover svg { transform:translateX(-3px); }

/* Banner */
.quiz-banner { background:linear-gradient(135deg,#279685,#1a6b5e); border-radius:16px; padding:22px 28px; margin-bottom:24px; color:#fff; display:flex; align-items:center; justify-content:space-between; gap:16px; }
.qb-title { font-size:18px; font-weight:800; margin-bottom:8px; letter-spacing:-.3px; }
.qb-chips { display:flex; flex-wrap:wrap; gap:6px; }
.qb-chip  { padding:4px 12px; border-radius:99px; font-size:11px; font-weight:600; background:rgba(255,255,255,.18); color:#fff; }
.qb-num   { font-size:36px; font-weight:800; line-height:1; }
.qb-lbl   { font-size:11px; opacity:.65; margin-top:4px; }

/* Tab bar */
.tab-bar { display:flex; gap:4px; background:#f5f6f8; border-radius:12px; padding:4px; margin-bottom:20px; width:fit-content; }
.tab-btn { padding:9px 22px; border-radius:9px; font-size:13px; font-weight:600; border:none; background:transparent; color:#9ca3af; cursor:pointer; transition:all .2s; font-family:inherit; display:flex; align-items:center; gap:7px; }
.tab-btn svg { width:15px; height:15px; }
.tab-btn.active { background:#fff; color:#279685; box-shadow:0 2px 8px rgba(0,0,0,.08); }

/* Podium */
.podium { display:flex; align-items:flex-end; justify-content:center; gap:12px; margin-bottom:28px; padding:0 20px; }
.podium-item { flex:1; max-width:180px; text-align:center; }
.podium-card { background:#fff; border-radius:16px; padding:16px 12px; border:2px solid #eff0f2; position:relative; transition:transform .2s; }
.podium-card:hover { transform:translateY(-4px); }
.podium-item.rank-1 .podium-card { border-color:#f59e0b; background:linear-gradient(180deg,#fffbeb,#fff 60%); }
.podium-item.rank-2 .podium-card { border-color:#9ca3af; background:linear-gradient(180deg,#f9fafb,#fff 60%); }
.podium-item.rank-3 .podium-card { border-color:#f97316; background:linear-gradient(180deg,#fff7ed,#fff 60%); }

.podium-rank-ico { font-size:26px; margin-bottom:8px; }
.podium-avatar { width:52px; height:52px; border-radius:50%; margin:0 auto 10px; display:flex; align-items:center; justify-content:center; font-size:20px; font-weight:800; color:#fff; }
.podium-item.rank-1 .podium-avatar { background:linear-gradient(135deg,#f59e0b,#d97706); }
.podium-item.rank-2 .podium-avatar { background:linear-gradient(135deg,#9ca3af,#6b7280); }
.podium-item.rank-3 .podium-avatar { background:linear-gradient(135deg,#f97316,#ea580c); }
.podium-name  { font-size:13px; font-weight:700; color:#111827; margin-bottom:4px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.podium-score { font-size:22px; font-weight:800; }
.podium-item.rank-1 .podium-score { color:#d97706; }
.podium-item.rank-2 .podium-score { color:#6b7280; }
.podium-item.rank-3 .podium-score { color:#ea580c; }
.podium-nim   { font-size:10px; color:#9ca3af; margin-top:2px; font-family:monospace; }
.podium-bar   { height:4px; border-radius:4px 4px 0 0; margin-top:10px; }
.podium-item.rank-1 .podium-bar { background:#f59e0b; }
.podium-item.rank-2 .podium-bar { background:#9ca3af; }
.podium-item.rank-3 .podium-bar { background:#f97316; }

/* Table */
.lb-wrap { background:#fff; border-radius:16px; border:1px solid #eff0f2; overflow:hidden; }
table { width:100%; border-collapse:collapse; }
thead tr { background:#fafafa; }
thead th { padding:11px 16px; font-size:11px; font-weight:700; color:#9ca3af; text-transform:uppercase; letter-spacing:.05em; text-align:left; border-bottom:1px solid #eff0f2; }
tbody tr { border-bottom:1px solid #f5f6f8; transition:background .1s; }
tbody tr:last-child { border-bottom:none; }
tbody tr:hover { background:#fafafa; }
tbody tr.is-top3 { background:#fffbf0; }
tbody tr.is-top3:hover { background:#fef3c7; }
tbody td { padding:13px 16px; font-size:13px; color:#111827; vertical-align:middle; }

.rank-badge { display:inline-flex; align-items:center; justify-content:center; width:30px; height:30px; border-radius:50%; font-size:14px; font-weight:700; }
.r1 { background:#fef3c7; } .r2 { background:#f3f4f6; } .r3 { background:#fff7ed; }
.rn { background:#f9fafb; color:#9ca3af; font-size:12px; }

.score-pill { display:inline-block; padding:4px 12px; border-radius:99px; font-size:13px; font-weight:700; }
.score-pass { background:#d1fae5; color:#15803d; }
.score-fail { background:#fee2e2; color:#b91c1c; }

/* Empty */
.empty-lb { text-align:center; padding:64px 20px; }
.empty-ico { width:60px; height:60px; border-radius:18px; background:#f5f6f8; display:flex; align-items:center; justify-content:center; margin:0 auto 16px; }
.empty-ico svg { width:30px; height:30px; color:#d1d5db; }
.empty-et { font-size:16px; font-weight:700; color:#111827; margin-bottom:8px; }
.empty-es { font-size:13px; color:#9ca3af; }

/* Loading */
.lb-loading { padding:48px; text-align:center; color:#9ca3af; }
.lb-spinner { display:inline-block; width:28px; height:28px; border:3px solid #e5e7eb; border-top-color:#279685; border-radius:50%; animation:spin .7s linear infinite; margin-bottom:12px; }
@keyframes spin { to{transform:rotate(360deg)} }

/* Skeleton */
.skeleton { background:linear-gradient(90deg,#f5f5f5 25%,#eee 50%,#f5f5f5 75%); background-size:200% 100%; animation:shimmer 1.2s infinite; border-radius:6px; }
@keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }
</style>

<a href="/kuis" class="back-link">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
    Kembali ke Daftar Kuis
</a>

<div class="quiz-banner">
    <div>
        <div class="qb-title" id="quizTitle">
            <div class="skeleton" style="height:22px;width:200px;background:rgba(255,255,255,.25);border-radius:8px"></div>
        </div>
        <div class="qb-chips" id="quizChips"></div>
    </div>
    <div style="text-align:right;flex-shrink:0">
        <div class="qb-num" id="totalPeserta">—</div>
        <div class="qb-lbl">peserta</div>
    </div>
</div>

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

<div id="podiumWrap" style="display:none">
    <div class="podium" id="podium"></div>
</div>

<div class="lb-wrap">
    <div class="lb-loading" id="lbLoading">
        <div class="lb-spinner"></div>
        <div>Memuat leaderboard...</div>
    </div>
    <table id="lbTable" style="display:none">
        <thead>
            <tr>
                <th width="60">Rank</th>
                <th>Nama</th>
                <th>NIM / Info</th>
                <th>Skor</th>
                <th>Benar</th>
                <th>Waktu</th>
                <th>Tanggal</th>
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
    const API=window.apiBaseUrl, token=window.token, QUIZ='{{ $quiz_id ?? "" }}';
    const $=id=>document.getElementById(id);
    if(!QUIZ){window.location.href='/kuis';return;}
    let currentTab='mahasiswa';

    function esc(s){return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');}

    const SVG_INBOX=`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>`;
    const SVG_GLOBE=`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>`;
    const SVG_GRAD =`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>`;

    async function loadQuizInfo(){
        try {
            const res=await fetch(`${API}/admin/quizzes/${QUIZ}`,{headers:{Authorization:'Bearer '+token,Accept:'application/json'}});
            if(!res.ok)return;
            const q=(await res.json()).data;if(!q)return;
            $('quizTitle').textContent=q.title||'Kuis';
            const chips=[q.course?.title?esc(q.course.title):null,q.duration_minutes?`${q.duration_minutes} mnt`:null,`KKM: ${q.passing_score||70}`].filter(Boolean);
            $('quizChips').innerHTML=chips.map(c=>`<span class="qb-chip">${c}</span>`).join('');
        } catch(_){$('quizTitle').textContent='Kuis';}
    }

    async function loadLeaderboard(tab){
        $('lbLoading').style.display='block';
        $('lbTable').style.display='none';
        $('lbEmpty').style.display='none';
        $('podiumWrap').style.display='none';
        try {
            const res=await fetch(`${API}/quizzes/${QUIZ}/leaderboard?tab=${tab}`,{headers:{Authorization:'Bearer '+token,Accept:'application/json'}});
            if(res.status===401){window.logout();return;}
            const list=(await res.json()).data||[];
            $('totalPeserta').textContent=list.length;
            $('lbLoading').style.display='none';
            if(!list.length){showEmpty(tab);return;}
            renderPodium(list.slice(0,3));
            renderTable(list,tab);
        } catch(e){
            $('lbLoading').style.display='none';
            $('lbEmpty').style.display='block';
            $('lbEmpty').innerHTML=`<div class="empty-lb"><div class="empty-ico">${SVG_INBOX}</div><div class="empty-et">Gagal memuat data</div><div class="empty-es">${esc(e.message)}</div><button onclick="loadLeaderboard('${tab}')" style="margin-top:16px;padding:9px 20px;background:#279685;color:#fff;border:none;border-radius:9px;cursor:pointer;font-weight:600;font-family:inherit">Coba Lagi</button></div>`;
        }
    }

    const MEDALS=['🥇','🥈','🥉'];

    function renderPodium(top3){
        if(!top3.length)return;
        const order=top3.length>=3?[top3[1],top3[0],top3[2]]:top3.length===2?[top3[1],top3[0]]:[top3[0]];
        const rankOf=item=>top3.indexOf(item)+1;
        $('podium').innerHTML=order.map(item=>{
            const rank=rankOf(item),user=item.user||{},name=user.name||'Unknown';
            const init=name.trim().split(' ').map(w=>w[0]).slice(0,2).join('').toUpperCase();
            const score=item.score??0,nim=user.nim?(user.role==='public'?'Umum':user.nim):'';
            return`<div class="podium-item rank-${rank}">
                <div class="podium-card">
                    <div class="podium-rank-ico">${MEDALS[rank-1]}</div>
                    <div class="podium-avatar">${init}</div>
                    <div class="podium-name" title="${esc(name)}">${esc(name)}</div>
                    <div class="podium-score">${score}</div>
                    ${nim?`<div class="podium-nim">${esc(nim)}</div>`:''}
                    <div class="podium-bar"></div>
                </div>
            </div>`;
        }).join('');
        $('podiumWrap').style.display='block';
    }

    function renderTable(list,tab){
        $('lbBody').innerHTML=list.map((item,idx)=>{
            const rank=idx+1,user=item.user||{},name=esc(user.name||'Unknown');
            const score=item.score??0,passed=item.is_passed,correct=item.correct_count??0,total=item.total_questions??0;
            const secs=item.time_taken_seconds??0,time=formatTime(secs);
            const date=item.created_at?new Date(item.created_at).toLocaleDateString('id-ID',{day:'numeric',month:'short',year:'numeric'}):'—';
            const rankIcon=rank<=3?MEDALS[rank-1]:rank;
            const rankCls=rank<=3?`r${rank}`:'rn';
            const nimCell=tab==='mahasiswa'?`<div style="font-family:monospace;font-size:12px;color:#6b7280">${esc(user.nim||'—')}</div>${user.kelas?`<div style="font-size:10px;color:#9ca3af">Kelas ${esc(user.kelas)}</div>`:''}`:
                `<div style="font-size:11px;color:#9ca3af">Pengguna Umum</div>`;
            return`<tr class="${rank<=3?'is-top3':''}">
                <td><span class="rank-badge ${rankCls}">${rankIcon}</span></td>
                <td><div style="font-weight:600">${name}</div></td>
                <td>${nimCell}</td>
                <td><span class="score-pill ${passed?'score-pass':'score-fail'}">${score}</span></td>
                <td style="color:#6b7280">${correct}<span style="color:#d1d5db">/${total}</span></td>
                <td style="font-size:12px;color:#9ca3af">${time}</td>
                <td style="font-size:12px;color:#9ca3af">${date}</td>
            </tr>`;
        }).join('');
        $('lbTable').style.display='table';
    }

    function showEmpty(tab){
        $('lbEmpty').style.display='block';
        $('lbEmpty').innerHTML=`<div class="empty-lb">
            <div class="empty-ico">${tab==='mahasiswa'?SVG_GRAD:SVG_GLOBE}</div>
            <div class="empty-et">Belum ada peserta</div>
            <div class="empty-es">Belum ada ${tab==='mahasiswa'?'mahasiswa':'pengguna umum'} yang mengerjakan kuis ini.</div>
        </div>`;
    }

    window.switchTab=function(tab){
        currentTab=tab;
        $('tabMhs').classList.toggle('active',tab==='mahasiswa');
        $('tabUmum').classList.toggle('active',tab==='umum');
        loadLeaderboard(tab);
    };

    function formatTime(s){if(!s)return'—';const m=Math.floor(s/60),sec=s%60;return`${m}:${String(sec).padStart(2,'0')}`;}

    loadQuizInfo();
    loadLeaderboard('mahasiswa');
})();
</script>
@endpush