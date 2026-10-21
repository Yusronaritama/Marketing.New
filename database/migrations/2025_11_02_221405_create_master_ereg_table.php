<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('master_ereg', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('nama');
            $table->string('whatsapp');
            $table->string('gmail');
            $table->string('kode_promo')->nullable();
            $table->string('jurusan1')->nullable();
            $table->string('jurusan2')->nullable();
            $table->string('file_kk')->nullable();
            $table->string('file_ijazah')->nullable();
            $table->integer('step_completed')->default(0);
            $table->enum('status', ['in_progress', 'data_confirmed', 'completed'])->default('in_progress');
            $table->timestamps();

            $table->index('user_id');
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('master_ereg');
    }
};