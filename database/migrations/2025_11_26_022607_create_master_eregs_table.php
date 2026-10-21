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
        // Ganti 'master_eregs' jadi 'master_ereg'
        Schema::table('master_ereg', function (Blueprint $table) {
            $table->string('file_surat_buta_warna')->nullable()->after('status');
        });
    }

    public function down()
    {
        // Ganti 'master_eregs' jadi 'master_ereg'
        Schema::table('master_ereg', function (Blueprint $table) {
            $table->dropColumn('file_surat_buta_warna');
        });
    }
};
