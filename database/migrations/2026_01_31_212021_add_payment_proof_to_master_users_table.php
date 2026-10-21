<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    Schema::table('master_users', function (Blueprint $table) {
        // Menambahkan kolom untuk menyimpan nama file/path foto bukti
        $table->string('payment_proof')->nullable()->after('payment_method');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_users', function (Blueprint $table) {
            //
        });
    }
};
