<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BerkasProfesi extends Model
{
    use HasFactory;

    // 1. Kasih tau Laravel nama tabel aslinya
    protected $table = 'berkas_profesi';

    // 2. Daftarin semua kolom biar bisa di-insert barengan (Mass Assignment)
    protected $fillable = [
        'user_id',
        'status_berkas',
        'file_akreditasi',
        'file_ijazah',
        'file_transkrip',
        'file_pasfoto',
        'file_ktp',
        'file_kk',
        'file_asuransi',
        'file_surat_sehat',
        'status_verifikasi',
        'catatan_verifikasi',
    ];

    /**
     * 3. Relasi ke tabel users
     * (Setiap 1 berkas profesi, pasti milik 1 User)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}