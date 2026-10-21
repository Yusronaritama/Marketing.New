<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bni_billings', function (Blueprint $table) {
            $table->id();
            $table->string('virtual_account')->unique();
            $table->string('trx_id')->unique();
            $table->unsignedBigInteger('user_id');
            $table->decimal('trx_amount', 15, 2);
            $table->string('customer_name');
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            $table->timestamp('datetime_expired')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bni_billings');
    }
};
