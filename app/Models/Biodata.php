<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\DataMahasiswaFinal;

class Biodata extends Model
{
    use HasFactory;

    protected $table = 'biodatas';

    protected $fillable = [
        'user_id',
        'nik',
        'nama_lengkap',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'nama_ibu',
        'nama_ayah',
        'alamat',
        'jenjang_pendidikan',
        'asal_sekolah',
        'jurusan_sekolah',
        'agama',
        'tempat_tinggal',
        'is_verified',
        'tahun_lulus',
        'daerah_asal',
        'provinsi_asal',
        'kota_kabupaten_asal',
        'status_bio'
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'is_verified' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function dataFinal()
    {
        return $this->hasOne(DataMahasiswaFinal::class, 'user_id', 'user_id');
    }

    // --- ACCESSOR/SCOPES (untuk referensi, tidak mengubah fungsionalitas utama) ---
    
    public function scopeCompleted($query)
    {
        return $query->where('status_bio', 'completed');
    }

    public function scopeDraft($query)
    {
        return $query->where('status_bio', 'draft');
    }

    public function getIsCompletedAttribute()
    {
        return $this->status_bio === 'completed';
    }

    public function getIsDraftAttribute()
    {
        return $this->status_bio === 'draft';
    }
}