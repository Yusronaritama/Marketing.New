<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ujian_countdowns', function (Blueprint $table) {
            $table->id();
            $table->string('ujian_name');
            $table->text('description')->nullable();
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->integer('duration_minutes');
            $table->boolean('is_active')->default(false);
            $table->string('status')->default('upcoming'); // upcoming, ongoing, finished
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ujian_countdowns');
    }
};