<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JalurPendaftaran extends Model
{
    use HasFactory;

    protected $table = 'jalur_pendaftarans';

    protected $fillable = [
        'nama_jalur',
    ];
}