<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Synapse')</title>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,400;0,14..32,600;0,14..32,700;1,14..32,400&display=swap" rel="stylesheet">

    {{-- Chart.js (shared, biar tiap page ga load ulang) --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>

    <style>
        /* ================================================
           RESET & BASE
           ================================================ */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --brand:        #279685;
            --brand-dark:   #1c6e60;
            --brand-light:  rgba(255,255,255,0.12);
            --brand-hover:  rgba(255,255,255,0.18);
            --brand-active: rgba(255,255,255,0.22);

            --sidebar-w:    256px;
            --topbar-h:     64px;

            --bg-page:      #F2F4F3;
            --bg-card:      #ffffff;
            --text-main:    #1a1a1a;
            --text-muted:   #6b7280;
            --border:       #e8eaed;

            --radius-sm:    8px;
            --radius-md:    12px;
            --radius-lg:    16px;
        }

        html, body {
            height: 100%;
            font-family: 'Inter', sans-serif;
            background: var(--bg-page);
            color: var(--text-main);
        }

        a { text-decoration: none; color: inherit; }

        /* ================================================
           LAYOUT SHELL
           ================================================ */
        .app-shell {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        /* ================================================
           SIDEBAR
           ================================================ */
        .sidebar {
            width: var(--sidebar-w);
            background: var(--brand);
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            height: 100vh;
            overflow-y: auto;
            overflow-x: hidden;
            scrollbar-width: none;
        }
        .sidebar::-webkit-scrollbar { display: none; }

        /* Brand header */
        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 20px 18px 18px;
            border-bottom: 1px solid rgba(255,255,255,0.12);
            flex-shrink: 0;
        }
        .sidebar-brand img {
            width: 36px;
            height: 36px;
            object-fit: contain;
            flex-shrink: 0;
        }
        .sidebar-brand-text {
            display: flex;
            flex-direction: column;
        }
        .sidebar-brand-name {
            font-size: 17px;
            font-weight: 700;
            color: #fff;
            letter-spacing: 0.5px;
            line-height: 1.2;
        }
        .sidebar-brand-tagline {
            font-size: 10px;
            color: rgba(255,255,255,0.55);
            font-weight: 400;
            letter-spacing: 0.03em;
        }

        /* Nav sections */
        .sidebar-nav {
            flex: 1;
            padding: 14px 12px;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        /* Section label (ADMIN / UMUM) */
        .nav-section-label {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.08em;
            color: rgba(255,255,255,0.4);
            padding: 10px 8px 4px;
            text-transform: uppercase;
            display: none; /* Akan ditampilkan via JS sesuai role */
        }
        .nav-section-label.visible { display: block; }

        /* Divider tipis antar section */
        .nav-divider {
            height: 1px;
            background: rgba(255,255,255,0.1);
            margin: 8px 0;
            display: none;
        }
        .nav-divider.visible { display: block; }

        /* Menu item */
        .menu-item {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 10px 12px;
            border-radius: var(--radius-sm);
            color: rgba(255,255,255,0.82);
            font-size: 13.5px;
            font-weight: 600;
            transition: background 0.15s, color 0.15s, transform 0.15s;
            cursor: pointer;
            position: relative;
        }
        .menu-item svg {
            flex-shrink: 0;
            opacity: 0.85;
            transition: opacity 0.15s;
        }
        .menu-item:hover {
            background: var(--brand-hover);
            color: #fff;
            transform: translateX(3px);
        }
        .menu-item:hover svg { opacity: 1; }

        /* Active state — pill putih di kiri + background lebih terang */
        .menu-item.active {
            background: var(--brand-active);
            color: #fff;
        }
        .menu-item.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 60%;
            background: #fff;
            border-radius: 0 3px 3px 0;
        }
        .menu-item.active svg { opacity: 1; }

        /* Sidebar footer (user card + logout) */
        .sidebar-footer {
            border-top: 1px solid rgba(255,255,255,0.12);
            padding: 14px 12px;
            flex-shrink: 0;
        }
        .sidebar-user-card {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 10px;
            border-radius: var(--radius-sm);
            margin-bottom: 6px;
            background: rgba(255,255,255,0.1);
        }
        .sidebar-avatar-sm {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: rgba(255,255,255,0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 700;
            color: #fff;
            flex-shrink: 0;
        }
        .sidebar-user-info { flex: 1; overflow: hidden; }
        .sidebar-user-name {
            font-size: 13px;
            font-weight: 700;
            color: #fff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .sidebar-user-role {
            font-size: 11px;
            color: rgba(255,255,255,0.55);
            font-weight: 400;
        }
        .btn-logout {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 12px;
            border-radius: var(--radius-sm);
            color: rgba(255,255,255,0.7);
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            background: transparent;
            width: 100%;
            transition: background 0.15s, color 0.15s;
            font-family: 'Inter', sans-serif;
        }
        .btn-logout:hover {
            background: rgba(255,80,80,0.2);
            color: #ffb3b3;
        }

        /* ================================================
           MAIN AREA
           ================================================ */
        .main-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* ================================================
           TOPBAR
           ================================================ */
        .topbar {
            height: var(--topbar-h);
            background: var(--bg-card);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px;
            position: sticky;
            top: 0;
            z-index: 50;
            flex-shrink: 0;
        }
        .topbar-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .topbar-page-title {
            font-size: 17px;
            font-weight: 700;
            color: var(--text-main);
        }
        .topbar-role-badge {
            font-size: 11px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 99px;
            letter-spacing: 0.04em;
        }
        .badge-admin  { background: #fef3e2; color: #854f0b; }
        .badge-dosen  { background: #e3faf8; color: #0f6e56; }

        /* User avatar + dropdown trigger */
        .topbar-right { display: flex; align-items: center; gap: 14px; }

        .topbar-user {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            padding: 6px 10px 6px 6px;
            border-radius: var(--radius-md);
            transition: background 0.15s;
            position: relative;
        }
        .topbar-user:hover { background: var(--bg-page); }

        .topbar-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--brand);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 700;
            color: #fff;
            flex-shrink: 0;
        }
        .topbar-user-info { display: flex; flex-direction: column; }
        .topbar-user-name {
            font-size: 13px;
            font-weight: 700;
            color: var(--text-main);
            line-height: 1.2;
        }
        .topbar-user-role {
            font-size: 11px;
            color: var(--text-muted);
            font-weight: 400;
        }

        /* Dropdown */
        .user-dropdown {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            width: 200px;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            box-shadow: 0 8px 24px rgba(0,0,0,0.1);
            overflow: hidden;
            display: none;
            z-index: 200;
        }
        .user-dropdown.open { display: block; }
        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 11px 16px;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-main);
            transition: background 0.12s;
            cursor: pointer;
        }
        .dropdown-item:hover { background: var(--bg-page); }
        .dropdown-item.danger { color: #dc2626; }
        .dropdown-item.danger:hover { background: #fef2f2; }
        .dropdown-divider { height: 1px; background: var(--border); }

        /* ================================================
           PAGE CONTENT
           ================================================ */
        .content-wrapper {
            flex: 1;
            overflow-y: auto;
            padding: 28px 30px;
        }
    </style>
</head>
<body>

<div class="app-shell">

    {{-- ===================== SIDEBAR ===================== --}}
    <aside class="sidebar">

        {{-- Brand --}}
        <a href="/dashboard" class="sidebar-brand">
            <img src="{{ asset('assets/image/synapseLogo.png') }}" alt="Synapse Logo">
            <div class="sidebar-brand-text">
                <span class="sidebar-brand-name">SYNAPSE</span>
                <span class="sidebar-brand-tagline">Learning Management System</span>
            </div>
        </a>

        {{-- Navigation --}}
        <nav class="sidebar-nav" id="sidebarNav">

            {{-- UMUM — tampil untuk semua role --}}
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
                <span>Aset AR</span>
            </a>

            {{-- ADMIN ONLY — disembunyikan, diungkap JS --}}
            <div class="nav-divider" id="dividerAdmin"></div>
            <span class="nav-section-label" id="labelAdmin">Admin</span>

            <a href="/kelolaAkunDosen" class="menu-item" data-path="/kelolaAkunDosen" id="menuKelolaDosen" style="display:none;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.25 6.75a3.75 3.75 0 1 1 7.5 0 3.75 3.75 0 0 1-7.5 0ZM15.75 9.75a3 3 0 1 1 6 0 3 3 0 0 1-6 0ZM2.25 9.75a3 3 0 1 1 6 0 3 3 0 0 1-6 0ZM6.31 15.117A6.745 6.745 0 0 1 12 12a6.745 6.745 0 0 1 6.709 7.498.75.75 0 0 1-.372.568A12.696 12.696 0 0 1 12 21.75c-2.305 0-4.47-.612-6.337-1.684a.75.75 0 0 1-.372-.568 6.787 6.787 0 0 1 1.019-4.38Z" clip-rule="evenodd"/>
                    <path d="M5.082 14.254a8.287 8.287 0 0 0-1.308 5.135 9.687 9.687 0 0 1-1.764-.44l-.115-.04a.563.563 0 0 1-.373-.487l-.01-.121a3.75 3.75 0 0 1 3.57-4.047ZM20.226 19.389a8.287 8.287 0 0 0-1.308-5.135 3.75 3.75 0 0 1 3.57 4.047l-.01.121a.563.563 0 0 1-.373.486l-.115.04c-.567.2-1.156.349-1.764.441Z"/>
                </svg>
                <span>Kelola Dosen</span>
            </a>

        </nav>

        {{-- Footer: user card + logout --}}
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

    {{-- ===================== MAIN AREA ===================== --}}
    <div class="main-area">

        {{-- Topbar --}}
        <header class="topbar">
            <div class="topbar-left">
                <span class="topbar-page-title">@yield('header_title', 'Dashboard')</span>
                <span class="topbar-role-badge" id="topbarRoleBadge"></span>
            </div>

            <div class="topbar-right">
                {{-- User info (no dropdown — read only display) --}}
                <div class="topbar-user" id="topbarUser" style="cursor:default;">
                    <div class="topbar-avatar" id="topbarAvatarInitials">?</div>
                    <div class="topbar-user-info">
                        <span class="topbar-user-name" id="topbarUserName">Memuat...</span>
                        <span class="topbar-user-role" id="topbarUserRole">—</span>
                    </div>
                </div>
            </div>
        </header>

        {{-- Page content --}}
        <main class="content-wrapper">
            @yield('content')
        </main>
    </div>
</div>

{{-- ===================== GLOBAL SCRIPTS ===================== --}}
<script>
(function () {
    // ── Globals ──────────────────────────────────────────────
    window.apiBaseUrl = "{{ config('app.api_url') }}";
    window.token      = localStorage.getItem('token');
    window.role       = localStorage.getItem('role') || 'dosen';
    window.user       = null;

    try {
        const u = localStorage.getItem('user');
        if (u) window.user = JSON.parse(u);
    } catch (e) { console.warn('[Synapse] Gagal parse user dari localStorage'); }

    // ── Auth guard ───────────────────────────────────────────
    if (!window.token) {
        window.location.href = '/';
        return;
    }

    // ── Helpers ──────────────────────────────────────────────
    const $ = id => document.getElementById(id);

    function initials(name) {
        if (!name) return '?';
        const parts = name.trim().split(' ');
        return parts.length >= 2
            ? (parts[0][0] + parts[1][0]).toUpperCase()
            : parts[0].slice(0, 2).toUpperCase();
    }

    function roleLabel(r) {
        const map = { admin: 'Admin', superadmin: 'Super Admin', dosen: 'Dosen' };
        return map[r] || r;
    }

    // ── Populate user info ───────────────────────────────────
    const name = window.user?.name || 'Pengguna';
    const role = window.role;
    const ini  = initials(name);
    const rl   = roleLabel(role);
    const isAdmin = role === 'admin' || role === 'superadmin';

    // Sidebar footer
    $('sidebarAvatarInitials').textContent = ini;
    $('sidebarUserName').textContent       = name;
    $('sidebarUserRole').textContent       = rl;

    // Topbar
    $('topbarAvatarInitials').textContent = ini;
    $('topbarUserName').textContent       = name;
    $('topbarUserRole').textContent       = rl;

    const badge = $('topbarRoleBadge');
    badge.textContent = rl;
    badge.className   = 'topbar-role-badge ' + (isAdmin ? 'badge-admin' : 'badge-dosen');

    // ── Admin-only menu items ────────────────────────────────
    if (isAdmin) {
        $('menuKelolaDosen').style.display = 'flex';
        $('dividerAdmin').classList.add('visible');
        $('labelAdmin').classList.add('visible');
    }

    // ── Active state: match current URL ──────────────────────
    // Cek apakah pathname dimulai dengan path menu item
    // (bukan exact match, biar child routes juga ikut aktif)
    const currentPath = window.location.pathname;

    // Urutan prioritas: path yang lebih panjang dicek dulu
    // biar /mata-kuliah/123/materi tidak trigger /mata-kuliah juga
    const menuItems = Array.from(document.querySelectorAll('.menu-item[data-path]'))
        .sort((a, b) => b.dataset.path.length - a.dataset.path.length);

    let matched = false;
    menuItems.forEach(item => {
        if (!matched && currentPath.startsWith(item.dataset.path)) {
            item.classList.add('active');
            matched = true;
        }
    });

    // Fallback: kalau tidak ada yang cocok, aktifkan dashboard
    if (!matched) {
        const dash = document.querySelector('.menu-item[data-path="/dashboard"]');
        if (dash) dash.classList.add('active');
    }

    // ── Logout ───────────────────────────────────────────────
    window.logout = function () {
        fetch(window.apiBaseUrl + '/auth/logout', {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + window.token,
                'Accept': 'application/json',
            }
        }).finally(() => {
            localStorage.clear();
            window.location.href = '/';
        });
    };

})();
</script>

@stack('scripts')
</body>
</html>