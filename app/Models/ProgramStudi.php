<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use App\Models\MasterEreg;

class ProgramStudi extends Model
{
    use HasFactory;

    protected $table = 'program_studi';
    protected $primaryKey = 'id'; 

    protected $fillable = [
        'kode_prodi',
        'nama_prodi',
        'jenjang',
        'fakultas',
        'passing_grade',
        'kuota',          
        'daya_tampung',   
        'harga_cicilan',
        'harga_semester',
        'harga_tahun',
        'beasiswa_kel_ubp', 
        'deskripsi',
        'akreditasi',
        'tahun_berdiri',
        'status_prodi',
        'urutan_tampil',
        'jumlah_pendaftar'
    ];

    protected $casts = [
        'passing_grade'    => 'float', 
        'kuota'            => 'integer',
        'daya_tampung'     => 'integer',
        'harga_cicilan'    => 'double',
        'harga_semester'   => 'double',
        'harga_tahun'      => 'double',
        'beasiswa_kel_ubp' => 'double', 
        'tahun_berdiri'    => 'integer',
        'status_prodi'     => 'boolean',
        'urutan_tampil'    => 'integer',
        'created_at'       => 'datetime',
        'updated_at'       => 'datetime',
    ];

    /**
     * Scope untuk mengambil data aktif
     */
     
    public function pendaftar()
    {
        // Parameter 2 ('pilihan_prodi_1'): Nama kolom di tabel 'mahasiswas' yang menyimpan ID jurusan.
        // Parameter 3 ('id'): Nama kolom primary key di tabel 'program_studi'.
        
        // CATATAN: Jika di tabel mahasiswa nama kolomnya 'id_prodi' atau 'jurusan_id', 
        // ganti 'pilihan_prodi_1' di bawah ini dengan nama yang benar.
        
        return $this->hasMany(MasterEreg::class, 'jurusan1', 'kode_prodi');
    }
    public function scopeAktif($query)
    {
        // Jika casting boolean aktif, gunakan ini
        return $query->where('status_prodi', true);
    }

    /**
     * Scope untuk urutan tampil - TAMBAHKAN INI
     */
    public function scopeUrutTampil($query)
    {
        return $query->orderBy('urutan_tampil', 'asc');
    }

    /**
     * Scope untuk urutan (alias untuk urutTampil)
     */
    public function scopeUrutan($query) 
    { 
        return $query->orderBy('urutan_tampil', 'asc')->orderBy('nama_prodi', 'asc'); 
    }

    public function scopeTersedia($query)
    {
        return $query->where(function($q) {
            $q->whereNull('kuota')
              ->orWhere('kuota', 0)
              ->orWhereColumn('daya_tampung', '<', 'kuota');
        });
    }

    public function scopeJenjang($query, $jenjang) 
    { 
        return $query->where('jenjang', $jenjang); 
    }

