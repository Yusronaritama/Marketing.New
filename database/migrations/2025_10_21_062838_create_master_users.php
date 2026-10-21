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
        // Periksa apakah tabel 'master_users' ada
        if (Schema::hasTable('master_users')) {
            Schema::table('master_users', function (Blueprint $table) {
                // Tambahkan kolom va_number setelah kolom 'role' (jika ada)
                // Jika tidak, tambahkan di akhir
                if (Schema::hasColumn('master_users', 'role')) {
                    $table->string('va_number', 50)->nullable()->after('role');
                    $table->string('va_status', 50)->nullable()->after('va_number');
                } else {
                    $table->string('va_number', 50)->nullable();
                    $table->string('va_status', 50)->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('master_users')) {
            Schema::table('master_users', function (Blueprint $table) {
                // Hapus kolom jika ada saat rollback
                $table->dropColumn(['va_number', 'va_status']);
            });
        }
    }
};
