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
        Schema::create('data_mahasiswa_finals', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke User (Wajib)
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // --- GAMBAR 1 ---
            $table->string('nama_mahasiswa', 100); // not null
            $table->char('jenis_kelamin', 1); // not null (L/P)
            $table->string('jalan', 80)->nullable();
            $table->integer('rt')->nullable(); // numeric(2,0)
            $table->integer('rw')->nullable(); // numeric(2,0)
            $table->string('dusun', 60)->nullable();
            $table->string('kelurahan', 60); // not null
            $table->char('kode_pos', 5)->nullable();
            $table->char('nisn', 10)->nullable();
            $table->char('nik', 16); // not null
            $table->string('tempat_lahir', 32); // not null
            $table->date('tanggal_lahir'); // not null
            
            // Data Ayah
            $table->string('nama_ayah', 100)->nullable();
            $table->date('tanggal_lahir_ayah')->nullable();
            $table->char('nik_ayah', 16)->nullable();
            $table->integer('id_jenjang_pendidikan_ayah')->nullable(); // Web Service
            $table->integer('id_pekerjaan_ayah')->nullable(); // Web Service
            $table->integer('id_penghasilan_ayah')->nullable(); // Web Service
            $table->integer('id_kebutuhan_khusus_ayah')->default(0); // not null default 0

            // Data Ibu
            $table->string('nama_ibu_kandung', 100); // not null
            $table->date('tanggal_lahir_ibu')->nullable();
            $table->char('nik_ibu', 16)->nullable();
            $table->integer('id_jenjang_pendidikan_ibu')->nullable();
            $table->integer('id_pekerjaan_ibu')->nullable();
            $table->integer('id_penghasilan_ibu')->nullable();
            $table->integer('id_kebutuhan_khusus_ibu')->default(0); // not null default 0

            // Data Wali
            $table->string('nama_wali', 100)->nullable();
            $table->date('tanggal_lahir_wali')->nullable();
            $table->integer('id_jenjang_pendidikan_wali')->nullable();
            $table->integer('id_pekerjaan_wali')->nullable();
            $table->integer('id_penghasilan_wali')->nullable();

            // Data Kontak
            $table->integer('id_kebutuhan_khusus_mahasiswa')->default(0); // not null default 0
            $table->string('telepon', 20)->nullable();
            $table->string('handphone', 20)->nullable();
            $table->string('email', 60)->nullable();

            // --- GAMBAR 2 ---
            $table->boolean('penerima_kps')->default(0); // not null (0/1)
            $table->string('no_kps', 80)->nullable();
            $table->char('npwp', 15)->nullable();
            $table->char('id_wilayah', 8); // not null (Kode Wilayah)
            $table->integer('id_jenis_tinggal')->nullable(); // Web Service
            $table->smallInteger('id_agama'); // not null (Web Service)
            $table->integer('id_alat_transportasi')->nullable(); // Web Service
            $table->char('kewarganegaraan', 2); // not null (ID/...)

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_mahasiswa_finals');
    }
};