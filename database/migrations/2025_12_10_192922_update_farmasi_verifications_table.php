<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateFarmasiVerificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Alternatif 1: Ubah menjadi VARCHAR dengan length cukup
        Schema::table('farmasi_verifications', function (Blueprint $table) {
            $table->string('status_verifikasi', 20)->change();
        });

        // Atau Alternatif 2: Jika menggunakan ENUM, perbarui nilai ENUM
        // DB::statement("ALTER TABLE farmasi_verifications 
        //     MODIFY COLUMN status_verifikasi 
        //     ENUM('menunggu', 'ditolak', 'diverifikasi') 
        //     NOT NULL DEFAULT 'menunggu'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('farmasi_verifications', function (Blueprint $table) {
            $table->string('status_verifikasi', 15)->change();
        });
    }
}