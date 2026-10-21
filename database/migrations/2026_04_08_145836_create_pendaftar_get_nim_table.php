<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pendaftar_get_nim', function (Blueprint $table) {
            $table->id();

            $table->foreignId('data_mahasiswa_final_id')
                  ->unique() // 1 mahasiswa hanya boleh 1 NIM
                  ->constrained('data_mahasiswa_finals')
                  ->cascadeOnDelete();

            $table->unsignedBigInteger('user_id')->index();

            // NIM: 2+4+5+3 = 14 karakter
            // Contoh: 26416255201001
            $table->string('nim', 14)->unique();

            $table->string('email_mhs')->unique();
            $table->string('kode_prodi', 10);
            $table->enum('jenjang', ['S1', 'S2'])->default('S1');

            // Format: YYYYsemester (20241 / 20242)
            $table->string('id_periode_masuk', 6)->nullable();

            // Syarat: 'payment' = sudah bayar, 'beasiswa' = surat rekomendasi pegawai
            $table->enum('syarat_type', ['payment', 'beasiswa'])->nullable();

            // Status pengiriman ke sistem eksternal
            $table->string('message_sipt')->nullable();
            $table->string('message_slik')->nullable();

            // Audit
            $table->unsignedBigInteger('generated_by')->nullable();
            $table->timestamp('generated_at')->nullable();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('generated_by')->references('id')->on('users');

            $table->timestamps();

            // Index untuk pencarian NIM berdasarkan prodi + tahun
            $table->index(['kode_prodi', 'nim']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pendaftar_get_nim');
    }
};