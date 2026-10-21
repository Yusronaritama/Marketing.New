<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kelulusan extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang digunakan oleh model.
     *
     * @var string
     */
    protected $table = 'kelulusan';

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_pendaftaran',
        'prodi',
        'status_lulus',
        'nama_petugas',
        'ket',
        'keterangan',
        'nilai_akhir',
        'ranking',
        'tanggal_pengumuman',
    ];

    /**
     * Atribut yang harus di-cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status_lulus' => 'string',
        'nilai_akhir' => 'decimal:2',
        'tanggal_pengumuman' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relasi ke model MasterEreg (Pendaftaran)
     */
    public function pendaftaran()
    {
        return $this->belongsTo(MasterEreg::class, 'id_pendaftaran');
    }

    /**
     * Relasi ke model User melalui MasterEreg
     */
    public function user()
    {
        return $this->hasOneThrough(
            User::class,
            MasterEreg::class,
            'id', // Foreign key pada MasterEreg
            'id', // Foreign key pada User
            'id_pendaftaran', // Local key pada Kelulusan
            'user_id' // Local key pada MasterEreg
        );
    }

    /**
     * Relasi ke model ProgramStudi (jika ada)
     */
    public function programStudi()
    {
        // Sesuaikan dengan model ProgramStudi Anda
        return $this->belongsTo(ProgramStudi::class, 'prodi', 'kode_prodi');
    }

    /**
     * Scope untuk peserta yang lulus
     */
    public function scopeLulus($query)
    {
        return $query->where('status_lulus', '1');
    }

    /**
     * Scope untuk peserta yang tidak lulus
     */
    public function scopeTidakLulus($query)
    {
        return $query->where('status_lulus', '2');
    }

    /**
     * Scope untuk prodi tertentu
     */
    public function scopeProdi($query, $prodi)
    {
        return $query->where('prodi', $prodi);
    }

    /**
     * Scope untuk ranking tertinggi
     */
    public function scopeRankingTertinggi($query, $limit = 10)
    {
        return $query->whereNotNull('ranking')
                    ->orderBy('ranking')
                    ->limit($limit);
    }

    /**
     * Accessor untuk status kelulusan lengkap
     */
    public function getStatusLengkapAttribute()
    {
        return $this->status_lulus == '1' ? 'LULUS' : 'TIDAK LULUS';
    }

    /**
     * Accessor untuk warna status
     */
    public function getWarnaStatusAttribute()
    {
        return $this->status_lulus == '1' ? 'success' : 'danger';
    }

    /**
     * Accessor untuk ikon status
     */
    public function getIkonStatusAttribute()
    {
        return $this->status_lulus == '1' ? 'check-circle' : 'x-circle';
    }

    /**
     * Method untuk mengecek apakah peserta lulus
     */
    public function isLulus(): bool
    {
        return $this->status_lulus == '1';
    }

    /**
     * Method untuk menandai sebagai lulus
     */
    public function tandaiLulus($keterangan = null): bool
    {
        return $this->update([
            'status_lulus' => '1',
            'keterangan' => $keterangan ?? $this->keterangan,
            'tanggal_pengumuman' => $this->tanggal_pengumuman ?? now(),
        ]);
    }

    /**
     * Method untuk menandai sebagai tidak lulus
     */
    public function tandaiTidakLulus($keterangan = null): bool
    {
        return $this->update([
            'status_lulus' => '2',
            'keterangan' => $keterangan ?? $this->keterangan,
            'tanggal_pengumuman' => $this->tanggal_pengumuman ?? now(),
        ]);
    }

    /**
     * Method untuk mendapatkan data statistik kelulusan per prodi
     */
    public static function statistikPerProdi()
    {
        return self::selectRaw('prodi, 
                               COUNT(*) as total, 
                               SUM(CASE WHEN status_lulus = "1" THEN 1 ELSE 0 END) as lulus,
                               SUM(CASE WHEN status_lulus = "2" THEN 1 ELSE 0 END) as tidak_lulus')
                  ->groupBy('prodi')
                  ->get()
                  ->mapWithKeys(function ($item) {
                      return [
                          $item->prodi => [
                              'total' => $item->total,
                              'lulus' => $item->lulus,
                              'tidak_lulus' => $item->tidak_lulus,
                              'persentase_lulus' => $item->total > 0 ? round(($item->lulus / $item->total) * 100, 2) : 0
                          ]
                      ];
                  });
    }
}