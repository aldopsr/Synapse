@extends('layouts.app')
@section('title','Kelola Akun Dosen - Synapse')
@section('header_title','Kelola Akun Dosen')

@section('content')
<style>
.page-header { display:flex; align-items:flex-start; justify-content:space-between; gap:16px; margin-bottom:24px; flex-wrap:wrap; }
.page-header-left h2 { font-size:20px; font-weight:800; color:#111827; margin:0 0 4px; letter-spacing:-.3px; }
.page-header-left p  { font-size:13px; color:#9ca3af; margin:0; }

/* Toolbar */
.toolbar { display:flex; align-items:center; gap:10px; margin-bottom:16px; flex-wrap:wrap; }
.search-wrap { position:relative; flex:1; min-width:200px; max-width:380px; }
.search-wrap svg { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#c4c8d0; pointer-events:none; width:14px; height:14px; }
.search-input { width:100%; padding:9px 12px 9px 38px; border:1.5px solid #e8eaed; border-radius:10px; font-size:13px; font-family:inherit; background:#fff; color:#111827; box-sizing:border-box; transition:border-color .15s; }
.search-input:focus { outline:none; border-color:#279685; box-shadow:0 0 0 3px rgba(39,150,133,.1); }
.search-input::placeholder { color:#d1d5db; }
.filter-select { padding:9px 14px; border:1.5px solid #e8eaed; border-radius:10px; font-size:13px; font-family:inherit; background:#fff; color:#374151; cursor:pointer; }
.filter-select:focus { outline:none; border-color:#279685; }
.count-badge { font-size:12px; font-weight:700; color:#9ca3af; white-space:nowrap; margin-left:auto; }
.count-badge span { color:#279685; font-weight:700; }

/* Table */
.table-wrap { background:#fff; border-radius:16px; border:1px solid #eff0f2; overflow:hidden; }
table { width:100%; border-collapse:collapse; font-size:13px; }
thead tr { background:#fafafa; border-bottom:1px solid #eff0f2; }
th { padding:11px 18px; text-align:left; font-size:11px; font-weight:700; color:#9ca3af; text-transform:uppercase; letter-spacing:.05em; white-space:nowrap; user-select:none; cursor:pointer; }
th:hover { color:#279685; }
th .sort-icon { margin-left:4px; opacity:.4; font-style:normal; }
th.sorted .sort-icon { opacity:1; color:#279685; }
tbody tr { border-bottom:1px solid #f5f6f8; transition:background .12s; }
tbody tr:last-child { border-bottom:none; }
tbody tr:hover { background:#f8fffe; }
td { padding:14px 18px; vertical-align:middle; }

/* Dosen cell */
.dosen-cell { display:flex; align-items:center; gap:12px; }
.avatar { width:40px; height:40px; border-radius:50%; background:linear-gradient(135deg,#279685,#4A90E2); color:#fff; font-size:14px; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; letter-spacing:.5px; }
.dosen-name  { font-weight:700; color:#111827; margin-bottom:2px; }
.dosen-email { font-size:11px; color:#9ca3af; }

/* Matkul badge */
.matkul-badge { display:inline-flex; align-items:center; gap:5px; padding:4px 10px; border-radius:99px; font-size:11px; font-weight:700; max-width:220px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.matkul-badge svg { width:12px; height:12px; flex-shrink:0; }
.matkul-badge.assigned   { background:#f0fdf9; color:#0f6e56; }
.matkul-badge.unassigned { background:#fffbeb; color:#92400e; }

/* Actions */
.actions { display:flex; align-items:center; gap:6px; }
.btn-icon { display:inline-flex; align-items:center; gap:5px; padding:6px 12px; border-radius:8px; font-size:12px; font-weight:700; border:1.5px solid var(--b,#e5e7eb); background:var(--bg,#fff); color:var(--c,#374151); cursor:pointer; transition:background .12s,border-color .12s; font-family:inherit; white-space:nowrap; }
.btn-icon svg { width:12px; height:12px; flex-shrink:0; }
.btn-icon:hover { background:var(--bgh,#f5f6f8); border-color:var(--bh,#d1d5db); }
.btn-edit   { --bg:#e8f1fd; --c:#185fa5; --b:#b5d4f4; --bgh:#dbeafe; --bh:#93c5fd; }
.btn-reset  { --bg:#f0eeff; --c:#534ab7; --b:#cec8f6; --bgh:#ede9fe; --bh:#a5b4fc; }
.btn-delete { --bg:#fef2f2; --c:#dc2626; --b:#fecaca; --bgh:#fee2e2; --bh:#fca5a5; }

/* Empty */
.empty-row td { text-align:center; padding:64px 20px; }
.empty-ico  { width:56px; height:56px; border-radius:16px; background:#f5f6f8; display:flex; align-items:center; justify-content:center; margin:0 auto 14px; }
.empty-ico svg { width:28px; height:28px; color:#d1d5db; }
.empty-label { font-size:14px; font-weight:700; color:#374151; margin-bottom:4px; }
.empty-sub   { font-size:12px; color:#9ca3af; }

/* Skeleton */
.skeleton { background:linear-gradient(90deg,#f5f5f5 25%,#eee 50%,#f5f5f5 75%); background-size:200% 100%; animation:shimmer 1.4s infinite; border-radius:6px; display:inline-block; }
@keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }

/* Modal */
.modal-overlay { display:none; position:fixed; inset:0; background:rgba(15,23,42,.4); backdrop-filter:blur(3px); z-index:1000; align-items:center; justify-content:center; padding:20px; }
.modal-overlay.open { display:flex; }
.modal-box { background:#fff; border-radius:20px; width:100%; max-width:500px; box-shadow:0 24px 64px rgba(0,0,0,.16); animation:dlgUp .2s cubic-bezier(.34,1.4,.64,1); overflow:hidden; }
@keyframes dlgUp { from{opacity:0;transform:translateY(20px) scale(.97)} to{opacity:1;transform:none} }
.modal-header { display:flex; align-items:center; justify-content:space-between; padding:20px 24px 16px; border-bottom:1px solid #f5f6f8; }
.modal-header h3 { font-size:15px; font-weight:800; color:#111827; margin:0; }
.modal-header p  { font-size:12px; color:#9ca3af; margin:3px 0 0; }
.modal-close { width:32px; height:32px; border-radius:9px; border:none; background:#f5f6f8; cursor:pointer; display:flex; align-items:center; justify-content:center; color:#6b7280; }
.modal-close:hover { background:#e5e7eb; color:#111827; }
.modal-close svg { width:14px; height:14px; }
.modal-body { padding:20px 24px; }
.form-row { display:flex; gap:12px; }
.form-row .fg { flex:1; }
.fg { margin-bottom:16px; }
.fg:last-child { margin-bottom:0; }
.fg label { display:block; font-size:11px; font-weight:700; color:#6b7280; margin-bottom:6px; text-transform:uppercase; letter-spacing:.04em; }
.fg label .req { color:#ef4444; margin-left:2px; }
.fc { width:100%; padding:10px 13px; border:1.5px solid #e5e7eb; border-radius:10px; font-size:13px; font-family:inherit; color:#111827; background:#fff; box-sizing:border-box; transition:border-color .15s,box-shadow .15s; }
.fc:focus { outline:none; border-color:#279685; box-shadow:0 0 0 3px rgba(39,150,133,.12); }
.fc::placeholder { color:#d1d5db; }
select.fc { cursor:pointer; }

/* Password */
.pwd-strength { height:3px; border-radius:99px; background:#f5f6f8; margin-top:7px; overflow:hidden; }
.pwd-strength-fill { height:100%; border-radius:99px; transition:width .3s,background .3s; width:0; }
.pwd-hint { font-size:11px; color:#9ca3af; margin-top:4px; }
.pwd-wrap { position:relative; }
.pwd-toggle { position:absolute; right:12px; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; color:#9ca3af; padding:0; line-height:1; display:flex; align-items:center; }
.pwd-toggle svg { width:16px; height:16px; }

/* Multi-select */
.multi-select { position:relative; }
.ms-trigger { min-height:42px; width:100%; padding:6px 36px 6px 10px; border:1.5px solid #e5e7eb; border-radius:10px; background:#fff; cursor:pointer; box-sizing:border-box; transition:border-color .15s,box-shadow .15s; display:flex; flex-wrap:wrap; gap:5px; align-items:center; position:relative; }
.ms-trigger.open, .ms-trigger:focus-within { border-color:#279685; box-shadow:0 0 0 3px rgba(39,150,133,.12); }
.ms-arrow { position:absolute; right:10px; top:50%; transform:translateY(-50%); pointer-events:none; color:#9ca3af; transition:transform .15s; flex-shrink:0; }
.ms-trigger.open .ms-arrow { transform:translateY(-50%) rotate(180deg); }
.ms-chip { display:inline-flex; align-items:center; gap:4px; background:#f0fdf9; color:#0f6e56; border:1px solid #b2e8e0; border-radius:99px; padding:2px 6px 2px 9px; font-size:11px; font-weight:700; }
.ms-chip button { background:none; border:none; cursor:pointer; color:#0f6e56; display:flex; align-items:center; padding:0; }
.ms-chip button:hover { color:#dc2626; }
.ms-chip button svg { width:10px; height:10px; }
.ms-placeholder { font-size:13px; color:#d1d5db; }
.ms-dropdown { position:absolute; top:calc(100% + 4px); left:0; right:0; background:#fff; border:1.5px solid #e5e7eb; border-radius:10px; box-shadow:0 8px 24px rgba(0,0,0,.1); z-index:200; max-height:200px; overflow-y:auto; display:none; }
.ms-dropdown.open { display:block; }
.ms-option { display:flex; align-items:center; gap:10px; padding:9px 13px; cursor:pointer; transition:background .1s; }
.ms-option:hover { background:#f8fffe; }
.ms-option input[type=checkbox] { width:14px; height:14px; accent-color:#279685; flex-shrink:0; cursor:pointer; }
.ms-option label { font-size:13px; color:#111827; cursor:pointer; flex:1; }
.ms-empty { padding:12px; text-align:center; font-size:12px; color:#9ca3af; }

/* Edit info card */
.edit-info-card { display:flex; align-items:center; gap:12px; background:#f9fafb; border-radius:11px; padding:12px 14px; margin-bottom:18px; }
.avatar-lg { width:46px; height:46px; border-radius:50%; background:linear-gradient(135deg,#279685,#4A90E2); color:#fff; font-size:16px; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.edit-info-card .info .name  { font-weight:700; color:#111827; font-size:14px; }
.edit-info-card .info .email { font-size:12px; color:#9ca3af; margin-top:2px; }

/* Footer */
.modal-footer { display:flex; gap:10px; padding:16px 24px 20px; border-top:1px solid #f5f6f8; }
.btn-cancel { flex:1; padding:10px; border:1.5px solid #e5e7eb; border-radius:10px; background:#fff; color:#6b7280; font-size:13px; font-weight:700; cursor:pointer; transition:background .15s; font-family:inherit; }
.btn-cancel:hover { background:#f5f6f8; }
.btn-submit { flex:2; padding:10px; background:#279685; color:#fff; border:none; border-radius:10px; font-size:13px; font-weight:700; cursor:pointer; transition:background .15s; font-family:inherit; display:flex; align-items:center; justify-content:center; gap:6px; }
.btn-submit:hover { background:#1c6e60; }
.btn-submit:disabled { background:#9ca3af; cursor:not-allowed; }
.btn-submit svg { width:14px; height:14px; }

@media (max-width: 768px) {
    .page-header { flex-direction:column; align-items:flex-start; gap:10px; }
    .page-header .btn { width:100%; justify-content:center; }
    .toolbar { flex-direction:column; align-items:stretch; gap:8px; }
    .search-wrap { max-width:100%; }
    .filter-select { width:100%; }
    .count-badge { margin-left:0; }
    .table-wrap { overflow-x:auto; -webkit-overflow-scrolling:touch; }
    table { min-width:560px; }
    .btn-icon { padding:5px 8px; font-size:11px; }
    .modal-overlay { padding:0; align-items:flex-end; }
    .modal-box { border-radius:20px 20px 0 0; max-width:100%; }
    .form-row { flex-direction:column; gap:0; }
}
@media (max-width: 480px) {
    .dosen-name { font-size:12px; }
    .dosen-email { font-size:10px; }
    .avatar { width:34px; height:34px; font-size:12px; }
}
</style>

<div class="page-header">
    <div class="page-header-left">
        <h2>Akun Dosen</h2>
        <p id="pageSubtitle">Memuat data...</p>
    </div>
    <button class="btn btn-primary" onclick="bukaModalTambah()" style="border-radius:99px">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Tambah Dosen
    </button>
</div>

<div class="toolbar">
    <div class="search-wrap">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        <input type="text" class="search-input" id="searchInput" placeholder="Cari nama atau email dosen..." oninput="filterTable()">
    </div>
    <select class="filter-select" id="filterMatkul" onchange="filterTable()">
        <option value="">Semua Matkul</option>
    </select>
    <span class="count-badge" id="countBadge">Memuat...</span>
</div>

<div class="table-wrap">
    <table id="mainTable">
        <thead>
            <tr>
                <th onclick="sortTable('name')" data-col="name">Dosen <i class="sort-icon" id="sort-name">↕</i></th>
                <th onclick="sortTable('matkul')" data-col="matkul">Mata Kuliah <i class="sort-icon" id="sort-matkul">↕</i></th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody id="tableBody">
            @for($i=0;$i<5;$i++)
            <tr>
                <td><div class="dosen-cell">
                    <div class="skeleton" style="width:40px;height:40px;border-radius:50%;flex-shrink:0"></div>
                    <div><div class="skeleton" style="width:140px;height:13px;margin-bottom:5px"></div><div class="skeleton" style="width:180px;height:11px"></div></div>
                </div></td>
                <td><div class="skeleton" style="width:130px;height:26px;border-radius:99px"></div></td>
                <td><div style="display:flex;gap:6px">
                    <div class="skeleton" style="width:60px;height:30px;border-radius:8px"></div>
                    <div class="skeleton" style="width:80px;height:30px;border-radius:8px"></div>
                    <div class="skeleton" style="width:60px;height:30px;border-radius:8px"></div>
                </div></td>
            </tr>
            @endfor
        </tbody>
    </table>
</div>

{{-- MODAL: TAMBAH --}}
<div class="modal-overlay" id="modalTambah">
    <div class="modal-box">
        <div class="modal-header">
            <div><h3>Tambah Akun Dosen</h3><p>Akun langsung aktif setelah disimpan</p></div>
            <button class="modal-close" onclick="tutupModal('modalTambah')">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="fg"><label>Nama Lengkap <span class="req">*</span></label><input type="text" id="addName" class="fc" placeholder="Contoh: Dr. Budi Santoso, M.Kom"></div>
            <div class="fg"><label>Email <span class="req">*</span></label><input type="email" id="addEmail" class="fc" placeholder="email@kampus.ac.id"></div>
            <div class="fg">
                <label>Mata Kuliah Diampu <span class="req">*</span></label>
                <div class="multi-select" id="addCourseWrap">
                    <div class="ms-trigger" onclick="toggleMultiSelect('addCourseWrap')">
                        <div class="ms-chips" id="addCourseChips"><span class="ms-placeholder">— Pilih mata kuliah —</span></div>
                        <svg class="ms-arrow" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                    </div>
                    <div class="ms-dropdown" id="addCourseDropdown"></div>
                </div>
            </div>
            <div class="form-row">
                <div class="fg">
                    <label>Password <span class="req">*</span></label>
                    <div class="pwd-wrap">
                        <input type="password" id="addPassword" class="fc" placeholder="Min. 8 karakter" oninput="checkStrength(this.value,'addStrengthFill','addStrengthHint')">
                        <button type="button" class="pwd-toggle" onclick="togglePwd('addPassword',this)">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
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
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel" onclick="tutupModal('modalTambah')">Batal</button>
            <button class="btn-submit" id="btnTambahSubmit" onclick="simpanTambah()">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                Simpan Akun
            </button>
        </div>
    </div>
</div>

{{-- MODAL: EDIT --}}
<div class="modal-overlay" id="modalEdit">
    <div class="modal-box">
        <div class="modal-header">
            <div><h3>Edit Akun Dosen</h3><p>Kosongkan password jika tidak ingin mengubah</p></div>
            <button class="modal-close" onclick="tutupModal('modalEdit')">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="editId">
            <div class="edit-info-card">
                <div class="avatar-lg" id="editAvatarLg">?</div>
                <div class="info"><div class="name" id="editInfoName">—</div><div class="email" id="editInfoEmail">—</div></div>
            </div>
            <div class="fg"><label>Nama Lengkap <span class="req">*</span></label><input type="text" id="editName" class="fc" placeholder="Nama lengkap"></div>
            <div class="fg"><label>Email <span class="req">*</span></label><input type="email" id="editEmail" class="fc" placeholder="Email"></div>
            <div class="fg">
                <label>Mata Kuliah Diampu <span class="req">*</span></label>
                <div class="multi-select" id="editCourseWrap">
                    <div class="ms-trigger" onclick="toggleMultiSelect('editCourseWrap')">
                        <div class="ms-chips" id="editCourseChips"><span class="ms-placeholder">— Pilih mata kuliah —</span></div>
                        <svg class="ms-arrow" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                    </div>
                    <div class="ms-dropdown" id="editCourseDropdown"></div>
                </div>
            </div>
            <div class="fg">
                <label>Password Baru <span style="color:#9ca3af;font-weight:400;text-transform:none">(opsional)</span></label>
                <div class="pwd-wrap">
                    <input type="password" id="editPassword" class="fc" placeholder="Kosongkan jika tidak diubah" oninput="checkStrength(this.value,'editStrengthFill','editStrengthHint')">
                    <button type="button" class="pwd-toggle" onclick="togglePwd('editPassword',this)">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
                <div class="pwd-strength"><div class="pwd-strength-fill" id="editStrengthFill"></div></div>
                <div class="pwd-hint" id="editStrengthHint"></div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel" onclick="tutupModal('modalEdit')">Batal</button>
            <button class="btn-submit" id="btnEditSubmit" onclick="simpanEdit()">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                Simpan Perubahan
            </button>
        </div>
    </div>
</div>

{{-- MODAL: RESET PASSWORD --}}
<div class="modal-overlay" id="modalReset">
    <div class="modal-box" style="max-width:420px">
        <div class="modal-header">
            <div><h3>Reset Password</h3><p id="resetSubtitle">—</p></div>
            <button class="modal-close" onclick="tutupModal('modalReset')">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="resetId">
            <div class="fg">
                <label>Password Baru <span class="req">*</span></label>
                <div class="pwd-wrap">
                    <input type="password" id="resetPassword" class="fc" placeholder="Min. 8 karakter" oninput="checkStrength(this.value,'resetStrengthFill','resetStrengthHint')">
                    <button type="button" class="pwd-toggle" onclick="togglePwd('resetPassword',this)">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
                <div class="pwd-strength"><div class="pwd-strength-fill" id="resetStrengthFill"></div></div>
                <div class="pwd-hint" id="resetStrengthHint">Masukkan password baru</div>
            </div>
            <div class="fg"><label>Konfirmasi Password <span class="req">*</span></label><input type="password" id="resetPasswordConfirm" class="fc" placeholder="Ulangi password baru"></div>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel" onclick="tutupModal('modalReset')">Batal</button>
            <button class="btn-submit" id="btnResetSubmit" onclick="simpanReset()">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                Reset Password
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
    const API=window.apiBaseUrl, token=window.token;
    const $=id=>document.getElementById(id);
    let allDosen=[], allCourses=[], courseMap={}, sortCol='name', sortAsc=true;

    function initials(name){if(!name)return'?';const p=name.trim().split(' ');return p.length>=2?(p[0][0]+p[1][0]).toUpperCase():p[0].slice(0,2).toUpperCase();}
    function esc(s){return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}

    window.tutupModal=id=>$(id).classList.remove('open');
    document.querySelectorAll('.modal-overlay').forEach(el=>el.addEventListener('click',e=>{if(e.target===el)el.classList.remove('open');}));

    window.togglePwd=function(inputId,btn){const inp=$(inputId);const show=inp.type==='password';inp.type=show?'text':'password';btn.style.color=show?'#279685':'#9ca3af';};

    window.checkStrength=function(val,barId,hintId){
        const bar=$(barId),hint=$(hintId);
        if(!val){bar.style.width='0';hint.textContent='';return;}
        let score=0;
        if(val.length>=8)score++;if(/[A-Z]/.test(val))score++;if(/[0-9]/.test(val))score++;if(/[^A-Za-z0-9]/.test(val))score++;
        const map=[{w:'25%',bg:'#ef4444',label:'Terlalu lemah'},{w:'50%',bg:'#f59e0b',label:'Cukup'},{w:'75%',bg:'#3b82f6',label:'Kuat'},{w:'100%',bg:'#279685',label:'Sangat kuat'}];
        const m=map[score-1]||map[0];bar.style.width=m.w;bar.style.background=m.bg;hint.textContent=m.label;hint.style.color=m.bg;
    };

    function fillCourseMulti(wrapId, selectedIds){
        selectedIds=(selectedIds||[]).map(String);
        const wrap=$(wrapId);
        const dropdown=wrap.querySelector('.ms-dropdown');
        dropdown.innerHTML='';
        const entries=Object.entries(courseMap);
        if(!entries.length){dropdown.innerHTML='<div class="ms-empty">Tidak ada mata kuliah</div>';updateChips(wrapId);return;}
        entries.forEach(([id,title])=>{
            const checked=selectedIds.includes(String(id));
            const div=document.createElement('div');div.className='ms-option';
            div.innerHTML=`<input type="checkbox" id="chk-${wrapId}-${id}" value="${id}"${checked?' checked':''}><label for="chk-${wrapId}-${id}">${esc(title)}</label>`;
            div.querySelector('input').addEventListener('change',()=>updateChips(wrapId));
            dropdown.appendChild(div);
        });
        updateChips(wrapId);
    }

    function updateChips(wrapId){
        const wrap=$(wrapId);
        const chipsDiv=wrap.querySelector('.ms-chips');
        const checked=wrap.querySelectorAll('.ms-dropdown input[type=checkbox]:checked');
        chipsDiv.innerHTML='';
        if(!checked.length){chipsDiv.innerHTML='<span class="ms-placeholder">— Pilih mata kuliah —</span>';return;}
        checked.forEach(cb=>{
            const title=courseMap[cb.value]||cb.value;
            const chip=document.createElement('span');chip.className='ms-chip';
            chip.innerHTML=`${esc(title)}<button type="button"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>`;
            chip.querySelector('button').addEventListener('click',e=>{e.stopPropagation();cb.checked=false;updateChips(wrapId);});
            chipsDiv.appendChild(chip);
        });
    }

    function getSelectedCourses(wrapId){
        return Array.from($(wrapId).querySelectorAll('.ms-dropdown input[type=checkbox]:checked')).map(cb=>cb.value);
    }

    window.toggleMultiSelect=function(wrapId){
        const wrap=$(wrapId);
        const trigger=wrap.querySelector('.ms-trigger');
        const dropdown=wrap.querySelector('.ms-dropdown');
        const isOpen=dropdown.classList.contains('open');
        document.querySelectorAll('.ms-dropdown.open').forEach(d=>d.classList.remove('open'));
        document.querySelectorAll('.ms-trigger.open').forEach(t=>t.classList.remove('open'));
        if(!isOpen){dropdown.classList.add('open');trigger.classList.add('open');}
    };
    document.addEventListener('click',e=>{
        if(!e.target.closest('.multi-select')){
            document.querySelectorAll('.ms-dropdown.open').forEach(d=>d.classList.remove('open'));
            document.querySelectorAll('.ms-trigger.open').forEach(t=>t.classList.remove('open'));
        }
    });

    const SVG_BOOK   =`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>`;
    const SVG_WARN   =`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.46 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>`;
    const SVG_DOSEN  =`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>`;
    const SVG_EDIT   =`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>`;
    const SVG_LOCK   =`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>`;
    const SVG_TRASH  =`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>`;

    async function fetchCourses(){
        try {
            const res=await fetch(API+'/courses',{headers:{Authorization:'Bearer '+token,Accept:'application/json'}});
            const list=(await res.json()).data||[];
            allCourses=list;list.forEach(c=>{courseMap[c._id||c.id]=c.title;});
            const filt=$('filterMatkul');filt.innerHTML='<option value="">Semua Matkul</option>';
            list.forEach(c=>{const id=c._id||c.id;const o=document.createElement('option');o.value=id;o.textContent=c.title;filt.appendChild(o);});
            fillCourseMulti('addCourseWrap',[]);fillCourseMulti('editCourseWrap',[]);
        } catch(e){console.warn('[Dosen] Gagal fetch courses:',e.message);}
    }

    async function fetchDosen(){
        try {
            const res=await fetch(API+'/dosen',{headers:{Authorization:'Bearer '+token,Accept:'application/json'}});
            if(res.status===401){window.logout();return;}
            allDosen=(await res.json()).data||[];
            $('pageSubtitle').textContent=allDosen.length+' akun dosen terdaftar';
            renderTable(allDosen);
        } catch(e){
            $('tableBody').innerHTML=`<tr class="empty-row"><td colspan="3"><div class="empty-ico">${SVG_WARN}</div><div class="empty-label">Gagal memuat data</div><div class="empty-sub">${esc(e.message)}</div></td></tr>`;
        }
    }

    let _pagDosen = null;

    function renderTable(data){
        const tbody=$('tableBody');
        if(!data||!data.length){
            tbody.innerHTML=`<tr class="empty-row"><td colspan="3">
                <div class="empty-ico">${SVG_DOSEN}</div>
                <div class="empty-label">Belum ada akun dosen</div>
                <div class="empty-sub">Klik "Tambah Dosen" untuk menambahkan akun pertama</div>
            </td></tr>`;
            $('countBadge').innerHTML='<span>0</span> dosen';
            const p=document.getElementById('pag-tableBody'); if(p) p.style.display='none';
            return;
        }
        $('countBadge').innerHTML='<span>'+data.length+'</span> dari '+allDosen.length+' dosen';
        if(window.Paginator){
            if(!_pagDosen) _pagDosen=window.Paginator('tableBody', data, 20, renderDosenPage);
            else _pagDosen.setData(data);
        } else {
            renderDosenPage(data);
        }
    }

    function getDosenCourseIds(d){
        const id=String(d._id||d.id);
        // 1. Dari field courses/course_ids di objek dosen (many-to-many dari sisi dosen)
        if(Array.isArray(d.courses)&&d.courses.length) return d.courses.map(c=>String(c._id||c.id));
        if(Array.isArray(d.course_ids)&&d.course_ids.length) return d.course_ids.map(String);
        // 2. Dari allCourses — cek dosen_ids array, dosens array, atau dosen_id singular
        const fromCourses=allCourses.filter(function(c){
            if(Array.isArray(c.dosen_ids)&&c.dosen_ids.length) return c.dosen_ids.map(String).includes(id);
            if(Array.isArray(c.dosens)&&c.dosens.length) return c.dosens.some(function(dos){return String(dos._id||dos.id)===id;});
            return String(c.dosen_id)===id;
        }).map(function(c){return String(c._id||c.id);});
        if(fromCourses.length) return fromCourses;
        // 3. Fallback singular
        if(d.course_id) return [String(d.course_id)];
        return[];
    }

    function renderDosenPage(slice){
        const tbody=$('tableBody');
        tbody.innerHTML='';
        slice.forEach(function(d){
            const id=d._id||d.id, name=d.name||'—', email=d.email||'—';
            const ini=initials(name);
            const assignedIds=getDosenCourseIds(d);
            const matkuls=assignedIds.map(function(cid){return courseMap[cid]||'';}).filter(Boolean);
            const matkulHtml=matkuls.length>0
                ?matkuls.map(function(t){return '<span class="matkul-badge assigned">'+SVG_BOOK+' '+esc(t)+'</span>';}).join(' ')
                :'<span class="matkul-badge unassigned">'+SVG_WARN+' Belum ditugaskan</span>';

            const tr=document.createElement('tr');
            tr.id='row-'+id;
            tr.innerHTML=
                '<td><div class="dosen-cell">'+
                    '<div class="avatar">'+esc(ini)+'</div>'+
                    '<div><div class="dosen-name">'+esc(name)+'</div>'+
                    '<div class="dosen-email">'+esc(email)+'</div></div>'+
                '</div></td>'+
                '<td>'+matkulHtml+'</td>'+
                '<td><div class="actions">'+
                    '<button class="btn-icon btn-edit" data-id="'+id+'" data-name="'+esc(name)+'" data-email="'+esc(email)+'">'+SVG_EDIT+' Edit</button>'+
                    '<button class="btn-icon btn-reset" data-id="'+id+'" data-name="'+esc(name)+'">'+SVG_LOCK+' Reset Sandi</button>'+
                    '<button class="btn-icon btn-delete" data-id="'+id+'" data-name="'+esc(name)+'">'+SVG_TRASH+' Hapus</button>'+
                '</div></td>';

            const btns=tr.querySelectorAll('button');
            btns[0]._assignedIds=assignedIds;
            btns[0].addEventListener('click',function(){
                bukaModalEdit(this.dataset.id,this.dataset.name,this.dataset.email,this._assignedIds||[]);
            });
            btns[1].addEventListener('click',function(){
                bukaModalReset(this.dataset.id,this.dataset.name);
            });
            btns[2].addEventListener('click',function(){
                hapusDosen(this.dataset.id,this.dataset.name);
            });
            tbody.appendChild(tr);
        });
    }


    window.filterTable=function(){
        const q=$('searchInput').value.trim().toLowerCase(),fMatkul=$('filterMatkul').value;
        let filtered=allDosen.filter(d=>{
            const matchQ=!q||(d.name||'').toLowerCase().includes(q)||(d.email||'').toLowerCase().includes(q);
            const matchM=!fMatkul||getDosenCourseIds(d).includes(String(fMatkul));
            return matchQ&&matchM;
        });
        filtered=sortData(filtered);renderTable(filtered);
    };

    function sortData(data){
        return[...data].sort((a,b)=>{
            let va,vb;
            if(sortCol==='name'){va=(a.name||'').toLowerCase();vb=(b.name||'').toLowerCase();}
            else{const gt=d=>{const cid=d.course_id;return cid&&courseMap[cid]?courseMap[cid].toLowerCase():'';};va=gt(a);vb=gt(b);}
            return sortAsc?va.localeCompare(vb):vb.localeCompare(va);
        });
    }

    window.sortTable=function(col){
        if(sortCol===col)sortAsc=!sortAsc;else{sortCol=col;sortAsc=true;}
        document.querySelectorAll('th .sort-icon').forEach(el=>el.textContent='↕');
        document.querySelectorAll('th').forEach(el=>el.classList.remove('sorted'));
        const th=document.querySelector(`th[data-col="${col}"]`);
        if(th){th.classList.add('sorted');th.querySelector('.sort-icon').textContent=sortAsc?'↑':'↓';}
        filterTable();
    };

    window.bukaModalTambah=function(){
        ['addName','addEmail','addPassword','addPasswordConfirm'].forEach(id=>$(id).value='');
        $('addStrengthFill').style.width='0';$('addStrengthHint').textContent='Masukkan password';
        fillCourseMulti('addCourseWrap',[]);
        $('modalTambah').classList.add('open');
        setTimeout(()=>$('addName').focus(),80);
    };

    window.simpanTambah=async function(){
        const name=$('addName').value.trim(),email=$('addEmail').value.trim();
        const pwd=$('addPassword').value,pwd2=$('addPasswordConfirm').value;
        const courses=getSelectedCourses('addCourseWrap');
        if(!name)return toast('Nama wajib diisi.','err');
        if(!email)return toast('Email wajib diisi.','err');
        if(!courses.length)return toast('Pilih minimal satu mata kuliah.','err');
        if(!pwd)return toast('Password wajib diisi.','err');
        if(pwd.length<8)return toast('Password minimal 8 karakter.','err');
        if(pwd!==pwd2)return toast('Konfirmasi password tidak cocok.','err');
        setLoading('btnTambahSubmit',true);
        try {
            const res=await fetch(API+'/dosen',{method:'POST',headers:{Authorization:'Bearer '+token,Accept:'application/json','Content-Type':'application/json'},body:JSON.stringify({name,email,password:pwd,password_confirmation:pwd2,course_ids:courses,course_id:courses[0],role:'dosen'})});
            const data=await res.json();
            if(res.ok){
                tutupModal('modalTambah');toast('Akun dosen berhasil dibuat!');
                await fetchCourses();await fetchDosen();
            } else{const msg=data.message||(data.errors?Object.values(data.errors)[0][0]:'Gagal menyimpan.');toast(msg,'err');}
        } catch(_){toast('Koneksi bermasalah.','err');}
        finally{setLoading('btnTambahSubmit',false,'Simpan Akun');}
    };

    window.bukaModalEdit=function(id,name,email,assignedIds){
        $('editId').value=id;$('editName').value=name;$('editEmail').value=email;
        $('editPassword').value='';$('editStrengthFill').style.width='0';$('editStrengthHint').textContent='';
        $('editAvatarLg').textContent=initials(name);$('editInfoName').textContent=name;$('editInfoEmail').textContent=email;
        fillCourseMulti('editCourseWrap', Array.isArray(assignedIds)?assignedIds:[]);
        $('modalEdit').classList.add('open');
        setTimeout(()=>$('editName').focus(),80);
    };

    window.simpanEdit=async function(){
        const id=$('editId').value,name=$('editName').value.trim(),email=$('editEmail').value.trim();
        const courses=getSelectedCourses('editCourseWrap'),pwd=$('editPassword').value;
        if(!name)return toast('Nama wajib diisi.','err');
        if(!email)return toast('Email wajib diisi.','err');
        if(!courses.length)return toast('Pilih minimal satu mata kuliah.','err');
        if(pwd&&pwd.length<8)return toast('Password minimal 8 karakter.','err');
        const body={name,email,course_ids:courses,course_id:courses[0]};if(pwd)body.password=pwd;
        setLoading('btnEditSubmit',true);
        try {
            const res=await fetch(API+'/dosen/'+id,{method:'PUT',headers:{Authorization:'Bearer '+token,Accept:'application/json','Content-Type':'application/json'},body:JSON.stringify(body)});
            const data=await res.json();
            if(res.ok){
                tutupModal('modalEdit');toast('Data dosen berhasil diperbarui!');
                await fetchCourses();await fetchDosen();
            } else{const msg=data.message||(data.errors?Object.values(data.errors)[0][0]:'Gagal menyimpan.');toast(msg,'err');}
        } catch(_){toast('Koneksi bermasalah.','err');}
        finally{setLoading('btnEditSubmit',false,'Simpan Perubahan');}
    };

    window.bukaModalReset=function(id,name){
        $('resetId').value=id;$('resetSubtitle').textContent='Untuk akun: '+name;
        $('resetPassword').value='';$('resetPasswordConfirm').value='';
        $('resetStrengthFill').style.width='0';$('resetStrengthHint').textContent='Masukkan password baru';
        $('modalReset').classList.add('open');setTimeout(()=>$('resetPassword').focus(),80);
    };

    window.simpanReset=async function(){
        const id=$('resetId').value,pwd=$('resetPassword').value,pwd2=$('resetPasswordConfirm').value;
        if(!pwd)return toast('Password baru wajib diisi.','err');
        if(pwd.length<8)return toast('Password minimal 8 karakter.','err');
        if(pwd!==pwd2)return toast('Konfirmasi password tidak cocok.','err');
        setLoading('btnResetSubmit',true);
        try {
            const res=await fetch(API+'/dosen/'+id,{method:'PUT',headers:{Authorization:'Bearer '+token,Accept:'application/json','Content-Type':'application/json'},body:JSON.stringify({password:pwd,password_confirmation:pwd2})});
            const data=await res.json();
            if(res.ok){tutupModal('modalReset');toast('Password berhasil direset!');}
            else toast(data.message||'Gagal reset password.','err');
        } catch(_){toast('Koneksi bermasalah.','err');}
        finally{setLoading('btnResetSubmit',false,'Reset Password');}
    };

    window.hapusDosen=function(id,name){
        showDialog({
            icon:'err', title:'Hapus Akun Dosen',
            msg:`Hapus akun dosen "${name}"? Tindakan ini tidak dapat dibatalkan.`,
            confirmText:'Hapus', confirmClass:'confirm-err',
            onConfirm:()=>doHapusDosen(id,name)
        });
    };

    window.doHapusDosen=async function(id,name){
        try {
            const res=await fetch(API+'/dosen/'+id,{method:'DELETE',headers:{Authorization:'Bearer '+token,Accept:'application/json'}});
            if(res.ok){
                const row=document.getElementById('row-'+id);
                if(row){row.style.transition='opacity .25s,transform .25s';row.style.opacity='0';row.style.transform='scale(.97)';}
                setTimeout(()=>{allDosen=allDosen.filter(d=>(d._id||d.id)!=id);filterTable();toast('Akun dosen berhasil dihapus.');},260);
            } else {
                const data=await res.json().catch(()=>({}));toast(data.message||'Gagal menghapus.','err');
            }
        } catch(_){toast('Koneksi bermasalah.','err');}
    };

    async function syncCourseAssignments(dosenId, newCourseIds, oldCourseIds){
        // Backend many-to-many: relationship dikelola via course_ids di PUT /dosen/{id}
        // Fungsi ini hanya sebagai fallback jika backend masih pakai dosen_id singular di course
        const headers={Authorization:'Bearer '+token,Accept:'application/json','Content-Type':'application/json'};
        const toRemove=oldCourseIds.filter(id=>!newCourseIds.includes(id));
        const toAdd=newCourseIds.filter(id=>!oldCourseIds.includes(id));
        await Promise.all([
            ...toRemove.map(cid=>fetch(API+'/courses/'+cid,{method:'PUT',headers,body:JSON.stringify({dosen_id:null,dosen_ids:[]})}).catch(()=>{})),
            ...toAdd.map(cid=>fetch(API+'/courses/'+cid,{method:'PUT',headers,body:JSON.stringify({dosen_id:dosenId})}).catch(()=>{}))
        ]);
    }

    const SVG_CHECK=`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>`;
    function setLoading(btnId,loading,label){
        const btn=$(btnId);btn.disabled=loading;
        if(loading){btn.innerHTML='<div style="width:14px;height:14px;border:2px solid rgba(255,255,255,.3);border-top-color:#fff;border-radius:50%;animation:spin .6s linear infinite"></div>Menyimpan...';}
        else{btn.innerHTML=SVG_CHECK+' '+(label||'Simpan');}
    }

    async function init(){await fetchCourses();await fetchDosen();}
    init();
})();
</script>
@endpush