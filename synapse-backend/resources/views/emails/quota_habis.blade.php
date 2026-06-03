<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Token Harian Habis</title>
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
                            <div style="display:inline-block; background:#fff8f0; border:2px solid #e07000; border-radius:50%; width:64px; height:64px; line-height:64px; text-align:center; font-size:30px;">
                                &#9888;
                            </div>
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td style="padding: 24px 40px 20px;">
                            <h2 style="margin:0 0 12px; font-size:22px; color:#1a1a1a; font-weight:700; text-align:center;">Token Harian Habis</h2>
                            <p style="margin:0 0 20px; font-size:15px; color:#555555; line-height:1.6; text-align:center;">
                                Halo, <strong>{{ $name }}</strong>!<br>
                                Token harian kamu untuk fitur <strong>{{ $featureLabel }}</strong> sudah habis hari ini.
                            </p>

                            {{-- Quota Info --}}
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
                                <tr>
                                    <td align="center" style="background:#f0fafa; border-left:4px solid #008080; border-radius:8px; padding:16px 20px;">
                                        <div style="font-size:13px; color:#555; margin-bottom:6px;">Total token harianmu</div>
                                        <div style="font-size:32px; font-weight:800; color:#008080; line-height:1;">{{ $limit }}</div>
                                        <div style="font-size:12px; color:#888; margin-top:4px;">
                                            @if($type === 'generate_questions')
                                                soal per hari
                                            @else
                                                chat per hari
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            {{-- Reset Info --}}
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:28px; background:#f0fafa; border:1px solid #b2dfdf; border-radius:10px;">
                                <tr>
                                    <td style="padding:16px 20px;">
                                        <p style="margin:0; font-size:13px; color:#006666; line-height:1.6; text-align:center;">
                                            &#128337; <strong>{{ $resetInfo }}</strong>
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            {{-- Role-specific message --}}
                            @if($role === 'public')
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:20px; background:#f8f8f8; border-radius:10px;">
                                <tr>
                                    <td style="padding:16px 20px; text-align:center;">
                                        <p style="margin:0; font-size:13px; color:#555; line-height:1.6;">
                                            Ingin akses lebih banyak? Daftar sebagai <strong>mahasiswa IPB</strong> untuk mendapatkan <strong>15 chat/hari</strong>!
                                        </p>
                                    </td>
                                </tr>
                            </table>
                            @endif
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
