<?php
// app/Console/Commands/ExpireDuels.php
// Scheduler: cek duel yang expired setiap menit
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Duel;
use App\Services\NotificationService;
use Carbon\Carbon;

class ExpireDuels extends Command
{
    protected $signature   = 'duels:expire';
    protected $description = 'Expire duels yang melewati batas waktu';

    public function __construct(
        private readonly NotificationService $notif
    ) {
        parent::__construct();
    }

    public function handle(): void
    {
        $now = Carbon::now();

        // Expire duel pending yang belum di-accept
        $pendingExpired = Duel::where('status', 'pending')
            ->where('expires_at', '<', $now->toISOString())
            ->get();

        foreach ($pendingExpired as $duel) {
            $duel->update(['status' => 'expired']);
            $this->notif->sendToUser(
                (string) $duel->challenger_id,
                '⏱️ Tantangan Kedaluwarsa',
                'Lawanmu tidak merespons tantangan dalam 5 menit.',
                ['type' => 'duel_expired', 'duel_id' => (string) $duel->id]
            );
            $this->info("Expired pending duel: {$duel->id}");
        }

        // Expire duel active yang salah satu tidak menjawab
        $activeExpired = Duel::where('status', 'active')
            ->where('expires_at', '<', $now->toISOString())
            ->get();

        foreach ($activeExpired as $duel) {
            $duel->update(['status' => 'expired']);
            foreach ([$duel->challenger_id, $duel->opponent_id] as $uid) {
                $this->notif->sendToUser(
                    (string) $uid,
                    '⏱️ Duel Kedaluwarsa',
                    'Salah satu peserta tidak menyelesaikan duel tepat waktu.',
                    ['type' => 'duel_expired', 'duel_id' => (string) $duel->id]
                );
            }
            $this->info("Expired active duel: {$duel->id}");
        }

        $this->info('Selesai. Expired: ' . ($pendingExpired->count() + $activeExpired->count()));
    }
}