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
        // Buat migration: php artisan make:migration create_whatsapp_logs_table

Schema::create('whatsapp_logs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->enum('type', ['registration', 'payment', 'reminder', 'info']);
    $table->text('message');
    $table->string('status'); // pending, sent, failed, retry
    $table->json('response')->nullable();
    $table->integer('attempts')->default(0);
    $table->timestamp('sent_at')->nullable();
    $table->timestamps();
    
    $table->index(['user_id', 'status']);
    $table->index(['type', 'created_at']);
});

// Tabel untuk retry queue
Schema::create('whatsapp_retry_queue', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('type');
    $table->json('payload');
    $table->integer('attempts')->default(0);
    $table->integer('max_attempts')->default(3);
    $table->timestamp('next_attempt_at');
    $table->timestamps();
    
    $table->index(['next_attempt_at', 'attempts']);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_logs');
    }
};
