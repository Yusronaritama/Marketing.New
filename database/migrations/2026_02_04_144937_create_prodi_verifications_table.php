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
        Schema::create('prodi_verifications', function (Blueprint $table) {
            $table->id();
            // Relasi ke tabel users (mahasiswa)
            $table->unsignedBigInteger('user_id');
            // Relasi ke tabel pendaftaran (master_ereg)
            $table->unsignedBigInteger('master_ereg_id');
            
            // Info Prodi & Mahasiswa (Denormalisasi untuk kecepatan query)
            $table->string('kode_prodi', 10);
            $table->string('nama_lengkap');
            $table->string('email')->index();

            // Status Verifikasi
            $table->enum('status_verifikasi', ['pending', 'verified', 'rejected'])->default('pending');
            
            // Info Verifikator (Admin Prodi)
            $table->unsignedBigInteger('verified_by'); // ID User Admin
            $table->string('nama_verifikator');
            $table->dateTime('tanggal_verifikasi');
            
            // Catatan Alasan (Penting jika di-reject)
            $table->text('keterangan')->nullable();

            $table->timestamps();

            // Indexing untuk performa filter prodi
            $table->index(['kode_prodi', 'status_verifikasi']);
            
            // Foreign Key (Opsional, pastikan tabel users dan master_ereg menggunakan engine InnoDB)
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prodi_verifications');
    }
};