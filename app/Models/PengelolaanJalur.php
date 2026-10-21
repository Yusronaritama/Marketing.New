<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengelolaanJalur extends Model
{
    use HasFactory;

    // Menghubungkan Model dengan nama tabel di database secara eksplisit
    protected $table = 'pengelolaan_jalur';

    // Kolom-kolom yang diizinkan untuk diisi secara massal (Mass Assignment)
    protected $fillable = [
        'user_id',
        'nama_lengkap',
        'jalur_pendaftaran',
        'status_verifikasi',
        'teks_informasi',
    ];

    // Mengubah tipe data saat ditarik dari database
    protected $casts = [
        'status_verifikasi' => 'boolean', // Karena pakai tinyint(1), kita cast jadi true/false
    ];

    /**
     * Relasi ke tabel users
     * Setiap pengelolaan jalur dimiliki oleh satu user
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}