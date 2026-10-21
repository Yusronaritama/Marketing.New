<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Mengubah nilai beasiswa_kel_ubp menjadi 3850000 untuk SEMUA baris
        DB::table('program_studi')->update([
            'beasiswa_kel_ubp' => 3850000
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Jika di-rollback, kembalikan ke 0 atau nilai default sebelumnya
        DB::table('program_studi')->update([
            'beasiswa_kel_ubp' => 0
        ]);
    }
};