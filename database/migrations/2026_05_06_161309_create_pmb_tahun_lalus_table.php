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
        Schema::create('pmb_tahun_lalu', function (Blueprint $table) {
            $table->id(); // Primary Key Auto Increment bawaan Laravel
            
            // Kolom berdasarkan tabel 'login' dari file SQL
            $table->string('id_pendaftaran')->nullable()->index(); // Di-index agar pencarian cepat
            $table->string('hitung')->nullable();
            $table->string('no_ujian')->nullable();
            $table->string('input')->nullable(); // Biasanya berisi 'admin' / 'bni'
            $table->string('nama_user')->nullable();
            $table->date('tanggal_reg')->nullable();
            
            // Kolom tambahan untuk menyimpan perhitungan otomatis kita
            $table->string('gelombang')->nullable(); 

            // Timestamps otomatis (created_at & updated_at)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pmb_tahun_lalu');
    }
};