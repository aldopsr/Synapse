<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }} - Login</title>
    <link rel="stylesheet" href="{{asset('assets/css/style.css')}}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <div class="main" style="height:100vh;">
        <div style="display: flex; flex-direction:column; align-items:center; justify-content:center; width:100%; height:100%; gap: 30px;">
            <img src="{{asset('assets/image/synapseLogo.png')}}" style="width: 90px; height: auto;">
            
            <div style="display: flex; flex-direction:column; justify-content:center; align-items:center;">
                <h1 style="color: #667C89; font-size: 48px; font-weight: 600;">SYNAPSE</h1>
                <p style="color: #667C89; font-size: 16px;">Small Steps. Big Leaps.</p>
            </div>

            <div id="errorMessage" style="display: none; color: #D8000C; background-color: #FFD2D2; padding: 10px; border-radius: 5px; width: 300px; text-align: center; font-size: 14px;">
            </div>

            <form id="loginForm" class="profile-card">
                
                <div class="profile-text" style="outline: solid 1px #44474D;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#279685" style="width: 36px; height: 36px;">
                        <path d="M1.5 8.67v8.58a3 3 0 0 0 3 3h15a3 3 0 0 0 3-3V8.67l-8.928 5.493a3 3 0 0 1-3.144 0L1.5 8.67Z" />
                        <path d="M22.5 6.908V6.75a3 3 0 0 0-3-3h-15a3 3 0 0 0-3 3v.158l9.714 5.978a1.5 1.5 0 0 0 1.572 0L22.5 6.908Z" />
                    </svg>
                    <input type="email" id="email" name="email" placeholder="Email" class="profile-text-form" required>
                </div>

                <div class="profile-text" style="outline: solid 1px #44474D; margin-top: 15px;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#279685" style="width: 36px; height: 36px;">
                        <path fill-rule="evenodd" d="M12 1.5a5.25 5.25 0 0 0-5.25 5.25v3a3 3 0 0 0-3 3v6.75a3 3 0 0 0 3 3h10.5a3 3 0 0 0 3-3v-6.75a3 3 0 0 0-3-3v-3c0-2.9-2.35-5.25-5.25-5.25Zm3.75 8.25v-3a3.75 3.75 0 1 0-7.5 0v3h7.5Z" clip-rule="evenodd" />
                    </svg>
                    <input type="password" id="password" name="password" placeholder="Kata Sandi" class="profile-text-form" required>
                </div>

                <div style="display: flex; flex-direction:row; justify-content:center; align-items:center; gap: 0px 3px; margin-top: 15px;">
                    <p style="color: #667C89; font-size: 14px;">Lupa Kata Sandi?</p>
                    <a href="/lupaSandi" style="color: #279685; font-size: 14px;">Klik di sini</a>
                </div>

                <div style="display:flex; flex-direction:row; justify-content:center; align-items:center; gap: 0px 8px; margin-top: 20px;">
                    <button type="submit" class="button1" id="loginBtn" style="width: 100%; border: none; cursor: pointer; text-decoration:none;">LOGIN</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    // Baca URL dari config Laravel — tidak hardcode
    const apiBaseUrl = "{{ config('app.api_url') }}";

    document.getElementById('loginForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const email    = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        const btn      = document.getElementById('loginBtn');
        const errorDiv = document.getElementById('errorMessage');

        btn.innerHTML = 'Loading...';
        btn.disabled  = true;
        errorDiv.style.display = 'none';

        try {
            const response = await fetch(apiBaseUrl + '/auth/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ email, password })
            });

            const data = await response.json();

            if (response.ok) {
                const token    = data.token    || (data.data && data.data.token);
                const userData = data.user     || (data.data && data.data.user);
                const userRole = (userData && userData.role) ? userData.role : 'dosen';

                if (token) {
                    localStorage.setItem('token', token);
                    localStorage.setItem('role',  userRole);
                    if (userData) {
                        localStorage.setItem('user', JSON.stringify(userData));
                    }
                    window.location.href = '/dashboard';
                } else {
                    errorDiv.style.display = 'block';
                    errorDiv.innerHTML     = 'Login sukses, tapi Token tidak ditemukan.';
                    btn.innerHTML = 'LOGIN';
                    btn.disabled  = false;
                }
            } else {
                errorDiv.style.display = 'block';
                errorDiv.innerHTML     = data.message || 'Email atau Kata Sandi salah.';
                btn.innerHTML = 'LOGIN';
                btn.disabled  = false;
            }
        } catch (error) {
            errorDiv.style.display = 'block';
            errorDiv.innerHTML     = 'Terjadi kesalahan jaringan.';
            console.error('Error:', error);
            btn.innerHTML = 'LOGIN';
            btn.disabled  = false;
        }
    });
</script>
</body>
</html>