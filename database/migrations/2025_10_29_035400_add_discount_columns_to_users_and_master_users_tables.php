<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Untuk tabel users
        Schema::table('users', function (Blueprint $table) {
            $table->string('promo_code_used')->nullable()->after('role');
            $table->decimal('original_amount', 15, 2)->nullable()->after('va_amount');
            $table->decimal('discount_amount', 15, 2)->default(0)->after('original_amount');
        });

        // Untuk tabel master_users
        Schema::table('master_users', function (Blueprint $table) {
            $table->string('promo_code_used')->nullable()->after('va_status');
            $table->decimal('original_amount', 15, 2)->nullable()->after('va_amount');
            $table->decimal('discount_amount', 15, 2)->default(0)->after('original_amount');
            $table->integer('discount_percentage')->default(0)->after('discount_amount');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['promo_code_used', 'original_amount', 'discount_amount']);
        });

        Schema::table('master_users', function (Blueprint $table) {
            $table->dropColumn(['promo_code_used', 'original_amount', 'discount_amount', 'discount_percentage']);
        });
    }
};