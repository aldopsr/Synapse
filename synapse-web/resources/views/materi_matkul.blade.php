@extends('layouts.app')

@section('title', 'Kelola Materi - Synapse')
@section('header_title', 'Daftar Materi')

@section('content')
<style>
.page-header {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 24px; gap: 12px;
}
.page-header h2 { font-size: 20px; font-weight: 700; color: #1a1a1a; margin: 0; }

.back-link {
    display: inline-flex; align-items: center; gap: 7px;
    color: #888; font-size: 13px; font-weight: 600;
    text-decoration: none; margin-bottom: 20px; transition: color .15s;
}
.back-link:hover { color: #279685; }
.back-link:hover svg { transform: translateX(-3px); }
.back-link svg { transition: transform .15s; }

.btn-primary {
    display: inline-flex; align-items: center; gap: 6px;
    background: #279685; color: #fff; border: none;
    padding: 10px 18px; border-radius: 10px; font-size: 13px;
    font-weight: 600; text-decoration: none; cursor: pointer;
    transition: background .15s;
}
.btn-primary:hover { background: #1f7a6d; }

/* Table card */
.table-card {
    background: #fff; border-radius: 16px;
    border: 1px solid #eee; overflow: hidden;
}
table { width: 100%; border-collapse: collapse; }
thead tr { background: #f9fafb; }
thead th {
    padding: 12px 18px; font-size: 11px; font-weight: 700;
    color: #888; text-transform: uppercase; letter-spacing: .04em;
    text-align: left; border-bottom: 1px solid #eee;
}
tbody tr { border-bottom: 1px solid #f3f4f6; transition: background .1s; }
tbody tr:last-child { border-bottom: none; }
tbody tr:hover { background: #f9fafb; }
tbody td { padding: 14px 18px; font-size: 13px; color: #1a1a1a; vertical-align: middle; }

/* Badges */
.badge {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 4px 10px; border-radius: 20px;
    font-size: 11px; font-weight: 700;
}
.badge-yes  { background: #dcfce7; color: #15803d; }
.badge-no   { background: #fee2e2; color: #b91c1c; }
.badge-ar   { background: #f0eeff; color: #7c3aed; }

/* Action buttons */
.action-btns { display: flex; gap: 6px; flex-wrap: wrap; }
.btn-action {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 6px 12px; border-radius: 8px; font-size: 12px;
    font-weight: 600; border: none; cursor: pointer;
    text-decoration: none; transition: opacity .15s;
    white-space: nowrap;
}
.btn-action:hover { opacity: .85; }
.btn-practice { background: #e0f2fe; color: #0369a1; }
.btn-edit     { background: #fef3c7; color: #92400e; }
.btn-delete   { background: #fee2e2; color: #b91c1c; }

/* Material title */
.mat-title { font-weight: 700; font-size: 14px; color: #1a1a1a; }
.mat-desc  { font-size: 12px; color: #888; margin-top: 2px; }

/* Empty & loading */
.empty-row td { text-align: center; padding: 48px 20px; color: #aaa; }
.empty-row .ei { font-size: 36px; margin-bottom: 10px; }

/* Toast */
.toast {
    position: fixed; bottom: 24px; right: 24px;
    padding: 11px 18px; border-radius: 10px; font-size: 13px;
    font-weight: 600; z-index: 9999; color: #fff;
    transform: translateY(60px); opacity: 0;
    transition: all .28s ease; pointer-events: none;
    box-shadow: 0 8px 24px rgba(0,0,0,.15);
}
.toast.show { transform: translateY(0); opacity: 1; }
.toast.ok  { background: #279685; }
.toast.err { background: #ef4444; }
</style>

{{-- Back link (hidden untuk dosen) --}}
<a href="/mata-kuliah" class="back-link" id="btnKembali">
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
        stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>
    </svg>
    Kembali ke Daftar Matkul
</a>

<div class="page-header">
    <h2 id="judulHalaman">Materi Mata Kuliah</h2>
    <a href="/mata-kuliah/{{ $course_id }}/tambah-materi" class="btn-primary" id="btnTambah">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
            <path fill-rule="evenodd" d="M12 3.75a.75.75 0 0 1 .75.75v6.75h6.75a.75.75 0 0 1 0 1.5h-6.75v6.75a.75.75 0 0 1-1.5 0v-6.75H4.5a.75.75 0 0 1 0-1.5h6.75V4.5a.75.75 0 0 1 .75-.75Z" clip-rule="evenodd"/>
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
                <th width="300">Aksi</th>
            </tr>
        </thead>
        <tbody id="tabelMateri">
            <tr class="empty-row">
                <td colspan="4">
                    <div class="ei">⏳</div>
                    <div>Memuat data...</div>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<div class="toast" id="toast"></div>
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

    function toast(msg, type = 'ok') {
        const el = document.getElementById('toast');
        el.textContent = (type === 'ok' ? '✓  ' : '✕  ') + msg;
        el.className = `toast ${type} show`;
        clearTimeout(el._t);
        el._t = setTimeout(() => el.classList.remove('show'), 3000);
    }

    function esc(s) {
        return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

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
                <tr class="empty-row">
                    <td colspan="4">
                        <div class="ei">⚠️</div>
                        <div>Gagal memuat data</div>
                    </td>
                </tr>`;
        }
    }

    function renderTable(materials) {
        const tbody = document.getElementById('tabelMateri');

        if (!materials || materials.length === 0) {
            tbody.innerHTML = `
                <tr class="empty-row">
                    <td colspan="4">
                        <div class="ei">📭</div>
                        <div>Belum ada materi. Klik "Tambah Materi" untuk mulai.</div>
                    </td>
                </tr>`;
            return;
        }

        tbody.innerHTML = materials.map((m, i) => {
            const id          = m._id || m.id;
            const hasPractice = (m.questions && m.questions.length > 0) || m.has_practice;
            const hasAr       = (m.ar_assets && m.ar_assets.length > 0) || m.has_ar;

            const practiceBadge = hasPractice
                ? `<span class="badge badge-yes">✓ Ada Soal</span>`
                : `<span class="badge badge-no">Belum Ada</span>`;

            const arBadge = hasAr
                ? `<span class="badge badge-ar" style="margin-left:6px;">✨ AR</span>`
                : '';

            return `
            <tr>
                <td style="color:#aaa;font-size:12px;">${i + 1}</td>
                <td>
                    <div class="mat-title">${esc(m.title)} ${arBadge}</div>
                    ${m.description ? `<div class="mat-desc">${esc(m.description)}</div>` : ''}
                </td>
                <td>${practiceBadge}</td>
                <td>
                    <div class="action-btns">
                        <a href="/mata-kuliah/${activeCourseId}/materi/${id}/practice"
                            class="btn-action btn-practice">
                            🎯 Kelola Latihan
                        </a>
                        <a href="/mata-kuliah/${activeCourseId}/edit-materi/${id}"
                            class="btn-action btn-edit">
                            ✏️ Edit
                        </a>
                        <button class="btn-action btn-delete" onclick="hapusMateri('${id}')">
                            🗑 Hapus
                        </button>
                    </div>
                </td>
            </tr>`;
        }).join('');
    }

    window.hapusMateri = async function(materiId) {
        if (!confirm('Yakin ingin menghapus materi ini? Semua soal latihan di dalamnya juga akan terhapus.')) return;

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