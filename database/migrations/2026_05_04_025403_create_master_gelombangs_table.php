<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_gelombangs', function (Blueprint $table) {
            $table->id();
            $table->string('nama_gelombang'); // Contoh: "Gelombang 5"
            $table->integer('angka_gelombang'); // Contoh: 5 (untuk disimpan ke master_users)
            $table->boolean('is_active')->default(false); // 1 = Aktif, 0 = Tutup
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_gelombangs');
    }
};