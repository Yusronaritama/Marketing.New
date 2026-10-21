<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('berkas_profesi', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke tabel users
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Kolom IPK
            $table->decimal('ipk', 3, 2);
            
            // Kolom File Berkas
            $table->string('file_akreditasi');
            $table->string('file_ijazah');
            $table->string('file_transkrip');
            $table->string('file_pasfoto');
            $table->string('file_ktp');
            $table->string('file_kk');
            $table->string('file_asuransi');
            $table->string('file_surat_sehat');
            
            // Kolom Status Verifikasi
            $table->enum('status_verifikasi', ['belum', 'proses', 'lulus', 'tolak'])->default('belum');
            $table->text('catatan_verifikasi')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('berkas_profesi');
    }
};