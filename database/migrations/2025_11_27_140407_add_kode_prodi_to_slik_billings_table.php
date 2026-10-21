<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('slik_billings', function (Blueprint $table) {
            // 1. Kolom Penghubung ke ProgramStudi
            // Pastikan tipe datanya sama dengan di tabel program_studis (biasanya string/varchar)
            $table->string('kode_prodi', 20)->nullable()->after('user_id');

            // 2. Kolom Info Tambahan (Semester/Tahun/Cicilan)
            $table->string('tenor_pembayaran', 50)->nullable()->after('amount');
        });
    }

    public function down(): void
    {
        Schema::table('slik_billings', function (Blueprint $table) {
            $table->dropColumn(['kode_prodi', 'tenor_pembayaran']);
        });
    }
};