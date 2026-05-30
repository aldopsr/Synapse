@extends('layouts.app')
@section('title', 'Kelola Materi - Synapse')
@section('header_title', 'Daftar Materi')

@section('content')
<style>
/* ── Back link ───────────────────────────────────────────── */
.back-link {
    display:inline-flex; align-items:center; gap:7px;
    color:#9ca3af; font-size:13px; font-weight:600;
    text-decoration:none; margin-bottom:20px; transition:color .15s;
}
.back-link:hover { color:#279685; }
.back-link:hover svg { transform:translateX(-3px); }
.back-link svg { width:15px; height:15px; transition:transform .15s; }

/* ── Page header ─────────────────────────────────────────── */
.page-header {
    display:flex; align-items:center; justify-content:space-between;
    margin-bottom:24px; gap:12px; flex-wrap:wrap;
}
.page-header h2 { font-size:20px; font-weight:800; color:#111827; margin:0; letter-spacing:-.3px; }

/* ── Table card ──────────────────────────────────────────── */
.table-card { background:#fff; border-radius:16px; border:1px solid #eff0f2; overflow:hidden; }
table { width:100%; border-collapse:collapse; }
thead tr { background:#fafafa; }
thead th {
    padding:11px 18px; font-size:11px; font-weight:700;
    color:#9ca3af; text-transform:uppercase; letter-spacing:.05em;
    text-align:left; border-bottom:1px solid #eff0f2;
}
tbody tr { border-bottom:1px solid #f5f6f8; transition:background .1s; }
tbody tr:last-child { border-bottom:none; }
tbody tr:hover { background:#f8fffe; }
tbody td { padding:14px 18px; font-size:13px; color:#111827; vertical-align:middle; }

/* ── Material info ───────────────────────────────────────── */
.mat-title { font-weight:700; font-size:14px; color:#111827; }
.mat-desc  { font-size:12px; color:#9ca3af; margin-top:3px; }

/* ── Badges ──────────────────────────────────────────────── */
.badge {
    display:inline-flex; align-items:center; gap:4px;
    padding:3px 10px; border-radius:99px; font-size:11px; font-weight:700;
}
.badge svg { width:10px; height:10px; }
.badge-yes { background:#f0fdf9; color:#279685; border:1px solid #b2e8e0; }
.badge-no  { background:#f9fafb; color:#9ca3af; border:1px solid #e5e7eb; }
.badge-ar  { background:#f5f3ff; color:#7c3aed; border:1px solid #ddd6fe; }

/* ── Row actions ─────────────────────────────────────────── */
.action-btns { display:flex; align-items:center; gap:6px; flex-wrap:wrap; }
.btn-act {
    display:inline-flex; align-items:center; gap:5px;
    padding:6px 12px; border-radius:8px; font-size:12px; font-weight:700;
    border:1.5px solid var(--b, #e5e7eb); background:var(--bg, #fff);
    color:var(--c, #374151); cursor:pointer; text-decoration:none;
    transition:background .12s, border-color .12s, color .12s;
    white-space:nowrap; font-family:inherit;
}
.btn-act svg { width:13px; height:13px; flex-shrink:0; }
.btn-act:hover { background:var(--bgh, #f5f6f8); border-color:var(--bh, #d1d5db); }

.ba-practice {
    --bg:#f0fdf9; --c:#0f6e56; --b:#b2e8e0;
    --bgh:#dcfaf5; --bh:#81c9c0;
}
.ba-edit {
    --bg:#fefce8; --c:#854d0e; --b:#fde68a;
    --bgh:#fef9c3; --bh:#fcd34d;
}
.ba-delete {
    --bg:#fef2f2; --c:#dc2626; --b:#fecaca;
    --bgh:#fee2e2; --bh:#fca5a5;
}

/* ── Empty / loading ─────────────────────────────────────── */
.empty-row td { text-align:center; padding:64px 20px; }
.empty-ico {
    width:52px; height:52px; border-radius:16px; background:#f5f6f8;
    display:flex; align-items:center; justify-content:center;
    margin:0 auto 14px;
}
.empty-ico svg { width:26px; height:26px; color:#d1d5db; }
.empty-label { font-size:14px; font-weight:700; color:#374151; margin-bottom:5px; }
.empty-sub   { font-size:13px; color:#9ca3af; }
</style>

<a href="/mata-kuliah" class="back-link" id="btnKembali">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>
    </svg>
    Kembali ke Daftar Matkul
</a>

<div class="page-header">
    <h2 id="judulHalaman">Materi Mata Kuliah</h2>
    <a href="/mata-kuliah/{{ $course_id }}/tambah-materi" class="btn btn-primary" id="btnTambah" style="border-radius:99px">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        Tambah Materi
    </a>
</div>

<div class="table-card">
    <table>
        <thead>
            <tr>
                <th width="48">#</th>
                <th>Materi</th>
                <th width="120">Latihan</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody id="tabelMateri">
            <tr class="empty-row">
                <td colspan="4">
                    <div class="empty-ico">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </div>
                    <div class="empty-label">Memuat data...</div>
                </td>
            </tr>
        </tbody>
    </table>
</div>
@endsection

@push('scripts')
<script>
(function() {
    const token = window.token || localStorage.getItem('token');
    if (!token) { window.location.href = '/'; return; }

    let activeCourseId = "{{ $course_id ?? '' }}";
    const userStr = localStorage.getItem('user');

    if (userStr) {
        try {
            const user = JSON.parse(userStr);
            if (user.role === 'dosen') {
                activeCourseId = user.course_id;
                document.getElementById('btnKembali').style.display = 'none';
                document.getElementById('judulHalaman').innerText = 'Materi Kuliah Saya';
                document.getElementById('btnTambah').href = `/mata-kuliah/${activeCourseId}/tambah-materi`;
            }
        } catch (e) {}
    }

    function esc(s) {
        return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    const SVG = {
        check: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>`,
        ar:    `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l9 4.9V17L12 22 3 17V6.9L12 2z"/><polyline points="3 7 12 12 21 7"/><line x1="12" y1="12" x2="12" y2="22"/></svg>`,
        practice:`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>`,
        edit:  `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>`,
        trash: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>`,
        inbox: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>`,
        warn:  `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.46 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>`,
    };

    async function fetchMaterials() {
        try {
            const res = await fetch(`${window.apiBaseUrl}/courses/${activeCourseId}/materials`, {
                headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
            });
            if (res.status === 401) { window.logout(); return; }
            const data = await res.json();
            renderTable(data.data || data);
        } catch (e) {
            document.getElementById('tabelMateri').innerHTML = `
                <tr class="empty-row"><td colspan="4">
                    <div class="empty-ico">${SVG.warn}</div>
                    <div class="empty-label">Gagal memuat data</div>
                    <div class="empty-sub">Periksa koneksi dan coba lagi</div>
                </td></tr>`;
        }
    }

    function renderTable(materials) {
        const tbody = document.getElementById('tabelMateri');

        if (!materials || materials.length === 0) {
            tbody.innerHTML = `
                <tr class="empty-row"><td colspan="4">
                    <div class="empty-ico">${SVG.inbox}</div>
                    <div class="empty-label">Belum ada materi</div>
                    <div class="empty-sub">Klik "Tambah Materi" untuk mulai menambahkan</div>
                </td></tr>`;
            return;
        }

        tbody.innerHTML = materials.map((m, i) => {
            const id          = m._id || m.id;
            const hasPractice = (m.questions && m.questions.length > 0) || m.has_practice;
            const hasAr       = (m.ar_assets && m.ar_assets.length > 0) || m.has_ar;

            const practiceBadge = hasPractice
                ? `<span class="badge badge-yes">${SVG.check} Ada Soal</span>`
                : `<span class="badge badge-no">Belum Ada</span>`;

            const arBadge = hasAr
                ? `<span class="badge badge-ar" style="margin-left:6px">${SVG.ar} 3D</span>`
                : '';

            return `<tr>
                <td style="color:#d1d5db;font-size:12px;font-weight:500">${i + 1}</td>
                <td>
                    <div class="mat-title">${esc(m.title)} ${arBadge}</div>
                    ${m.description ? `<div class="mat-desc">${esc(m.description)}</div>` : ''}
                </td>
                <td>${practiceBadge}</td>
                <td>
                    <div class="action-btns">
                        <a href="/mata-kuliah/${activeCourseId}/materi/${id}/practice" class="btn-act ba-practice">
                            ${SVG.practice} Latihan
                        </a>
                        <a href="/mata-kuliah/${activeCourseId}/edit-materi/${id}" class="btn-act ba-edit">
                            ${SVG.edit} Edit
                        </a>
                        <button class="btn-act ba-delete" onclick="hapusMateri('${id}')">
                            ${SVG.trash} Hapus
                        </button>
                    </div>
                </td>
            </tr>`;
        }).join('');
    }

    window.hapusMateri = function(materiId) {
        showDialog({
            icon: 'err',
            title: 'Hapus Materi',
            msg: 'Yakin hapus materi ini? Semua soal latihan di dalamnya juga akan ikut terhapus.',
            confirmText: 'Hapus',
            confirmClass: 'confirm-err',
            onConfirm: () => doHapusMateri(materiId)
        });
    };

    window.doHapusMateri = async function(materiId) {
        try {
            const res = await fetch(`${window.apiBaseUrl}/materials/${materiId}`, {
                method: 'DELETE',
                headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
            });
            if (res.ok) {
                toast('Materi berhasil dihapus!');
                fetchMaterials();
            } else {
                const data = await res.json();
                toast(data.message || 'Gagal menghapus materi.', 'err');
            }
        } catch (e) {
            toast('Koneksi bermasalah.', 'err');
        }
    };

    if (activeCourseId) fetchMaterials();
})();
</script>
@endpush