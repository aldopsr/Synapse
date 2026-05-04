<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="stylesheet" href="{{asset('assets/css/style.css')}}">
</head>
<body>
    <!-- MAIN -->
    <div class="main" style="height:100vh;">
        <div style="display: flex; flex-direction:column; align-items:center; justify-content:center; width:100%; height:100%; gap: 30px;">
            <img src="{{asset('assets/image/synapseLogo.png')}}" style="width: 90px; height: auto;">
            <div style="display: flex; flex-direction:column; justify-content:center; align-items:center;">
                <h1 style="color: #667C89; font-size: 48px; font-weight: 600;">Verifikasi OTP</h1>
                <p style="color: #667C89; font-size: 16px;">Kirim kode verifikasi OTP ke email anda</p>
            </div>
            <form action="#" method="POST" class="profile-card">
                <!-- Form Email -->
                <div class="profile-text" style="outline: solid 1px #44474D;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#279685" style="width: 36px; height: 36px;">
                        <path d="M1.5 8.67v8.58a3 3 0 0 0 3 3h15a3 3 0 0 0 3-3V8.67l-8.928 5.493a3 3 0 0 1-3.144 0L1.5 8.67Z" />
                        <path d="M22.5 6.908V6.75a3 3 0 0 0-3-3h-15a3 3 0 0 0-3 3v.158l9.714 5.978a1.5 1.5 0 0 0 1.572 0L22.5 6.908Z" />
                    </svg>

                    <input type="text" name="email" placeholder="Email" class="profile-text-form">
                </div>
                <div style="display:flex; flex-direction:row; justify-content:center; align-items:center; gap: 0px 8px;">
                    <a href="/login" class="button2">Kembali</a>
                    <a href="/lupaSandiOTP" class="button1">Kirim</a>
                </div>
            </form>
        </div>
    </div>

</body>
</html>