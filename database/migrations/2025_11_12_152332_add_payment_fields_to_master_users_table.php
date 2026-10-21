<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaymentFieldsToMasterUsersTable extends Migration
{
    public function up()
    {
        Schema::table('master_users', function (Blueprint $table) {
            $table->timestamp('paid_at')->nullable()->after('va_expired_at');
            $table->string('payment_method', 50)->nullable()->after('paid_at');
        });
    }

    public function down()
    {
        Schema::table('master_users', function (Blueprint $table) {
            $table->dropColumn(['paid_at', 'payment_method']);
        });
    }
}