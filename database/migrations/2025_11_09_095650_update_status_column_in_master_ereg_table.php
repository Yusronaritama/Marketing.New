<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateStatusColumnInMasterEregTable extends Migration
{
    public function up()
    {
        Schema::table('master_ereg', function (Blueprint $table) {
            // Ubah tipe data kolom status menjadi string yang lebih panjang
            $table->string('status', 50)->default('in_progress')->change();
        });
    }

    public function down()
    {
        Schema::table('master_ereg', function (Blueprint $table) {
            $table->string('status', 20)->default('in_progress')->change();
        });
    }
}