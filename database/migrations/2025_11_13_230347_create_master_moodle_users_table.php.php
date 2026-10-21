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
        Schema::create('master_moodle_users', function (Blueprint $table) {
            $table->id();
            
            // Foreign key ke master_users
            $table->unsignedBigInteger('master_user_id')->nullable();
            
            // ID user dari Moodle
            $table->unsignedBigInteger('moodle_user_id')->nullable();
            
            // Data dasar user
            $table->string('username', 255)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('firstname', 255)->nullable();
            $table->string('lastname', 255)->nullable();
            
            // Data quiz
            $table->unsignedBigInteger('quiz_id')->nullable();
            $table->decimal('nilai_quiz', 8, 2)->nullable()->default(0);
            $table->integer('attempt_count')->nullable()->default(0);
            
            // Data kelulusan
            $table->string('status_kelulusan', 50)->nullable()->default('Belum diketahui');
            $table->string('prodi_lulus', 20)->nullable();
            $table->string('nama_prodi_lulus', 255)->nullable();
            $table->string('pilihan_lulus', 20)->nullable();
            $table->decimal('selisih_nilai', 8, 2)->nullable();
            
            // Informasi tambahan
            $table->text('keterangan')->nullable();
            $table->string('data_source', 50)->nullable()->default('manual');
            
            // Timestamps
            $table->timestamps();
            
            // Indexes
            $table->index('master_user_id');
            $table->index('moodle_user_id');
            $table->index('username');
            $table->index('email');
            $table->index('status_kelulusan');
            $table->index('quiz_id');
            $table->index('created_at');
            
            // Unique constraints
            $table->unique(['master_user_id', 'moodle_user_id']);
            
            // Foreign key constraints (optional)
            $table->foreign('master_user_id')
                  ->references('id')
                  ->on('master_users')
                  ->onDelete('set null');
        });

        // Tambahkan index composite untuk pencarian yang lebih cepat
        Schema::table('master_moodle_users', function (Blueprint $table) {
            $table->index(['status_kelulusan', 'nilai_quiz']);
            $table->index(['prodi_lulus', 'status_kelulusan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_moodle_users');
    }
};