<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pmb_tahun_lalu', function (Blueprint $table) {
            // Tambahan kolom untuk memperkaya data riwayat dan sinkronisasi data saat ini
            $table->string('tahun_pmb')->nullable()->after('gelombang'); // Contoh: 2021, 2024, 2026
            $table->string('jalur_pendaftaran')->nullable()->after('tahun_pmb');
            $table->string('prodi_pilihan')->nullable()->after('jalur_pendaftaran');
            $table->string('status_pembayaran')->nullable()->after('prodi_pilihan');
        });
    }

    public function down(): void
    {
        Schema::table('pmb_tahun_lalu', function (Blueprint $table) {
            $table->dropColumn(['tahun_pmb', 'jalur_pendaftaran', 'prodi_pilihan', 'status_pembayaran']);
        });
    }
};