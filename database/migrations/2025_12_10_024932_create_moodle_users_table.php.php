<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('moodle_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('master_user_id')->constrained('master_users')->onDelete('cascade');
            $table->string('moodle_user_id')->nullable(); // ID dari Moodle
            $table->string('moodle_username');
            $table->string('moodle_password');
            $table->string('va_number', 16)->nullable();
            $table->enum('status', ['pending', 'created', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'master_user_id']);
            $table->index('moodle_user_id');
            $table->index('va_number');
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('moodle_users');
    }
};