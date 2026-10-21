<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AkunPendaftarMail extends Mailable
{
    use Queueable, SerializesModels;

    public $username;
    public $passwordPlain;

    public function __construct($username, $passwordPlain)
    {
        $this->username = $username;
        $this->passwordPlain = $passwordPlain;
    }

    public function build()
    {
        return $this->subject('Informasi Akun Pendaftaran PMB UBP')
                    ->view('emails.akun_pendaftar');
    }
}