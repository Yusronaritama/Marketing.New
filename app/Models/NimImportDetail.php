<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NimImportDetail extends Model
{
    public $timestamps = false; // Hanya pakai created_at (didefinisikan manual di migration)

    protected $fillable = [
        'import_log_id',
        'mahasiswa_id',
        'row_number',
        'nim_baru',
        'nama_excel',
        'nama_db',
        'nim_lama',
        'result_status',
        'pesan',
        'created_at',
    ];

    protected $casts = [
        'row_number'  => 'integer',
        'mahasiswa_id' => 'integer',
        'created_at'  => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Relasi
    // -------------------------------------------------------------------------

    public function importLog(): BelongsTo
    {
        return $this->belongsTo(NimImportLog::class, 'import_log_id');
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(DataMahasiswaFinal::class, 'mahasiswa_id');
    }

    // -------------------------------------------------------------------------
    // Scope
    // -------------------------------------------------------------------------

    public function scopeBerhasil($query)
    {
        return $query->where('result_status', 'berhasil');
    }

    public function scopeDilewati($query)
    {
        return $query->where('result_status', 'dilewati');
    }

    public function scopeTidakDitemukan($query)
    {
        return $query->where('result_status', 'tidak_ditemukan');
    }
}