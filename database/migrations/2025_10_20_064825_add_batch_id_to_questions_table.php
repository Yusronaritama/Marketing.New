<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->unsignedBigInteger('batch_id')->after('uuid')->nullable();

            // jika kamu ingin relasi ke tabel batches
            $table->foreign('batch_id')->references('id')->on('batches')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropForeign(['batch_id']);
            $table->dropColumn('batch_id');
        });
    }
};
