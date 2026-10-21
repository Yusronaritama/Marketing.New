<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    Schema::table('program_studi', function (Blueprint $table) {
        $table->decimal('biaya_pendaftaran', 15, 2)->after('harga_tahun')->default(300000);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('program_studi', function (Blueprint $table) {
            //
        });
    }
};
