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
            // Ubah panjang kolom menjadi 15 karakter (atau lebih, 
            // 10 sudah cukup untuk 'completed'/'draft')
            $table->string('status_bio', 15)->change(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('biodatas', function (Blueprint $table) {
            // Opsional: kembalikan ke panjang sebelumnya jika Anda tahu itu
            // $table->string('status_bio', 5)->change(); 
        });
    }
};