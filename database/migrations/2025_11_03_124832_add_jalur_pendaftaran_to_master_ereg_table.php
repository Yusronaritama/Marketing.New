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
            // $table->string('jalur_pendaftaran')->nullable()->after('gmail');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('master_ereg', function (Blueprint $table) {
            // $table->dropColumn('jalur_pendaftaran');
        });
    }
};
