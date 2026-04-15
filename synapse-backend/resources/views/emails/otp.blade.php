<!DOCTYPE html>
<html>
<head>
    <title>Verifikasi OTP</title>
</head>
<body style="font-family: Arial, sans-serif; text-align: center; padding: 20px;">
    <h2>Halo, Agen SYNAPSE!</h2>
    <p>Terima kasih telah mendaftar. Berikut adalah kode OTP Anda untuk verifikasi email:</p>
    <h1 style="background-color: #f3f4f6; padding: 10px; letter-spacing: 5px; color: #008080;">
        {{ $otp }}
    </h1>
    <p style="color: red; font-size: 12px;">Kode ini hanya berlaku selama 10 menit.</p>
</body>
</html>