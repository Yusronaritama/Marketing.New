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
        // DIGANTI: Gunakan 'biodatas'
        Schema::table('biodatas', function (Blueprint $table) { 
            $table->unsignedSmallInteger('tahun_lulus')->nullable()->after('user_id'); 
            $table->enum('daerah_asal', ['luar_karawang', 'dalam_karawang'])->nullable()->after('tahun_lulus');
        });
    }

    public function down(): void
    {
        // DIGANTI: Gunakan 'biodatas'
        Schema::table('biodatas', function (Blueprint $table) { 
            $table->dropColumn(['tahun_lulus', 'daerah_asal']);
        });
    }
};