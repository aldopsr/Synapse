<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Synapse')</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body { font-family: 'Inter', sans-serif; background-color: #F4F7F6; margin: 0; display: flex; height: 100vh; overflow: hidden; }
        .sidebar { width: 260px; background-color: #279685; color: white; display: flex; flex-direction: column; transition: 0.3s; }
        .sidebar-header { padding: 25px 20px; text-align: center; text-decoration: none; color: white; display: block; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .menu { padding: 20px 15px; flex: 1; display: flex; flex-direction: column; gap: 10px; }
        .menu-item { display: flex; align-items: center; gap: 15px; padding: 12px 15px; color: white; text-decoration: none; border-radius: 8px; font-weight: 600; transition: all 0.2s; }
        .menu-item:hover { background-color: rgba(255,255,255,0.15); transform: translateX(5px); }
        .main { flex: 1; display: flex; flex-direction: column; overflow-y: auto; }
        .topbar { background: white; padding: 20px 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 10; }
        .content-wrapper { padding: 30px; }
    </style>
</head>
<body>

    <div class="sidebar">
        <a href="/dashboard" class="sidebar-header">
            <img src="{{ asset('assets/image/synapseLogo.png') }}" style="width: 50px; height: auto; margin-bottom: 10px;">
            <h2 style="margin: 0; font-size: 20px; letter-spacing: 1px;">SYNAPSE</h2>
        </a>

        <div class="menu">
            <a href="/dashboard" class="menu-item">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width:22px; height:22px;">
                    <path d="M11.47 3.841a.75.75 0 0 1 1.06 0l8.69 8.69a.75.75 0 1 0 1.06-1.061l-8.689-8.69a2.25 2.25 0 0 0-3.182 0l-8.69 8.69a.75.75 0 1 0 1.061 1.06l8.69-8.689Z" />
                    <path d="m12 5.432 8.159 8.159c.03.03.06.058.091.086v6.198c0 1.035-.84 1.875-1.875 1.875H15a.75.75 0 0 1-.75-.75v-4.5a.75.75 0 0 0-.75-.75h-3a.75.75 0 0 0-.75.75V21a.75.75 0 0 1-.75.75H5.625a1.875 1.875 0 0 1-1.875-1.875v-6.198a2.29 2.29 0 0 0 .091-.086L12 5.432Z" />
                </svg>
                <span>Dashboard</span>
            </a>
            
            <a href="/kelolaAkunDosen" class="menu-item" id="menuKelolaDosen" style="display: none;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width:22px; height:22px;">
                    <path fill-rule="evenodd" d="M18.685 19.097A9.723 9.723 0 0 0 21.75 12c0-5.385-4.365-9.75-9.75-9.75S2.25 6.615 2.25 12a9.723 9.723 0 0 0 3.065 7.097A9.716 9.716 0 0 0 12 21.75a9.716 9.716 0 0 0 6.685-2.653Zm-12.54-1.285A7.486 7.486 0 0 1 12 15a7.486 7.486 0 0 1 5.855 2.812A8.224 8.224 0 0 1 12 20.25a8.224 8.224 0 0 1-5.855-2.438ZM15.75 9a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" clip-rule="evenodd" />
                </svg>
                <span>Kelola Akun Dosen</span>
            </a>

            <a href="/mata-kuliah" class="menu-item" id="menumatkul" style="display: none;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width:22px; height:22px;">
                    <path fill-rule="evenodd" d="M18.685 19.097A9.723 9.723 0 0 0 21.75 12c0-5.385-4.365-9.75-9.75-9.75S2.25 6.615 2.25 12a9.723 9.723 0 0 0 3.065 7.097A9.716 9.716 0 0 0 12 21.75a9.716 9.716 0 0 0 6.685-2.653Zm-12.54-1.285A7.486 7.486 0 0 1 12 15a7.486 7.486 0 0 1 5.855 2.812A8.224 8.224 0 0 1 12 20.25a8.224 8.224 0 0 1-5.855-2.438ZM15.75 9a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" clip-rule="evenodd" />
                </svg>
                <span>Kelola matkul</span>
            </a>

            <a href="#" class="menu-item" id="menuMateriSaya" style="display: none;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width:22px; height:22px;">
                    <path d="M11.25 4.533A9.707 9.707 0 0 0 6 3a9.735 9.735 0 0 0-3.25.555.75.75 0 0 0-.5.707v14.25a.75.75 0 0 0 1 .707A8.237 8.237 0 0 1 6 17.25c1.626 0 3.184.468 4.5 1.288v-14.005ZM12.75 4.533v14.005c1.316-.82 2.874-1.288 4.5-1.288 1.137 0 2.222.23 3.25.642a.75.75 0 0 0 1-.707V4.262a.75.75 0 0 0-.5-.707A9.735 9.735 0 0 0 18 3a9.707 9.707 0 0 0-5.25 1.533Z" />
                </svg>
                <span>Materi Saya</span>
            </a>

            <a href="/kelola-ar" class="menu-item" id="menuKelolaAR">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width:22px; height:22px;">
                    <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25ZM12 4.5a7.5 7.5 0 1 0 0 15 7.5 7.5 0 0 0 0-15Z" clip-rule="evenodd" />
                </svg>
                <span>Kelola Aset AR</span>
            </a>

            <a href="/kuis" class="menu-item">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width:22px; height:22px;">
                    <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                <span>Kelola Kuis</span>
            </a>

            <a href="#" class="menu-item" onclick="logout()" style="margin-top: auto; color: #FFD6D6;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width:22px; height:22px;">
                    <path fill-rule="evenodd" d="M7.5 3.75A1.5 1.5 0 0 0 6 5.25v13.5a1.5 1.5 0 0 0 1.5 1.5h6a1.5 1.5 0 0 0 1.5-1.5V15a.75.75 0 0 1 1.5 0v3.75a3 3 0 0 1-3 3h-6a3 3 0 0 1-3-3V5.25a3 3 0 0 1 3-3h6a3 3 0 0 1 3 3V9A.75.75 0 0 1 15 9V5.25a1.5 1.5 0 0 0-1.5-1.5h-6Zm10.72 4.72a.75.75 0 0 1 1.06 0l3 3a.75.75 0 0 1 0 1.06l-3 3a.75.75 0 1 1-1.06-1.06l1.72-1.72H9a.75.75 0 0 1 0-1.5h10.94l-1.72-1.72a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                </svg>
                <span>Logout</span>
            </a>


        </div>
    </div>

    <div class="main">
        <div class="topbar">
            <h2 style="margin: 0; color: #333;">@yield('header_title', 'Dashboard') <span id="roleBadge" style="font-size: 14px; color: #888; font-weight: normal; background: #eee; padding: 4px 10px; border-radius: 20px; margin-left: 10px;"></span></h2>
        </div>

        <div class="content-wrapper">
            @yield('content')
        </div>
    </div>

    <script>
    // URL Backend — dibaca dari config, bukan hardcode
    window.apiBaseUrl = "{{ config('app.api_url') }}";

    window.token = localStorage.getItem('token');
    window.role  = localStorage.getItem('role') || 'dosen';
    window.user  = null;

    const userStr = localStorage.getItem('user');
    if (userStr) {
        try { window.user = JSON.parse(userStr); } catch (e) { console.error("Gagal parse data user"); }
    }

    // Proteksi Halaman — kalau tidak ada token, tendang ke login
    if (!window.token) {
        window.location.href = '/';
    }

    // Set Role Badge di Topbar
    const roleBadge = document.getElementById('roleBadge');
    if (roleBadge) {
        roleBadge.innerText = window.role.toUpperCase();
    }

    // Tampilkan menu khusus Admin
    if (window.role === 'admin' || window.role === 'superadmin') {
        const menuKelolaDosen = document.getElementById('menuKelolaDosen');
        const menuMatkul      = document.getElementById('menumatkul');
        if (menuKelolaDosen) menuKelolaDosen.style.display = 'flex';
        if (menuMatkul)      menuMatkul.style.display      = 'flex';
    }

    // Fungsi Logout
    function logout() {
        fetch(window.apiBaseUrl + '/api/auth/logout', {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + window.token,
                'Accept': 'application/json',
            }
        }).finally(() => {
            localStorage.clear();
            window.location.href = '/';
        });
    }
</script>
    
    @stack('scripts')
</body>
</html>