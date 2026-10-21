<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeactivationColumnsToMasterUsersTable extends Migration
{
    public function up()
    {
        Schema::table('master_users', function (Blueprint $table) {
            $table->timestamp('deactivated_at')->nullable()->after('va_expired_at');
            $table->unsignedBigInteger('deactivated_by')->nullable()->after('deactivated_at');
            $table->text('deactivation_reason')->nullable()->after('deactivated_by');
            
            // Index
            $table->index('deactivated_at');
            $table->index('deactivated_by');
        });
    }

    public function down()
    {
        Schema::table('master_users', function (Blueprint $table) {
            $table->dropColumn(['deactivated_at', 'deactivated_by', 'deactivation_reason']);
        });
    }
}