<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class QuotaHabisMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $name;
    public string $role;
    public string $type;
    public int    $limit;
    public string $featureLabel;
    public string $resetInfo;

    public function __construct(string $name, string $role, string $type, int $limit)
    {
        $this->name  = $name;
        $this->role  = $role;
        $this->type  = $type;
        $this->limit = $limit;

        $this->featureLabel = match($type) {
            'generate_questions' => 'Generate Soal AI',
            default              => 'Chat dengan SYNAPSE AI',
        };

        $this->resetInfo = 'Token harianmu akan direset otomatis besok pukul 00.00 WIB.';
    }

    public function build()
    {
        return $this->subject("Token {$this->featureLabel} Kamu Sudah Habis")
                    ->view('emails.quota_habis');
    }
}
