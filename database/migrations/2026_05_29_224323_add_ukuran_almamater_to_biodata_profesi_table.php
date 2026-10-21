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
            // Tambahin kolom ukuran_almamater, dibikin nullable biar data lama gak error
            $table->string('ukuran_almamater', 10)->nullable()->after('user_id'); 
            // Catatan: 'after' bebas lu taruh setelah kolom apa aja biar rapi, di sini gua contohin abis user_id
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('biodata_profesi', function (Blueprint $table) {
            $table->dropColumn('ukuran_almamater');
        });
    }
};