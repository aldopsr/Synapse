<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Berhasil Diubah</title>
</head>
<body style="margin:0; padding:0; background-color:#f0f4f4; font-family: 'Segoe UI', Arial, sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f0f4f4; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="100%" cellpadding="0" cellspacing="0" style="max-width:560px; background:#ffffff; border-radius:16px; overflow:hidden; box-shadow: 0 4px 20px rgba(0,128,128,0.1);">

                    {{-- Header / Logo --}}
                    <tr>
                        <td align="center" style="background: linear-gradient(135deg, #006666 0%, #008080 60%, #00a0a0 100%); padding: 36px 40px 28px;">
                            <img src="{{ asset('images/synapseLogo.png') }}" alt="" width="160" style="display:block; margin:0 auto 12px; max-width:160px;">
                            <div style="font-size:30px; font-weight:800; color:#ffffff; letter-spacing:5px; text-transform:uppercase; font-family:'Segoe UI',Arial,sans-serif;">SYNAPSE</div>
                            <div style="font-size:12px; color:rgba(255,255,255,0.8); letter-spacing:1px; margin-top:6px; font-style:italic;">Small Steps. Big Leaps.</div>
                        </td>
                    </tr>

                    {{-- Icon --}}
                    <tr>
                        <td align="center" style="padding-top:36px; padding-bottom:4px;">
                            <div style="display:inline-block; background:#f0fafa; border:2px solid #008080; border-radius:50%; width:64px; height:64px; line-height:64px; text-align:center; font-size:30px;">
                                &#128274;
                            </div>
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td style="padding: 24px 40px 20px;">
                            <h2 style="margin:0 0 12px; font-size:22px; color:#1a1a1a; font-weight:700; text-align:center;">Password Berhasil Diubah</h2>
                            <p style="margin:0 0 20px; font-size:15px; color:#555555; line-height:1.6; text-align:center;">
                                Halo, <strong>{{ $name }}</strong>!<br>
                                Password akun SYNAPSE kamu baru saja berhasil diubah pada:
                            </p>

                            {{-- Timestamp --}}
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
                                <tr>
                                    <td align="center" style="background:#f0fafa; border-left:4px solid #008080; border-radius:8px; padding:14px 20px;">
                                        <span style="font-size:14px; color:#008080; font-weight:600;">{{ $changedAt }}</span>
                                    </td>
                                </tr>
                            </table>

                            {{-- Warning --}}
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:28px; background:#fff8f0; border:1px solid #f0c080; border-radius:10px;">
                                <tr>
                                    <td style="padding:16px 20px;">
                                        <p style="margin:0; font-size:13px; color:#885500; line-height:1.6;">
                                            <strong>&#9888; Bukan kamu yang melakukan ini?</strong><br>
                                            Segera hubungi tim SYNAPSE atau amankan akun kamu secepatnya.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="background:#f8fafa; border-top:1px solid #e8f0f0; padding:20px 40px; text-align:center;">
                            <p style="margin:0; font-size:12px; color:#aaaaaa;">
                                &copy; {{ date('Y') }} SYNAPSE &mdash; IPB University<br>
                                Email ini dikirim secara otomatis, harap jangan membalas.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
