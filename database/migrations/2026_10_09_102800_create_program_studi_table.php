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
        Schema::create('program_studi', function (Blueprint $table) {
            $table->id();
            $table->string('kode_prodi')->unique();
            $table->string('nama_prodi');
            $table->string('jenjang')->nullable();
            $table->string('periode')->nullable();
            $table->string('fakultas')->nullable();
            
            $table->float('passing_grade')->default(0);
            $table->integer('kuota')->nullable();
            $table->integer('daya_tampung')->default(0);
            
            $table->double('harga_cicilan')->default(0);
            $table->double('harga_semester')->default(0);
            $table->double('harga_tahun')->default(0);
            $table->double('biaya_pendaftaran')->default(0);
            $table->double('beasiswa_kel_ubp')->default(0);
            $table->double('pendaftaran_alumni_ubp')->default(0);
            
            $table->text('deskripsi')->nullable();
            $table->string('akreditasi')->nullable();
            $table->integer('tahun_berdiri')->nullable();
            
            $table->boolean('status_prodi')->default(true);
            $table->integer('urutan_tampil')->default(0);
            $table->integer('jumlah_pendaftar')->default(0);
            
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
        Schema::dropIfExists('program_studi');
    }
};
