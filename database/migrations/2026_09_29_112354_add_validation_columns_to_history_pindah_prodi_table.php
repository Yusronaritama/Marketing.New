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
        Schema::table('history_pindah_prodi', function (Blueprint $table) {
            $table->string('prodi_awal_kode')->nullable()->after('trx_id');
            $table->string('prodi_awal_nama')->nullable()->after('prodi_awal_kode');
            $table->string('prodi_lulus_kode')->nullable()->after('prodi_awal_nama');
            $table->string('prodi_lulus_nama')->nullable()->after('prodi_lulus_kode');
            $table->string('slik_prodi_kode')->nullable()->after('prodi_lulus_nama');
            $table->string('slik_prodi_nama')->nullable()->after('slik_prodi_kode');
            $table->string('slik_status', 30)->nullable()->after('slik_prodi_nama');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('history_pindah_prodi', function (Blueprint $table) {
            $table->dropColumn([
                'prodi_awal_kode',
                'prodi_awal_nama',
                'prodi_lulus_kode',
                'prodi_lulus_nama',
                'slik_prodi_kode',
                'slik_prodi_nama',
                'slik_status',
            ]);
        });
    }
};
