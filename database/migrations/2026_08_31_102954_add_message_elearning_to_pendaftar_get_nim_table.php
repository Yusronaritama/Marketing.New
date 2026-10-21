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
    Schema::table('pendaftar_get_nim', function (Blueprint $table) {
        $table->string('message_elearning')->nullable()->after('message_slik');
    });
}

public function down()
{
    Schema::table('pendaftar_get_nim', function (Blueprint $table) {
        $table->dropColumn('message_elearning');
    });
}
};
