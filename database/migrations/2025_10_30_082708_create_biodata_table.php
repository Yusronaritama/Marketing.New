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
            $table->string('nama_lengkap');
            $table->text('alamat');
            $table->date('tanggal_lahir');
            $table->string('nama_ibu');
            $table->string('nama_ayah');
            $table->string('asal_sekolah');
            $table->string('jurusan_sekolah');
            $table->boolean('is_verified')->default(false);
            $table->timestamps();

            // Index untuk performa
            $table->index('user_id');
            $table->index('is_verified');
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