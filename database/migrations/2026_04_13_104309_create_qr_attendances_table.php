<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qr_attendances', function (Blueprint $table) {
            $table->id();

            // Relasi ke user mahasiswa
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Token QR unik — ini yang di-encode ke QR Code
            $table->string('token', 64)->unique();

            // Status QR
            $table->enum('status', ['active', 'scanned', 'expired'])->default('active');
            $table->index('status');

            // Waktu QR dibuat & kapan expired otomatis (opsional, untuk sweep)
            $table->timestamp('expired_at')->nullable();

            // Waktu & info saat QR di-scan
            $table->timestamp('scanned_at')->nullable();
            $table->string('scanned_by')->nullable()->comment('IP atau identifier scanner');
            $table->string('scanned_location')->nullable()->comment('Keterangan lokasi/sesi absensi');

            // Sesi absensi (misal: Orientasi, Kuliah Perdana, dll)
            $table->string('sesi', 100)->default('Daftar Hadir')->comment('Nama sesi/kegiatan');

            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('token');
            $table->index('expired_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qr_attendances');
    }
};