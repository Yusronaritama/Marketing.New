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
        Schema::create('biodata', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Data Diri
            $table->string('nama_lengkap');
            $table->string('nik', 20)->unique();
            $table->string('tempat_lahir');
            $table->date('tanggal_lahir');
            $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan']);
            $table->string('agama');
            
            // Data Kartu Keluarga
            $table->string('no_kk', 20);
            $table->string('nama_kepala_keluarga');
            $table->text('alamat_kk');
            
            // Data Pendidikan
            $table->string('asal_sekolah');
            $table->string('nisn', 20);
            $table->string('no_ijazah');
            $table->string('jurusan_sekolah');
            $table->year('tahun_lulus');
            
            // Data Tambahan (opsional)
            $table->string('no_hp')->nullable();
            $table->string('email')->nullable();
            $table->string('provinsi')->nullable();
            $table->string('kabupaten')->nullable();
            $table->string('kecamatan')->nullable();
            $table->string('kelurahan')->nullable();
            $table->string('kode_pos', 10)->nullable();
            
            // Status
            $table->enum('status', ['draft', 'submitted', 'verified', 'rejected'])->default('draft');
            $table->text('catatan_verifikasi')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('user_id');
            $table->index('nik');
            $table->index('no_kk');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('biodata');
    }
};