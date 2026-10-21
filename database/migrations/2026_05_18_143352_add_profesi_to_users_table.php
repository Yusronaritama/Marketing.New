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
        // Schema::create('berkas_profesi', function (Blueprint $table) {
        //     $table->id();
            
        //     // Relasi ke tabel users pendaftar (Otomatis terhapus jika akun user dihapus)
        //     $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
        //     // Kolom Data Akademik (IPK maks 4.00, jadi pakai precision 3 dan scale 2)
        //     $table->decimal('ipk', 3, 2);
            
        //     // Kolom Penyimpanan Path File Berkas (Format: string/varchar)
        //     $table->string('file_akreditasi');
        //     $table->string('file_ijazah');
        //     $table->string('file_transkrip');
        //     $table->string('file_pasfoto');
        //     $table->string('file_ktp');
        //     $table->string('file_kk');
        //     $table->string('file_asuransi');
        //     $table->string('file_surat_sehat');
            
        //     // Kolom Status Verifikasi oleh Admin Farmasi
        //     // Default-nya 'belum' di-review
        //     $table->enum('status_verifikasi', ['belum', 'proses', 'lulus', 'tolak'])->default('belum');
            
        //     // Catatan dari tim Farmasi jika berkas ditolak (misal: "Scan ijazah blur, tolong upload ulang")
        //     $table->text('catatan_verifikasi')->nullable();
            
        //     $table->timestamps();
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::dropIfExists('berkas_profesi');
    }
};