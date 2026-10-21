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
            // Tambahkan kolom baru setelah gmail
            $table->string('jalur_pendaftaran')->nullable()->after('gmail');
            $table->string('status_pekerjaan')->nullable()->after('jalur_pendaftaran');
            $table->string('jurusan_lulusan')->nullable()->after('status_pekerjaan');
            $table->text('alasan_jurusan_1')->nullable()->after('jurusan2');
            $table->text('alasan_jurusan_2')->nullable()->after('alasan_jurusan_1');
            
            // Kolom domisili
            $table->string('domisili_provinsi')->nullable()->after('alasan_jurusan_2');
            $table->string('domisili_kota')->nullable()->after('domisili_provinsi');
            $table->string('domisili_kecamatan')->nullable()->after('domisili_kota');
            $table->string('domisili_kelurahan')->nullable()->after('domisili_kecamatan');
            $table->string('domisili_kodepos')->nullable()->after('domisili_kelurahan');
            $table->text('domisili_alamat')->nullable()->after('domisili_kodepos');
            
            // File upload tambahan untuk berbagai jalur
            $table->string('file_ktp')->nullable()->after('file_ijazah');
            $table->string('file_ijazah_s1')->nullable()->after('file_ktp');
            $table->string('file_transkrip_s1')->nullable()->after('file_ijazah_s1');
            $table->string('file_surat_pindah')->nullable()->after('file_transkrip_s1');
            $table->string('file_surat_akademik_pindahan')->nullable()->after('file_surat_pindah');
            $table->string('file_surat_alih_jenjang')->nullable()->after('file_surat_akademik_pindahan');
            $table->string('file_surat_akademik_ekstensi')->nullable()->after('file_surat_alih_jenjang');
            $table->string('file_surat_tugas_kerja')->nullable()->after('file_surat_akademik_ekstensi');
            $table->string('file_transkrip_nilai_rpl')->nullable()->after('file_surat_tugas_kerja');
            $table->string('file_surat_akademik_rpl')->nullable()->after('file_transkrip_nilai_rpl');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('master_ereg', function (Blueprint $table) {
            $table->dropColumn([
                'jalur_pendaftaran',
                'status_pekerjaan',
                'jurusan_lulusan',
                'alasan_jurusan_1',
                'alasan_jurusan_2',
                'domisili_provinsi',
                'domisili_kota',
                'domisili_kecamatan',
                'domisili_kelurahan',
                'domisili_kodepos',
                'domisili_alamat',
                'file_ktp',
                'file_ijazah_s1',
                'file_transkrip_s1',
                'file_surat_pindah',
                'file_surat_akademik_pindahan',
                'file_surat_alih_jenjang',
                'file_surat_akademik_ekstensi',
                'file_surat_tugas_kerja',
                'file_transkrip_nilai_rpl',
                'file_surat_akademik_rpl'
            ]);
        });
    }
};