<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FarmasiVerification extends Model
{
    use HasFactory;

    protected $table = 'farmasi_verifications';

    protected $fillable = [
        'user_id',
        'master_ereg_id',
        'nama_lengkap',
        'email',
        'nomer_hp',
        'phone',
        'prodi_pilihan1',
        'prodi_pilihan2',
        'status_verifikasi',
        'verified_by',
        'nama_verifikator',
        'tanggal_verifikasi',
        'keterangan'
    ];

    protected $casts = [
        'tanggal_verifikasi' => 'datetime',
        'master_ereg_id'     => 'integer',
        'user_id'            => 'integer',
        'verified_by'        => 'integer',
    ];

    // Validasi nilai status_verifikasi
    public function setStatusVerifikasiAttribute($value)
    {
        // Samakan dengan nilai yang dikirim dari form/controller
        $validStatuses = ['pending', 'rejected', 'verified'];

        if (!in_array($value, $validStatuses)) {
            throw new \InvalidArgumentException("Status verifikasi tidak valid: {$value}");
        }

        $this->attributes['status_verifikasi'] = $value;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function masterEreg()
    {
        return $this->belongsTo(MasterEreg::class, 'master_ereg_id');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
    
    /**
     * "CCTV" otomatis untuk memantau perubahan status verifikasi Farmasi
     * Bakal otomatis sync kuota dan jurusan lulusan kalau statusnya diubah
     */
    protected static function booted()
    {
        static::saved(function ($verif) {
            // Cek apakah kolom 'status_verifikasi' beneran ngalamin perubahan
            if ($verif->isDirty('status_verifikasi')) {
                
                $ereg = \App\Models\MasterEreg::where('user_id', $verif->user_id)->first();
                
                if ($ereg) {
                    $status = strtolower($verif->status_verifikasi);

                    // JIKA TIDAK LOLOS TAHAP 2 (Buta Warna / Gagal / Ditolak)
                    if (in_array($status, ['rejected', 'ditolak', 'tidak lulus', 'gagal'])) {
                        
                        // Cari pelarian ke pilihan 2
                        $prodiTujuan = $verif->prodi_pilihan2 ?? $ereg->jurusan2;
                        
                        if ($prodiTujuan) {
                            // Pindahkan jurusan finalnya ke pilihan 2
                            $ereg->update(['jurusan_lulusan' => $prodiTujuan]);
                            
                            // Hitung ulang kuota Farmasi (biar kursi balik) & Pilihan 2 (biar kursi terpotong)
                            \App\Models\ProgramStudi::where('kode_prodi', $ereg->jurusan1)->first()?->syncDayaTampung();
                            \App\Models\ProgramStudi::where('kode_prodi', $prodiTujuan)->first()?->syncDayaTampung();
                        }

                    } 
                    // JIKA LOLOS TAHAP 2 (Sehat / Diverifikasi / Lulus)
                    else if (in_array($status, ['verified', 'diverifikasi', 'lulus'])) {
                        
                        // Kunci status lulusannya murni di Farmasi
                        $ereg->update(['jurusan_lulusan' => $ereg->jurusan1]);
                        
                        // Hitung ulang kuota Farmasi (karena sekarang dia beneran ngambil kursi)
                        \App\Models\ProgramStudi::where('kode_prodi', $ereg->jurusan1)->first()?->syncDayaTampung();
                    }
                }
            }
        });
    }
}