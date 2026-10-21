<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Ganti 'master_eregs' jadi 'master_ereg'
        Schema::table('master_ereg', function (Blueprint $table) {
            // $table->string('file_surat_buta_warna')->nullable()->after('status');
        });
    }

/*************  ✨ Windsurf Command ⭐  *************/
/**
 * Ganti 'master_eregs' jadi 'master_ereg'
 *
 * Schema::table('master_ereg', function (Blueprint $table) that drops the 'file_surat_buta_warna' column from the 'master_ereg' table.
 */
/*******  d44236e7-f316-4cdc-9feb-c77285cb661e  *******/
    public function down()
    {
        // Ganti 'master_eregs' jadi 'master_ereg'
        Schema::table('master_ereg', function (Blueprint $table) {
            // $table->dropColumn('file_surat_buta_warna');
        });
    }
};
