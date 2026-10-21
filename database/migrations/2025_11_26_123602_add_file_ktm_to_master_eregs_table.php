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
        // UBAH DARI 'master_eregs' MENJADI 'master_ereg'
        Schema::table('master_ereg', function (Blueprint $table) { 
            // $table->string('file_ktm', 255)->nullable()->after('file_surat_pindah'); 
        });
    }

    public function down(): void
    {
        // UBAH DARI 'master_eregs' MENJADI 'master_ereg'
        Schema::table('master_ereg', function (Blueprint $table) { 
            // $table->dropColumn('file_ktm');
        });
    }
};
