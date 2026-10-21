<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('master_users_verified', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('master_user_id')->constrained('master_users')->onDelete('cascade');
            $table->timestamp('verified_at');
            $table->string('verified_by')->nullable(); // bisa diisi dengan admin ID atau nama
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Unique constraint untuk mencegah duplikasi
            $table->unique(['user_id', 'master_user_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('master_users_verified');
    }
};