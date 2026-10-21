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
            $table->string('jurusan1_kode')->nullable()->after('jurusan1');
            $table->string('jurusan2_kode')->nullable()->after('jurusan2');
        });
    }

    public function down()
    {
        Schema::table('master_ereg', function (Blueprint $table) {
            $table->dropColumn(['jurusan1_kode', 'jurusan2_kode']);
        });
    }
};
