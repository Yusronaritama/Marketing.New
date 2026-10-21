<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Jalankan migrasi: Tambah kolom harga dan transfer data dari jurusan_passing_grades.
     */
    public function up(): void
    {
        // --- 1. RENAME KOLOM STATUS (is_aktif -> status_prodi) ---
        Schema::table('program_studi', function (Blueprint $table) {
            // Periksa apakah is_aktif ada sebelum rename
            if (Schema::hasColumn('program_studi', 'is_aktif')) {
                // Rename is_aktif menjadi status_prodi
                $table->renameColumn('is_aktif', 'status_prodi');
            } else {
                // Jika is_aktif tidak ada (tapi dibutuhkan), tambahkan status_prodi sebagai boolean
                if (!Schema::hasColumn('program_studi', 'status_prodi')) {
                    $table->boolean('status_prodi')->default(true);
                }
            }
        });


        // --- 2. TAMBAH KOLOM HARGA KE PROGRAM_STUDI ---
        Schema::table('program_studi', function (Blueprint $table) {
            // Pastikan kolom belum ada untuk menghindari error 1060 Duplicate column
            if (!Schema::hasColumn('program_studi', 'harga_cicilan')) {
                $table->decimal('harga_cicilan', 10, 2)->nullable()->after('kuota');
                $table->decimal('harga_semester', 10, 2)->nullable()->after('harga_cicilan');
                $table->decimal('harga_tahun', 10, 2)->nullable()->after('harga_semester');
            }
        });

        // --- 3. MEMASTIKAN DATA POKOK ADA (INSERT IGNORE) ---
        // PENTING: ID Dihapus dari SELECT agar Auto Increment (AI) bekerja
        // dan menghindari konflik PRIMARY KEY pada tabel program_studi.
        DB::statement("
            INSERT IGNORE INTO program_studi (kode_prodi, nama_prodi, passing_grade, kuota, status_prodi, created_at, updated_at)
            SELECT 
                jpg.kode_jurusan,
                jpg.nama_jurusan,
                jpg.passing_grade,
                jpg.kuota,
                CASE WHEN jpg.status = 'active' THEN 1 ELSE 0 END AS status_prodi_value,
                NOW(),
                NOW()
            FROM jurusan_passing_grades jpg
            ON DUPLICATE KEY UPDATE 
                nama_prodi = jpg.nama_jurusan, 
                passing_grade = jpg.passing_grade, 
                kuota = jpg.kuota, 
                status_prodi = CASE WHEN jpg.status = 'active' THEN 1 ELSE 0 END;
        ");


        // --- 4. KONSOLIDASI DATA (TRANSFER SEMUA KOLOM TERMASUK HARGA) ---
        // Pastikan kolom ps.kode_prodi adalah UNIQUE KEY di program_studi agar JOIN berjalan cepat.
        DB::statement("
            UPDATE program_studi ps
            JOIN jurusan_passing_grades jpg ON ps.kode_prodi = jpg.kode_jurusan
            SET 
                ps.passing_grade = jpg.passing_grade,
                ps.kuota = jpg.kuota,
                ps.harga_cicilan = jpg.harga_cicilan,
                ps.harga_semester = jpg.harga_semester,
                ps.harga_tahun = jpg.harga_tahun,
                /* Mengkonversi status ke status_prodi */
                ps.status_prodi = CASE 
                    WHEN jpg.status = 'active' THEN 1 
                    ELSE 0 
                END
        ");
    }

    /**
     * Balikkan migrasi.
     */
    public function down(): void
    {
        // 1. Hapus kolom harga yang baru ditambahkan
        Schema::table('program_studi', function (Blueprint $table) {
            $table->dropColumn(['harga_cicilan', 'harga_semester', 'harga_tahun']);
        });

        // 2. Rollback rename kolom (status_prodi -> is_aktif)
        Schema::table('program_studi', function (Blueprint $table) {
            if (Schema::hasColumn('program_studi', 'status_prodi')) {
                $table->renameColumn('status_prodi', 'is_aktif');
            }
        });
    }
};