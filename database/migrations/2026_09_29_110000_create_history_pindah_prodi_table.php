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
        if (!Schema::hasTable('history_pindah_prodi')) {
            Schema::create('history_pindah_prodi', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->unsignedBigInteger('master_ereg_id')->nullable()->index();
                $table->string('nama_mahasiswa')->nullable();
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->string('trx_id')->nullable()->index();
                $table->string('prodi_lama_kode')->nullable();
                $table->string('prodi_lama_nama')->nullable();
                $table->string('prodi_baru_kode');
                $table->string('prodi_baru_nama');
                $table->decimal('nominal_selisih', 14, 2)->default(0);
                $table->string('status', 30)->default('selesai'); // 'selesai', 'pending_va', 'batal_va'
                $table->unsignedBigInteger('slik_billing_id')->nullable()->index();
                $table->string('va_number', 50)->nullable();
                $table->unsignedBigInteger('admin_id')->nullable();
                $table->string('admin_nama')->nullable();
                $table->text('catatan')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('history_pindah_prodi');
    }
};
