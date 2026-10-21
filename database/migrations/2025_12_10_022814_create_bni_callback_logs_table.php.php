<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('bni_callback_logs', function (Blueprint $table) {
            $table->id();
            $table->string('client_id');
            $table->text('raw_data'); // Data terenkripsi
            $table->json('decrypted_data')->nullable();
            $table->string('virtual_account', 16);
            $table->string('trx_id', 50);
            $table->integer('trx_amount');
            $table->integer('payment_amount');
            $table->datetime('datetime_payment')->nullable();
            $table->string('payment_ntb', 50)->nullable();
            $table->enum('status', ['received', 'processing', 'processed', 'failed'])->default('received');
            $table->text('error_message')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('master_user_id')->nullable()->constrained('master_users')->onDelete('cascade');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['virtual_account', 'trx_id']);
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('bni_callback_logs');
    }
};