<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RegistrationSuccessMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $masterUser;
    public $plainPassword;

    public function __construct($user, $masterUser, $plainPassword)
    {
        $this->user = $user;
        $this->masterUser = $masterUser;
        $this->plainPassword = $plainPassword;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pendaftaran Berhasil - Informasi Login Mahasiswa Baru',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.registration_success', // Kita akan buat view ini di langkah 2
        );
    }
}