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
            // Menambahkan kolom NIK (16 digit, unik)
            // Ditaruh setelah kolom 'id' (opsional, hanya untuk kerapian urutan di database)
            $table->string('nik', 16)->unique()->nullable()->after('id');

            
            $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan'])->nullable()->after('nama_lengkap');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('biodatas', function (Blueprint $table) {
            // Menghapus kolom jika migrasi di-rollback
            $table->dropColumn(['nik', 'jenis_kelamin']);
        });
    }
};