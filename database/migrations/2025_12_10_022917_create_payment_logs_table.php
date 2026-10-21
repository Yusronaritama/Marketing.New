<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payment_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('master_user_id')->constrained('master_users')->onDelete('cascade');
            $table->string('virtual_account', 16);
            $table->string('trx_id', 50);
            $table->integer('trx_amount');
            $table->integer('payment_amount');
            $table->integer('cumulative_payment_amount');
            $table->string('payment_ntb', 50)->nullable();
            $table->datetime('datetime_payment')->nullable();
            $table->string('datetime_payment_iso8601')->nullable();
            $table->enum('status', ['paid', 'partial', 'duplicate', 'failed']);
            $table->json('payment_data')->nullable();
            $table->timestamps();

            $table->index(['virtual_account', 'trx_id']);
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('payment_logs');
    }
};