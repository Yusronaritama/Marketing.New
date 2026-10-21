<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PembayaranBerhasilMail extends Mailable
{
    use Queueable, SerializesModels;

    public $nama;
    public $va_number;

    public function __construct($nama, $va_number)
    {
        $this->nama = $nama;
        $this->va_number = $va_number;
    }

    public function build()
    {
        return $this->subject('Pembayaran Pendaftaran PMB UBP Berhasil')
                    ->view('emails.pembayaran_berhasil');
    }
}