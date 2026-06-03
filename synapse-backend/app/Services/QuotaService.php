<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class QuotaService
{
    const LIMITS = [
        'chat' => [
            'public'    => 15,
            'mahasiswa' => 20,
        ],
        'generate_questions' => [
            'dosen'      => 35,
            'admin'      => 35,
            'superadmin' => 35,
        ],
    ];

    // Threshold sisa token sebelum reminder muncul
    const WARNING_THRESHOLD = 3;

    public function getLimit(string $role, string $type): ?int
    {
        return self::LIMITS[$type][$role] ?? null;
    }

    private function usageKey(string|int $userId, string $type): string
    {
        return "quota_{$type}_{$userId}_" . now()->toDateString();
    }

    public function getUsed(string|int $userId, string $type): int
    {
        return (int) Cache::get($this->usageKey((string) $userId, $type), 0);
    }

    public function getRemaining($user, string $type): ?int
    {
        $limit = $this->getLimit($user->role, $type);
        if ($limit === null) return null;
        return max(0, $limit - $this->getUsed($user->id, $type));
    }

    public function isExhausted($user, string $type): bool
    {
        $limit = $this->getLimit($user->role, $type);
        if ($limit === null) return false;
        return $this->getUsed($user->id, $type) >= $limit;
    }

    /**
     * Consume one quota slot. Returns remaining count, or null if no limit applies.
     */
    public function consume($user, string $type): ?int
    {
        $limit = $this->getLimit($user->role, $type);
        if ($limit === null) return null;

        $key  = $this->usageKey($user->id, $type);
        $used = (int) Cache::get($key, 0);
        $ttl  = now()->secondsUntilEndOfDay() + 1;

        Cache::put($key, $used + 1, $ttl);

        return max(0, $limit - ($used + 1));
    }

    /**
     * Returns a notification array to show in-app, or null if nothing to show.
     * Call this after consume() with the returned remaining value.
     *
     * @param  int|null  $remaining  Value returned by consume()
     * @param  string    $type       'chat' | 'generate_questions'
     */
    public function buildNotification(?int $remaining, string $type): ?array
    {
        if ($remaining === null) return null;

        $label = $type === 'generate_questions' ? 'soal' : 'chat';

        if ($remaining === 0) {
            return [
                'type'    => 'error',
                'title'   => 'Token habis!',
                'message' => "Kuota {$label} harian kamu sudah habis. Token akan direset esok hari pukul 00.00 WIB.",
            ];
        }

        if ($remaining <= self::WARNING_THRESHOLD) {
            return [
                'type'    => 'warning',
                'title'   => 'Token hampir habis',
                'message' => "Sisa {$remaining} {$label} lagi hari ini.",
            ];
        }

        return null;
    }

    /**
     * Returns quota info plus current notification state.
     */
    public function info($user, string $type): array
    {
        $limit = $this->getLimit($user->role, $type);
        if ($limit === null) {
            return [
                'limited'      => false,
                'remaining'    => null,
                'limit'        => null,
                'used'         => null,
                'notification' => null,
            ];
        }

        $used      = $this->getUsed($user->id, $type);
        $remaining = max(0, $limit - $used);

        return [
            'limited'      => true,
            'used'         => $used,
            'remaining'    => $remaining,
            'limit'        => $limit,
            'notification' => $this->buildNotification($remaining, $type),
        ];
    }
}
