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
        Schema::create('kelulusan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_pendaftaran');
            $table->string('prodi', 10); // Kode program studi
            $table->enum('status_lulus', ['1', '2'])->default('2'); // 1 = Lulus, 2 = Tidak Lulus
            $table->string('nama_petugas')->nullable(); // Nama petugas yang input
            $table->string('ket')->nullable(); // Keterangan
            $table->string('keterangan')->nullable(); // Keterangan tambahan
            $table->decimal('nilai_akhir', 5, 2)->nullable(); // Nilai akhir
            $table->integer('ranking')->nullable(); // Peringkat
            $table->timestamp('tanggal_pengumuman')->nullable(); // Tanggal pengumuman
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('id_pendaftaran')
                  ->references('id')
                  ->on('master_ereg')
                  ->onDelete('cascade');

            // Composite unique key - satu pendaftaran hanya bisa punya satu status per prodi
            $table->unique(['id_pendaftaran', 'prodi']);

            // Indexes
            $table->index('id_pendaftaran');
            $table->index('prodi');
            $table->index('status_lulus');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kelulusan');
    }
};