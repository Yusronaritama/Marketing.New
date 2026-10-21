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
        Schema::create('master_moodle_lulus', function (Blueprint $table) {
            $table->id();
            
            // IDs
            $table->unsignedBigInteger('master_user_id')->nullable();
            $table->unsignedBigInteger('moodle_user_id')->nullable();
            
            // User info
            $table->string('username', 255);
            $table->string('email', 255);
            $table->string('nama_lengkap', 255);
            
            // Prodi lulus
            $table->string('prodi_lulus', 20)->nullable();
            $table->string('nama_prodi_lulus', 255)->nullable();
            $table->string('pilihan_lulus', 20)->nullable();
            
            // Quiz data
            $table->unsignedBigInteger('quiz_id')->nullable();
            $table->decimal('nilai_quiz', 8, 2)->default(0);
            $table->integer('attempt_count')->default(0);
            
            // Status
            $table->string('status_kelulusan', 100);
            $table->text('keterangan')->nullable();
            $table->timestamp('tanggal_lulus')->nullable();
            
            // Timestamps
            $table->timestamps();
            
            // Indexes
            $table->index('master_user_id');
            $table->index('moodle_user_id');
            $table->index('email');
            $table->index('prodi_lulus');
            $table->index('status_kelulusan');
            $table->index('tanggal_lulus');
            $table->index('nilai_quiz');
            
            // Unique constraint
            $table->unique(['master_user_id', 'moodle_user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_moodle_lulus');
    }
};