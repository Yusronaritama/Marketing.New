<?php
namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PembayaranBerhasil extends Mailable
{
    use SerializesModels;
    public $nama; // Kita bisa kirim nama user

    public function __construct($nama) {
        $this->nama = $nama;
    }

    public function build() {
        return $this->subject('Konfirmasi Pembayaran Berhasil')
                    ->view('emails.pembayaran_sukses');
    }
}