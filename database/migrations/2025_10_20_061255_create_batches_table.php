<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('batch_name');
            $table->integer('total_questions');
            $table->integer('answers_per_question');
            $table->integer('duration'); // menit
            $table->dateTime('expired_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batches');
    }
};
