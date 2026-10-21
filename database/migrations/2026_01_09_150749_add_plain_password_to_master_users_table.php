<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('master_users', function (Blueprint $table) {
            if (!Schema::hasColumn('master_users', 'plain_password')) {
                // Menambahkan kolom plain_password setelah kolom password
                $table->string('plain_password')->nullable()->after('password');
            }
        });
    }

    public function down(): void
    {
        Schema::table('master_users', function (Blueprint $table) {
            if (Schema::hasColumn('master_users', 'plain_password')) {
                $table->dropColumn('plain_password');
            }
        });
    }
};