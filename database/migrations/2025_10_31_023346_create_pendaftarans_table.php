<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
{
    Schema::create('pendaftarans', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        
        $table->string('jalur_pendaftaran')->nullable();
        $table->string('whatsapp')->nullable();
        $table->string('status_pekerjaan')->nullable();
        $table->string('jurusan_lulusan')->nullable();
        $table->string('jurusan_1')->nullable();
        $table->text('alasan_jurusan_1')->nullable();
        $table->string('jurusan_2')->nullable();
        $table->text('alasan_jurusan_2')->nullable();
        
        // Domisili
        $table->string('domisili_negara')->nullable();
        $table->string('domisili_provinsi')->nullable();
        $table->string('domisili_kota')->nullable();
        $table->string('domisili_kecamatan')->nullable();
        $table->string('domisili_kelurahan')->nullable();
        $table->string('domisili_kodepos')->nullable();
        $table->text('domisili_alamat')->nullable();
        
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pendaftarans');
    }
};