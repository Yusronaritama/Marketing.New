<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('bni_callback_logs', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
            $table->string('deletion_note')->nullable()->after('deleted_at');
        });
    }

    public function down()
    {
        Schema::table('bni_callback_logs', function (Blueprint $table) {
            $table->dropColumn(['deleted_at', 'deletion_note']);
        });
    }
};