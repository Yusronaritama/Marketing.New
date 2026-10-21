<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Jalankan migrasi: Update program_studi dengan data dari jurusan_passing_grades.
     */
    public function up(): void
    {
        // Peringatan: Gunakan query ini HANYA jika Anda yakin kode_prodi di program_studi
        // cocok dengan kode_jurusan di jurusan_passing_grades.
        
        DB::statement("
            UPDATE program_studi ps
            JOIN jurusan_passing_grades jpg ON ps.kode_prodi = jpg.kode_jurusan
            SET 
                ps.passing_grade = jpg.passing_grade,
                ps.kuota = jpg.kuota,
                /* Mengkonversi string 'active'/'inactive' ke boolean 1/0 (is_aktif) */
                ps.is_aktif = CASE 
                    WHEN jpg.status = 'active' THEN 1 
                    ELSE 0 
                END
        ");
        
        // Catatan: Tabel jurusan_passing_grades TIDAK dihapus.
    }

    /**
     * Balikkan migrasi.
     * Karena kita hanya melakukan UPDATE data, rollback akan dibiarkan kosong
     * karena mengembalikan nilai lama sangat kompleks.
     */
    public function down(): void
    {
        // Tidak ada tindakan (No-op)
    }
};