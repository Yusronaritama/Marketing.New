<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ProgramStudi;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JurusanPassingGrade extends Model
{
    use HasFactory;

    protected $table = 'jurusan_passing_grades';

    protected $fillable = [
        'kode_jurusan',
        'nama_jurusan',
        'passing_grade',
        'kuota',
        'status'
    ];

    protected $casts = [
        'passing_grade' => 'decimal:2',
        'kuota' => 'integer'
    ];

    /**
     * Scope untuk jurusan aktif
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope untuk jurusan dengan kuota tersedia
     */
    public function scopeAvailable($query)
    {
        return $query->where('kuota', '>', 0);
    }
    public static function getJurusanMapping(): array
    {
        // 1. Ambil semua data Program Studi
        $jurusanData = ProgramStudi::query() 
                        ->select('kode_prodi', 'nama_prodi')
                        ->get();

        // 2. Map data ke format yang dibutuhkan oleh loop di Controller
        return $jurusanData->mapWithKeys(function ($jurusan) {
            return [
                $jurusan->kode_prodi => [
                    'nama' => $jurusan->nama_prodi,
                ]
            ];
        })
        ->toArray();
    }

    /**
     * Get kode jurusan dari nama jurusan
     */
    public static function getKodeJurusan($namaJurusan)
    {
        $mapping = self::getJurusanMapping();
        
        foreach ($mapping as $kode => $data) {
            if (strtolower($data['nama']) === strtolower($namaJurusan)) {
                return $kode;
            }
        }

        // Jika tidak ditemukan exact match, cari partial match
        foreach ($mapping as $kode => $data) {
            if (strpos(strtolower($namaJurusan), strtolower($data['nama'])) !== false) {
                return $kode;
            }
        }

        return null;
    }

    /**
     * Get passing grade untuk jurusan tertentu
     */
    public static function getPassingGrade($kodeJurusan)
    {
        $mapping = self::getJurusanMapping();
        return $mapping[$kodeJurusan]['passing_grade'] ?? null;
    }

        /**
     * Get nama jurusan dari kode
     */
    public static function getNamaJurusan($kodeJurusan)
    {
        $mapping = self::getJurusanMapping();
        return $mapping[$kodeJurusan]['nama'] ?? 'Unknown';
    }

    /**
     * Check apakah nilai memenuhi passing grade
     */
    public static function isLolos($nilai, $kodeJurusan)
    {
        $passingGrade = self::getPassingGrade($kodeJurusan);
        if ($passingGrade === null) return false;
        
        return $nilai >= $passingGrade;
    }

    /**
     * Get status kelolosan
     */
    public static function getStatusLolos($nilai, $kodeJurusan)
    {
        return self::isLolos($nilai, $kodeJurusan) ? 'LOLOS' : 'TIDAK LOLOS';
    }

    /**
     * Get rekomendasi jurusan berdasarkan nilai
     */
    public static function getRekomendasiJurusan($nilai)
    {
        $mapping = self::getJurusanMapping();
        $rekomendasi = [];

        foreach ($mapping as $kode => $data) {
            if ($nilai >= $data['passing_grade']) {
                $rekomendasi[] = [
                    'kode' => $kode,
                    'nama' => $data['nama'],
                    'passing_grade' => $data['passing_grade'],
                    'selisih' => $nilai - $data['passing_grade']
                ];
            }
        }

        // Urutkan berdasarkan selisih tertinggi (nilai paling jauh di atas passing grade)
        usort($rekomendasi, function($a, $b) {
            return $b['selisih'] <=> $a['selisih'];
        });

        return $rekomendasi;
    }
}