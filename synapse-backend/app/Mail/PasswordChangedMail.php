<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordChangedMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $name;
    public string $changedAt;

    public function __construct(string $name)
    {
        $this->name      = $name;
        $this->changedAt = now()->timezone('Asia/Jakarta')->format('d M Y, H:i') . ' WIB';
    }

    public function build()
    {
        return $this->subject('Password SYNAPSE Kamu Berhasil Diubah')
                    ->view('emails.password_changed');
    }
}
