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
        Schema::create('master_moodle', function (Blueprint $table) {
            $table->id();
            // Relasi ke tabel master_users
            $table->foreignId('master_user_id')->constrained('master_users')->onDelete('cascade');
            
            $table->unsignedBigInteger('moodle_user_id')->nullable()->comment('ID user dari Moodle');
            $table->string('username')->comment('Username yang dikirim ke Moodle (VA Number)');
            $table->enum('status', ['success', 'failed'])->comment('Status pembuatan user di Moodle');
            $table->string('enrol_status')->nullable()->comment('Status pendaftaran ke course');
            $table->text('error_message')->nullable()->comment('Pesan error jika gagal');
            $table->timestamps();

            // Tambahkan unique constraint agar satu master_user_id hanya bisa ada satu kali
            $table->unique('master_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_moodle');
    }
};