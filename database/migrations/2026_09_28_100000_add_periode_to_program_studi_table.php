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
        if (!Schema::hasColumn('program_studi', 'periode')) {
            Schema::table('program_studi', function (Blueprint $table) {
                $table->string('periode', 6)->nullable()->default('20261')->after('jenjang');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('program_studi', 'periode')) {
            Schema::table('program_studi', function (Blueprint $table) {
                $table->dropColumn('periode');
            });
        }
    }
};
