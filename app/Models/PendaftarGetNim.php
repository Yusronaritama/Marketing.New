<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PendaftarGetNim extends Model
{
    protected $table = 'pendaftar_get_nim';

    protected $fillable = [
        'data_mahasiswa_final_id',
        'user_id',
        'nim',
        'email_mhs',
        'kode_prodi',
        'jenjang',
        'id_periode_masuk',
        'syarat_type',
        'message_sipt',
        'message_slik',
        'message_elearning',
        'generated_by',
        'generated_at',
    ];

    protected $casts = [
        'generated_at' => 'datetime',
    ];

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(DataMahasiswaFinal::class, 'data_mahasiswa_final_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    /**
     * Ambil 3 digit akhir NIM (nomor urut).
     */
    public function getNomorUrutAttribute(): string
    {
        return substr($this->nim, -3);
    }

    /**
     * Apakah sudah terkirim ke SIPT?
     */
    public function isSiptSent(): bool
    {
        return $this->message_sipt === 'Success';
    }
}