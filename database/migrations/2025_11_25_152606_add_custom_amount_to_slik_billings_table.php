<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   // Di file migration
public function up()
{
    Schema::table('slik_billings', function (Blueprint $table) {
        if (!Schema::hasColumn('slik_billings', 'is_custom_amount')) {
            $table->boolean('is_custom_amount')->default(false)->after('amount');
        }
        if (!Schema::hasColumn('slik_billings', 'standard_amount')) {
            $table->decimal('standard_amount', 15, 2)->nullable()->after('is_custom_amount');
        }
    });
}

public function down()
{
    Schema::table('slik_billings', function (Blueprint $table) {
        $columns = [];
        if (Schema::hasColumn('slik_billings', 'is_custom_amount')) $columns[] = 'is_custom_amount';
        if (Schema::hasColumn('slik_billings', 'standard_amount')) $columns[] = 'standard_amount';
        
        if (!empty($columns)) {
            $table->dropColumn($columns);
        }
    });
}
};
