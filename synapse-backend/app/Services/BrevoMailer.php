<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

class BrevoMailer
{
    public static function sendOtp(string $toEmail, string $otp, ?string $toName = null): bool
    {
        $html = View::make('emails.otp', ['otp' => $otp])->render();

        return self::send(
            toEmail: $toEmail,
            toName: $toName ?? $toEmail,
            subject: 'Kode Verifikasi SYNAPSE',
            htmlContent: $html
        );
    }

    public static function sendPasswordChanged(string $toEmail, string $userName): bool
{
    $changedAt = now()->timezone('Asia/Jakarta')->format('d M Y, H:i') . ' WIB';

    $html = View::make('emails.password_changed', [
        'name' => $userName,
        'changedAt' => $changedAt,
    ])->render();

    return self::send(
        toEmail: $toEmail,
        toName: $userName,
        subject: 'Password SYNAPSE Kamu Berhasil Diubah',
        htmlContent: $html
    );
}

    private static function send(string $toEmail, string $toName, string $subject, string $htmlContent): bool
    {
        $response = Http::withHeaders([
            'api-key' => config('services.brevo.key'),
            'Content-Type' => 'application/json',
            'accept' => 'application/json',
        ])->post('https://api.brevo.com/v3/smtp/email', [
            'sender' => [
                'name' => config('mail.from.name'),
                'email' => config('mail.from.address'),
            ],
            'to' => [
                ['email' => $toEmail, 'name' => $toName],
            ],
            'subject' => $subject,
            'htmlContent' => $htmlContent,
        ]);

        if (!$response->successful()) {
            Log::error('Brevo send failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'to' => $toEmail,
            ]);
            return false;
        }

        return true;
    }
}