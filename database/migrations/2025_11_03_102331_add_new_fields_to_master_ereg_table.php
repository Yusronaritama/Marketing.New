<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('master_ereg', function (Blueprint $table) {
            // // 1. Kolom baru untuk Step 1 (Data Diri)
            // $table->string('status_pekerjaan')->nullable()->after('gmail');
            // $table->string('jurusan_lulusan')->nullable()->after('status_pekerjaan');
            
            // // 2. Kolom baru untuk Domisili (Rinci)
            // $table->string('domisili_provinsi')->nullable()->after('jurusan_lulusan');
            // $table->string('domisili_kota')->nullable()->after('domisili_provinsi');
            // $table->string('domisili_kecamatan')->nullable()->after('domisili_kota');
            // $table->string('domisili_kelurahan')->nullable()->after('domisili_kecamatan');
            // $table->string('domisili_kodepos')->nullable()->after('domisili_kelurahan');
            // $table->text('domisili_alamat')->nullable()->after('domisili_kodepos');

            // // 3. Kolom baru untuk Step 2 (Alasan Prodi)
            // $table->text('alasan_jurusan_1')->nullable()->after('jurusan2');
            // $table->text('alasan_jurusan_2')->nullable()->after('alasan_jurusan_1');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('master_ereg', function (Blueprint $table) {
            // $table->dropColumn([
            //     'status_pekerjaan',
            //     'jurusan_lulusan',
            //     'domisili_provinsi',
            //     'domisili_kota',
            //     'domisili_kecamatan',
            //     'domisili_kelurahan',
            //     'domisili_kodepos',
            //     'domisili_alamat',
            //     'alasan_jurusan_1',
            //     'alasan_jurusan_2'
            // ]);
        });
    }
};
