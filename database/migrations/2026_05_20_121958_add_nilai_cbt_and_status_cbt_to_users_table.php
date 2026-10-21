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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'profesi')) {
                $table->string('profesi')->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'nilai_cbt')) {
                $table->integer('nilai_cbt')->nullable()->after('profesi');
            }
            if (!Schema::hasColumn('users', 'status_cbt')) {
                $table->string('status_cbt', 20)->nullable()->after('nilai_cbt');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('users', 'profesi')) $columns[] = 'profesi';
            if (Schema::hasColumn('users', 'nilai_cbt')) $columns[] = 'nilai_cbt';
            if (Schema::hasColumn('users', 'status_cbt')) $columns[] = 'status_cbt';
            
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};