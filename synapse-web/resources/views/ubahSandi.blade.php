<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }} - Ubah Sandi</title>
    <link rel="stylesheet" href="{{asset('assets/css/style.css')}}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <div class="main" style="height:100vh;">
        <div style="display: flex; flex-direction:column; align-items:center; justify-content:center; width:100%; height:100%; gap: 30px;">
            <img src="{{asset('assets/image/synapseLogo.png')}}" style="width: 90px; height: auto;">

            <div style="display: flex; flex-direction:column; justify-content:center; align-items:center;">
                <h1 style="color: #667C89; font-size: 48px; font-weight: 600;">Ubah Sandi</h1>
                <p style="color: #667C89; font-size: 16px;">Buat sandi baru untuk akun anda</p>
            </div>

            <div id="errorMessage" style="display: none; padding: 10px; border-radius: 5px; width: 352px; text-align: center; font-size: 14px;"></div>

            <form id="ubahSandiForm" class="profile-card">
                <!-- Sandi Baru -->
                <div class="profile-text" style="outline: solid 1px #44474D;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#279685" style="width: 36px; height: 36px;">
                        <path fill-rule="evenodd" d="M12 1.5a5.25 5.25 0 0 0-5.25 5.25v3a3 3 0 0 0-3 3v6.75a3 3 0 0 0 3 3h10.5a3 3 0 0 0 3-3v-6.75a3 3 0 0 0-3-3v-3c0-2.9-2.35-5.25-5.25-5.25Zm3.75 8.25v-3a3.75 3.75 0 1 0-7.5 0v3h7.5Z" clip-rule="evenodd" />
                    </svg>
                    <input type="password" id="newPassword" placeholder="Sandi Baru" class="profile-text-form" required>
                    <svg id="toggleNew" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#9E9E9E" style="width: 35px; height: 35px; cursor:pointer; flex-shrink:0;">
                        <path d="M3.53 2.47a.75.75 0 0 0-1.06 1.06l18 18a.75.75 0 1 0 1.06-1.06l-18-18ZM22.676 12.553a11.249 11.249 0 0 1-2.631 4.31l-3.099-3.099a5.25 5.25 0 0 0-6.71-6.71L7.759 4.577a11.217 11.217 0 0 1 4.242-.827c4.97 0 9.185 3.223 10.675 7.69.12.362.12.752 0 1.113Z" />
                        <path d="M15.75 12c0 .18-.013.357-.037.53l-4.244-4.243A3.75 3.75 0 0 1 15.75 12ZM12.53 15.713l-4.243-4.244a3.75 3.75 0 0 0 4.244 4.243Z" />
                        <path d="M6.75 12c0-.619.107-1.213.304-1.764l-3.1-3.1a11.25 11.25 0 0 0-2.63 4.31c-.12.362-.12.752 0 1.114 1.489 4.467 5.704 7.69 10.675 7.69 1.5 0 2.933-.294 4.242-.827l-2.477-2.477A5.25 5.25 0 0 1 6.75 12Z" />
                    </svg>
                </div>

                <!-- Konfirmasi Sandi Baru -->
                <div class="profile-text" style="outline: solid 1px #44474D;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#279685" style="width: 36px; height: 36px;">
                        <path fill-rule="evenodd" d="M12 1.5a5.25 5.25 0 0 0-5.25 5.25v3a3 3 0 0 0-3 3v6.75a3 3 0 0 0 3 3h10.5a3 3 0 0 0 3-3v-6.75a3 3 0 0 0-3-3v-3c0-2.9-2.35-5.25-5.25-5.25Zm3.75 8.25v-3a3.75 3.75 0 1 0-7.5 0v3h7.5Z" clip-rule="evenodd" />
                    </svg>
                    <input type="password" id="confirmPassword" placeholder="Konfirmasi Sandi Baru" class="profile-text-form" required>
                    <svg id="toggleConfirm" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#9E9E9E" style="width: 35px; height: 35px; cursor:pointer; flex-shrink:0;">
                        <path d="M3.53 2.47a.75.75 0 0 0-1.06 1.06l18 18a.75.75 0 1 0 1.06-1.06l-18-18ZM22.676 12.553a11.249 11.249 0 0 1-2.631 4.31l-3.099-3.099a5.25 5.25 0 0 0-6.71-6.71L7.759 4.577a11.217 11.217 0 0 1 4.242-.827c4.97 0 9.185 3.223 10.675 7.69.12.362.12.752 0 1.113Z" />
                        <path d="M15.75 12c0 .18-.013.357-.037.53l-4.244-4.243A3.75 3.75 0 0 1 15.75 12ZM12.53 15.713l-4.243-4.244a3.75 3.75 0 0 0 4.244 4.243Z" />
                        <path d="M6.75 12c0-.619.107-1.213.304-1.764l-3.1-3.1a11.25 11.25 0 0 0-2.63 4.31c-.12.362-.12.752 0 1.114 1.489 4.467 5.704 7.69 10.675 7.69 1.5 0 2.933-.294 4.242-.827l-2.477-2.477A5.25 5.25 0 0 1 6.75 12Z" />
                    </svg>
                </div>

                <div style="display:flex; flex-direction:row; justify-content:center; align-items:center; gap: 0px 8px; margin-top: 5px;">
                    <a href="/lupaSandiOTP" class="button2">Kembali</a>
                    <button type="submit" id="simpanBtn" class="button1" style="border: none; cursor: pointer; text-decoration: none;">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    const apiBaseUrl = "{{ config('app.api_url') }}";

    const email      = sessionStorage.getItem('reset_email');
    const resetToken = sessionStorage.getItem('reset_token');
    if (!email || !resetToken) {
        window.location.href = '/lupaSandi';
    }

    // Toggle show/hide password
    function togglePassword(inputId, toggleId) {
        document.getElementById(toggleId).addEventListener('click', function() {
            const input = document.getElementById(inputId);
            input.type  = input.type === 'password' ? 'text' : 'password';
        });
    }
    togglePassword('newPassword',     'toggleNew');
    togglePassword('confirmPassword', 'toggleConfirm');

    document.getElementById('ubahSandiForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const newPassword     = document.getElementById('newPassword').value;
        const confirmPassword = document.getElementById('confirmPassword').value;
        const btn             = document.getElementById('simpanBtn');
        const errorDiv        = document.getElementById('errorMessage');

        errorDiv.style.display = 'none';

        if (newPassword.length < 6) {
            errorDiv.style.display          = 'block';
            errorDiv.style.color            = '#D8000C';
            errorDiv.style.backgroundColor  = '#FFD2D2';
            errorDiv.innerHTML              = 'Sandi baru minimal 6 karakter.';
            return;
        }

        if (newPassword !== confirmPassword) {
            errorDiv.style.display          = 'block';
            errorDiv.style.color            = '#D8000C';
            errorDiv.style.backgroundColor  = '#FFD2D2';
            errorDiv.innerHTML              = 'Konfirmasi sandi tidak cocok.';
            return;
        }

        btn.innerHTML = 'Loading...';
        btn.disabled  = true;

        try {
            const response = await fetch(apiBaseUrl + '/forgot-password/reset', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    email,
                    reset_token:               resetToken,
                    new_password:              newPassword,
                    new_password_confirmation: confirmPassword,
                }),
            });

            const data = await response.json();

            if (response.ok) {
                sessionStorage.removeItem('reset_email');
                sessionStorage.removeItem('reset_token');
                errorDiv.style.display         = 'block';
                errorDiv.style.color           = '#007E33';
                errorDiv.style.backgroundColor = '#C8F7C5';
                errorDiv.innerHTML             = 'Sandi berhasil diubah! Mengalihkan ke halaman login...';
                setTimeout(() => window.location.href = '/login', 1500);
            } else {
                errorDiv.style.display          = 'block';
                errorDiv.style.color            = '#D8000C';
                errorDiv.style.backgroundColor  = '#FFD2D2';
                errorDiv.innerHTML              = data.message || 'Terjadi kesalahan.';
                btn.innerHTML = 'Simpan';
                btn.disabled  = false;
            }
        } catch (error) {
            errorDiv.style.display          = 'block';
            errorDiv.style.color            = '#D8000C';
            errorDiv.style.backgroundColor  = '#FFD2D2';
            errorDiv.innerHTML              = 'Terjadi kesalahan jaringan.';
            btn.innerHTML = 'Simpan';
            btn.disabled  = false;
        }
    });
    </script>
</body>
</html>
