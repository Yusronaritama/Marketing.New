<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('slik_billings', function (Blueprint $table) {
            if (!Schema::hasColumn('slik_billings', 'nama_user')) {
                $table->string('nama_user', 100)->nullable()->after('user_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('slik_billings', function (Blueprint $table) {
            if (Schema::hasColumn('slik_billings', 'nama_user')) {
                $table->dropColumn('nama_user');
            }
        });
    }
};