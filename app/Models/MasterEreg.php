<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class MasterEreg extends Model
{
    use HasFactory;

    protected $table = 'master_ereg';

    // =========================================================================
    // 1. CONFIGURATION (FILLABLE & CASTS)
    // =========================================================================

    // Sesuai dengan kolom di Database Anda
    protected $fillable = [
        'user_id',
        'nama',
        'phone',
        'gmail',
        'jalur_pendaftaran',
        'status_pekerjaan',
        'jurusan_lulusan',
        'kode_promo',
        'jurusan1',
        'jurusan2',
        'alasan_jurusan_1',
        'alasan_jurusan_2',
        
        // File Uploads
        'file_kk',
        'file_ijazah',
        'file_skl',
        'file_rapor',
        'file_ktp',
        'file_surat_keterangan_sehat',
        'file_ijazah_s1',
        'file_transkrip_s1',
        'file_surat_pindah',
        'file_ktm',
        'file_surat_akademik_pindahan',
        'file_surat_alih_jenjang',
        'file_surat_akademik_ekstensi',
        'file_surat_tugas_kerja',
        'file_transkrip_nilai_rpl',
        'file_surat_akademik_rpl',
        'file_surat_buta_warna',
        
        // Data Tambahan
        'ukuran_almamater',
        
        // Status & System
        'step_completed',
        'status',
        'scan_results',
        'scan_status',
        'is_quota_taken',
        // created_at & updated_at dihandle otomatis oleh Eloquent
    ];

    protected $casts = [
        'step_completed' => 'integer',
        'scan_results'   => 'array', // Agar tersimpan sebagai JSON
        'created_at'     => 'datetime',
        'updated_at'     => 'datetime',
        'is_quota_taken' => 'boolean',
    ];

    // =========================================================================
    // 2. RELATIONSHIPS (LOGIC RELASI)
    // =========================================================================

    public function applicantUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function biodata(): HasOne
    {
        return $this->hasOne(Biodata::class, 'user_id', 'user_id');
    }

    public function masterUser(): BelongsTo
    {
        return $this->belongsTo(MasterUser::class, 'user_id', 'user_id');
    }

    // Relasi ke Program Studi (Foreign Key: Kode Prodi)
    public function programStudi1(): BelongsTo
    {
        return $this->belongsTo(ProgramStudi::class, 'jurusan1', 'kode_prodi')
            ->withDefault(['nama_prodi' => 'Program Studi Tidak Ditemukan', 'passing_grade' => 0, 'kuota' => 0, 'status_prodi' => 0]);
    }

    public function programStudi2(): BelongsTo
    {
        return $this->belongsTo(ProgramStudi::class, 'jurusan2', 'kode_prodi')
            ->withDefault(['nama_prodi' => 'Program Studi Tidak Ditemukan', 'passing_grade' => 0, 'kuota' => 0, 'status_prodi' => 0]);
    }

    // =========================================================================
    // 3. ACCESSORS (LOGIC DATA TAMBAHAN)
    // =========================================================================
public function prodi()
{
    // 'jurusan1' adalah foreign key di tabel master_ereg
    // 'kode_prodi' adalah owner key di tabel program_studi
    return $this->belongsTo(ProgramStudi::class, 'jurusan1', 'kode_prodi');
}
    public function getJurusan1NamaAttribute()
    {
        return $this->programStudi1->nama_prodi ?? $this->jurusan1;
    }

    public function getJurusan2NamaAttribute()
    {
        return $this->programStudi2->nama_prodi ?? $this->jurusan2;
    }

    // Logic untuk mengambil status ujian dari tabel relasi lain
    public function getStatusUjianAttribute(): string
    {
        $user = $this->applicantUser()->first();
        $masterUser = optional($user)->masterUser()->first();
        
        if ($masterUser) {
            // Pastikan model MasterMoodleUser ada di namespace yang benar
            $relasiStatus = $masterUser->hasOne(\App\Models\MasterMoodleUser::class, 'master_user_id', 'id')->first();
            return optional($relasiStatus)->status_kelulusan ?? 'pending';
        }
        return 'pending'; 
    }

    // =========================================================================
    // 4. STATIC HELPERS (LOGIC BISNIS)
    // =========================================================================

    public static function getJalurPendaftaranList()
    {
        return [
            'Jalur Reguler' => 'Jalur Reguler',
            'Jalur Ekstensi' => 'Jalur Ekstensi',
            'Pindahan' => 'Pindahan',
            'Jalur S2 Manajemen' => 'Jalur S2 Manajemen',
            'Jalur RPL' => 'Jalur RPL (Rekognisi Pembelajaran Lampau)',
        ];
    }

    protected static function isS2Jurusan(string $kodeProdi): bool
    {
        $program = ProgramStudi::select('jenjang')->where('kode_prodi', $kodeProdi)->first();
        return $program && Str::startsWith(strtoupper($program->jenjang), 'S2');
    }

    public static function validateJurusanForJalur($jalur, $jurusan1Kode, $jurusan2Kode = null)
    {
        $jurusan1 = ProgramStudi::withoutGlobalScopes()->where('kode_prodi', $jurusan1Kode)->first();
        // $jurusan2 opsional
        $jurusan2 = $jurusan2Kode ? ProgramStudi::withoutGlobalScopes()->where('kode_prodi', $jurusan2Kode)->first() : null;

        if (!$jurusan1) return false;
        
        $isJurusan1S2 = self::isS2Jurusan($jurusan1Kode);
        $isJurusan2S2 = $jurusan2Kode ? self::isS2Jurusan($jurusan2Kode) : false;

        // Logic validasi S2 vs S1
        if ($jalur === 'Jalur S2 Manajemen') {
            if (!$isJurusan1S2) return false;
            // Jika ambil S2, jurusan kedua (jika ada) harus S2 juga atau logic lain sesuai kampus
            if ($jurusan2 && !$isJurusan2S2) return false;
        } else {
            // Jalur selain S2 tidak boleh ambil jurusan S2
            if ($isJurusan1S2) return false;
            if ($jurusan2 && $isJurusan2S2) return false;
        }
        
        return true;
    }
}