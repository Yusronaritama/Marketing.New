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
            if (!Schema::hasColumn('biodatas', 'asal_sekolah')) {
                $table->string('asal_sekolah')->nullable()->after('nama_ayah');
            }
            if (!Schema::hasColumn('biodatas', 'jurusan_sekolah')) {
                $table->string('jurusan_sekolah')->nullable()->after('asal_sekolah');
            }
            if (!Schema::hasColumn('biodatas', 'agama')) {
                $table->string('agama')->nullable()->after('jurusan_sekolah');
            }
            if (!Schema::hasColumn('biodatas', 'tempat_tinggal')) {
                $table->text('tempat_tinggal')->nullable()->after('agama');
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
            if (Schema::hasColumn('biodatas', 'asal_sekolah')) $columns[] = 'asal_sekolah';
            if (Schema::hasColumn('biodatas', 'jurusan_sekolah')) $columns[] = 'jurusan_sekolah';
            if (Schema::hasColumn('biodatas', 'agama')) $columns[] = 'agama';
            if (Schema::hasColumn('biodatas', 'tempat_tinggal')) $columns[] = 'tempat_tinggal';
            
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};