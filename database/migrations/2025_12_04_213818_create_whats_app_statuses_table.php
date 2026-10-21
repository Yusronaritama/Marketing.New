<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('whatsapp_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('status'); // CONNECTED, DISCONNECTED, QR_GENERATED, etc.
            $table->text('qr_code')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('session_name');
            $table->boolean('is_connected')->default(false);
            $table->json('server_info')->nullable();
            $table->json('details')->nullable();
            $table->timestamp('last_heartbeat')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('whatsapp_statuses');
    }
};