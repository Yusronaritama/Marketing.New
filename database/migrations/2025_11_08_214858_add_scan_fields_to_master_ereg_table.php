<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('master_ereg', function (Blueprint $table) {
            $table->json('scan_results')->nullable()->after('status');
            $table->string('scan_status')->default('not_scanned')->after('scan_results');
        });
    }

    public function down()
    {
        Schema::table('master_ereg', function (Blueprint $table) {
            $table->dropColumn(['scan_results', 'scan_status']);
        });
    }
};