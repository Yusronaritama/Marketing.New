<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nilai extends Model
{
    use HasFactory;

    protected $table = 'nilai';
    
    protected $fillable = [
        'id_pendaftaran',
        'nilai',
        'ket',
        'nama_petugas',
        'attempt_id',
        'attempt_number',
        'time_start',
        'time_finish',
        'attempt_data',
    ];

    protected $casts = [
        'nilai' => 'decimal:2',
        'time_start' => 'datetime',
        'time_finish' => 'datetime',
        'attempt_data' => 'array',
    ];

    public function pendaftaran()
    {
        return $this->belongsTo(MasterEreg::class, 'id_pendaftaran');
    }

    public function user()
    {
        return $this->hasOneThrough(
            User::class,
            MasterEreg::class,
            'id',
            'id',
            'id_pendaftaran',
            'user_id'
        );
    }

    /**
     * Scope untuk nilai yang memiliki attempt
     */
    public function scopeHasAttempt($query)
    {
        return $query->whereNotNull('attempt_id');
    }

    /**
     * Scope untuk nilai berdasarkan rentang nilai
     */
    public function scopeNilaiBetween($query, $min, $max)
    {
        return $query->whereBetween('nilai', [$min, $max]);
    }

    /**
     * Scope untuk nilai terbaru
     */
    public function scopeTerbaru($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Accessor untuk status nilai
     */
    public function getStatusAttribute()
    {
        if ($this->nilai === null) {
            return 'Belum Ujian';
        }

        if ($this->nilai >= 75) {
            return 'Lulus';
        } elseif ($this->nilai >= 50) {
            return 'Cukup';
        } else {
            return 'Tidak Lulus';
        }
    }

    /**
     * Accessor untuk durasi ujian dalam menit
     */
    public function getDurasiUjianAttribute()
    {
        if ($this->time_start && $this->time_finish) {
            return $this->time_start->diffInMinutes($this->time_finish);
        }
        return null;
    }

    /**
     * Method untuk mengecek apakah nilai sudah diinput
     */
    public function sudahDinilai(): bool
    {
        return !is_null($this->nilai);
    }

    /**
     * Method untuk mendapatkan nilai dalam format huruf
     */
    public function getNilaiHuruf(): string
    {
        if ($this->nilai === null) return '-';

        if ($this->nilai >= 85) return 'A';
        if ($this->nilai >= 75) return 'B';
        if ($this->nilai >= 65) return 'C';
        if ($this->nilai >= 50) return 'D';
        return 'E';
    }
}