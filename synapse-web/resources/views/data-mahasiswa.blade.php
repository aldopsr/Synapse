@extends('layouts.app')

@section('title') Data Mahasiswa - Synapse @endsection
@section('header_title') Data Mahasiswa @endsection

@section('content')
<style>
.page-top {
    display: flex; align-items: center; gap: 12px; margin-bottom: 24px;
}
.page-top h2 { font-size: 20px; font-weight: 700; color: #1a1a1a; margin: 0; }
.page-top p  { font-size: 13px; color: #888; margin: 4px 0 0; }

.filter-bar {
    display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 20px; align-items: center;
}
.filter-bar input, .filter-bar select {
    padding: 9px 14px; border: 1px solid #e5e7eb; border-radius: 9px;
    font-size: 13px; font-family: inherit; color: #1a1a1a; background: #fff;
    outline: none; transition: border-color .15s;
}
.filter-bar input:focus, .filter-bar select:focus { border-color: #279685; }
.filter-bar input { flex: 1; min-width: 200px; }

.stats-row {
    display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 12px; margin-bottom: 24px;
}
.stat-card { background: #fff; border: 1px solid #eee; border-radius: 14px; padding: 16px 18px; }
.stat-card .num { font-size: 26px; font-weight: 700; color: #279685; }
.stat-card .lbl { font-size: 11px; color: #888; margin-top: 4px; }

.table-wrap { background: #fff; border: 1px solid #eee; border-radius: 14px; overflow: hidden; }
table { width: 100%; border-collapse: collapse; }
thead tr { background: #f9fafb; }
thead th {
    padding: 12px 16px; font-size: 11px; font-weight: 700;
    color: #888; text-transform: uppercase; letter-spacing: .04em;
    text-align: left; border-bottom: 1px solid #eee;
}
tbody tr { border-bottom: 1px solid #f3f4f6; transition: background .1s; }
tbody tr:last-child { border-bottom: none; }
tbody tr:hover { background: #f9fafb; }
tbody td { padding: 12px 16px; font-size: 13px; color: #1a1a1a; }

.nim-badge {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 3px 9px; border-radius: 6px; font-size: 11px;
    font-weight: 600; font-family: monospace;
    background: #f0fdfb; color: #0f766e; border: 1px solid #ccfbf1;
}
.chip-angkatan {
    display: inline-block; padding: 2px 8px; border-radius: 99px;
    font-size: 11px; font-weight: 600; background: #fef3c7;
    color: #92400e; border: 1px solid #fde68a;
}
.score-pill { display: inline-block; padding: 3px 10px; border-radius: 8px; font-size: 12px; font-weight: 700; }
.score-pass { background: #dcfce7; color: #15803d; }
.score-fail { background: #fee2e2; color: #b91c1c; }
.score-none { background: #f3f4f6; color: #9ca3af; }

/* Action buttons */
.action-btns { display: flex; gap: 6px; align-items: center; }
.btn-detail {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 5px 10px; border-radius: 7px; font-size: 11px; font-weight: 600;
    border: 1px solid #e5e7eb; background: #fff; color: #555;
    cursor: pointer; transition: all .15s; font-family: inherit;
}
.btn-detail:hover { background: #f9fafb; border-color: #279685; color: #279685; }
.btn-detail.open  { background: #f0fdfb; border-color: #279685; color: #279685; }

.btn-export {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 5px 10px; border-radius: 7px; font-size: 11px; font-weight: 600;
    border: 1px solid #ccfbf1; background: #f0fdfb; color: #279685;
    cursor: pointer; transition: all .15s; font-family: inherit;
}
.btn-export:hover { background: #279685; color: #fff; border-color: #279685; }

/* Detail row */
.detail-row { display: none; }
.detail-row.open { display: table-row; }
.detail-cell { padding: 0 16px 16px 40px; background: #fafafa; }

/* Attempt chips */
.attempt-grid {
    display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 10px; padding-top: 12px;
}
.attempt-card {
    background: #fff; border: 1px solid #e5e7eb; border-radius: 10px;
    padding: 12px 14px;
}
.attempt-card .ac-title { font-size: 12px; font-weight: 700; color: #1a1a1a; margin-bottom: 6px; }
.attempt-card .ac-score {
    font-size: 20px; font-weight: 700; margin-bottom: 4px;
}
.attempt-card .ac-meta { font-size: 11px; color: #aaa; }

/* Group header */
.group-header td {
    background: #f3f4f6; font-size: 11px; font-weight: 700;
    color: #555; text-transform: uppercase; letter-spacing: .05em;
    padding: 8px 16px; border-top: 1px solid #e5e7eb;
}

/* Empty */
.empty-state { text-align: center; padding: 60px 20px; color: #aaa; }
.empty-state .ei { font-size: 40px; margin-bottom: 12px; }

/* Skeleton */
.skeleton {
    background: linear-gradient(90deg, #f0f0f0 25%, #e8e8e8 50%, #f0f0f0 75%);
    background-size: 200% 100%; animation: shimmer 1.2s infinite; border-radius: 6px;
}
@keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }

.toast {
    position: fixed; bottom: 24px; left: 50%;
    transform: translateX(-50%) translateY(60px);
    background: #1a1a1a; color: #fff; padding: 10px 20px;
    border-radius: 10px; font-size: 13px; font-weight: 600;
    opacity: 0; transition: all .3s; z-index: 999;
}
.toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
</style>

<div class="page-top">
    <div>
        <h2>Data Mahasiswa</h2>
        <p id="pageSubtitle">Memuat data...</p>
    </div>
    <div style="margin-left:auto; display:flex; gap:8px;">
        <button onclick="exportAllCSV()" style="
            padding:8px 16px; border-radius:9px; font-size:12px; font-weight:600;
            border:1px solid #e5e7eb; background:#fff; cursor:pointer; color:#555;
            display:flex; align-items:center; gap:6px;
        ">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                <path fill-rule="evenodd" d="M12 2.25a.75.75 0 0 1 .75.75v11.69l3.22-3.22a.75.75 0 1 1 1.06 1.06l-4.5 4.5a.75.75 0 0 1-1.06 0l-4.5-4.5a.75.75 0 1 1 1.06-1.06l3.22 3.22V3a.75.75 0 0 1 .75-.75Zm-9 13.5a.75.75 0 0 1 .75.75v2.25a1.5 1.5 0 0 0 1.5 1.5h13.5a1.5 1.5 0 0 0 1.5-1.5V16.5a.75.75 0 0 1 1.5 0v2.25a3 3 0 0 1-3 3H5.25a3 3 0 0 1-3-3V16.5a.75.75 0 0 1 .75-.75Z" clip-rule="evenodd"/>
            </svg>
            Export Semua
        </button>
    </div>
</div>

<div class="stats-row" id="statsRow">
    <div class="stat-card"><div class="skeleton" style="height:26px;width:60px;margin-bottom:6px;"></div><div class="lbl">Total Peserta</div></div>
    <div class="stat-card"><div class="skeleton" style="height:26px;width:60px;margin-bottom:6px;"></div><div class="lbl">Rata-rata Skor</div></div>
    <div class="stat-card"><div class="skeleton" style="height:26px;width:60px;margin-bottom:6px;"></div><div class="lbl">Rata-rata ≥ 70</div></div>
    <div class="stat-card"><div class="skeleton" style="height:26px;width:60px;margin-bottom:6px;"></div><div class="lbl">Angkatan</div></div>
</div>

<div class="filter-bar">
    <input type="text" id="searchInput" placeholder="🔍  Cari nama, NIM, atau email..." oninput="applyFilter()">
    <select id="filterAngkatan" onchange="applyFilter()">
        <option value="">Semua Angkatan</option>
    </select>
    <select id="filterFakultas" onchange="applyFilter()">
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
            <tr><td colspan="8" style="text-align:center;padding:40px;">
                <div class="skeleton" style="height:14px;width:60%;margin:0 auto 10px;"></div>
                <div class="skeleton" style="height:14px;width:40%;margin:0 auto;"></div>
            </td></tr>
        </tbody>
    </table>
</div>

<div class="toast" id="toast"></div>
@endsection

@push('scripts')
<script>
(function () {
    const API      = window.apiBaseUrl;
    const token    = window.token;
    const role     = window.role || 'dosen';
    const isAdmin  = role === 'admin' || role === 'superadmin';
    const $        = id => document.getElementById(id);

    let allData  = [];
    let filtered = [];

    // ── Toast ─────────────────────────────────────────────────
    function toast(msg) {
        const el = $('toast');
        el.textContent = '✓  ' + msg;
        el.className = 'toast show';
        clearTimeout(el._t);
        el._t = setTimeout(() => el.classList.remove('show'), 3000);
    }

    // ── Fetch data ────────────────────────────────────────────
    async function fetchData() {
        const endpoint = isAdmin
            ? `${API}/student-data/all`
            : `${API}/student-data/quiz-participants`;

        try {
            const res  = await fetch(endpoint, {
                headers: { Authorization: 'Bearer ' + token, Accept: 'application/json' }
            });
            if (res.status === 401) { window.logout(); return; }
            const data = await res.json();

            allData = data.data || [];
            buildFilterOptions(allData);
            renderStats(allData);
            $('pageSubtitle').textContent = `${allData.length} mahasiswa ${isAdmin ? 'terdaftar' : 'mengerjakan quiz'}`;
            applyFilter();
        } catch (e) {
            $('tableBody').innerHTML = `<tr><td colspan="8">
                <div class="empty-state">
                    <div class="ei">⚠️</div>
                    <p>Gagal memuat data. <button onclick="fetchData()" style="color:#279685;background:none;border:none;cursor:pointer;font-weight:600;">Coba lagi</button></p>
                </div>
            </td></tr>`;
        }
    }

    // ── Filter options ────────────────────────────────────────
    function buildFilterOptions(data) {
        const angkatanSet = new Set();
        const fakultasSet = new Set();
        data.forEach(s => {
            if (s.nim_info?.angkatan) angkatanSet.add(s.nim_info.angkatan);
            if (s.nim_info?.sekolah)  fakultasSet.add(s.nim_info.sekolah);
        });

        const angkEl = $('filterAngkatan');
        const fakEl  = $('filterFakultas');
        angkEl.innerHTML = '<option value="">Semua Angkatan</option>';
        fakEl.innerHTML  = '<option value="">Semua Fakultas</option>';
        [...angkatanSet].sort().forEach(a => angkEl.innerHTML += `<option value="${a}">${a}</option>`);
        [...fakultasSet].sort().forEach(f => fakEl.innerHTML += `<option value="${f}">${esc(f)}</option>`);
    }

    // ── Stats ─────────────────────────────────────────────────
    function renderStats(data) {
        const total  = data.length;
        const avgArr = data.map(d => d.avg_score).filter(s => s !== null && s !== undefined);
        const avg    = avgArr.length > 0 ? (avgArr.reduce((a,b)=>a+b,0)/avgArr.length).toFixed(1) : '-';
        const lulus  = data.filter(d => d.avg_score !== null && d.avg_score >= 70).length;
        const angkSz = new Set(data.map(d => d.nim_info?.angkatan).filter(Boolean)).size;

        $('statsRow').innerHTML = `
            <div class="stat-card"><div class="num">${total}</div><div class="lbl">Total Peserta</div></div>
            <div class="stat-card"><div class="num">${avg}</div><div class="lbl">Rata-rata Skor</div></div>
            <div class="stat-card"><div class="num">${lulus}</div><div class="lbl">Rata-rata ≥ 70</div></div>
            <div class="stat-card"><div class="num">${angkSz}</div><div class="lbl">Angkatan</div></div>
        `;
    }

    // ── Filter ────────────────────────────────────────────────
    window.applyFilter = function() {
        const q    = $('searchInput').value.trim().toLowerCase();
        const angk = $('filterAngkatan').value;
        const fak  = $('filterFakultas').value;

        filtered = allData.filter(s => {
            const mQ = !q || (s.name||'').toLowerCase().includes(q)
                || (s.nim||'').toLowerCase().includes(q)
                || (s.email||'').toLowerCase().includes(q);
            const mA = !angk || s.nim_info?.angkatan === angk;
            const mF = !fak  || s.nim_info?.sekolah  === fak;
            return mQ && mA && mF;
        });

        renderTable(filtered);
    };

    // ── Render table ──────────────────────────────────────────
    function renderTable(data) {
        const tbody = $('tableBody');

        if (data.length === 0) {
            tbody.innerHTML = `<tr><td colspan="8">
                <div class="empty-state">
                    <div class="ei">🎓</div>
                    <p>Belum ada mahasiswa yang cocok.</p>
                </div>
            </td></tr>`;
            return;
        }

        // Group by angkatan
        const grouped = {};
        data.forEach(s => {
            const key = s.nim_info?.angkatan || 'Tidak Diketahui';
            if (!grouped[key]) grouped[key] = [];
            grouped[key].push(s);
        });

        let html = '';
        let counter = 1;

        Object.keys(grouped).sort().reverse().forEach(angkatan => {
            html += `<tr class="group-header"><td colspan="8">Angkatan ${esc(angkatan)} — ${grouped[angkatan].length} mahasiswa</td></tr>`;

            grouped[angkatan].forEach(s => {
                const uid    = s.user_id;
                const avg    = s.avg_score !== null && s.avg_score !== undefined ? s.avg_score : null;
                const scoreEl = avg !== null
                    ? `<span class="score-pill ${avg >= 70 ? 'score-pass' : 'score-fail'}">${avg}</span>`
                    : `<span class="score-pill score-none">-</span>`;

                // Simpan data untuk export
                window[`_student_${uid}`] = s;
                if (s.attempts) window[`_attempts_${uid}`] = s.attempts;

                html += `
                <tr>
                    <td style="color:#aaa;font-size:12px;">${counter++}</td>
                    <td>
                        <div style="font-weight:600;">${esc(s.name)}</div>
                        <div style="font-size:11px;color:#888;">${esc(s.email)}</div>
                    </td>
                    <td>
                        ${s.nim ? `<span class="nim-badge">${esc(s.nim)}</span>` : '<span style="color:#ccc;">-</span>'}
                        <div style="font-size:10px;color:#aaa;margin-top:2px;">${esc(s.nim_info?.sekolah || '')}</div>
                    </td>
                    <td>
                        ${s.nim_info?.angkatan
                            ? `<span class="chip-angkatan">${esc(s.nim_info.angkatan)}</span>`
                            : '<span style="color:#ccc;">-</span>'}
                    </td>
                    <td>${esc(s.kelas || '-')}</td>
                    <td style="font-weight:600;">${s.total_quiz_taken ?? 0}</td>
                    <td>${scoreEl}</td>
                    <td>
                        <div class="action-btns">
                            <button class="btn-detail" id="btnDetail-${uid}"
                                onclick="toggleDetail('${uid}')">
                                📊 Detail
                            </button>
                            <button class="btn-export"
                                onclick="exportStudentCSV('${uid}')"
                                title="Export riwayat quiz mahasiswa ini">
                                ⬇ Export
                            </button>
                        </div>
                    </td>
                </tr>
                <tr class="detail-row" id="detail-${uid}">
                    <td colspan="8" class="detail-cell" id="detailCell-${uid}">
                        <div style="color:#aaa;font-size:12px;padding:8px 0;">Memuat...</div>
                    </td>
                </tr>`;
            });
        });

        tbody.innerHTML = html;
    }

    // ── Toggle detail row ─────────────────────────────────────
    window.toggleDetail = function(uid) {
        const row  = $(`detail-${uid}`);
        const cell = $(`detailCell-${uid}`);
        const btn  = $(`btnDetail-${uid}`);

        if (row.classList.toggle('open')) {
            btn.classList.add('open');
            btn.textContent = '📊 Tutup';
            renderDetailCell(uid, cell);
        } else {
            btn.classList.remove('open');
            btn.textContent = '📊 Detail';
        }
    };

    function renderDetailCell(uid, cell) {
        const s        = window[`_student_${uid}`] || {};
        const attempts = window[`_attempts_${uid}`] || [];

        if (attempts.length === 0) {
            cell.innerHTML = '<div style="color:#aaa;font-size:12px;padding:12px 0;">Belum ada quiz yang dikerjakan.</div>';
            return;
        }

        cell.innerHTML = `
            <div style="display:flex;gap:16px;flex-wrap:wrap;padding:12px 0 8px;">
                <div>
                    <div style="font-size:10px;color:#aaa;margin-bottom:3px;">SEKOLAH/FAKULTAS</div>
                    <div style="font-size:12px;font-weight:600;">${esc(s.nim_info?.sekolah || '-')}</div>
                    <div style="font-size:11px;color:#aaa;">${esc(s.nim_info?.jenjang || '')}</div>
                </div>
                <div>
                    <div style="font-size:10px;color:#aaa;margin-bottom:3px;">TOTAL QUIZ</div>
                    <div style="font-size:20px;font-weight:700;color:#279685;">${s.total_quiz_taken ?? 0}</div>
                </div>
                <div>
                    <div style="font-size:10px;color:#aaa;margin-bottom:3px;">RATA-RATA SKOR</div>
                    <div style="font-size:20px;font-weight:700;color:${(s.avg_score||0)>=70?'#15803d':'#b91c1c'};">${s.avg_score ?? '-'}</div>
                </div>
            </div>
            <div class="attempt-grid">
                ${attempts.map(a => `
                    <div class="attempt-card">
                        <div class="ac-title">${esc(a.quiz_title || 'Quiz')}</div>
                        <div class="ac-score" style="color:${(a.score||0)>=70?'#15803d':'#b91c1c'};">${a.score ?? '-'}</div>
                        <div class="ac-meta">
                            ${a.is_passed
                                ? '<span style="color:#15803d;font-weight:600;">✓ Lulus</span>'
                                : '<span style="color:#b91c1c;font-weight:600;">✗ Tidak Lulus</span>'}
                            &nbsp;·&nbsp;
                            ${a.created_at ? new Date(a.created_at).toLocaleDateString('id-ID', {day:'numeric',month:'short',year:'numeric'}) : '-'}
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
    }

    // ── Export per mahasiswa ──────────────────────────────────
    window.exportStudentCSV = function(uid) {
        const s        = window[`_student_${uid}`];
        const attempts = window[`_attempts_${uid}`] || [];

        if (!s) return;

        const rows = [
            ['=== DATA MAHASISWA ==='],
            ['Nama', s.name],
            ['Email', s.email],
            ['NIM', s.nim || '-'],
            ['Kelas', s.kelas || '-'],
            ['Angkatan', s.nim_info?.angkatan || '-'],
            ['Fakultas', s.nim_info?.sekolah || '-'],
            ['Jenjang', s.nim_info?.jenjang || '-'],
            ['Total Quiz', s.total_quiz_taken ?? 0],
            ['Rata-rata Skor', s.avg_score ?? '-'],
            [],
            ['=== RIWAYAT QUIZ ==='],
            ['No', 'Nama Quiz', 'Skor', 'Status', 'Tanggal'],
            ...attempts.map((a, i) => [
                i + 1,
                a.quiz_title || '-',
                a.score ?? '-',
                a.is_passed ? 'Lulus' : 'Tidak Lulus',
                a.created_at ? new Date(a.created_at).toLocaleDateString('id-ID') : '-',
            ])
        ];

        const csv  = rows.map(r => r.map(c => `"${String(c).replace(/"/g,'""')}"`).join(',')).join('\n');
        const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const name = (s.name || 'mahasiswa').replace(/\s+/g, '_').toLowerCase();
        link.href     = URL.createObjectURL(blob);
        link.download = `riwayat_${name}_${new Date().toISOString().split('T')[0]}.csv`;
        link.click();
        toast(`Berhasil export data ${s.name}`);
    };

    // ── Export semua ──────────────────────────────────────────
    window.exportAllCSV = function() {
        const data = filtered.length > 0 ? filtered : allData;
        if (data.length === 0) return;

        const rows = [
            ['Nama', 'Email', 'NIM', 'Kelas', 'Angkatan', 'Fakultas', 'Total Quiz', 'Rata-rata Skor'],
            ...data.map(s => [
                s.name, s.email, s.nim || '', s.kelas || '',
                s.nim_info?.angkatan || '', s.nim_info?.sekolah || '',
                s.total_quiz_taken ?? 0, s.avg_score ?? '',
            ])
        ];

        const csv  = rows.map(r => r.map(c => `"${String(c).replace(/"/g,'""')}"`).join(',')).join('\n');
        const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href     = URL.createObjectURL(blob);
        link.download = `data_mahasiswa_${new Date().toISOString().split('T')[0]}.csv`;
        link.click();
        toast('Export berhasil!');
    };

    // ── Escape ────────────────────────────────────────────────
    function esc(s) {
        return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    // ── Init ─────────────────────────────────────────────────
    fetchData();
})();
</script>
@endpush