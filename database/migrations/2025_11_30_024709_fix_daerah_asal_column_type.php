<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Kita ubah paksa kolom daerah_asal menjadi VARCHAR(255) agar bisa menampung teks apa saja
        // Ini akan menghapus batasan ENUM yang menyebabkan error "Data truncated"
        DB::statement("ALTER TABLE biodatas MODIFY COLUMN daerah_asal VARCHAR(255) NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Opsional: Kembalikan ke enum jika rollback (biasanya tidak perlu untuk fix bug)
        // DB::statement("ALTER TABLE biodatas MODIFY COLUMN daerah_asal ENUM('dalam karawang', 'luar karawang') NULL");
    }
};