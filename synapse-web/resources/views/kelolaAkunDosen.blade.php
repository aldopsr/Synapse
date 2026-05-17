@extends('layouts.app')

@section('title', 'Kelola Akun Dosen - Synapse')
@section('header_title', 'Kelola Akun Dosen')

@section('content')
<style>
/* =====================================================
   KELOLA AKUN DOSEN — modernized
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
    display: inline-flex;
    align-items: center;
    gap: 7px;
    background: #279685;
    color: #fff;
    border: none;
    padding: 10px 18px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    transition: background .18s, transform .18s;
    white-space: nowrap;
    font-family: inherit;
}
.btn-primary:hover { background: #1f7a6c; transform: translateY(-2px); }

/* --- Toolbar (search + filter) --- */
.toolbar {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 16px;
    flex-wrap: wrap;
}
.search-wrap {
    position: relative;
    flex: 1;
    min-width: 200px;
    max-width: 380px;
}
.search-wrap svg {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #aaa;
    pointer-events: none;
}
.search-input {
    width: 100%;
    padding: 9px 12px 9px 38px;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    font-size: 13px;
    font-family: inherit;
    background: #fff;
    color: #1a1a1a;
    box-sizing: border-box;
    transition: border-color .15s, box-shadow .15s;
}
.search-input:focus {
    outline: none;
    border-color: #279685;
    box-shadow: 0 0 0 3px rgba(39,150,133,.1);
}
.search-input::placeholder { color: #bbb; }

.filter-select {
    padding: 9px 14px;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    font-size: 13px;
    font-family: inherit;
    background: #fff;
    color: #444;
    cursor: pointer;
    transition: border-color .15s;
}
.filter-select:focus { outline: none; border-color: #279685; }

.count-badge {
    font-size: 12px;
    font-weight: 700;
    color: #888;
    white-space: nowrap;
    margin-left: auto;
}
.count-badge span { color: #279685; }

/* --- Table container --- */
.table-wrap {
    background: #fff;
    border-radius: 16px;
    border: 1px solid #eee;
    overflow: hidden;
}

table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}
thead tr {
    background: #f8fafa;
    border-bottom: 1px solid #eee;
}
th {
    padding: 12px 18px;
    text-align: left;
    font-size: 11px;
    font-weight: 700;
    color: #888;
    text-transform: uppercase;
    letter-spacing: .05em;
    white-space: nowrap;
    user-select: none;
    cursor: pointer;
}
th:hover { color: #279685; }
th .sort-icon { margin-left: 4px; opacity: .4; font-style: normal; }
th.sorted .sort-icon { opacity: 1; color: #279685; }

tbody tr {
    border-bottom: 1px solid #f5f5f5;
    transition: background .12s;
}
tbody tr:last-child { border-bottom: none; }
tbody tr:hover { background: #fafcfb; }

td { padding: 14px 18px; vertical-align: middle; }

/* --- Avatar + name cell --- */
.dosen-cell { display: flex; align-items: center; gap: 12px; }
.avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #279685, #4A90E2);
    color: #fff;
    font-size: 14px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    letter-spacing: .5px;
}
.dosen-name  { font-weight: 700; color: #1a1a1a; margin-bottom: 2px; font-size: 13px; }
.dosen-email { font-size: 11px; color: #aaa; }

/* --- Matkul badge --- */
.matkul-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 5px 10px;
    border-radius: 99px;
    font-size: 11px;
    font-weight: 700;
    max-width: 220px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.matkul-badge.assigned   { background: #e3faf8; color: #0f6e56; }
.matkul-badge.unassigned { background: #fff3cd; color: #856404; }

/* --- Action buttons --- */
.actions { display: flex; align-items: center; gap: 6px; }
.btn-icon {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 700;
    border: none;
    cursor: pointer;
    transition: background .15s, transform .12s;
    font-family: inherit;
    text-decoration: none;
    white-space: nowrap;
}
.btn-icon:hover { transform: translateY(-1px); }
.btn-edit   { background: #e8f1fd; color: #185fa5; }
.btn-edit:hover   { background: #b5d4f4; }
.btn-reset  { background: #f0eeff; color: #534ab7; }
.btn-reset:hover  { background: #cec8f6; }
.btn-delete { background: #fdeaea; color: #991b1b; }
.btn-delete:hover { background: #f7c1c1; }

/* --- Skeleton loader --- */
.skeleton {
    background: linear-gradient(90deg,#f0f0f0 25%,#e8e8e8 50%,#f0f0f0 75%);
    background-size: 200% 100%;
    animation: shimmer 1.4s infinite;
    border-radius: 6px;
    display: inline-block;
}
@keyframes shimmer {
    0%   { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

/* --- Empty state --- */
.empty-row td {
    text-align: center;
    padding: 60px 20px;
    color: #bbb;
}
.empty-icon { font-size: 40px; margin-bottom: 10px; }
.empty-label { font-size: 14px; font-weight: 600; color: #888; margin-bottom: 4px; }
.empty-sub   { font-size: 12px; color: #bbb; }

/* =====================================================
   MODAL
   ===================================================== */
.modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.45);
    z-index: 1000;
    align-items: center;
    justify-content: center;
    padding: 20px;
}
.modal-overlay.open { display: flex; }

.modal-box {
    background: #fff;
    border-radius: 18px;
    width: 100%;
    max-width: 500px;
    box-shadow: 0 24px 60px rgba(0,0,0,.18);
    animation: slideUp .22s ease;
    overflow: hidden;
}
@keyframes slideUp {
    from { opacity:0; transform: translateY(20px); }
    to   { opacity:1; transform: translateY(0); }
}

.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 22px 24px 18px;
    border-bottom: 1px solid #f0f0f0;
}
.modal-header h3 { font-size: 16px; font-weight: 700; color: #1a1a1a; margin: 0; }
.modal-header p  { font-size: 12px; color: #aaa; margin: 3px 0 0; }
.modal-close {
    width: 32px; height: 32px;
    border-radius: 8px; border: none;
    background: #f3f4f6; cursor: pointer; font-size: 16px;
    display: flex; align-items: center; justify-content: center;
    transition: background .15s; color: #555;
}
.modal-close:hover { background: #e5e7eb; }

.modal-body { padding: 20px 24px; }

.form-row { display: flex; gap: 12px; }
.form-row .fg { flex: 1; }

.fg { margin-bottom: 16px; }
.fg label {
    display: block;
    font-size: 12px;
    font-weight: 700;
    color: #555;
    margin-bottom: 6px;
    text-transform: uppercase;
    letter-spacing: .04em;
}
.fg label .req { color: #ef4444; margin-left: 2px; }
.fc {
    width: 100%;
    padding: 10px 13px;
    border: 1px solid #e5e7eb;
    border-radius: 9px;
    font-size: 13px;
    font-family: inherit;
    color: #1a1a1a;
    background: #fff;
    box-sizing: border-box;
    transition: border-color .15s, box-shadow .15s;
}
.fc:focus {
    outline: none;
    border-color: #279685;
    box-shadow: 0 0 0 3px rgba(39,150,133,.1);
}
.fc::placeholder { color: #ccc; }
select.fc { cursor: pointer; }

/* Password strength bar */
.pwd-strength { height: 3px; border-radius: 99px; background: #eee; margin-top: 6px; overflow: hidden; }
.pwd-strength-fill { height: 100%; border-radius: 99px; transition: width .3s, background .3s; width: 0; }
.pwd-hint { font-size: 11px; color: #aaa; margin-top: 4px; }

/* Password toggle */
.pwd-wrap { position: relative; }
.pwd-toggle {
    position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
    background: none; border: none; cursor: pointer; color: #aaa; padding: 0; line-height: 1;
}

.modal-footer {
    display: flex; gap: 10px;
    padding: 16px 24px 22px;
    border-top: 1px solid #f0f0f0;
}
.btn-cancel {
    flex: 1; padding: 10px;
    border: 1px solid #e5e7eb; border-radius: 9px;
    background: #fff; color: #555; font-size: 13px; font-weight: 700;
    cursor: pointer; transition: background .15s; font-family: inherit;
}
.btn-cancel:hover { background: #f3f4f6; }
.btn-submit {
    flex: 2; padding: 10px;
    background: #279685; color: #fff; border: none; border-radius: 9px;
    font-size: 13px; font-weight: 700; cursor: pointer;
    transition: background .15s; font-family: inherit;
    display: flex; align-items: center; justify-content: center; gap: 6px;
}
.btn-submit:hover { background: #1f7a6c; }
.btn-submit:disabled { background: #aaa; cursor: not-allowed; }

/* --- Modal edit: read-only info --- */
.edit-info-card {
    display: flex; align-items: center; gap: 12px;
    background: #f8fafa; border-radius: 10px;
    padding: 12px 14px; margin-bottom: 18px;
}
.edit-info-card .avatar-lg {
    width: 46px; height: 46px; border-radius: 50%;
    background: linear-gradient(135deg,#279685,#4A90E2);
    color: #fff; font-size: 16px; font-weight: 700;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.edit-info-card .info .name  { font-weight: 700; color: #1a1a1a; font-size: 14px; }
.edit-info-card .info .email { font-size: 12px; color: #888; margin-top: 2px; }

/* --- Toast --- */
.toast {
    position: fixed; bottom: 24px; right: 24px;
    padding: 12px 18px; border-radius: 10px;
    font-size: 13px; font-weight: 600; z-index: 9999;
    transform: translateY(80px); opacity: 0;
    transition: all .3s cubic-bezier(.34,1.56,.64,1);
    display: flex; align-items: center; gap: 8px;
    max-width: 320px; box-shadow: 0 8px 24px rgba(0,0,0,.18);
    color: #fff;
}
.toast.show { transform: translateY(0); opacity: 1; }
.toast.success { background: #279685; }
.toast.error   { background: #ef4444; }
.toast.info    { background: #185fa5; }
</style>

{{-- =========== PAGE HEADER =========== --}}
<div class="page-header">
    <div class="page-header-left">
        <h2>Akun Dosen</h2>
        <p id="pageSubtitle">Memuat data...</p>
    </div>
    <button class="btn-primary" onclick="bukaModalTambah()">
        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="currentColor">
            <path fill-rule="evenodd" d="M12 3.75a.75.75 0 0 1 .75.75v6.75h6.75a.75.75 0 0 1 0 1.5h-6.75v6.75a.75.75 0 0 1-1.5 0v-6.75H4.5a.75.75 0 0 1 0-1.5h6.75V4.5a.75.75 0 0 1 .75-.75Z" clip-rule="evenodd"/>
        </svg>
        Tambah Dosen
    </button>
</div>

{{-- =========== TOOLBAR =========== --}}
<div class="toolbar">
    <div class="search-wrap">
        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
        </svg>
        <input type="text" class="search-input" id="searchInput"
            placeholder="Cari nama atau email dosen..."
            oninput="filterTable()">
    </div>
    <select class="filter-select" id="filterMatkul" onchange="filterTable()">
        <option value="">Semua Matkul</option>
    </select>
    <span class="count-badge" id="countBadge">Memuat...</span>
</div>

{{-- =========== TABLE =========== --}}
<div class="table-wrap">
    <table id="mainTable">
        <thead>
            <tr>
                <th onclick="sortTable('name')" data-col="name">
                    Dosen <i class="sort-icon" id="sort-name">↕</i>
                </th>
                <th onclick="sortTable('matkul')" data-col="matkul">
                    Mata Kuliah <i class="sort-icon" id="sort-matkul">↕</i>
                </th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody id="tableBody">
            {{-- Skeleton rows --}}
            @for ($i = 0; $i < 5; $i++)
            <tr>
                <td>
                    <div class="dosen-cell">
                        <div class="skeleton" style="width:40px;height:40px;border-radius:50%;flex-shrink:0;"></div>
                        <div>
                            <div class="skeleton" style="width:140px;height:13px;margin-bottom:5px;"></div>
                            <div class="skeleton" style="width:180px;height:11px;"></div>
                        </div>
                    </div>
                </td>
                <td><div class="skeleton" style="width:130px;height:26px;border-radius:99px;"></div></td>
                <td>
                    <div style="display:flex;gap:6px;">
                        <div class="skeleton" style="width:60px;height:30px;border-radius:8px;"></div>
                        <div class="skeleton" style="width:50px;height:30px;border-radius:8px;"></div>
                        <div class="skeleton" style="width:60px;height:30px;border-radius:8px;"></div>
                    </div>
                </td>
            </tr>
            @endfor
        </tbody>
    </table>
</div>

{{-- =========== MODAL: TAMBAH DOSEN =========== --}}
<div class="modal-overlay" id="modalTambah">
    <div class="modal-box">
        <div class="modal-header">
            <div>
                <h3>Tambah Akun Dosen</h3>
                <p>Akun langsung aktif setelah disimpan</p>
            </div>
            <button class="modal-close" onclick="tutupModal('modalTambah')">✕</button>
        </div>
        <div class="modal-body">
            <div class="fg">
                <label>Nama Lengkap <span class="req">*</span></label>
                <input type="text" id="addName" class="fc" placeholder="Contoh: Dr. Budi Santoso, M.Kom">
            </div>
            <div class="fg">
                <label>Email <span class="req">*</span></label>
                <input type="email" id="addEmail" class="fc" placeholder="email@kampus.ac.id">
            </div>
            <div class="fg">
                <label>Mata Kuliah Diampu <span class="req">*</span></label>
                <select id="addCourse" class="fc">
                    <option value="">— Pilih mata kuliah —</option>
                </select>
            </div>
            <div class="form-row">
                <div class="fg">
                    <label>Password <span class="req">*</span></label>
                    <div class="pwd-wrap">
                        <input type="password" id="addPassword" class="fc" placeholder="Min. 8 karakter"
                            oninput="checkStrength(this.value,'addStrengthFill','addStrengthHint')">
                        <button type="button" class="pwd-toggle" onclick="togglePwd('addPassword',this)">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                    <div class="pwd-strength"><div class="pwd-strength-fill" id="addStrengthFill"></div></div>
                    <div class="pwd-hint" id="addStrengthHint">Masukkan password</div>
                </div>
                <div class="fg">
                    <label>Konfirmasi Password <span class="req">*</span></label>
                    <div class="pwd-wrap">
                        <input type="password" id="addPasswordConfirm" class="fc" placeholder="Ulangi password">
                        <button type="button" class="pwd-toggle" onclick="togglePwd('addPasswordConfirm',this)">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel" onclick="tutupModal('modalTambah')">Batal</button>
            <button class="btn-submit" id="btnTambahSubmit" onclick="simpanTambah()">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M19.916 4.626a.75.75 0 0 1 .208 1.04l-9 13.5a.75.75 0 0 1-1.154.114l-6-6a.75.75 0 0 1 1.06-1.06l5.353 5.353 8.493-12.739a.75.75 0 0 1 1.04-.208Z" clip-rule="evenodd"/></svg>
                Simpan Akun
            </button>
        </div>
    </div>
</div>

{{-- =========== MODAL: EDIT DOSEN =========== --}}
<div class="modal-overlay" id="modalEdit">
    <div class="modal-box">
        <div class="modal-header">
            <div>
                <h3>Edit Akun Dosen</h3>
                <p>Kosongkan password jika tidak ingin mengubah</p>
            </div>
            <button class="modal-close" onclick="tutupModal('modalEdit')">✕</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="editId">
            <div class="edit-info-card">
                <div class="avatar-lg" id="editAvatarLg">?</div>
                <div class="info">
                    <div class="name"  id="editInfoName">—</div>
                    <div class="email" id="editInfoEmail">—</div>
                </div>
            </div>
            <div class="fg">
                <label>Nama Lengkap <span class="req">*</span></label>
                <input type="text" id="editName" class="fc" placeholder="Nama lengkap">
            </div>
            <div class="fg">
                <label>Email <span class="req">*</span></label>
                <input type="email" id="editEmail" class="fc" placeholder="Email">
            </div>
            <div class="fg">
                <label>Mata Kuliah Diampu <span class="req">*</span></label>
                <select id="editCourse" class="fc">
                    <option value="">— Pilih mata kuliah —</option>
                </select>
            </div>
            <div class="fg">
                <label>Password Baru <span style="color:#aaa;font-weight:400;text-transform:none;">(opsional)</span></label>
                <div class="pwd-wrap">
                    <input type="password" id="editPassword" class="fc" placeholder="Kosongkan jika tidak diubah"
                        oninput="checkStrength(this.value,'editStrengthFill','editStrengthHint')">
                    <button type="button" class="pwd-toggle" onclick="togglePwd('editPassword',this)">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
                <div class="pwd-strength"><div class="pwd-strength-fill" id="editStrengthFill"></div></div>
                <div class="pwd-hint" id="editStrengthHint"></div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel" onclick="tutupModal('modalEdit')">Batal</button>
            <button class="btn-submit" id="btnEditSubmit" onclick="simpanEdit()">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M19.916 4.626a.75.75 0 0 1 .208 1.04l-9 13.5a.75.75 0 0 1-1.154.114l-6-6a.75.75 0 0 1 1.06-1.06l5.353 5.353 8.493-12.739a.75.75 0 0 1 1.04-.208Z" clip-rule="evenodd"/></svg>
                Simpan Perubahan
            </button>
        </div>
    </div>
</div>

{{-- =========== MODAL: RESET PASSWORD =========== --}}
<div class="modal-overlay" id="modalReset">
    <div class="modal-box" style="max-width:420px;">
        <div class="modal-header">
            <div>
                <h3>Reset Password</h3>
                <p id="resetSubtitle">—</p>
            </div>
            <button class="modal-close" onclick="tutupModal('modalReset')">✕</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="resetId">
            <div class="fg">
                <label>Password Baru <span class="req">*</span></label>
                <div class="pwd-wrap">
                    <input type="password" id="resetPassword" class="fc" placeholder="Min. 8 karakter"
                        oninput="checkStrength(this.value,'resetStrengthFill','resetStrengthHint')">
                    <button type="button" class="pwd-toggle" onclick="togglePwd('resetPassword',this)">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
                <div class="pwd-strength"><div class="pwd-strength-fill" id="resetStrengthFill"></div></div>
                <div class="pwd-hint" id="resetStrengthHint">Masukkan password baru</div>
            </div>
            <div class="fg">
                <label>Konfirmasi Password <span class="req">*</span></label>
                <input type="password" id="resetPasswordConfirm" class="fc" placeholder="Ulangi password baru">
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel" onclick="tutupModal('modalReset')">Batal</button>
            <button class="btn-submit" id="btnResetSubmit" onclick="simpanReset()">
                Reset Password
            </button>
        </div>
    </div>
</div>

{{-- Toast --}}
<div class="toast" id="toast"></div>

@endsection

@push('scripts')
<script>
(function () {
    /* ── globals ─────────────────────────────────────────────── */
    const API   = window.apiBaseUrl;
    const token = window.token;
    const $     = id => document.getElementById(id);

    let allDosen  = [];   // raw data dari server
    let courseMap = {};   // id → title
    let sortCol   = 'name';
    let sortAsc   = true;

    /* ── helpers ─────────────────────────────────────────────── */
    function initials(name) {
        if (!name) return '?';
        const p = name.trim().split(' ');
        return p.length >= 2 ? (p[0][0] + p[1][0]).toUpperCase() : p[0].slice(0,2).toUpperCase();
    }
    function esc(s) {
        return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    /* ── Toast ───────────────────────────────────────────────── */
    function toast(msg, type = 'success') {
        const el = $('toast');
        el.textContent = type === 'success' ? '✓  ' + msg : type === 'error' ? '✕  ' + msg : 'ℹ  ' + msg;
        el.className   = 'toast ' + type + ' show';
        clearTimeout(el._t);
        el._t = setTimeout(() => el.classList.remove('show'), 3400);
    }

    /* ── Modal helpers ───────────────────────────────────────── */
    window.tutupModal = id => $(id).classList.remove('open');
    document.querySelectorAll('.modal-overlay').forEach(el =>
        el.addEventListener('click', e => { if (e.target === el) el.classList.remove('open'); })
    );

    /* ── Password helpers ────────────────────────────────────── */
    window.togglePwd = function(inputId, btn) {
        const inp = $(inputId);
        const show = inp.type === 'password';
        inp.type = show ? 'text' : 'password';
        btn.style.color = show ? '#279685' : '#aaa';
    };

    window.checkStrength = function(val, barId, hintId) {
        const bar  = $(barId);
        const hint = $(hintId);
        if (!val) { bar.style.width = '0'; hint.textContent = ''; return; }
        let score = 0;
        if (val.length >= 8)  score++;
        if (/[A-Z]/.test(val)) score++;
        if (/[0-9]/.test(val)) score++;
        if (/[^A-Za-z0-9]/.test(val)) score++;
        const map = [
            { w: '25%', bg: '#ef4444', label: 'Terlalu lemah' },
            { w: '50%', bg: '#f59e0b', label: 'Cukup' },
            { w: '75%', bg: '#3b82f6', label: 'Kuat' },
            { w: '100%',bg: '#279685', label: 'Sangat kuat ✓' },
        ];
        const m = map[score - 1] || map[0];
        bar.style.width      = m.w;
        bar.style.background = m.bg;
        hint.textContent     = m.label;
        hint.style.color     = m.bg;
    };

    /* ── Fill course select ──────────────────────────────────── */
    function fillCourseSelect(selId, selectedId = null) {
        const sel = $(selId);
        sel.innerHTML = '<option value="">— Pilih mata kuliah —</option>';
        Object.entries(courseMap).forEach(([id, title]) => {
            const opt = document.createElement('option');
            opt.value = id; opt.textContent = title;
            if (selectedId && id == selectedId) opt.selected = true;
            sel.appendChild(opt);
        });
    }

    /* ── FETCH courses (untuk dropdown) ─────────────────────── */
    async function fetchCourses() {
        try {
            const res  = await fetch(API + '/courses', {
                headers: { Authorization: 'Bearer ' + token, Accept: 'application/json' }
            });
            const data = await res.json();
            const list = data.data || [];
            list.forEach(c => { courseMap[c._id || c.id] = c.title; });

            /* Fill filter dropdown */
            const filt = $('filterMatkul');
            filt.innerHTML = '<option value="">Semua Matkul</option>';
            list.forEach(c => {
                const id  = c._id || c.id;
                const opt = document.createElement('option');
                opt.value = id; opt.textContent = c.title;
                filt.appendChild(opt);
            });
        } catch (e) { console.warn('[Dosen] Gagal fetch courses:', e.message); }
    }

    /* ── FETCH dosen ─────────────────────────────────────────── */
    async function fetchDosen() {
        try {
            const res  = await fetch(API + '/dosen', {
                headers: { Authorization: 'Bearer ' + token, Accept: 'application/json' }
            });
            if (res.status === 401) { window.logout(); return; }
            const data = await res.json();
            allDosen   = data.data || [];
            $('pageSubtitle').textContent = allDosen.length + ' akun dosen terdaftar';
            renderTable(allDosen);
        } catch (e) {
            $('tableBody').innerHTML = `<tr class="empty-row"><td colspan="3">
                <div class="empty-icon">⚠️</div>
                <div class="empty-label">Gagal memuat data</div>
                <div class="empty-sub">${esc(e.message)}</div>
            </td></tr>`;
        }
    }

    /* ── RENDER table ────────────────────────────────────────── */
    function renderTable(data) {
        const tbody = $('tableBody');

        if (!data || data.length === 0) {
            tbody.innerHTML = `<tr class="empty-row"><td colspan="3">
                <div class="empty-icon">👨‍🏫</div>
                <div class="empty-label">Belum ada akun dosen</div>
                <div class="empty-sub">Klik "Tambah Dosen" untuk menambahkan akun pertama</div>
            </td></tr>`;
            $('countBadge').innerHTML = '<span>0</span> dosen';
            return;
        }

        $('countBadge').innerHTML = `<span>${data.length}</span> dari ${allDosen.length} dosen`;

        tbody.innerHTML = data.map(d => {
            const id       = d._id || d.id;
            const name     = d.name  || '—';
            const email    = d.email || '—';
            const ini      = initials(name);
            const courseId = d.course_id;
            const courseTitle = courseId && courseMap[courseId]
                ? courseMap[courseId]
                : (d.course ? (d.course.title || d.course.name) : null);

            const matkulHtml = courseTitle
                ? `<span class="matkul-badge assigned">📚 ${esc(courseTitle)}</span>`
                : `<span class="matkul-badge unassigned">⚠ Belum ditugaskan</span>`;

            return `
            <tr id="row-${id}">
                <td>
                    <div class="dosen-cell">
                        <div class="avatar">${esc(ini)}</div>
                        <div>
                            <div class="dosen-name">${esc(name)}</div>
                            <div class="dosen-email">${esc(email)}</div>
                        </div>
                    </div>
                </td>
                <td>${matkulHtml}</td>
                <td>
                    <div class="actions">
                        <button class="btn-icon btn-edit"
                            onclick="bukaModalEdit('${id}','${esc(name).replace(/'/g,"\\'")}','${esc(email).replace(/'/g,"\\'")}','${courseId||''}')">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-8.4 8.4a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32l8.4-8.4Z"/><path d="M5.25 5.25a3 3 0 0 0-3 3v10.5a3 3 0 0 0 3 3h10.5a3 3 0 0 0 3-3V13.5a.75.75 0 0 0-1.5 0v5.25a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5V8.25a1.5 1.5 0 0 1 1.5-1.5h5.25a.75.75 0 0 0 0-1.5H5.25Z"/></svg>
                            Edit
                        </button>
                        <button class="btn-icon btn-reset"
                            onclick="bukaModalReset('${id}','${esc(name).replace(/'/g,"\\'")}')">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M12 1.5a5.25 5.25 0 0 0-5.25 5.25v3a3 3 0 0 0-3 3v6.75a3 3 0 0 0 3 3h10.5a3 3 0 0 0 3-3v-6.75a3 3 0 0 0-3-3v-3c0-2.9-2.35-5.25-5.25-5.25Zm3.75 8.25v-3a3.75 3.75 0 1 0-7.5 0v3h7.5Z" clip-rule="evenodd"/></svg>
                            Reset Sandi
                        </button>
                        <button class="btn-icon btn-delete"
                            onclick="hapusDosen('${id}','${esc(name).replace(/'/g,"\\'")}')">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M16.5 4.478v.227a48.816 48.816 0 0 1 3.878.512.75.75 0 1 1-.256 1.478l-.209-.035-1.005 13.07a3 3 0 0 1-2.991 2.77H8.084a3 3 0 0 1-2.991-2.77L4.087 6.66l-.209.035a.75.75 0 0 1-.256-1.478A48.567 48.567 0 0 1 7.5 4.705v-.227c0-1.564 1.213-2.9 2.816-2.951a52.662 52.662 0 0 1 3.369 0c1.603.051 2.815 1.387 2.815 2.951Zm-6.136-1.452a51.196 51.196 0 0 1 3.273 0C14.39 3.05 15 3.684 15 4.478v.113a49.488 49.488 0 0 0-6 0v-.113c0-.794.609-1.428 1.364-1.452Zm-.355 5.945a.75.75 0 1 0-1.5.058l.347 9a.75.75 0 1 0 1.499-.058l-.346-9Zm5.48.058a.75.75 0 1 0-1.498-.058l-.347 9a.75.75 0 0 0 1.5.058l.345-9Z" clip-rule="evenodd"/></svg>
                            Hapus
                        </button>
                    </div>
                </td>
            </tr>`;
        }).join('');
    }

    /* ── FILTER + SEARCH + SORT ──────────────────────────────── */
    window.filterTable = function() {
        const q      = $('searchInput').value.trim().toLowerCase();
        const fMatkul= $('filterMatkul').value;

        let filtered = allDosen.filter(d => {
            const name  = (d.name  || '').toLowerCase();
            const email = (d.email || '').toLowerCase();
            const matchQ = !q || name.includes(q) || email.includes(q);

            const cid = d.course_id || (d.course && (d.course._id || d.course.id));
            const matchMatkul = !fMatkul || cid == fMatkul;

            return matchQ && matchMatkul;
        });

        // Sort
        filtered = sortData(filtered);
        renderTable(filtered);
    };

    function sortData(data) {
        return [...data].sort((a, b) => {
            let va, vb;
            if (sortCol === 'name') {
                va = (a.name || '').toLowerCase();
                vb = (b.name || '').toLowerCase();
            } else {
                const getTitle = d => {
                    const cid = d.course_id;
                    return cid && courseMap[cid] ? courseMap[cid].toLowerCase() : '';
                };
                va = getTitle(a); vb = getTitle(b);
            }
            return sortAsc ? va.localeCompare(vb) : vb.localeCompare(va);
        });
    }

    window.sortTable = function(col) {
        if (sortCol === col) sortAsc = !sortAsc;
        else { sortCol = col; sortAsc = true; }

        // Update sort icons
        document.querySelectorAll('th .sort-icon').forEach(el => el.textContent = '↕');
        document.querySelectorAll('th').forEach(el => el.classList.remove('sorted'));
        const th = document.querySelector(`th[data-col="${col}"]`);
        if (th) {
            th.classList.add('sorted');
            th.querySelector('.sort-icon').textContent = sortAsc ? '↑' : '↓';
        }
        filterTable();
    };

    /* ── MODAL: TAMBAH ───────────────────────────────────────── */
    window.bukaModalTambah = function() {
        ['addName','addEmail','addPassword','addPasswordConfirm'].forEach(id => $(id).value = '');
        $('addStrengthFill').style.width = '0';
        $('addStrengthHint').textContent = 'Masukkan password';
        fillCourseSelect('addCourse');
        $('modalTambah').classList.add('open');
        setTimeout(() => $('addName').focus(), 80);
    };

    window.simpanTambah = async function() {
        const name  = $('addName').value.trim();
        const email = $('addEmail').value.trim();
        const pwd   = $('addPassword').value;
        const pwd2  = $('addPasswordConfirm').value;
        const course= $('addCourse').value;

        if (!name)   return toast('Nama wajib diisi.', 'error');
        if (!email)  return toast('Email wajib diisi.', 'error');
        if (!course) return toast('Pilih mata kuliah terlebih dahulu.', 'error');
        if (!pwd)    return toast('Password wajib diisi.', 'error');
        if (pwd.length < 8) return toast('Password minimal 8 karakter.', 'error');
        if (pwd !== pwd2)   return toast('Konfirmasi password tidak cocok.', 'error');

        setLoading('btnTambahSubmit', true, 'Menyimpan...');
        try {
            const res  = await fetch(API + '/dosen', {
                method: 'POST',
                headers: { Authorization: 'Bearer ' + token, Accept: 'application/json', 'Content-Type': 'application/json' },
                body: JSON.stringify({ name, email, password: pwd, password_confirmation: pwd2, course_id: course, role: 'dosen' }),
            });
            const data = await res.json();
            if (res.ok) {
                tutupModal('modalTambah');
                toast('Akun dosen berhasil dibuat!');
                await fetchDosen();
            } else {
                const msg = data.message || (data.errors ? Object.values(data.errors)[0][0] : 'Gagal menyimpan.');
                toast(msg, 'error');
            }
        } catch (e) { toast('Koneksi bermasalah.', 'error'); }
        finally { setLoading('btnTambahSubmit', false, 'Simpan Akun'); }
    };

    /* ── MODAL: EDIT ─────────────────────────────────────────── */
    window.bukaModalEdit = function(id, name, email, courseId) {
        $('editId').value    = id;
        $('editName').value  = name;
        $('editEmail').value = email;
        $('editPassword').value = '';
        $('editStrengthFill').style.width = '0';
        $('editStrengthHint').textContent = '';
        $('editAvatarLg').textContent = initials(name);
        $('editInfoName').textContent  = name;
        $('editInfoEmail').textContent = email;
        fillCourseSelect('editCourse', courseId);
        $('modalEdit').classList.add('open');
        setTimeout(() => $('editName').focus(), 80);
    };

    window.simpanEdit = async function() {
        const id     = $('editId').value;
        const name   = $('editName').value.trim();
        const email  = $('editEmail').value.trim();
        const course = $('editCourse').value;
        const pwd    = $('editPassword').value;

        if (!name)   return toast('Nama wajib diisi.', 'error');
        if (!email)  return toast('Email wajib diisi.', 'error');
        if (!course) return toast('Pilih mata kuliah terlebih dahulu.', 'error');
        if (pwd && pwd.length < 8) return toast('Password minimal 8 karakter.', 'error');

        const body = { name, email, course_id: course };
        if (pwd) body.password = pwd;

        setLoading('btnEditSubmit', true, 'Menyimpan...');
        try {
            const res  = await fetch(API + '/dosen/' + id, {
                method: 'PUT',
                headers: { Authorization: 'Bearer ' + token, Accept: 'application/json', 'Content-Type': 'application/json' },
                body: JSON.stringify(body),
            });
            const data = await res.json();
            if (res.ok) {
                tutupModal('modalEdit');
                toast('Data dosen berhasil diperbarui!');
                await fetchDosen();
            } else {
                const msg = data.message || (data.errors ? Object.values(data.errors)[0][0] : 'Gagal menyimpan.');
                toast(msg, 'error');
            }
        } catch (e) { toast('Koneksi bermasalah.', 'error'); }
        finally { setLoading('btnEditSubmit', false, 'Simpan Perubahan'); }
    };

    /* ── MODAL: RESET PASSWORD ───────────────────────────────── */
    window.bukaModalReset = function(id, name) {
        $('resetId').value = id;
        $('resetSubtitle').textContent = 'Untuk akun: ' + name;
        $('resetPassword').value = '';
        $('resetPasswordConfirm').value = '';
        $('resetStrengthFill').style.width = '0';
        $('resetStrengthHint').textContent = 'Masukkan password baru';
        $('modalReset').classList.add('open');
        setTimeout(() => $('resetPassword').focus(), 80);
    };

    window.simpanReset = async function() {
        const id   = $('resetId').value;
        const pwd  = $('resetPassword').value;
        const pwd2 = $('resetPasswordConfirm').value;

        if (!pwd)               return toast('Password baru wajib diisi.', 'error');
        if (pwd.length < 8)     return toast('Password minimal 8 karakter.', 'error');
        if (pwd !== pwd2)       return toast('Konfirmasi password tidak cocok.', 'error');

        setLoading('btnResetSubmit', true, 'Menyimpan...');
        try {
            // Pakai endpoint edit dosen (PUT) untuk update password
            const res = await fetch(API + '/dosen/' + id, {
                method: 'PUT',
                headers: { Authorization: 'Bearer ' + token, Accept: 'application/json', 'Content-Type': 'application/json' },
                body: JSON.stringify({ password: pwd, password_confirmation: pwd2 }),
            });
            const data = await res.json();
            if (res.ok) {
                tutupModal('modalReset');
                toast('Password berhasil direset!');
            } else {
                const msg = data.message || 'Gagal reset password.';
                toast(msg, 'error');
            }
        } catch (e) { toast('Koneksi bermasalah.', 'error'); }
        finally { setLoading('btnResetSubmit', false, 'Reset Password'); }
    };

    /* ── HAPUS dosen ─────────────────────────────────────────── */
    window.hapusDosen = async function(id, name) {
        if (!confirm(`Hapus akun dosen "${name}"?\n\nTindakan ini tidak dapat dibatalkan.`)) return;
        try {
            const res = await fetch(API + '/dosen/' + id, {
                method: 'DELETE',
                headers: { Authorization: 'Bearer ' + token, Accept: 'application/json' }
            });
            if (res.ok) {
                // Hapus baris dari DOM secara optimistic
                const row = document.getElementById('row-' + id);
                if (row) row.style.transition = 'opacity .25s'; row && (row.style.opacity = '0');
                setTimeout(() => {
                    allDosen = allDosen.filter(d => (d._id||d.id) != id);
                    filterTable();
                    toast('Akun dosen berhasil dihapus.');
                }, 260);
            } else {
                const data = await res.json().catch(() => ({}));
                toast(data.message || 'Gagal menghapus.', 'error');
            }
        } catch (e) { toast('Koneksi bermasalah.', 'error'); }
    };

    /* ── Button loading state ────────────────────────────────── */
    function setLoading(btnId, loading, label) {
        const btn = $(btnId);
        btn.disabled = loading;
        btn.textContent = loading ? label : label;
        if (!loading) {
            // Restore icon
            btn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M19.916 4.626a.75.75 0 0 1 .208 1.04l-9 13.5a.75.75 0 0 1-1.154.114l-6-6a.75.75 0 0 1 1.06-1.06l5.353 5.353 8.493-12.739a.75.75 0 0 1 1.04-.208Z" clip-rule="evenodd"/></svg> ${label}`;
        }
    }

    /* ── Init ────────────────────────────────────────────────── */
    async function init() {
        await fetchCourses();
        await fetchDosen();
    }
    init();
})();
</script>
@endpush