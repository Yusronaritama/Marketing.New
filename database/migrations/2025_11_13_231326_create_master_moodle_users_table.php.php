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
        // Schema::create('master_moodle_users', function (Blueprint $table) {
        //     // $table->id();
            
        //     // IDs
        //     // $table->unsignedBigInteger('master_user_id')->nullable();
        //     // $table->unsignedBigInteger('moodle_user_id')->nullable();
            
        //     // User info
        //     // $table->string('username', 255);
        //     // $table->string('email', 255);
        //     // $table->string('firstname', 255);
        //     // $table->string('lastname', 255)->nullable();
            
        //     // Prodi pilihan
        //     // $table->string('prodi_pilihan1', 20)->nullable();
        //     // $table->string('nama_prodi_pilihan1', 255)->nullable();
        //     // $table->string('prodi_pilihan2', 20)->nullable();
        //     // $table->string('nama_prodi_pilihan2', 255)->nullable();
            
        //     // Quiz data
        //     // $table->unsignedBigInteger('quiz_id')->nullable();
        //     // $table->decimal('nilai_quiz', 8, 2)->default(0);
        //     // $table->integer('attempt_count')->default(0);
            
        //     // Kelulusan data
        //     // $table->string('status_kelulusan', 50)->default('Belum diketahui');
        //     // $table->string('prodi_lulus', 20)->nullable();
        //     // $table->string('nama_prodi_lulus', 255)->nullable();
        //     // $table->string('pilihan_lulus', 20)->nullable();
        //     // $table->decimal('selisih_nilai', 8, 2)->nullable();
            
        //     // Metadata
        //     // $table->string('data_source', 50)->default('manual');
        //     // $table->text('keterangan')->nullable();
            
        //     // $table->timestamps();
            
        //     // Indexes
        //     // $table->index('master_user_id');
        //     // $table->index('moodle_user_id');
        //     // $table->index('username');
        //     // $table->index('email');
        //     // $table->index('status_kelulusan');
        //     // $table->index('quiz_id');
        //     // $table->index(['status_kelulusan', 'nilai_quiz']);
            
        //     // Unique constraint
        //     // $table->unique(['master_user_id', 'moodle_user_id']);
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::dropIfExists('master_moodle_users');
    }
};