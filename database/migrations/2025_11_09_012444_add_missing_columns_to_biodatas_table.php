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
        Schema::table('biodatas', function (Blueprint $table) {
            if (!Schema::hasColumn('biodatas', 'alamat')) {
                $table->text('alamat')->nullable()->after('nama_ayah');
            }
            if (!Schema::hasColumn('biodatas', 'jenjang_pendidikan')) {
                $table->string('jenjang_pendidikan')->default('sma')->after('alamat');
            }
            if (!Schema::hasColumn('biodatas', 'is_verified')) {
                $table->boolean('is_verified')->default(false)->after('tempat_tinggal');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('biodatas', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('biodatas', 'alamat')) $columns[] = 'alamat';
            if (Schema::hasColumn('biodatas', 'jenjang_pendidikan')) $columns[] = 'jenjang_pendidikan';
            if (Schema::hasColumn('biodatas', 'is_verified')) $columns[] = 'is_verified';
            
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};