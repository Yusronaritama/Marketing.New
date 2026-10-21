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
        Schema::table('slik_billings', function (Blueprint $table) {
            // Kita tambahkan kolom-kolom yang ada di pesan error tadi
            
            // 1. Tambah kode_prodi jika belum ada
            if (!Schema::hasColumn('slik_billings', 'kode_prodi')) {
                $table->string('kode_prodi')->nullable()->after('amount');
            }
    
            // 2. Tambah nama_jurusan (YANG BIKIN ERROR SEKARANG)
            if (!Schema::hasColumn('slik_billings', 'nama_jurusan')) {
                $table->string('nama_jurusan')->nullable()->after('kode_prodi');
            }
            
            if (!Schema::hasColumn('slik_billings', 'tenor_pembayaran')) {
                $table->string('tenor_pembayaran')->nullable()->after('nama_jurusan');
            }
    
            // 4. Tambah datetime_expired
            if (!Schema::hasColumn('slik_billings', 'datetime_expired')) {
                $table->dateTime('datetime_expired')->nullable()->after('tenor_pembayaran');
            }
        });
    }
    
    public function down()
    {
        Schema::table('slik_billings', function (Blueprint $table) {
            $table->dropColumn(['kode_prodi', 'nama_jurusan', 'tenor_pembayaran', 'datetime_expired']);
        });
    }
};
