<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Synapse')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,400;0,14..32,500;0,14..32,600;0,14..32,700;0,14..32,800;1,14..32,400&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --brand:        #279685;
            --brand-dark:   #1c6e60;
            --brand-light:  rgba(255,255,255,0.12);
            --brand-hover:  rgba(255,255,255,0.15);
            --brand-active: rgba(255,255,255,0.2);
            --sidebar-w:    256px;
            --topbar-h:     64px;
            --bg-page:      #F0F2F1;
            --bg-card:      #ffffff;
            --text-main:    #111827;
            --text-muted:   #6b7280;
            --border:       #e8eaed;
            --radius-sm:    10px;
            --radius-md:    14px;
            --radius-lg:    18px;
            --shadow-md:    0 4px 20px rgba(0,0,0,.09);
        }

        html, body { height: 100%; font-family: 'Inter', sans-serif; background: var(--bg-page); color: var(--text-main); -webkit-font-smoothing: antialiased; }
        a { text-decoration: none; color: inherit; }
        button { font-family: inherit; }

        /* ── Shell ── */
        .app-shell { display: flex; height: 100vh; overflow: hidden; }

        /* ── Sidebar ── */
        .sidebar {
            width: var(--sidebar-w); background: var(--brand);
            display: flex; flex-direction: column; flex-shrink: 0;
            height: 100vh; overflow-y: auto; overflow-x: hidden; scrollbar-width: none;
        }
        .sidebar::-webkit-scrollbar { display: none; }

        .sidebar-brand {
            display: flex; align-items: center; gap: 12px;
            padding: 22px 20px 18px; border-bottom: 1px solid var(--brand-light); flex-shrink: 0;
        }
        .sidebar-brand img { width: 38px; height: 38px; object-fit: contain; flex-shrink: 0; background: rgba(255,255,255,.15); border-radius: 9px; padding: 4px; }
        .sidebar-brand-text { display: flex; flex-direction: column; }
        .sidebar-brand-name { font-size: 16px; font-weight: 800; color: #fff; letter-spacing: -.2px; line-height: 1.2; }
        .sidebar-brand-tagline { font-size: 10px; color: rgba(255,255,255,.5); font-weight: 500; margin-top: 1px; }

        .sidebar-nav { flex: 1; padding: 14px 12px; display: flex; flex-direction: column; gap: 2px; }

        .nav-section-label {
            font-size: 10px; font-weight: 700; letter-spacing: .08em;
            color: rgba(255,255,255,.35); padding: 12px 10px 5px;
            text-transform: uppercase; display: none;
        }
        .nav-section-label.visible { display: block; }
        .nav-divider { height: 1px; background: rgba(255,255,255,.1); margin: 10px 4px; display: none; }
        .nav-divider.visible { display: block; }

        .menu-item {
            display: flex; align-items: center; gap: 11px;
            padding: 10px 12px; border-radius: var(--radius-sm);
            color: rgba(255,255,255,.8); font-size: 13.5px; font-weight: 600;
            transition: background .15s, color .15s;
            cursor: pointer; position: relative;
        }
        .menu-item svg { flex-shrink: 0; opacity: .8; transition: opacity .15s; }
        .menu-item:hover { background: var(--brand-hover); color: #fff; }
        .menu-item:hover svg { opacity: 1; }
        .menu-item.active { background: var(--brand-active); color: #fff; font-weight: 700; }
        .menu-item.active svg { opacity: 1; }
        .menu-item.active::before {
            content: ''; position: absolute; left: 0; top: 50%;
            transform: translateY(-50%);
            width: 3px; height: 60%; background: #fff; border-radius: 0 3px 3px 0;
        }

        .sidebar-footer { border-top: 1px solid var(--brand-light); padding: 14px 12px; flex-shrink: 0; }
        .sidebar-user-card {
            display: flex; align-items: center; gap: 10px;
            padding: 10px; border-radius: var(--radius-sm);
            margin-bottom: 6px; background: rgba(255,255,255,.12);
        }
        .sidebar-avatar-sm {
            width: 34px; height: 34px; border-radius: 50%;
            background: rgba(255,255,255,.25); display: flex; align-items: center;
            justify-content: center; font-size: 13px; font-weight: 700;
            color: #fff; flex-shrink: 0;
        }
        .sidebar-user-info { flex: 1; overflow: hidden; }
        .sidebar-user-name { font-size: 13px; font-weight: 700; color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .sidebar-user-role { font-size: 11px; color: rgba(255,255,255,.5); }
        .btn-logout {
            display: flex; align-items: center; gap: 10px;
            padding: 9px 12px; border-radius: var(--radius-sm);
            color: rgba(255,255,255,.65); font-size: 13px; font-weight: 600;
            cursor: pointer; border: none; background: transparent;
            width: 100%; transition: background .15s, color .15s;
            font-family: 'Inter', sans-serif;
        }
        .btn-logout:hover { background: rgba(255,60,60,.18); color: #ffb3b3; }

        /* ── Main ── */
        .main-area { flex: 1; display: flex; flex-direction: column; overflow: hidden; }

        .topbar {
            height: var(--topbar-h); background: var(--bg-card);
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 28px; position: sticky; top: 0; z-index: 50; flex-shrink: 0;
        }
        .topbar-left { display: flex; align-items: center; gap: 12px; }
        .topbar-page-title { font-size: 17px; font-weight: 800; color: var(--text-main); letter-spacing: -.3px; }
        .topbar-role-badge { font-size: 11px; font-weight: 700; padding: 4px 11px; border-radius: 99px; }
        .badge-admin { background: #fef3e2; color: #854f0b; }
        .badge-dosen { background: rgba(39,150,133,.12); color: var(--brand-dark); }

        .topbar-right { display: flex; align-items: center; gap: 14px; }
        .topbar-user {
            display: flex; align-items: center; gap: 10px;
            padding: 6px 10px 6px 6px; border-radius: var(--radius-md);
            transition: background .15s; position: relative;
        }
        .topbar-user:hover { background: var(--bg-page); }
        .topbar-avatar {
            width: 36px; height: 36px; border-radius: 50%;
            background: var(--brand); display: flex; align-items: center;
            justify-content: center; font-size: 14px; font-weight: 700;
            color: #fff; flex-shrink: 0;
            box-shadow: 0 2px 8px rgba(39,150,133,.3);
        }
        .topbar-user-info { display: flex; flex-direction: column; }
        .topbar-user-name { font-size: 13px; font-weight: 700; color: var(--text-main); line-height: 1.2; }
        .topbar-user-role { font-size: 11px; color: var(--text-muted); }

        .user-dropdown {
            position: absolute; top: calc(100% + 8px); right: 0;
            width: 200px; background: var(--bg-card);
            border: 1px solid var(--border); border-radius: var(--radius-md);
            box-shadow: var(--shadow-md); overflow: hidden; display: none; z-index: 200;
        }
        .user-dropdown.open { display: block; animation: ddIn .15s ease; }
        @keyframes ddIn { from { opacity:0; transform:translateY(-6px); } to { opacity:1; transform:none; } }
        .dropdown-item {
            display: flex; align-items: center; gap: 10px;
            padding: 11px 16px; font-size: 13px; font-weight: 600;
            color: var(--text-main); transition: background .12s; cursor: pointer;
        }
        .dropdown-item:hover { background: var(--bg-page); }
        .dropdown-item.danger { color: #dc2626; }
        .dropdown-item.danger:hover { background: #fef2f2; }
        .dropdown-divider { height: 1px; background: var(--border); }

        .content-wrapper { flex: 1; overflow-y: auto; padding: 28px 30px; }

        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(0,0,0,.12); border-radius: 99px; }

        /* ── Custom Dialog ── */
        .dlg-overlay {
            position: fixed; inset: 0; background: rgba(0,0,0,.4);
            backdrop-filter: blur(3px); z-index: 1000;
            display: none; align-items: center; justify-content: center;
        }
        .dlg-overlay.open { display: flex; }
        .dlg {
            background: #fff; border-radius: 20px; width: 100%; max-width: 420px;
            box-shadow: 0 24px 60px rgba(0,0,0,.18);
            animation: dlgIn .2s cubic-bezier(.34,1.4,.64,1); overflow: hidden;
        }
        @keyframes dlgIn { from { opacity:0; transform:scale(.92) translateY(12px); } to { opacity:1; transform:none; } }
        .dlg-icon { width: 52px; height: 52px; border-radius: 16px; display: flex; align-items: center; justify-content: center; margin: 28px auto 0; }
        .dlg-icon.warn { background: #fef3c7; } .dlg-icon.err { background: #fee2e2; }
        .dlg-icon.info { background: #dbeafe; } .dlg-icon.ok  { background: #d1fae5; }
        .dlg-title { font-size: 16px; font-weight: 800; color: var(--text-main); text-align: center; padding: 14px 24px 4px; }
        .dlg-msg   { font-size: 13px; color: var(--text-muted); text-align: center; padding: 0 24px 24px; line-height: 1.65; }
        .dlg-actions { display: flex; gap: 10px; padding: 0 20px 20px; }
        .dlg-btn {
            flex: 1; padding: 11px; border-radius: 11px;
            font-size: 13px; font-weight: 700; border: none; cursor: pointer;
            font-family: inherit; transition: filter .15s, transform .1s;
        }
        .dlg-btn:hover { filter: brightness(.92); transform: translateY(-1px); }
        .dlg-btn.cancel { background: #f3f4f6; color: #555; }
        .dlg-btn.confirm-warn { background: #f59e0b; color: #fff; }
        .dlg-btn.confirm-err  { background: #ef4444; color: #fff; }
        .dlg-btn.confirm-ok   { background: var(--brand); color: #fff; }

        /* ── Toast ── */
        .toast-wrap { position: fixed; bottom: 24px; right: 24px; z-index: 9999; display: flex; flex-direction: column; gap: 8px; pointer-events: none; }
        .toast {
            display: flex; align-items: center; gap: 10px;
            padding: 12px 18px; border-radius: 12px; font-size: 13px; font-weight: 600;
            color: #fff; box-shadow: 0 8px 24px rgba(0,0,0,.18);
            transform: translateX(80px); opacity: 0;
            transition: all .28s cubic-bezier(.34,1.4,.64,1); max-width: 340px;
        }
        .toast.show { transform: none; opacity: 1; }
        .toast.ok  { background: var(--brand); }
        .toast.err { background: #ef4444; }
        .toast.warn { background: #f59e0b; }
        .toast-dot { width: 8px; height: 8px; border-radius: 50%; background: rgba(255,255,255,.5); flex-shrink: 0; }

        /* ── Modal shared ── */
        .modal-overlay {
            position: fixed; inset: 0; background: rgba(0,0,0,.4);
            backdrop-filter: blur(2px); z-index: 500;
            display: none; align-items: center; justify-content: center; padding: 20px;
        }
        .modal-overlay.open { display: flex; }
        .modal {
            background: #fff; border-radius: 20px; width: 100%; max-width: 500px;
            box-shadow: 0 24px 60px rgba(0,0,0,.15);
            animation: dlgIn .2s cubic-bezier(.34,1.4,.64,1);
            max-height: 90vh; display: flex; flex-direction: column; overflow: hidden;
        }
        .modal-head {
            display: flex; align-items: center; justify-content: space-between;
            padding: 20px 24px 16px; border-bottom: 1px solid var(--border); flex-shrink: 0;
        }
        .modal-head h3 { font-size: 15px; font-weight: 700; color: var(--text-main); margin: 0; }
        .modal-close {
            width: 30px; height: 30px; border-radius: 8px;
            border: none; background: #f3f4f6;
            cursor: pointer; display: flex; align-items: center; justify-content: center;
            color: #888; transition: background .15s;
        }
        .modal-close:hover { background: #e5e7eb; }
        .modal-body { padding: 20px 24px; overflow-y: auto; flex: 1; }
        .modal-foot { display: flex; gap: 10px; padding: 14px 24px 20px; border-top: 1px solid var(--border); flex-shrink: 0; }
        .modal-foot .btn { flex: 1; }

        /* ── Form ── */
        .fg { margin-bottom: 15px; }
        .fg:last-child { margin-bottom: 0; }
        .fg label { display: block; font-size: 11px; font-weight: 700; color: #555; text-transform: uppercase; letter-spacing: .05em; margin-bottom: 6px; }
        .fc {
            width: 100%; padding: 9px 12px; border: 1.5px solid var(--border);
            border-radius: 10px; font-size: 13px; font-family: inherit;
            color: var(--text-main); background: #fff; box-sizing: border-box;
            transition: border-color .15s, box-shadow .15s;
        }
        .fc:focus { outline: none; border-color: var(--brand); box-shadow: 0 0 0 3px rgba(39,150,133,.1); }
        .fc::placeholder { color: #c8cbd0; }
        textarea.fc { resize: vertical; min-height: 80px; }
        select.fc { cursor: pointer; }

        /* ── Buttons ── */
        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 7px;
            padding: 10px 18px; border-radius: 11px; font-size: 13px; font-weight: 700;
            border: none; cursor: pointer; font-family: inherit;
            transition: filter .15s, transform .1s;
            text-decoration: none; white-space: nowrap;
        }
        .btn:hover { filter: brightness(.92); transform: translateY(-1px); }
        .btn:active { transform: none; }
        .btn-primary { background: var(--brand); color: #fff; }
        .btn-ghost   { background: #f3f4f6; color: #555; }
        .btn-ghost:hover { background: #e9eaec; filter: none; }
        .btn-danger  { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
        .btn-danger:hover { background: #fee2e2; filter: none; }
        .btn-sm { padding: 7px 13px; font-size: 12px; border-radius: 9px; }
        .btn-xs { padding: 5px 10px; font-size: 11px; border-radius: 7px; }

        /* ── Table ── */
        .tbl-wrap { background: #fff; border-radius: var(--radius-md); border: 1px solid var(--border); overflow: hidden; }
        table { width: 100%; border-collapse: collapse; }
        thead tr { background: #f9fafb; }
        thead th { padding: 11px 16px; font-size: 11px; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: .05em; text-align: left; border-bottom: 1px solid var(--border); }
        tbody tr { border-bottom: 1px solid #f3f4f6; transition: background .1s; }
        tbody tr:last-child { border-bottom: none; }
        tbody tr:hover { background: #fafafa; }
        tbody td { padding: 13px 16px; font-size: 13px; color: var(--text-main); vertical-align: middle; }

        /* ── Badge ── */
        .badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 9px; border-radius: 99px; font-size: 11px; font-weight: 700; }
        .badge-green  { background: #d1fae5; color: #065f46; }
        .badge-red    { background: #fee2e2; color: #991b1b; }
        .badge-blue   { background: #dbeafe; color: #1e40af; }
        .badge-amber  { background: #fef3c7; color: #92400e; }
        .badge-teal   { background: #ccfbf1; color: #0f766e; }
        .badge-gray   { background: #f3f4f6; color: #6b7280; }
        .badge-purple { background: #f3f4f6; color: #6b7280; }

        /* ── Empty ── */
        .empty { text-align: center; padding: 56px 20px; }
        .empty-icon { font-size: 40px; margin-bottom: 12px; opacity: .4; }
        .empty-title { font-size: 14px; font-weight: 700; color: #9ca3af; margin-bottom: 6px; }
        .empty-sub   { font-size: 12px; color: #c4c8d0; }

        /* ── Search ── */
        .search-bar { position: relative; display: inline-flex; align-items: center; }
        .search-bar svg { position: absolute; left: 11px; color: #bbb; pointer-events: none; }
        .search-input {
            padding: 9px 12px 9px 36px; border: 1.5px solid var(--border);
            border-radius: 10px; font-size: 13px; font-family: inherit;
            background: #fff; color: var(--text-main); transition: border-color .15s; min-width: 220px;
        }
        .search-input:focus { outline: none; border-color: var(--brand); }
        .search-input::placeholder { color: #c8cbd0; }

        /* ── Page header ── */
        .ph { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 24px; flex-wrap: wrap; }
        .ph-left h2 { font-size: 20px; font-weight: 800; color: var(--text-main); margin: 0 0 3px; letter-spacing: -.3px; }
        .ph-left p  { font-size: 13px; color: var(--text-muted); margin: 0; }

        /* ── Card ── */
        .card { background: #fff; border-radius: var(--radius-md); border: 1px solid var(--border); }
        .card-h { display: flex; align-items: center; gap: 12px; padding: 16px 20px; border-bottom: 1px solid var(--border); }
        .card-h h3 { font-size: 14px; font-weight: 700; margin: 0; }
        .card-b { padding: 20px; }
    </style>
</head>
<body>
<div class="dlg-overlay" id="dlgOverlay">
    <div class="dlg" id="dlgBox">
        <div class="dlg-icon" id="dlgIcon"></div>
        <div class="dlg-title" id="dlgTitle"></div>
        <div class="dlg-msg" id="dlgMsg"></div>
        <div class="dlg-actions">
            <button class="dlg-btn cancel" id="dlgCancel">Batal</button>
            <button class="dlg-btn confirm-err" id="dlgConfirm">Hapus</button>
        </div>
    </div>
</div>
<div class="toast-wrap" id="toastWrap"></div>

<div class="app-shell">
    <aside class="sidebar">
        <a href="/dashboard" class="sidebar-brand">
            <img src="{{ asset('assets/image/synapseLogo.png') }}" alt="Synapse Logo">
            <div class="sidebar-brand-text">
                <span class="sidebar-brand-name">SYNAPSE</span>
                <span class="sidebar-brand-tagline">Learning Management System</span>
            </div>
        </a>

        <nav class="sidebar-nav" id="sidebarNav">
            <span class="nav-section-label visible">Umum</span>

            <a href="/dashboard" class="menu-item" data-path="/dashboard">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M11.47 3.841a.75.75 0 0 1 1.06 0l8.69 8.69a.75.75 0 1 0 1.06-1.061l-8.689-8.69a2.25 2.25 0 0 0-3.182 0l-8.69 8.69a.75.75 0 1 0 1.061 1.06l8.69-8.689Z"/>
                    <path d="m12 5.432 8.159 8.159c.03.03.06.058.091.086v6.198c0 1.035-.84 1.875-1.875 1.875H15a.75.75 0 0 1-.75-.75v-4.5a.75.75 0 0 0-.75-.75h-3a.75.75 0 0 0-.75.75V21a.75.75 0 0 1-.75.75H5.625a1.875 1.875 0 0 1-1.875-1.875v-6.198a2.29 2.29 0 0 0 .091-.086L12 5.432Z"/>
                </svg>
                <span>Dashboard</span>
            </a>

            <a href="/mata-kuliah" class="menu-item" data-path="/mata-kuliah">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M11.25 4.533A9.707 9.707 0 0 0 6 3a9.735 9.735 0 0 0-3.25.555.75.75 0 0 0-.5.707v14.25a.75.75 0 0 0 1 .707A8.237 8.237 0 0 1 6 18.75c1.995 0 3.823.707 5.25 1.886V4.533ZM12.75 20.636A8.214 8.214 0 0 1 18 18.75c.966 0 1.89.166 2.75.47a.75.75 0 0 0 1-.708V4.262a.75.75 0 0 0-.5-.707A9.735 9.735 0 0 0 18 3a9.707 9.707 0 0 0-5.25 1.533v16.103Z"/>
                </svg>
                <span>Mata Kuliah</span>
            </a>

            <a href="/kuis" class="menu-item" data-path="/kuis">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <path fill-rule="evenodd" d="M7.502 6h7.128A3.375 3.375 0 0 1 18 9.375v9.375a3 3 0 0 0 3-3V6.108c0-1.505-1.125-2.811-2.664-2.94a48.972 48.972 0 0 0-.673-.05A3 3 0 0 0 15 1.5h-1.5a3 3 0 0 0-2.663 1.618c-.225.015-.45.032-.673.05C8.662 3.295 7.554 4.542 7.502 6ZM13.5 3A1.5 1.5 0 0 0 12 4.5h4.5A1.5 1.5 0 0 0 15 3h-1.5Z" clip-rule="evenodd"/>
                    <path fill-rule="evenodd" d="M3 9.375C3 8.339 3.84 7.5 4.875 7.5h9.75c1.036 0 1.875.84 1.875 1.875v11.25c0 1.035-.84 1.875-1.875 1.875h-9.75A1.875 1.875 0 0 1 3 20.625V9.375Zm9.586 4.594a.75.75 0 0 0-1.172-.938l-2.476 3.096-.908-.907a.75.75 0 0 0-1.06 1.06l1.5 1.5a.75.75 0 0 0 1.116-.062l3-3.75Z" clip-rule="evenodd"/>
                </svg>
                <span>Kelola Kuis</span>
            </a>

            <a href="/kelola-ar" class="menu-item" data-path="/kelola-ar">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12.378 1.602a.75.75 0 0 0-.756 0L3 6.632l9 5.25 9-5.25-8.622-5.03ZM21.75 7.93l-9 5.25v9l8.628-5.032a.75.75 0 0 0 .372-.648V7.93ZM11.25 22.18v-9l-9-5.25v8.57a.75.75 0 0 0 .372.648l8.628 5.033Z"/>
                </svg>
                <span>Aset 3D</span>
            </a>

            <div class="nav-divider" id="dividerAdmin"></div>
            <span class="nav-section-label" id="labelAdmin">Admin</span>

            <a href="/data-mahasiswa" class="menu-item" data-path="/data-mahasiswa">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <path fill-rule="evenodd" d="M7.5 6a4.5 4.5 0 1 1 9 0 4.5 4.5 0 0 1-9 0ZM3.751 20.105a8.25 8.25 0 0 1 16.498 0 .75.75 0 0 1-.437.695A18.683 18.683 0 0 1 12 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 0 1-.437-.695Z" clip-rule="evenodd"/>
                </svg>
                <span>Data Mahasiswa</span>
            </a>

            <a href="/kelolaAkunDosen" class="menu-item" data-path="/kelolaAkunDosen" id="menuKelolaDosen" style="display:none;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.25 6.75a3.75 3.75 0 1 1 7.5 0 3.75 3.75 0 0 1-7.5 0ZM15.75 9.75a3 3 0 1 1 6 0 3 3 0 0 1-6 0ZM2.25 9.75a3 3 0 1 1 6 0 3 3 0 0 1-6 0ZM6.31 15.117A6.745 6.745 0 0 1 12 12a6.745 6.745 0 0 1 6.709 7.498.75.75 0 0 1-.372.568A12.696 12.696 0 0 1 12 21.75c-2.305 0-4.47-.612-6.337-1.684a.75.75 0 0 1-.372-.568 6.787 6.787 0 0 1 1.019-4.38Z" clip-rule="evenodd"/>
                    <path d="M5.082 14.254a8.287 8.287 0 0 0-1.308 5.135 9.687 9.687 0 0 1-1.764-.44l-.115-.04a.563.563 0 0 1-.373-.487l-.01-.121a3.75 3.75 0 0 1 3.57-4.047ZM20.226 19.389a8.287 8.287 0 0 0-1.308-5.135 3.75 3.75 0 0 1 3.57 4.047l-.01.121a.563.563 0 0 1-.373.486l-.115.04c-.567.2-1.156.349-1.764.441Z"/>
                </svg>
                <span>Kelola Dosen</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="sidebar-user-card">
                <div class="sidebar-avatar-sm" id="sidebarAvatarInitials">?</div>
                <div class="sidebar-user-info">
                    <div class="sidebar-user-name" id="sidebarUserName">Memuat...</div>
                    <div class="sidebar-user-role" id="sidebarUserRole">—</div>
                </div>
            </div>
            <button class="btn-logout" onclick="logout()">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                    <path fill-rule="evenodd" d="M7.5 3.75A1.5 1.5 0 0 0 6 5.25v13.5a1.5 1.5 0 0 0 1.5 1.5h6a1.5 1.5 0 0 0 1.5-1.5V15a.75.75 0 0 1 1.5 0v3.75a3 3 0 0 1-3 3h-6a3 3 0 0 1-3-3V5.25a3 3 0 0 1 3-3h6a3 3 0 0 1 3 3V9A.75.75 0 0 1 15 9V5.25a1.5 1.5 0 0 0-1.5-1.5h-6Zm10.72 4.72a.75.75 0 0 1 1.06 0l3 3a.75.75 0 0 1 0 1.06l-3 3a.75.75 0 1 1-1.06-1.06l1.72-1.72H9a.75.75 0 0 1 0-1.5h10.94l-1.72-1.72a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/>
                </svg>
                <span>Keluar</span>
            </button>
        </div>
    </aside>

    <div class="main-area">
        <header class="topbar">
            <div class="topbar-left">
                <span class="topbar-page-title">@yield('header_title', 'Dashboard')</span>
                <span class="topbar-role-badge" id="topbarRoleBadge"></span>
            </div>
            <div class="topbar-right">
                <div class="topbar-user" id="topbarUser" style="cursor:default;">
                    <div class="topbar-avatar" id="topbarAvatarInitials">?</div>
                    <div class="topbar-user-info">
                        <span class="topbar-user-name" id="topbarUserName">Memuat...</span>
                        <span class="topbar-user-role" id="topbarUserRole">—</span>
                    </div>
                </div>
            </div>
        </header>
        <main class="content-wrapper">
            @yield('content')
        </main>
    </div>
</div>

<script>
(function () {
    window.apiBaseUrl = "{{ config('app.api_url') }}";
    window.token      = localStorage.getItem('token');
    window.role       = localStorage.getItem('role') || 'dosen';
    window.user       = null;
    try { const u = localStorage.getItem('user'); if (u) window.user = JSON.parse(u); } catch (e) {}

    if (!window.token) { window.location.href = '/'; return; }

    const $ = id => document.getElementById(id);
    function initials(name) {
        if (!name) return '?';
        const parts = name.trim().split(' ');
        return parts.length >= 2 ? (parts[0][0] + parts[1][0]).toUpperCase() : parts[0].slice(0, 2).toUpperCase();
    }
    function roleLabel(r) { return { admin:'Admin', superadmin:'Super Admin', dosen:'Dosen' }[r] || r; }

    const name = window.user?.name || 'Pengguna';
    const role = window.role;
    const ini  = initials(name);
    const rl   = roleLabel(role);
    const isAdmin = role === 'admin' || role === 'superadmin';

    $('sidebarAvatarInitials').textContent = ini;
    $('sidebarUserName').textContent       = name;
    $('sidebarUserRole').textContent       = rl;
    $('topbarAvatarInitials').textContent  = ini;
    $('topbarUserName').textContent        = name;
    $('topbarUserRole').textContent        = rl;

    const badge = $('topbarRoleBadge');
    badge.textContent = rl;
    badge.className   = 'topbar-role-badge ' + (isAdmin ? 'badge-admin' : 'badge-dosen');

    if (isAdmin) {
        $('menuKelolaDosen').style.display = 'flex';
        $('dividerAdmin').classList.add('visible');
        $('labelAdmin').classList.add('visible');
    }

    const currentPath = window.location.pathname;
    const menuItems = Array.from(document.querySelectorAll('.menu-item[data-path]'))
        .sort((a, b) => b.dataset.path.length - a.dataset.path.length);
    let matched = false;
    menuItems.forEach(item => {
        if (!matched && currentPath.startsWith(item.dataset.path)) {
            item.classList.add('active'); matched = true;
        }
    });
    if (!matched) {
        const dash = document.querySelector('.menu-item[data-path="/dashboard"]');
        if (dash) dash.classList.add('active');
    }

    window.logout = function () {
        fetch(window.apiBaseUrl + '/auth/logout', {
            method: 'POST',
            headers: { 'Authorization': 'Bearer ' + window.token, 'Accept': 'application/json' }
        }).finally(() => { localStorage.clear(); window.location.href = '/'; });
    };

    window.toast = function(msg, type) {
        type = type || 'ok';
        const wrap = $('toastWrap');
        const el = document.createElement('div');
        el.className = 'toast ' + type;
        el.innerHTML = '<span class="toast-dot"></span>' + msg;
        wrap.appendChild(el);
        requestAnimationFrame(() => requestAnimationFrame(() => el.classList.add('show')));
        setTimeout(() => { el.classList.remove('show'); setTimeout(() => el.remove(), 400); }, 3200);
    };

    window.showDialog = function({ icon='warn', title, msg, confirmText='Hapus', confirmClass='confirm-err', onConfirm, cancelText='Batal' }) {
        const overlay = $('dlgOverlay');
        const iconEl  = $('dlgIcon');
        const icons = {
            warn: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
            err:  '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>',
            ok:   '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>',
            info: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>',
        };
        iconEl.className = 'dlg-icon ' + icon;
        iconEl.innerHTML = icons[icon] || icons.warn;
        $('dlgTitle').textContent   = title || '';
        $('dlgMsg').textContent     = msg   || '';
        const confirmBtn = $('dlgConfirm');
        const cancelBtn  = $('dlgCancel');
        confirmBtn.textContent = confirmText;
        confirmBtn.className   = 'dlg-btn ' + confirmClass;
        cancelBtn.textContent  = cancelText;
        overlay.classList.add('open');
        const close = () => overlay.classList.remove('open');
        confirmBtn.onclick = () => { close(); if (onConfirm) onConfirm(); };
        cancelBtn.onclick  = close;
        overlay.onclick = e => { if (e.target === overlay) close(); };
    };

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape')
            document.querySelectorAll('.dlg-overlay.open, .modal-overlay.open')
                .forEach(el => el.classList.remove('open'));
    });
})();
</script>

@stack('scripts')
</body>
</html>