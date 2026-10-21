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
        Schema::create('biodata_profesi', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke tabel users biar tahu biodata ini punya maba yang mana
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Parameter data yang dibutuhkan
            $table->string('nama');
            $table->string('tempat_lahir');
            $table->date('tanggal_lahir');
            $table->string('asal_s1');
            $table->year('tahun_lulus'); // Pakai tipe year biar presisi empat angka (misal: 2025)
            $table->text('alamat'); // Pakai text karena alamat biasanya panjang
            $table->string('no_wa', 20); // Dibatasi panjangnya biar optimal
            $table->string('email_aktif')->unique(); // Dipastikan unik biar gak double input
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('biodata_profesi');
    }
};