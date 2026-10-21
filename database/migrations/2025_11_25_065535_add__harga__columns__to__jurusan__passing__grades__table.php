<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHargaColumnsToJurusanPassingGradesTable extends Migration
{
    public function up()
    {
        Schema::table('jurusan_passing_grades', function (Blueprint $table) {
            $table->integer('harga_cicilan')->default(0)->after('kuota');
            $table->integer('harga_semester')->default(0)->after('harga_cicilan');
            $table->integer('harga_tahun')->default(0)->after('harga_semester');
        });
    }

    public function down()
    {
        Schema::table('jurusan_passing_grades', function (Blueprint $table) {
            $table->dropColumn(['harga_cicilan', 'harga_semester', 'harga_tahun']);
        });
    }
}