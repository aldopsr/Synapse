<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }} - Lupa Sandi</title>
    <link rel="stylesheet" href="{{asset('assets/css/style.css')}}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <div class="main" style="height:100vh;">
        <div style="display: flex; flex-direction:column; align-items:center; justify-content:center; width:100%; height:100%; gap: 30px;">
            <img src="{{asset('assets/image/synapseLogo.png')}}" style="width: 90px; height: auto;">

            <div style="display: flex; flex-direction:column; justify-content:center; align-items:center;">
                <h1 style="color: #667C89; font-size: 48px; font-weight: 600;">Lupa Sandi</h1>
                <p style="color: #667C89; font-size: 16px;">Masukkan email dosen untuk menerima kode OTP</p>
            </div>

            <div id="errorMessage" style="display: none; color: #D8000C; background-color: #FFD2D2; padding: 10px; border-radius: 5px; width: 352px; text-align: center; font-size: 14px;"></div>

            <form id="lupaSandiForm" class="profile-card">
                <div class="profile-text" style="outline: solid 1px #44474D;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#279685" style="width: 36px; height: 36px;">
                        <path d="M1.5 8.67v8.58a3 3 0 0 0 3 3h15a3 3 0 0 0 3-3V8.67l-8.928 5.493a3 3 0 0 1-3.144 0L1.5 8.67Z" />
                        <path d="M22.5 6.908V6.75a3 3 0 0 0-3-3h-15a3 3 0 0 0-3 3v.158l9.714 5.978a1.5 1.5 0 0 0 1.572 0L22.5 6.908Z" />
                    </svg>
                    <input type="email" id="email" name="email" placeholder="Email Dosen" class="profile-text-form" required>
                </div>

                <div style="display:flex; flex-direction:row; justify-content:center; align-items:center; gap: 0px 8px; margin-top: 5px;">
                    <a href="/login" class="button2">Kembali</a>
                    <button type="submit" id="kirimBtn" class="button1" style="border: none; cursor: pointer; text-decoration: none;">Kirim OTP</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    const apiBaseUrl = "{{ config('app.api_url') }}";

    document.getElementById('lupaSandiForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const email    = document.getElementById('email').value.trim();
        const btn      = document.getElementById('kirimBtn');
        const errorDiv = document.getElementById('errorMessage');

        btn.innerHTML = 'Loading...';
        btn.disabled  = true;
        errorDiv.style.display = 'none';

        try {
            const response = await fetch(apiBaseUrl + '/forgot-password/send-otp', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ email }),
            });

            const data = await response.json();

            if (response.ok) {
                sessionStorage.setItem('reset_email', email);
                window.location.href = '/lupaSandiOTP';
            } else {
                errorDiv.style.display = 'block';
                errorDiv.innerHTML     = data.message || 'Terjadi kesalahan.';
                btn.innerHTML = 'Kirim OTP';
                btn.disabled  = false;
            }
        } catch (error) {
            errorDiv.style.display = 'block';
            errorDiv.innerHTML     = 'Terjadi kesalahan jaringan.';
            btn.innerHTML = 'Kirim OTP';
            btn.disabled  = false;
        }
    });
    </script>
</body>
</html>
