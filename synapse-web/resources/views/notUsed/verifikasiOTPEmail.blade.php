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
            <p style="color: #9E9E9E; font-size: 16px";>Kirim kode verifikasi OTP ke email anda</p>
        </div>

        <!-- Form -->
        <form action="#" method="POST" class="center-container card-container">
            <!-- Form Email -->
            <div class="fill-box">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#279685" style="width: 28px; height: 28px;">
                    <path d="M1.5 8.67v8.58a3 3 0 0 0 3 3h15a3 3 0 0 0 3-3V8.67l-8.928 5.493a3 3 0 0 1-3.144 0L1.5 8.67Z" />
                    <path d="M22.5 6.908V6.75a3 3 0 0 0-3-3h-15a3 3 0 0 0-3 3v.158l9.714 5.978a1.5 1.5 0 0 0 1.572 0L22.5 6.908Z" />
                </svg>
                
                <input type="text" name="email" placeholder="Email" class="fill-box-text">
            </div>

            <!-- Tombol Submit -->
            <div class="button" type="submit">
                <p class="button-text">KIRIM</p>
            </div>
        </form>
    </body>
</html>
