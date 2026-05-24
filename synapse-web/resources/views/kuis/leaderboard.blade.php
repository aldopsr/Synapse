@extends('layouts.app')

@section('title', 'Leaderboard - Synapse')
@section('header_title', 'Leaderboard Kuis')

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

/* Quiz info banner */
.quiz-banner {
    background: linear-gradient(135deg, #279685 0%, #1f7a6d 100%);
    border-radius: 18px; padding: 24px 28px; margin-bottom: 24px;
    color: #fff; display: flex; align-items: center; justify-content: space-between;
    gap: 16px;
}
.qb-title { font-size: 20px; font-weight: 700; margin-bottom: 8px; }
.qb-chips { display: flex; flex-wrap: wrap; gap: 6px; }
.qb-chip {
    padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 600;
    background: rgba(255,255,255,.2); color: #fff;
}
.qb-right { text-align: right; flex-shrink: 0; }
.qb-num   { font-size: 36px; font-weight: 700; line-height: 1; }
.qb-lbl   { font-size: 11px; opacity: .75; margin-top: 4px; }

/* Tab bar */
.tab-bar {
    display: flex; gap: 4px; background: #f3f4f6;
    border-radius: 14px; padding: 5px; margin-bottom: 20px;
    width: fit-content;
}
.tab-btn {
    padding: 10px 22px; border-radius: 10px; font-size: 13px; font-weight: 600;
    border: none; background: transparent; color: #888;
    cursor: pointer; transition: all .2s; font-family: inherit;
    display: flex; align-items: center; gap: 6px;
}
.tab-btn.active {
    background: #fff; color: #279685;
    box-shadow: 0 2px 8px rgba(0,0,0,.1);
}

/* Top 3 podium */
.podium {
    display: flex; align-items: flex-end; justify-content: center;
    gap: 12px; margin-bottom: 28px; padding: 0 20px;
}
.podium-item {
    flex: 1; max-width: 180px; text-align: center;
}
.podium-card {
    background: #fff; border-radius: 16px; padding: 16px 12px;
    border: 2px solid #eee; position: relative;
    transition: transform .2s;
}
.podium-card:hover { transform: translateY(-4px); }
.podium-item.rank-1 .podium-card { border-color: #f59e0b; background: linear-gradient(180deg, #fffbeb 0%, #fff 60%); }
.podium-item.rank-2 .podium-card { border-color: #9ca3af; background: linear-gradient(180deg, #f9fafb 0%, #fff 60%); }
.podium-item.rank-3 .podium-card { border-color: #f97316; background: linear-gradient(180deg, #fff7ed 0%, #fff 60%); }
.podium-crown { font-size: 28px; margin-bottom: 8px; }
.podium-avatar {
    width: 52px; height: 52px; border-radius: 50%; margin: 0 auto 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 20px; font-weight: 700; color: #fff;
}
.podium-item.rank-1 .podium-avatar { background: linear-gradient(135deg, #f59e0b, #d97706); }
.podium-item.rank-2 .podium-avatar { background: linear-gradient(135deg, #9ca3af, #6b7280); }
.podium-item.rank-3 .podium-avatar { background: linear-gradient(135deg, #f97316, #ea580c); }
.podium-name  { font-size: 13px; font-weight: 700; color: #1a1a1a; margin-bottom: 4px;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.podium-score { font-size: 22px; font-weight: 700; }
.podium-item.rank-1 .podium-score { color: #d97706; }
.podium-item.rank-2 .podium-score { color: #6b7280; }
.podium-item.rank-3 .podium-score { color: #ea580c; }
.podium-nim   { font-size: 10px; color: #aaa; margin-top: 2px; font-family: monospace; }
.podium-bar   {
    height: 8px; border-radius: 4px 4px 0 0; margin-top: 10px;
}
.podium-item.rank-1 .podium-bar { height: 8px; background: #f59e0b; }
.podium-item.rank-2 .podium-bar { height: 6px; background: #9ca3af; }
.podium-item.rank-3 .podium-bar { height: 4px; background: #f97316; }

/* Table */
.lb-table-wrap {
    background: #fff; border-radius: 16px; border: 1px solid #eee; overflow: hidden;
}
table { width: 100%; border-collapse: collapse; }
thead tr { background: #f9fafb; }
thead th {
    padding: 12px 16px; font-size: 11px; font-weight: 700; color: #888;
    text-transform: uppercase; letter-spacing: .04em; text-align: left;
    border-bottom: 1px solid #eee;
}
tbody tr { border-bottom: 1px solid #f3f4f6; transition: background .1s; }
tbody tr:last-child { border-bottom: none; }
tbody tr:hover { background: #f9fafb; }
tbody tr.is-top3 { background: #fffbf0; }
tbody tr.is-top3:hover { background: #fef3c7; }
tbody td { padding: 13px 16px; font-size: 13px; color: #1a1a1a; }

.rank-badge {
    display: inline-flex; align-items: center; justify-content: center;
    width: 30px; height: 30px; border-radius: 50%; font-size: 14px; font-weight: 700;
}
.rank-1b { background: #fef3c7; }
.rank-2b { background: #f3f4f6; }
.rank-3b { background: #fff7ed; }
.rank-nb { background: #f9fafb; color: #9ca3af; font-size: 12px; }

.score-pill { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 13px; font-weight: 700; }
.score-pass { background: #dcfce7; color: #15803d; }
.score-fail { background: #fee2e2; color: #b91c1c; }

.nim-text { font-family: monospace; font-size: 12px; color: #555; }
.time-text { font-size: 12px; color: #aaa; }

/* Empty */
.empty-lb {
    text-align: center; padding: 60px 20px;
}
.empty-lb .ei { font-size: 48px; margin-bottom: 16px; }
.empty-lb .et { font-size: 16px; font-weight: 700; color: #1a1a1a; margin-bottom: 8px; }
.empty-lb .es { font-size: 13px; color: #aaa; }

/* Skeleton */
.skeleton {
    background: linear-gradient(90deg, #f0f0f0 25%, #e8e8e8 50%, #f0f0f0 75%);
    background-size: 200% 100%; animation: shimmer 1.2s infinite; border-radius: 6px;
}
@keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }

/* Loading state */
.lb-loading { padding: 40px; text-align: center; color: #aaa; }
.lb-spinner {
    display: inline-block; width: 28px; height: 28px;
    border: 3px solid #e5e7eb; border-top-color: #279685;
    border-radius: 50%; animation: spin .7s linear infinite; margin-bottom: 12px;
}
@keyframes spin { to { transform: rotate(360deg); } }
</style>

<a href="/kuis" class="back-link">
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
        stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>
    </svg>
    Kembali ke Daftar Kuis
</a>

{{-- Banner --}}
<div class="quiz-banner" id="quizBanner">
    <div>
        <div class="qb-title" id="quizTitle">
            <div class="skeleton" style="height:22px;width:200px;background:rgba(255,255,255,.3);"></div>
        </div>
        <div class="qb-chips" id="quizChips"></div>
    </div>
    <div class="qb-right">
        <div class="qb-num" id="totalPeserta">—</div>
        <div class="qb-lbl">peserta</div>
    </div>
</div>

{{-- Tab bar --}}
<div class="tab-bar">
    <button class="tab-btn active" id="tabMhs" onclick="switchTab('mahasiswa')">
        🎓 Mahasiswa IPB
    </button>
    <button class="tab-btn" id="tabUmum" onclick="switchTab('umum')">
        🌐 Pengguna Umum
    </button>
</div>

{{-- Podium top 3 --}}
<div id="podiumWrap" style="display:none;">
    <div class="podium" id="podium"></div>
</div>

{{-- Table --}}
<div class="lb-table-wrap">
    <div class="lb-loading" id="lbLoading">
        <div class="lb-spinner"></div>
        <div>Memuat leaderboard...</div>
    </div>
    <table id="lbTable" style="display:none;">
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
    <div id="lbEmpty" style="display:none;"></div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    const API   = window.apiBaseUrl;
    const token = window.token;
    const QUIZ  = '{{ $quiz_id ?? "" }}';
    const $     = id => document.getElementById(id);

    if (!QUIZ) { window.location.href = '/kuis'; return; }

    let currentTab = 'mahasiswa';

    // ── Load info kuis ────────────────────────────────────────
    async function loadQuizInfo() {
        try {
            const res = await fetch(`${API}/admin/quizzes/${QUIZ}`, {
                headers: { Authorization: 'Bearer ' + token, Accept: 'application/json' }
            });
            if (!res.ok) return;
            const q = (await res.json()).data;
            if (!q) return;

            $('quizTitle').textContent = q.title || 'Kuis';

            const chips = [
                q.course?.title ? `📚 ${q.course.title}` : null,
                q.duration_minutes ? `⏱ ${q.duration_minutes} mnt` : null,
                `🎯 KKM: ${q.passing_score || 70}`,
            ].filter(Boolean);

            $('quizChips').innerHTML = chips
                .map(c => `<span class="qb-chip">${esc(c)}</span>`)
                .join('');
        } catch (_) {
            $('quizTitle').textContent = 'Kuis';
        }
    }

    // ── Load leaderboard ──────────────────────────────────────
    async function loadLeaderboard(tab) {
        $('lbLoading').style.display = 'block';
        $('lbTable').style.display   = 'none';
        $('lbEmpty').style.display   = 'none';
        $('podiumWrap').style.display = 'none';

        try {
            const res  = await fetch(`${API}/quizzes/${QUIZ}/leaderboard?tab=${tab}`, {
                headers: { Authorization: 'Bearer ' + token, Accept: 'application/json' }
            });
            if (res.status === 401) { window.logout(); return; }
            const data = await res.json();
            const list = data.data || [];

            $('totalPeserta').textContent = list.length;
            $('lbLoading').style.display  = 'none';

            if (list.length === 0) {
                showEmpty(tab);
                return;
            }

            renderPodium(list.slice(0, 3));
            renderTable(list, tab);

        } catch (e) {
            $('lbLoading').style.display = 'none';
            $('lbEmpty').style.display   = 'block';
            $('lbEmpty').innerHTML = `<div class="empty-lb">
                <div class="ei">⚠️</div>
                <div class="et">Gagal memuat data</div>
                <div class="es">${esc(e.message)}</div>
                <button onclick="loadLeaderboard('${tab}')"
                    style="margin-top:16px;padding:9px 20px;background:#279685;color:#fff;border:none;border-radius:9px;cursor:pointer;font-weight:600;font-family:inherit;">
                    Coba Lagi
                </button>
            </div>`;
        }
    }

    // ── Podium top 3 ──────────────────────────────────────────
    function renderPodium(top3) {
        if (top3.length === 0) return;

        // Urutan tampilan: 2, 1, 3 (standar podium)
        const order = top3.length >= 3
            ? [top3[1], top3[0], top3[2]]
            : top3.length === 2
            ? [top3[1], top3[0]]
            : [top3[0]];

        const rankOf = (item) => top3.indexOf(item) + 1;
        const crowns = ['🥇', '🥈', '🥉'];

        $('podium').innerHTML = order.map(item => {
            const rank  = rankOf(item);
            const user  = item.user || {};
            const name  = user.name || 'Unknown';
            const init  = name.trim().split(' ').map(w => w[0]).slice(0,2).join('').toUpperCase();
            const score = item.score ?? 0;
            const nim   = user.nim ? user.nim : (user.role === 'public' ? 'Umum' : '');

            return `
            <div class="podium-item rank-${rank}">
                <div class="podium-card">
                    <div class="podium-crown">${crowns[rank-1]}</div>
                    <div class="podium-avatar">${init}</div>
                    <div class="podium-name" title="${esc(name)}">${esc(name)}</div>
                    <div class="podium-score">${score}</div>
                    ${nim ? `<div class="podium-nim">${esc(nim)}</div>` : ''}
                    <div class="podium-bar"></div>
                </div>
            </div>`;
        }).join('');

        $('podiumWrap').style.display = 'block';
    }

    // ── Render table ──────────────────────────────────────────
    function renderTable(list, tab) {
        const tbody = $('lbBody');

        tbody.innerHTML = list.map((item, idx) => {
            const rank  = idx + 1;
            const user  = item.user || {};
            const name  = esc(user.name || 'Unknown');
            const score = item.score ?? 0;
            const passed= item.is_passed;
            const correct = item.correct_count ?? 0;
            const total   = item.total_questions ?? 0;
            const secs    = item.time_taken_seconds ?? 0;
            const time    = formatTime(secs);
            const date    = item.created_at
                ? new Date(item.created_at).toLocaleDateString('id-ID', {day:'numeric',month:'short',year:'numeric'})
                : '—';

            const rankIcon = rank === 1 ? '🥇' : rank === 2 ? '🥈' : rank === 3 ? '🥉' : rank;
            const rankCls  = rank <= 3 ? `rank-${rank}b` : 'rank-nb';
            const rowCls   = rank <= 3 ? 'is-top3' : '';

            const nimCell = tab === 'mahasiswa'
                ? `<div class="nim-text">${esc(user.nim || '—')}</div>
                   ${user.kelas ? `<div style="font-size:10px;color:#aaa;">Kelas ${esc(user.kelas)}</div>` : ''}`
                : `<div style="font-size:11px;color:#888;">Pengguna Umum</div>`;

            return `
            <tr class="${rowCls}">
                <td><span class="rank-badge ${rankCls}">${rankIcon}</span></td>
                <td>
                    <div style="font-weight:600;">${name}</div>
                </td>
                <td>${nimCell}</td>
                <td><span class="score-pill ${passed ? 'score-pass' : 'score-fail'}">${score}</span></td>
                <td style="color:#555;">${correct}<span style="color:#ccc;">/${total}</span></td>
                <td class="time-text">${time}</td>
                <td class="time-text">${date}</td>
            </tr>`;
        }).join('');

        $('lbTable').style.display = 'table';
    }

    // ── Empty state ───────────────────────────────────────────
    function showEmpty(tab) {
        $('lbEmpty').style.display = 'block';
        $('lbEmpty').innerHTML = `<div class="empty-lb">
            <div class="ei">${tab === 'mahasiswa' ? '🎓' : '🌐'}</div>
            <div class="et">Belum ada peserta</div>
            <div class="es">Belum ada ${tab === 'mahasiswa' ? 'mahasiswa IPB' : 'pengguna umum'} yang mengerjakan kuis ini.</div>
        </div>`;
    }

    // ── Switch tab ────────────────────────────────────────────
    window.switchTab = function(tab) {
        currentTab = tab;
        $('tabMhs').classList.toggle('active',  tab === 'mahasiswa');
        $('tabUmum').classList.toggle('active', tab === 'umum');
        loadLeaderboard(tab);
    };

    // ── Helpers ───────────────────────────────────────────────
    function formatTime(s) {
        if (!s) return '—';
        const m = Math.floor(s / 60);
        const sec = s % 60;
        return `${m}:${String(sec).padStart(2,'0')}`;
    }

    function esc(s) {
        return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    // ── Init ─────────────────────────────────────────────────
    loadQuizInfo();
    loadLeaderboard('mahasiswa');
})();
</script>
@endpush