<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class JadwalUjianFarmasi extends Model
{
    use HasFactory;

    protected $table = 'jadwal_ujian_farmasis';

    protected $fillable = [
        'tanggal_ujian',
        'waktu_ujian',
        'deskripsi',
        'is_active',
    ];

    protected $casts = [
        'tanggal_ujian' => 'date',
        // 'waktu_ujian' => 'datetime', // Time kadang tricky, biarkan string
    ];
    
    // Helper: Format Jam (Contoh: 09:30)
    public function getWaktuFormatAttribute()
    {
        return Carbon::parse($this->waktu_ujian)->format('H:i');
    }
    
    // Helper: Format Tanggal Indo (Contoh: 10 Februari 2026)
    public function getTanggalIndoAttribute()
    {
        return Carbon::parse($this->tanggal_ujian)->translatedFormat('d F Y');
    }
}