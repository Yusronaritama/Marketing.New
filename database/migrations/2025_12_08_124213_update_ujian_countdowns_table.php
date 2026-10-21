<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('ujian_countdowns', function (Blueprint $table) {
            // Hapus kolom lama
            $table->dropColumn(['start_time', 'duration_minutes']);
            
            // Tambah kolom end_date (hanya tanggal)
            $table->date('end_date')->after('description');
            
            // Ubah end_time menjadi hanya time (opsional, bisa dihapus jika tidak perlu)
            $table->time('end_time')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('ujian_countdowns', function (Blueprint $table) {
            // Kembalikan kolom lama
            $table->dateTime('start_time');
            $table->integer('duration_minutes');
            
            // Hapus end_date
            $table->dropColumn('end_date');
            
            // Kembalikan end_time ke datetime
            $table->dateTime('end_time')->nullable(false)->change();
        });
    }
};