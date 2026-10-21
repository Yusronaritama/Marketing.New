<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataMahasiswaFinal extends Model
{
    use HasFactory;

    /**
     * Nama tabel di database.
     */
    protected $table = 'data_mahasiswa_finals';

    /**
     * Daftar kolom yang bisa diisi (Mass Assignment).
     * Sesuai dengan standar Feeder PDDIKTI.
     */
    protected $fillable = [
        'user_id',
        
        // --- DATA PRIBADI ---
        'nama_mahasiswa', 
        'jenis_kelamin', 
        'jalan', 
        'rt', 
        'rw', 
        'dusun', 
        'kelurahan', 
        'kode_pos',
        'nisn', 
        'nik', 
        'tempat_lahir', 
        'tanggal_lahir',
        'telepon', 
        'handphone', 
        'email',
        'kewarganegaraan',
        'id_agama', 
        'id_alat_transportasi',
        'penerima_kps', 
        'no_kps', 
        'npwp',
        'id_kebutuhan_khusus_mahasiswa',
        'id_provinsi',
        'id_kabupaten',
        'id_kecamatan',
        'id_jenis_tinggal',

        // --- DATA AYAH ---
        'nama_ayah', 
        'tanggal_lahir_ayah', 
        'nik_ayah', 
        'id_jenjang_pendidikan_ayah', 
        'id_pekerjaan_ayah', 
        'id_penghasilan_ayah', 
        'id_kebutuhan_khusus_ayah',

        // --- DATA IBU ---
        'nama_ibu_kandung', 
        'tanggal_lahir_ibu', 
        'nik_ibu', 
        'id_jenjang_pendidikan_ibu', 
        'id_pekerjaan_ibu', 
        'id_penghasilan_ibu', 
        'id_kebutuhan_khusus_ibu',

        // --- DATA WALI ---
        'nama_wali', 
        'tanggal_lahir_wali', 
        'id_jenjang_pendidikan_wali', 
        'id_pekerjaan_wali', 
        'id_penghasilan_wali',
        'status_data',
        'status_reset',
    ];

    /**
     * Casting tipe data otomatis.
     * Mengubah string tanggal dari database menjadi objek Carbon (Date).
     */
    protected $casts = [
        'tanggal_lahir' => 'date',
        'tanggal_lahir_ayah' => 'date',
        'tanggal_lahir_ibu' => 'date',
        'tanggal_lahir_wali' => 'date',
        'penerima_kps' => 'boolean', // Otomatis jadi true/false (1/0)
    ];

    /**
     * Relasi ke User (Pemilik Data).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    /**
     * Relasi ke MasterUser melalui user_id
     */
    public function masterUser(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        // Kita menghubungkan user_id di DataMahasiswaFinal ke user_id di MasterUser
        return $this->hasOne(MasterUser::class, 'user_id', 'user_id');
    }
    /**
     * Relasi ke MasterEreg (Data Pendaftaran)
     */
    public function masterEreg()
    {
        // Sesuaikan 'user_id' jika foreign key di tabel master_eregs berbeda
        return $this->hasOne(MasterEreg::class, 'user_id', 'user_id');
    }
        
    public function nimImportDetail()
    {
        return $this->hasMany(\App\Models\NimImportDetail::class, 'mahasiswa_id');
    }
    
    public function latestNim(): string
    {
        return $this->nimImportDetail()
            ->where('result_status', 'berhasil')
            ->latest('created_at')
            ->value('nim_baru') ?? '-';
    }
    public function pendaftarGetNim(): HasOne
    {
        return $this->hasOne(PendaftarGetNim::class, 'data_mahasiswa_final_id');
    }
    public function programStudi()
    {
        // Jika di tabel data_mahasiswa_finals ada kolom kode_prodi atau sejenisnya
        // return $this->belongsTo(ProgramStudi::class, 'kode_prodi', 'kode_prodi');
        
        // ATAU jika ProgramStudi terikat ke MasterEreg, Anda bisa menggunakan HasOneThrough 
        // atau biarkan Controller mengambilnya lewat MasterEreg.
        
        // Untuk solusi cepat agar tidak error (asumsi ada kolom jurusan/prodi):
        return $this->belongsTo(ProgramStudi::class, 'prodi_id'); 
    }
}