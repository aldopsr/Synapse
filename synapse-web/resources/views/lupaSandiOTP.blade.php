<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }} - Verifikasi OTP</title>
    <link rel="stylesheet" href="{{asset('assets/css/style.css')}}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .otp-box {
            width: 48px;
            height: 52px;
            border-radius: 12px;
            outline: solid 2px #667C89;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            transition: outline-color 0.15s;
        }
        .otp-box:focus-within {
            outline: solid 2.5px #279685;
        }
        .otp-digit {
            text-align: center;
            font-size: 22px;
            font-weight: 600;
            width: 100%;
            height: 100%;
            border: none;
            background: transparent;
            outline: none;
            color: #44474D;
        }
    </style>
</head>
<body>
    <div class="main" style="height:100vh;">
        <div style="display:flex; flex-direction:column; align-items:center; justify-content:center; width:100%; height:100%; gap: 30px;">
            <img src="{{asset('assets/image/synapseLogo.png')}}" style="width: 90px; height: auto;">

            <div style="display:flex; flex-direction:column; justify-content:center; align-items:center;">
                <h1 style="color: #667C89; font-size: 48px; font-weight: 600;">Verifikasi OTP</h1>
                <p id="emailInfo" style="color: #667C89; font-size: 16px;">Kami telah mengirimkan kode OTP ke email anda</p>
            </div>

            <div id="errorMessage" style="display:none; padding: 10px; border-radius: 5px; width: 352px; text-align: center; font-size: 14px;"></div>

            <form id="otpForm" class="profile-card">
                <div style="display:flex; justify-content:center; gap: 10px;">
                    <div class="otp-box"><input type="text" inputmode="numeric" class="otp-digit" maxlength="1"></div>
                    <div class="otp-box"><input type="text" inputmode="numeric" class="otp-digit" maxlength="1"></div>
                    <div class="otp-box"><input type="text" inputmode="numeric" class="otp-digit" maxlength="1"></div>
                    <div class="otp-box"><input type="text" inputmode="numeric" class="otp-digit" maxlength="1"></div>
                    <div class="otp-box"><input type="text" inputmode="numeric" class="otp-digit" maxlength="1"></div>
                    <div class="otp-box"><input type="text" inputmode="numeric" class="otp-digit" maxlength="1"></div>
                </div>

                <div style="display:flex; flex-direction:row; justify-content:center; align-items:center; gap: 4px;">
                    <p style="color: #667C89; font-size: 14px; margin: 0;">Tidak menerima kode?</p>
                    <button type="button" id="resendBtn" style="background:none; border:none; color:#279685; font-size:14px; cursor:pointer; padding:0;">Kirim ulang</button>
                </div>

                <div style="display:flex; flex-direction:row; justify-content:center; align-items:center; gap: 8px;">
                    <a href="/lupaSandi" class="button2">Kembali</a>
                    <button type="submit" id="verifikasiBtn" class="button1" style="border:none; cursor:pointer; text-decoration:none;">Verifikasi</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    const apiBaseUrl = "{{ config('app.api_url') }}";

    const email = sessionStorage.getItem('reset_email');
    if (!email) {
        window.location.href = '/lupaSandi';
    } else {
        document.getElementById('emailInfo').textContent = 'Kami telah mengirimkan kode OTP ke ' + email;
    }

    // Auto-focus dan auto-tab antar digit OTP
    const digits = document.querySelectorAll('.otp-digit');
    digits[0].focus();
    digits.forEach((input, index) => {
        input.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value && index < digits.length - 1) {
                digits[index + 1].focus();
            }
        });
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Backspace' && !this.value && index > 0) {
                digits[index - 1].focus();
            }
        });
    });

    function showError(msg) {
        const el = document.getElementById('errorMessage');
        el.style.display         = 'block';
        el.style.color           = '#D8000C';
        el.style.backgroundColor = '#FFD2D2';
        el.innerHTML             = msg;
    }

    function showSuccess(msg) {
        const el = document.getElementById('errorMessage');
        el.style.display         = 'block';
        el.style.color           = '#007E33';
        el.style.backgroundColor = '#C8F7C5';
        el.innerHTML             = msg;
    }

    document.getElementById('otpForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const otp      = Array.from(digits).map(d => d.value).join('');
        const btn      = document.getElementById('verifikasiBtn');
        const errorDiv = document.getElementById('errorMessage');

        errorDiv.style.display = 'none';

        if (otp.length < 6) {
            showError('Masukkan 6 digit kode OTP.');
            return;
        }

        btn.innerHTML = 'Loading...';
        btn.disabled  = true;

        try {
            const response = await fetch(apiBaseUrl + '/forgot-password/verify-otp', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ email, otp }),
            });

            const data = await response.json();

            if (response.ok) {
                sessionStorage.setItem('reset_token', data.reset_token);
                window.location.href = '/ubahSandi';
            } else {
                showError(data.message || 'OTP salah atau kadaluarsa.');
                btn.innerHTML = 'Verifikasi';
                btn.disabled  = false;
            }
        } catch (error) {
            showError('Terjadi kesalahan jaringan.');
            btn.innerHTML = 'Verifikasi';
            btn.disabled  = false;
        }
    });

    document.getElementById('resendBtn').addEventListener('click', async function() {
        const btn = this;
        document.getElementById('errorMessage').style.display = 'none';
        btn.textContent = 'Mengirim...';
        btn.disabled    = true;

        try {
            const response = await fetch(apiBaseUrl + '/forgot-password/send-otp', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ email }),
            });
            const data = await response.json();
            if (response.ok) {
                showSuccess('Kode OTP baru telah dikirim.');
            } else {
                showError(data.message || 'Gagal mengirim ulang OTP.');
            }
        } catch {
            showError('Terjadi kesalahan jaringan.');
        }

        setTimeout(() => { btn.textContent = 'Kirim ulang'; btn.disabled = false; }, 3000);
    });
    </script>
</body>
</html>
