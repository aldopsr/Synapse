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
                <p style="color: #667C89; font-size: 16px;">Kami telah mengirimkan kode verifikasi OTP ke email anda</p>
            </div>
            <form action="#" method="POST" class="profile-card">
                <!-- Form OTP -->
                <div style="display: flex; justify-content:center; gap: 0px 30px;">
                    <div class="profile-text" style="width: 65px; height: 65px; border-radius:16px; outline: solid 3px #667C89;">
                        <input type="text" name="otp" maxlength="1" class="profile-text-form" style="text-align: center; font-size:24px;">
                    </div>
                    <div class="profile-text" style="width: 65px; height: 65px; border-radius:16px; outline: solid 3px #667C89;">
                        <input type="text" name="otp" maxlength="1" class="profile-text-form" style="text-align: center; font-size:24px;">
                    </div>
                    <div class="profile-text" style="width: 65px; height: 65px; border-radius:16px; outline: solid 3px #667C89;">
                        <input type="text" name="otp" maxlength="1" class="profile-text-form" style="text-align: center; font-size:24px;">
                    </div>
                    <div class="profile-text" style="width: 65px; height: 65px; border-radius:16px; outline: solid 3px #667C89;">
                        <input type="text" name="otp" maxlength="1" class="profile-text-form" style="text-align: center; font-size:24px;">
                    </div>
                </div>
                <div style="display:flex; flex-direction:row; justify-content:center; align-items:center; gap: 0px 8px;">
                    <a href="/lupaSandi" class="button2">Kembali</a>
                    <a href="/ubahSandi" class="button1">Kirim</a>
                </div>
            </form>
        </div>
    </div>

</body>
</html>