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
        Schema::table('program_studi', function (Blueprint $table) {
            // Menambahkan kolom 'beasiswa_kel_ubp' setelah kolom 'harga_tahun'
            // Menggunakan tipe decimal(10, 2) untuk harga, nullable (boleh kosong)
            $table->decimal('beasiswa_kel_ubp', 10, 2)->nullable()->after('harga_tahun');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('program_studi', function (Blueprint $table) {
            // Menghapus kolom jika migration di-rollback
            $table->dropColumn('beasiswa_kel_ubp');
        });
    }
};