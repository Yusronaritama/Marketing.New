<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('group_gelombang', function (Blueprint $table) {
            $table->id();
            $table->integer('gelombang')->unique(); // 1-10
            $table->string('nama_gelombang'); // Nama gelombang
            $table->text('deskripsi')->nullable(); // Deskripsi gelombang
            $table->string('link_group'); // Link group WhatsApp/Telegram/dll
            $table->string('tipe_group')->default('whatsapp'); // whatsapp, telegram, discord, dll
            $table->boolean('is_active')->default(true); // Status aktif
            $table->integer('urutan')->default(1); // Urutan tampilan
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('group_gelombang');
    }
};