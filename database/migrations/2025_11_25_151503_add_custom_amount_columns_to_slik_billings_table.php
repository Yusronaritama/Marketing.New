<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('slik_billings', function (Blueprint $table) {
            $table->boolean('is_custom_amount')->default(false)->after('amount');
            $table->decimal('standard_amount', 15, 2)->nullable()->after('is_custom_amount');
        });
    }

    public function down()
    {
        Schema::table('slik_billings', function (Blueprint $table) {
            $table->dropColumn(['is_custom_amount', 'standard_amount']);
        });
    }
};