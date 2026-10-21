<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('biodatas', function (Blueprint $table) {
            if (!Schema::hasColumn('biodatas', 'provinsi_asal')) {
                $table->string('provinsi_asal')->nullable()->after('daerah_asal');
            }
            if (!Schema::hasColumn('biodatas', 'kota_kabupaten_asal')) {
                $table->string('kota_kabupaten_asal')->nullable()->after('provinsi_asal');
            }
        });
    }

    public function down(): void
    {
        Schema::table('biodatas', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('biodatas', 'provinsi_asal')) {
                $columns[] = 'provinsi_asal';
            }
            if (Schema::hasColumn('biodatas', 'kota_kabupaten_asal')) {
                $columns[] = 'kota_kabupaten_asal';
            }
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
