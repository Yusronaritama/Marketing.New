<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('absensi_pmb')) {
            Schema::create('absensi_pmb', function (Blueprint $table) {
                $table->id();
                $table->string('id_pendaftar', 100)->index();
                $table->date('tanggal')->index();
                $table->time('jam_masuk')->nullable();
                $table->string('email_masuk')->nullable()->index();
                $table->time('jam_pulang')->nullable();
                $table->string('email_pulang')->nullable()->index();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensi_pmb');
    }
};
