<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('master_ereg', function (Blueprint $table) {
        // // Kolom Wajib Baru
        // $table->string('file_ktp')->nullable()->after('file_ijazah');

        // // Kolom Jalur S2 (sudah ada di validasi JS Anda)
        // $table->string('file_ijazah_s1')->nullable()->after('file_ktp');
        // $table->string('file_transkrip_s1')->nullable()->after('file_ijazah_s1');

        // // Kolom Jalur Pindahan (baru)
        // $table->string('file_surat_pindah')->nullable()->after('file_transkrip_s1');
        // $table->string('file_surat_akademik_pindahan')->nullable()->after('file_surat_pindah');

        // // Kolom Jalur Ekstensi (baru)
        // $table->string('file_surat_alih_jenjang')->nullable()->after('file_surat_akademik_pindahan');
        // $table->string('file_surat_akademik_ekstensi')->nullable()->after('file_surat_alih_jenjang');

        // // Kolom Jalur RPL (baru)
        // $table->string('file_surat_tugas_kerja')->nullable()->after('file_surat_akademik_ekstensi');
        // $table->string('file_transkrip_nilai_rpl')->nullable()->after('file_surat_tugas_kerja');
        // $table->string('file_surat_akademik_rpl')->nullable()->after('file_transkrip_nilai_rpl');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_ereg', function (Blueprint $table) {
            //
        });
    }
};
