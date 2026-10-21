<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterGelombang extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_gelombang',
        'angka_gelombang',
        'is_active',
        'tanggal_mulai',
        'tanggal_selesai'
    ];
}