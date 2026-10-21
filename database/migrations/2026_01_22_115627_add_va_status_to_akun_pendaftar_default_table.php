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
    Schema::table('akun_pendaftar_default', function (Blueprint $table) {
        $table->string('va_status')->default('pending')->after('va_number');
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('akun_pendaftar_default', function (Blueprint $table) {
            //
        });
    }
};
