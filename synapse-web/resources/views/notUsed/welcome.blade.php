<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Laravel') }}</title>
        <link rel="stylesheet" href="{{asset('assets/css/style.css')}}">
    </head>
    <body class="center-container" style="background-color: #EDEFF1; gap: 40px 0px; padding: 60px 24px 60px 24px">
        <!-- Logo Synapse -->
        <img src="{{asset('assets/image/synapseLogo.png')}}" style="width: 90px; height: auto;">

        <!-- Judul -->
        <div style="display: flex; flex-direction: column; align-items:center ;justify-content:center; gap: 8px 0px">
            <h1 style="color: #667C89; font-size: 48px; font-weight: 600;">SYNAPSE</h1>
            <p style="color: #9E9E9E; font-size: 16px";>Small Steps. Big Leaps.</p>
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

            <!-- Form Kata Sandi -->
            <div class="fill-box">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#279685" style="width: 33px; height: 33px;">
                    <path fill-rule="evenodd" d="M12 1.5a5.25 5.25 0 0 0-5.25 5.25v3a3 3 0 0 0-3 3v6.75a3 3 0 0 0 3 3h10.5a3 3 0 0 0 3-3v-6.75a3 3 0 0 0-3-3v-3c0-2.9-2.35-5.25-5.25-5.25Zm3.75 8.25v-3a3.75 3.75 0 1 0-7.5 0v3h7.5Z" clip-rule="evenodd" />
                </svg>

                <input type="text" name="kataSandi" placeholder="Kata Sandi" class="fill-box-text">

                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#9E9E9E" style="width: 33px; height: 33px;">
                    <path d="M3.53 2.47a.75.75 0 0 0-1.06 1.06l18 18a.75.75 0 1 0 1.06-1.06l-18-18ZM22.676 12.553a11.249 11.249 0 0 1-2.631 4.31l-3.099-3.099a5.25 5.25 0 0 0-6.71-6.71L7.759 4.577a11.217 11.217 0 0 1 4.242-.827c4.97 0 9.185 3.223 10.675 7.69.12.362.12.752 0 1.113Z" />
                    <path d="M15.75 12c0 .18-.013.357-.037.53l-4.244-4.243A3.75 3.75 0 0 1 15.75 12ZM12.53 15.713l-4.243-4.244a3.75 3.75 0 0 0 4.244 4.243Z" />
                    <path d="M6.75 12c0-.619.107-1.213.304-1.764l-3.1-3.1a11.25 11.25 0 0 0-2.63 4.31c-.12.362-.12.752 0 1.114 1.489 4.467 5.704 7.69 10.675 7.69 1.5 0 2.933-.294 4.242-.827l-2.477-2.477A5.25 5.25 0 0 1 6.75 12Z" />
                </svg>
            </div>

            <!-- Lupa Kata Sandi -->
            <div style="display: flex; flex-direction: row">
                <p style="color: #74777E; font-size: 14px;">Lupa Kata Sandi?&nbsp;</p>
                <a href="#" style="color: #279685; font-size: 14px; text-decoration: underline;">Klik di sini</a> 
            </div>

            <!-- Tombol Submit -->
            <div class="button" type="submit">
                <p class="button-text">MASUK SISTEM</p>
            </div>
        </form>

        <!-- Belum Punya Akun -->
        <div style="display: flex; flex-direction: row">
            <p style="color: #74777E; font-size: 14px;">Belum memiliki akses?&nbsp;</p>
            <a href="#" style="color: #279685; font-size: 14px; text-decoration: underline;">Daftar di sini</a> 
        </div>

        <!-- Tulisan Atau -->
        <div style="display: flex; flex-direction: row; width: 390px; align-items: center; justify-content: center; gap: 0px 8px;">
            <div style="width: 100%; height:1px; background-color:#CDCDCD;"></div>

            <p style="color: #74777E; font-size: 14px;">ATAU</p>

            <div style="width: 100%; height:1px; background-color:#CDCDCD;"></div>
        </div>

        <!-- Tombol as Guest -->
        <div style="display:flex; align-items:center; justify-content:center; width: 392px; height: 36px; outline: #B6B6B6 solid 1px; border-radius:24px; gap: 0px 10px">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#667C89" style="width: 25px; height: 25px;">
                <path fill-rule="evenodd" d="M10.5 3.798v5.02a3 3 0 0 1-.879 2.121l-2.377 2.377a9.845 9.845 0 0 1 5.091 1.013 8.315 8.315 0 0 0 5.713.636l.285-.071-3.954-3.955a3 3 0 0 1-.879-2.121v-5.02a23.614 23.614 0 0 0-3 0Zm4.5.138a.75.75 0 0 0 .093-1.495A24.837 24.837 0 0 0 12 2.25a25.048 25.048 0 0 0-3.093.191A.75.75 0 0 0 9 3.936v4.882a1.5 1.5 0 0 1-.44 1.06l-6.293 6.294c-1.62 1.621-.903 4.475 1.471 4.88 2.686.46 5.447.698 8.262.698 2.816 0 5.576-.239 8.262-.697 2.373-.406 3.092-3.26 1.47-4.881L15.44 9.879A1.5 1.5 0 0 1 15 8.818V3.936Z" clip-rule="evenodd" />
            </svg>
            
            <p style="color: #667C89; font-size:16px;font-weight:600">Eksplorasi sebagai Tamu</p>
        </div>
    </body>
</html>
