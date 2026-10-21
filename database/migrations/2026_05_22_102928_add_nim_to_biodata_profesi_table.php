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
        Schema::table('biodata_profesi', function (Blueprint $table) {
            // Kita taruh kolom nim tepat setelah user_id biar rapi di database
            $table->string('nim', 50)->after('user_id')->nullable(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('biodata_profesi', function (Blueprint $table) {
            // Buat ngehapus kolom kalau kita ngerollback migrasi
            $table->dropColumn('nim');
        });
    }
};