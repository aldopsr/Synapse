<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Laravel') }}</title>
        <link rel="stylesheet" href="{{asset('assets/css/style.css')}}">
    </head>
    <body class="center-container" style="background-color: #EDEFF1; padding: 40px 24px 40px 24px; gap: 40px 0px;">
        <!-- Tombol Kembali -->
        <div style="display: flex; align-items:normal; justify-content: left; width: 425px; height: auto; gap: 0px 8px;">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                fill="none" stroke="#44474D" stroke-width="2.7"
                stroke-linecap="round" stroke-linejoin="round"
                style="width:35px; height: 28px;">
                <line x1="20" y1="12" x2="3" y2="12" />
                <polyline points="9 6 3 12 9 18" />
            </svg>

            <p style="display: flex; align-items:center; font-size:20px; font-weight:600; color:#44474D">Kembali</p>
        </div>

        <!-- Logo Synapse -->
        <img src="{{asset('assets/image/synapseLogo.png')}}" style="width: 90px; height: auto;">

        <!-- Judul -->
        <div style="display: flex; flex-direction: column; align-items:center ;justify-content:center; gap: 8px 0px">
            <h1 style="color: #667C89; font-size: 48px; font-weight: 600;">Verifikasi OTP</h1>
            <p style="color: #9E9E9E; font-size: 16px; text-align:center">Kami telah mengirimkan kode verifikasi OTP ke<br>email anda</p>
        </div>

        <!-- Form -->
        <form action="#" method="POST" class="center-container card-container">
            <!-- Form Kode OTP -->
            <div style="display:flex; flex-direction:row; gap: 0px 24px;">
                <div class="fill-box" style="width:50px; height:50px; outline: solid #667C89 2px; justify-content:center;">
                    <input type="text" maxlength="1" name="otp1" class="fill-box-text" style="width:100%; height:100%; text-align:center; font-size:24px; font-weight:600; color:#666B89;">
                </div>
                <div class="fill-box" style="width:50px; height:50px; outline: solid #667C89 2px">
                    <input type="text" maxlength="1" name="otp2" class="fill-box-text" style="width:100%; height:100%; text-align:center; font-size:24px; font-weight:600; color:#666B89;">
                </div>
                <div class="fill-box" style="width:50px; height:50px; outline: solid #667C89 2px">
                    <input type="text" maxlength="1" name="otp3" class="fill-box-text" style="width:100%; height:100%; text-align:center; font-size:24px; font-weight:600; color:#666B89;">
                </div>
                <div class="fill-box" style="width:50px; height:50px; outline: solid #667C89 2px">
                    <input type="text" maxlength="1" name="otp4" class="fill-box-text" style="width:100%; height:100%; text-align:center; font-size:24px; font-weight:600; color:#666B89;">
                </div>
            </div>

            <!-- Tombol Submit -->
            <div class="button" type="submit">
                <p class="button-text">KIRIM</p>
            </div>
        </form>
    </body>
</html>
