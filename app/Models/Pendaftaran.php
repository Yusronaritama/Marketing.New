<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pendaftaran extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    // Nanti kita akan isi properti $fillable di sini setelah migrasi berhasil
    protected $fillable = [
        'user_id',
        'jalur_pendaftaran',
        'whatsapp',
        'status_pekerjaan',
        'jurusan_lulusan',
        'jurusan_1',
        'alasan_jurusan_1',
        'jurusan_2',
        'alasan_jurusan_2',
        'domisili_negara',
        'domisili_provinsi',
        'domisili_kota',
        'domisili_kecamatan',
        'domisili_kelurahan',
        'domisili_kodepos',
        'domisili_alamat',
    ];
}