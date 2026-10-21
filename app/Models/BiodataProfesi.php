<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BiodataProfesi extends Model
{
    use HasFactory;

    protected $table = 'biodata_profesi';
    
    protected $fillable = [
        'user_id', 'nama', 'tempat_lahir', 'tanggal_lahir', 
        'asal_s1', 'tahun_lulus', 'alamat', 'no_wa', 'email_aktif', 'nim', 'status',
        'ukuran_almamater'
    ];

    // [BARU] Relasi balik ke tabel users (Satu biodata dimiliki oleh satu user)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}