    public function scopePopuler($query)
    {
        return $query->orderBy('daya_tampung', 'desc');
    }
    
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('kode_prodi', 'like', "%{$search}%")
              ->orWhere('nama_prodi', 'like', "%{$search}%")
              ->orWhere('fakultas', 'like', "%{$search}%");
        });
    }

    // Accessors
    public function getJumlahTerisiAttribute()
    {
        return (int) $this->daya_tampung;
    }

    public function getIsPenuhAttribute(): bool
    {
        if (empty($this->kuota)) {
            return false; 
        }
        return $this->daya_tampung >= $this->kuota;
    }

    public function getSisaKuotaAttribute()
    {
        if (empty($this->kuota)) {
            return 'Unlimited'; 
        }
        
        $sisa = $this->kuota - $this->daya_tampung;
        return max(0, $sisa);
    }

    public function getPersentaseKuotaAttribute()
    {
        if (empty($this->kuota)) {
            return 0;
        }
        $persen = ($this->daya_tampung / $this->kuota) * 100;
        return round($persen, 1);
    }

    public function getStatusPendaftaranAttribute()
    {
        $isActive = $this->status_prodi == 1 || $this->status_prodi === true;
        
        if (!$isActive) return 'NON-AKTIF';
        if ($this->is_penuh) return 'TUTUP (PENUH)';
        return 'BUKA';
    }

    public function getHargaCicilanLabelAttribute()
    {
        return 'Rp ' . number_format($this->harga_cicilan, 0, ',', '.');
    }

    public function getHargaSemesterLabelAttribute()
    {
        return 'Rp ' . number_format($this->harga_semester, 0, ',', '.');
    }

    public function getHargaTahunLabelAttribute()
    {
        return 'Rp ' . number_format($this->harga_tahun, 0, ',', '.');
    }

    public function getBeasiswaKelUbpLabelAttribute()
    {
        return 'Rp ' . number_format($this->beasiswa_kel_ubp, 0, ',', '.');
    }
    
    public function getNamaLengkapAttribute() 
    { 
        return "{$this->jenjang} - {$this->nama_prodi}"; 
    }

    // Relations
    public function pendaftaranJurusan1() 
    { 
        return $this->hasMany(MasterEreg::class, 'jurusan1', 'kode_prodi'); 
    }

    public function pendaftaranJurusan2() 
    { 
        return $this->hasMany(MasterEreg::class, 'jurusan2', 'kode_prodi'); 
    }

    public function kelulusan() 
    { 
        return $this->hasMany(Kelulusan::class, 'prodi', 'kode_prodi'); 
    }

    public function slikBillings() 
    { 
        return $this->hasMany(SlikBilling::class, 'kode_prodi', 'kode_prodi'); 
    }

    // Utilities
    public function syncDayaTampung()
    {
        // KITA HITUNG BERDASARKAN JURUSAN FINAL TEMPAT DIA LULUS!
        $jumlahReal = \App\Models\MasterEreg::where('jurusan_lulusan', $this->kode_prodi)
                        ->where('is_quota_taken', 1)
                        ->count();

        $kapasitas = (int) $this->daya_tampung; 
        $sisa = $kapasitas - $jumlahReal; 

        $this->update([
            'jumlah_pendaftar' => $jumlahReal,       
            'kuota'            => ($sisa < 0) ? 0 : $sisa 
        ]);

        return $this->kuota;
    }

    public static function cekKetersediaanKuota($kodeProdi): bool
    {
        $prodi = self::where('kode_prodi', $kodeProdi)->first();

        if (!$prodi) return false;
        if (!$prodi->status_prodi) return false;
        
        return !$prodi->is_penuh;
    }

    public static function getNamaProdiByKode($kodeProdi) 
    {
        $prodi = self::select('nama_prodi')->where('kode_prodi', $kodeProdi)->first();
        return $prodi->nama_prodi ?? 'Jurusan Tidak Ditemukan';
    }

    public static function getListForDropdown()
    {
        
        return self::where('status_prodi', 1)
            ->where('kuota', '>', 0) 
            ->orderBy('urutan_tampil', 'asc')
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->kode_prodi,
                    'text' => "{$item->jenjang} - {$item->nama_prodi} (Sisa: {$item->kuota})"
                ];
            });
    }
 public static function getSingkatan($kodeProdi): string
    {
        return match ((string)$kodeProdi) {
            '21201' => 'tm',   // Teknik Mesin
            '26201' => 'ti',   // Teknik Industri (Sebelumnya salah tulis 24201)
            '48201' => 'fm',   // Farmasi
            '55201' => 'if',   // Teknik Informatika
            '57201' => 'si',   // Sistem Informasi
            '61201' => 'mn',   // Manajemen (S1)
            '62201' => 'ak',   // Akuntansi
            '73201' => 'ps',   // Psikologi
            '74201' => 'hk',   // Ilmu Hukum
            '86206' => 'sd',   // Pendidikan Guru Sekolah Dasar (PGSD)
            '86230' => 'pi',   // Pendidikan Agama Islam (PAI)
            '87205' => 'pk',   // Pendidikan Pancasila dan Kewarganegaraan (PPKn)
            '61114' => 'mm',   // Manajemen S2
            '48901' => 'ap',   // Apoteker Farmasi
            '00000' => 'ap',   // Apoteker Farmasi (Kode alternatif)
            default => 'mhs',
        };
    }

}
