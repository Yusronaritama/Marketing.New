<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AkunPendaftarDefault extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terkait dengan model.
     *
     * @var string
     */
    protected $table = 'akun_pendaftar_default';

    /**
     * Atribut yang dapat diisi (mass assignable).
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'va_number',
        'va_status',
        'password_plain',
    ];

    /**
     * Atribut yang harus dikonversi ke tipe data tertentu.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}