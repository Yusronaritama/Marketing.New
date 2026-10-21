<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::dropIfExists('farmasi_verifications');
        Schema::create('farmasi_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('master_ereg_id')->nullable()->constrained('master_ereg')->onDelete('cascade');
            $table->string('nama_lengkap');
            $table->string('email');
            $table->string('nomer_hp')->nullable();
            $table->string('phone')->nullable();
            $table->string('prodi_pilihan1')->nullable();
            $table->string('prodi_pilihan2')->nullable();
            $table->enum('status_verifikasi', ['pending', 'verified', 'rejected'])->default('pending');
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('nama_verifikator')->nullable();
            $table->timestamp('tanggal_verifikasi')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('master_ereg_id');
            $table->index('status_verifikasi');
        });
    }

    public function down()
    {
        Schema::dropIfExists('farmasi_verifications');
    }
};