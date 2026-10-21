<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbsensiPmb extends Model
{
    use HasFactory;

    protected $table = 'absensi_pmb';

    protected $fillable = [
        'id_pendaftar',
        'tanggal',
        'jam_masuk',
        'email_masuk',
        'jam_pulang',
        'email_pulang',
    ];
}
