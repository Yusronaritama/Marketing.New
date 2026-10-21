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
        Schema::table('data_mahasiswa_finals', function (Blueprint $table) {
            // Default 'pending', tidak boleh null
            $table->string('status_data')->default('pending')->after('user_id'); 
        });
    }
    
    public function down()
    {
        Schema::table('data_mahasiswa_finals', function (Blueprint $table) {
            $table->dropColumn('status_data');
        });
    }
};
