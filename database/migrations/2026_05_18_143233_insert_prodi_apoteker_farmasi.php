<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Tambahkan kolom jika belum ada
        Schema::table('program_studi', function (Blueprint $table) {
            if (!Schema::hasColumn('program_studi', 'pendaftaran_alumni_ubp')) {
                $table->decimal('pendaftaran_alumni_ubp', 15, 2)
                    ->default(0)
                    ->after('beasiswa_kel_ubp');
            }
        });

        // Insert data prodi
        DB::table('program_studi')->insert([
            'kode_prodi' => '00000',
            'nama_prodi' => 'Apoteker Farmasi',
            'jenjang' => 'Profesi',
            'fakultas' => null,
            'passing_grade' => 0,
            'kuota' => 20000,
            'harga_cicilan' => 0,
            'harga_semester' => 50000000,
            'harga_tahun' => 0,
            'biaya_pendaftaran' => 1000000,
            'beasiswa_kel_ubp' => 0,
            'pendaftaran_alumni_ubp' => 750000,
            'daya_tampung' => 20000,
            'status_prodi' => 1,
            'urutan_tampil' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('program_studi')
            ->where('kode_prodi', '00000')
            ->delete();

        Schema::table('program_studi', function (Blueprint $table) {
            if (Schema::hasColumn('program_studi', 'pendaftaran_alumni_ubp')) {
                $table->dropColumn('pendaftaran_alumni_ubp');
            }
        });
    }
};