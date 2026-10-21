<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PmbTahunLalu extends Model
{
    use HasFactory;

    // Arahkan ke nama tabel yang benar
    protected $table = 'pmb_tahun_lalu';

    // Daftarkan kolom yang diizinkan untuk diisi
    protected $fillable = [
        'id_pendaftaran',
        'hitung',
        'no_ujian',
        'input',
        'nama_user',
        'tanggal_reg',
        'gelombang',
        'tahun_pmb',
        'jalur_pendaftaran',
        'prodi_pilihan',
        'status_pembayaran'
    ];
}