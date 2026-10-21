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
        Schema::create('master_users_va_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('trx_id', 50);
            $table->string('va_number', 20);
            $table->string('operation', 50); // create, update, regenerate, close
            $table->json('old_data')->nullable();
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('trx_id');
            $table->index('va_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_users_va_history');
    }
};