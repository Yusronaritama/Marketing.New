<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('biodatas', function (Blueprint $table) {
            // // Kolom untuk Tahun Lulus (gunakan integer)
            // $table->year('tahun_lulus')->nullable()->after('jurusan_sekolah');
            
            // // Kolom untuk Asal Daerah (gunakan enum untuk membatasi pilihan)
            // $table->enum('daerah_asal', ['dalam karawang', 'luar karawang'])
            //       ->nullable()
            //       ->after('tahun_lulus');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('biodatas', function (Blueprint $table) {
            // Hapus kolom saat rollback
            // $table->dropColumn('daerah_asal');
            // $table->dropColumn('tahun_lulus');
        });
    }
};