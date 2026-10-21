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
        Schema::table('posts', function (Blueprint $table) {
            $table->timestamp('publish_start')->nullable()->after('status');
            $table->timestamp('publish_end')->nullable()->after('publish_start');
            $table->enum('schedule_type', ['immediate', 'scheduled'])->default('immediate')->after('publish_end');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['publish_start', 'publish_end', 'schedule_type']);
        });
    }
};