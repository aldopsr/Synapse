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

/* Filter bar */
.filter-bar {
    display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 20px;
    align-items: center;
}
.filter-bar input, .filter-bar select {
    padding: 9px 14px; border: 1px solid #e5e7eb; border-radius: 9px;
    font-size: 13px; font-family: inherit; color: #1a1a1a; background: #fff;
    outline: none; transition: border-color .15s;
}
.filter-bar input:focus, .filter-bar select:focus { border-color: #279685; }
.filter-bar input { flex: 1; min-width: 200px; }

/* Stats row */
.stats-row {
    display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 12px; margin-bottom: 24px;
}
.stat-card {
    background: #fff; border: 1px solid #eee; border-radius: 14px;
    padding: 16px 18px;
}
.stat-card .num  { font-size: 26px; font-weight: 700; color: #279685; }
.stat-card .lbl  { font-size: 11px; color: #888; margin-top: 4px; }

/* Table */
.table-wrap {
    background: #fff; border: 1px solid #eee; border-radius: 14px;
    overflow: hidden;
}
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

/* NIM badge */
.nim-badge {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 3px 9px; border-radius: 6px;
    font-size: 11px; font-weight: 600; font-family: monospace;
    background: #f0fdfb; color: #0f766e;
    border: 1px solid #ccfbf1;
}
/* Angkatan chip */
.chip-angkatan {
    display: inline-block; padding: 2px 8px; border-radius: 99px;
    font-size: 11px; font-weight: 600;
    background: #fef3c7; color: #92400e;
    border: 1px solid #fde68a;
}
/* Score */
.score-pill {
    display: inline-block; padding: 3px 10px; border-radius: 8px;
    font-size: 12px; font-weight: 700;
}
.score-pass   { background: #dcfce7; color: #15803d; }
.score-fail   { background: #fee2e2; color: #b91c1c; }
.score-none   { background: #f3f4f6; color: #9ca3af; }

/* Expand row */
.expand-btn {
    background: none; border: none; cursor: pointer;
    font-size: 11px; color: #279685; font-weight: 600;
    padding: 4px 8px; border-radius: 6px; transition: background .15s;
}
.expand-btn:hover { background: #f0fdfb; }
.detail-row { display: none; }
.detail-row.open { display: table-row; }
.detail-cell {
    padding: 0 16px 14px 40px;
    background: #fafafa;
}
.attempt-list { display: flex; flex-wrap: wrap; gap: 8px; padding-top: 10px; }
.attempt-chip {
    display: inline-flex; flex-direction: column; gap: 2px;
    padding: 8px 12px; border-radius: 10px;
    border: 1px solid #e5e7eb; background: #fff;
    font-size: 11px; min-width: 130px;
}
.attempt-chip .ql { font-weight: 700; color: #1a1a1a; }
.attempt-chip .qs { color: #888; }

/* Empty */
.empty-state {
    text-align: center; padding: 60px 20px;
    color: #aaa;
}
.empty-state .ei { font-size: 40px; margin-bottom: 12px; }

/* Section header (angkatan group) */
.group-header td {
    background: #f3f4f6; font-size: 11px; font-weight: 700;
    color: #555; text-transform: uppercase; letter-spacing: .05em;
    padding: 8px 16px; border-top: 1px solid #e5e7eb;
}

/* Skeleton */
.skeleton { background: linear-gradient(90deg, #f0f0f0 25%, #e8e8e8 50%, #f0f0f0 75%);
    background-size: 200% 100%; animation: shimmer 1.2s infinite; border-radius: 6px; }
@keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }

.toast { position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%) translateY(60px);
    background: #1a1a1a; color: #fff; padding: 10px 20px; border-radius: 10px;
    font-size: 13px; font-weight: 600; opacity: 0; transition: all .3s; z-index: 999; }
.toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }

/* Course filter (dosen only) */
.course-tabs {
    display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 16px;
}
.course-tab {
    padding: 7px 14px; border-radius: 20px; font-size: 12px; font-weight: 600;
    border: 1.5px solid #e5e7eb; background: #fff; cursor: pointer;
    transition: all .15s; color: #555;
}
.course-tab.active {
    background: #279685; border-color: #279685; color: #fff;
}
</style>

<div class="page-top">
    <div>
        <h2>Data Mahasiswa</h2>
        <p id="pageSubtitle">Memuat data...</p>
    </div>
    <div style="margin-left:auto; display:flex; gap:8px;">
        <button onclick="exportCSV()" style="
            padding:8px 16px; border-radius:9px; font-size:12px; font-weight:600;
            border:1px solid #e5e7eb; background:#fff; cursor:pointer; color:#555;
        ">⬇ Export CSV</button>
    </div>
</div>

{{-- Course tabs (untuk dosen dengan beberapa matkul, atau admin) --}}
<div class="course-tabs" id="courseTabs" style="display:none;"></div>

{{-- Stats row --}}
<div class="stats-row" id="statsRow">
    <div class="stat-card"><div class="skeleton" style="height:26px;width:60px;margin-bottom:6px;"></div><div class="lbl">Total Peserta</div></div>
    <div class="stat-card"><div class="skeleton" style="height:26px;width:60px;margin-bottom:6px;"></div><div class="lbl">Rata-rata Skor</div></div>
    <div class="stat-card"><div class="skeleton" style="height:26px;width:60px;margin-bottom:6px;"></div><div class="lbl">Sudah Lulus</div></div>
    <div class="stat-card"><div class="skeleton" style="height:26px;width:60px;margin-bottom:6px;"></div><div class="lbl">Angkatan</div></div>
</div>

{{-- Filter --}}
<div class="filter-bar">
    <input type="text" id="searchInput" placeholder="🔍  Cari nama, NIM, atau email..."
        oninput="applyFilter()">
    <select id="filterAngkatan" onchange="applyFilter()">
        <option value="">Semua Angkatan</option>
    </select>
    <select id="filterFakultas" onchange="applyFilter()">
        <option value="">Semua Fakultas/Sekolah</option>
    </select>
</div>

{{-- Table --}}
<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Mahasiswa</th>
                <th>NIM</th>
                <th>Angkatan</th>
                <th>Kelas</th>
                <th>Quiz Dikerjakan</th>
                <th>Avg Skor</th>
                <th></th>
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

    let allData    = [];   // flat list semua mahasiswa
    let filtered   = [];
    let currentCourseId = null;

    // ── Toast ─────────────────────────────────────────────────
    function toast(msg, type = 'ok') {
        const el = $('toast');
        el.textContent = (type === 'ok' ? '✓  ' : '✕  ') + msg;
        el.className   = 'toast show';
        clearTimeout(el._t);
        el._t = setTimeout(() => el.classList.remove('show'), 3000);
    }

    // ── Fetch data ────────────────────────────────────────────
    async function fetchData(courseId) {
        const endpoint = isAdmin
            ? `${API}/student-data/all`
            : `${API}/student-data/quiz-participants${courseId ? '?course_id=' + courseId : ''}`;

        try {
            const res  = await fetch(endpoint, {
                headers: { Authorization: 'Bearer ' + token, Accept: 'application/json' }
            });
            if (res.status === 401) { window.logout(); return; }
            const data = await res.json();

            allData = data.data || [];

            // Isi filter options
            if (data.filter_options) {
                fillFilterOptions(data.filter_options);
            } else {
                buildFilterOptions(allData);
            }

            $('pageSubtitle').textContent = isAdmin
                ? `${allData.length} mahasiswa terdaftar`
                : `${allData.length} mahasiswa mengerjakan quiz`;

            renderStats(allData);
            applyFilter();

        } catch (e) {
            $('tableBody').innerHTML = `<tr><td colspan="8">
                <div class="empty-state">
                    <div class="ei">⚠️</div>
                    <p>Gagal memuat data: ${e.message}</p>
                    <button onclick="fetchData()" style="margin-top:12px;padding:8px 20px;background:#279685;color:#fff;border:none;border-radius:8px;cursor:pointer;font-weight:600;">Coba Lagi</button>
                </div>
            </td></tr>`;
        }
    }

    // ── Filter options ────────────────────────────────────────
    function fillFilterOptions(opts) {
        const angkSelect = $('filterAngkatan');
        const fakSelect  = $('filterFakultas');

        angkSelect.innerHTML = '<option value="">Semua Angkatan</option>';
        (opts.angkatan || []).forEach(a => {
            angkSelect.innerHTML += `<option value="${a}">${a}</option>`;
        });

        fakSelect.innerHTML  = '<option value="">Semua Fakultas/Sekolah</option>';
        (opts.fakultas || []).forEach(f => {
            fakSelect.innerHTML += `<option value="${f}">${f}</option>`;
        });
    }

    function buildFilterOptions(data) {
        const angkatanSet = new Set();
        const fakultasSet = new Set();

        data.forEach(s => {
            if (s.nim_info?.angkatan) angkatanSet.add(s.nim_info.angkatan);
            if (s.nim_info?.sekolah)  fakultasSet.add(s.nim_info.sekolah);
        });

        fillFilterOptions({
            angkatan: [...angkatanSet].sort(),
            fakultas: [...fakultasSet].sort(),
        });
    }

    // ── Stats ─────────────────────────────────────────────────
    function renderStats(data) {
        const total   = data.length;
        const avgArr  = data.map(d => d.avg_score).filter(s => s !== null && s !== undefined);
        const avg     = avgArr.length > 0 ? (avgArr.reduce((a,b)=>a+b,0)/avgArr.length).toFixed(1) : '-';
        const lulus   = data.filter(d => d.avg_score !== null && d.avg_score >= 70).length;
        const angkSet = new Set(data.map(d => d.nim_info?.angkatan).filter(Boolean));

        $('statsRow').innerHTML = `
            <div class="stat-card"><div class="num">${total}</div><div class="lbl">Total Peserta</div></div>
            <div class="stat-card"><div class="num">${avg}</div><div class="lbl">Rata-rata Skor</div></div>
            <div class="stat-card"><div class="num">${lulus}</div><div class="lbl">Rata-rata ≥ 70</div></div>
            <div class="stat-card"><div class="num">${angkSet.size}</div><div class="lbl">Angkatan</div></div>
        `;
    }

    // ── Apply filter ──────────────────────────────────────────
    window.applyFilter = function() {
        const q    = $('searchInput').value.trim().toLowerCase();
        const angk = $('filterAngkatan').value;
        const fak  = $('filterFakultas').value;

        filtered = allData.filter(s => {
            const matchQ = !q || (s.name||'').toLowerCase().includes(q)
                || (s.nim||'').toLowerCase().includes(q)
                || (s.email||'').toLowerCase().includes(q);
            const matchA = !angk || s.nim_info?.angkatan === angk;
            const matchF = !fak  || s.nim_info?.sekolah === fak;
            return matchQ && matchA && matchF;
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
                    <p>Belum ada mahasiswa yang cocok dengan filter.</p>
                </div>
            </td></tr>`;
            return;
        }

        // Group by angkatan
        const grouped = {};
        data.forEach(s => {
            const key = s.nim_info?.angkatan || 'Angkatan Tidak Diketahui';
            if (!grouped[key]) grouped[key] = [];
            grouped[key].push(s);
        });

        let html = '';
        let counter = 1;

        Object.keys(grouped).sort().reverse().forEach(angkatan => {
            html += `<tr class="group-header"><td colspan="8">Angkatan ${angkatan} (${grouped[angkatan].length} mahasiswa)</td></tr>`;

            grouped[angkatan].forEach(s => {
                const uid     = s.user_id;
                const avg     = s.avg_score !== null ? s.avg_score : null;
                const scoreEl = avg !== null
                    ? `<span class="score-pill ${avg >= 70 ? 'score-pass' : 'score-fail'}">${avg}</span>`
                    : `<span class="score-pill score-none">-</span>`;

                const sekolah = s.nim_info?.sekolah || '-';
                const jenjang = s.nim_info?.jenjang || '';

                html += `
                <tr>
                    <td style="color:#aaa;">${counter++}</td>
                    <td>
                        <div style="font-weight:600;">${esc(s.name)}</div>
                        <div style="font-size:11px;color:#888;">${esc(s.email)}</div>
                    </td>
                    <td>
                        ${s.nim ? `<span class="nim-badge">${esc(s.nim)}</span>` : '<span style="color:#ccc;">-</span>'}
                        <div style="font-size:10px;color:#aaa;margin-top:2px;">${esc(sekolah)}</div>
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
                        ${isAdmin
                            ? `<button class="expand-btn" onclick="loadDetail('${uid}', this)">Detail ▾</button>`
                            : (s.attempts ? `<button class="expand-btn" onclick="toggleAttempts('${uid}')">Lihat ▾</button>` : '')
                        }
                    </td>
                </tr>
                <tr class="detail-row" id="detail-${uid}">
                    <td colspan="8" class="detail-cell" id="detailCell-${uid}">
                        <div style="color:#aaa;font-size:12px;padding:8px 0;">Memuat detail...</div>
                    </td>
                </tr>`;

                // Simpan attempts untuk dosen (sudah ada di response)
                if (s.attempts) {
                    window[`_attempts_${uid}`] = s.attempts;
                }
            });
        });

        tbody.innerHTML = html;
    }

    // ── Toggle attempts (dosen - data sudah ada) ──────────────
    window.toggleAttempts = function(uid) {
        const row  = $(`detail-${uid}`);
        const cell = $(`detailCell-${uid}`);
        const btn  = row.previousElementSibling?.querySelector('.expand-btn');

        if (row.classList.toggle('open')) {
            if (btn) { btn.textContent = 'Tutup ▴'; btn.classList.add('open'); }
            const attempts = window[`_attempts_${uid}`] || [];
            if (attempts.length === 0) {
                cell.innerHTML = '<div style="color:#aaa;font-size:12px;padding:8px 0;">Belum ada data quiz.</div>';
                return;
            }
            cell.innerHTML = `<div class="attempt-list">${attempts.map(a => `
                <div class="attempt-chip">
                    <span class="ql">${esc(a.quiz_title || a.quiz_id)}</span>
                    <span class="qs ${a.is_passed ? 'score-pass' : 'score-fail'}" style="font-size:12px;font-weight:700;border-radius:4px;padding:1px 5px;">${a.score ?? '-'}</span>
                    <span class="qs">${a.created_at ? new Date(a.created_at).toLocaleDateString('id-ID') : ''}</span>
                </div>
            `).join('')}</div>`;
        } else {
            if (btn) { btn.textContent = 'Lihat ▾'; btn.classList.remove('open'); }
        }
    };

    // ── Load detail admin (fetch dari server) ─────────────────
    window.loadDetail = async function(uid, btn) {
        const row  = $(`detail-${uid}`);
        const cell = $(`detailCell-${uid}`);

        if (row.classList.toggle('open')) {
            btn.textContent = 'Tutup ▴';
            cell.innerHTML  = '<div style="color:#aaa;font-size:12px;padding:8px 0;">Memuat detail...</div>';

            try {
                const res  = await fetch(`${API}/student-data/${uid}/detail`, {
                    headers: { Authorization: 'Bearer ' + token, Accept: 'application/json' }
                });
                const data = await res.json();
                const d    = data.student;
                const s    = data.summary;
                const att  = data.attempts || [];

                cell.innerHTML = `
                    <div style="display:flex;gap:20px;flex-wrap:wrap;padding:10px 0 14px;">
                        <div>
                            <div style="font-size:11px;color:#888;margin-bottom:4px;">Info NIM</div>
                            <div style="font-size:12px;font-weight:600;">${esc(d?.nim_info?.sekolah || '-')}</div>
                            <div style="font-size:11px;color:#888;">${esc(d?.nim_info?.jenjang || '')}</div>
                        </div>
                        <div>
                            <div style="font-size:11px;color:#888;margin-bottom:4px;">Total Quiz</div>
                            <div style="font-size:18px;font-weight:700;color:#279685;">${s?.total_quiz_taken || 0}</div>
                        </div>
                        <div>
                            <div style="font-size:11px;color:#888;margin-bottom:4px;">Avg Skor</div>
                            <div style="font-size:18px;font-weight:700;color:${(s?.avg_score||0)>=70?'#15803d':'#b91c1c'};">${s?.avg_score || '-'}</div>
                        </div>
                        <div>
                            <div style="font-size:11px;color:#888;margin-bottom:4px;">Lulus / Gagal</div>
                            <div style="font-size:14px;font-weight:600;">
                                <span style="color:#15803d;">${s?.passed_count || 0} lulus</span>
                                &nbsp;/&nbsp;
                                <span style="color:#b91c1c;">${s?.failed_count || 0} gagal</span>
                            </div>
                        </div>
                    </div>
                    <div class="attempt-list">${att.length === 0
                        ? '<div style="color:#aaa;font-size:12px;">Belum ada quiz yang dikerjakan.</div>'
                        : att.map(a => `
                            <div class="attempt-chip" style="min-width:160px;">
                                <span class="ql">${esc(a.quiz_title)}</span>
                                <span class="qs">
                                    <span style="font-weight:700;color:${(a.score||0)>=70?'#15803d':'#b91c1c'};">${a.score ?? '-'}</span>
                                    ${a.is_passed ? '✓' : '✗'}
                                </span>
                                <span class="qs">${a.correct_count}/${a.total_questions} benar</span>
                                <span class="qs">${a.created_at ? new Date(a.created_at).toLocaleDateString('id-ID') : ''}</span>
                            </div>
                        `).join('')
                    }</div>
                `;
            } catch(e) {
                cell.innerHTML = `<div style="color:#b91c1c;font-size:12px;">Gagal memuat detail: ${e.message}</div>`;
            }
        } else {
            btn.textContent = 'Detail ▾';
        }
    };

    // ── Export CSV ────────────────────────────────────────────
    window.exportCSV = function() {
        const data = filtered.length > 0 ? filtered : allData;
        if (data.length === 0) { toast('Tidak ada data untuk diekspor.', 'err'); return; }

        const rows = [
            ['Nama', 'NIM', 'Email', 'Kelas', 'Angkatan', 'Sekolah/Fakultas', 'Jenjang', 'Total Quiz', 'Rata-rata Skor'],
            ...data.map(s => [
                s.name, s.nim || '', s.email, s.kelas || '',
                s.nim_info?.angkatan || '',
                s.nim_info?.sekolah  || '',
                s.nim_info?.jenjang  || '',
                s.total_quiz_taken ?? 0,
                s.avg_score ?? '',
            ])
        ];

        const csv  = rows.map(r => r.map(c => `"${String(c).replace(/"/g,'""')}"`).join(',')).join('\n');
        const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href  = URL.createObjectURL(blob);
        link.download = `data_mahasiswa_${new Date().toISOString().split('T')[0]}.csv`;
        link.click();
        toast('CSV berhasil diunduh!');
    };

    // ── Escape helper ─────────────────────────────────────────
    function esc(s) {
        return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    // ── Init ──────────────────────────────────────────────────
    fetchData(null);
})();
</script>
@endpush