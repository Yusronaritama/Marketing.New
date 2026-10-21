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
        Schema::create('biodatas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('nama_lengkap');
            $table->text('alamat');
            $table->date('tanggal_lahir');
            $table->string('nama_ibu');
            $table->string('nama_ayah');
            $table->string('asal_sekolah');
            $table->string('jurusan_sekolah');
            $table->timestamps();
            
            // Unique constraint untuk memastikan satu user hanya punya satu biodata
            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('biodatas');
    }
};