<?php
// app/Services/NotificationService.php
namespace App\Services;

use App\Models\FcmToken;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class NotificationService
{
    public function __construct(
        private readonly Messaging $messaging
    ) {}

    public function sendToUser(
        string $userId,
        string $title,
        string $body,
        array  $data = []
    ): bool {
        $fcmToken = FcmToken::where('user_id', $userId)->first();

        if (!$fcmToken || empty($fcmToken->token)) {
            Log::info("FCM: user {$userId} tidak punya token, skip.");
            return false;
        }

        return $this->send($fcmToken->token, $title, $body, $data);
    }

    public function send(
        string $token,
        string $title,
        string $body,
        array  $data = []
    ): bool {
        try {
            $stringData = array_map('strval', $data);

            // kreait/firebase-php v8.x: pakai withToken()
            $message = CloudMessage::new()
                ->withToken($token)
                ->withNotification(
                    Notification::create($title, $body)
                )
                ->withData($stringData);

            $this->messaging->send($message);
            return true;
        } catch (\Kreait\Firebase\Exception\Messaging\NotFound $e) {
            Log::warning("FCM token tidak valid, hapus: {$token}");
            FcmToken::where('token', $token)->delete();
            return false;
        } catch (\Exception $e) {
            Log::error('FCM send error: ' . $e->getMessage());
            return false;
        }
    }
}