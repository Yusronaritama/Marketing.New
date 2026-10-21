<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('biodata_profesi', function (Blueprint $table) {
            // Kita taruh kolom status di paling ujung (setelah email_aktif)
            $table->enum('status', ['pending', 'completed'])->default('pending')->after('email_aktif'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('biodata_profesi', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};