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
        Schema::create('nilai', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_pendaftaran');
            $table->decimal('nilai', 5, 2)->nullable(); // Nilai dengan 2 digit desimal
            $table->string('ket')->nullable(); // Keterangan
            $table->string('nama_petugas')->nullable(); // Nama petugas yang input
            $table->unsignedBigInteger('attempt_id')->nullable(); // ID attempt dari Moodle
            $table->integer('attempt_number')->nullable(); // Nomor attempt
            $table->timestamp('time_start')->nullable(); // Waktu mulai ujian
            $table->timestamp('time_finish')->nullable(); // Waktu selesai ujian
            $table->text('attempt_data')->nullable(); // Data lengkap attempt (JSON)
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('id_pendaftaran')
                  ->references('id')
                  ->on('master_ereg')
                  ->onDelete('cascade');

            // Indexes
            $table->index('id_pendaftaran');
            $table->index('attempt_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nilai');
    }
};