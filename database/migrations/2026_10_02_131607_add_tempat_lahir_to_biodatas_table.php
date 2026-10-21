<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('biodatas', function (Blueprint $table) {
            if (!Schema::hasColumn('biodatas', 'tempat_lahir')) {
                $table->string('tempat_lahir')->nullable()->after('jenis_kelamin');
            }
        });
    }

    public function down(): void
    {
        Schema::table('biodatas', function (Blueprint $table) {
            if (Schema::hasColumn('biodatas', 'tempat_lahir')) {
                $table->dropColumn('tempat_lahir');
            }
        });
    }
};
