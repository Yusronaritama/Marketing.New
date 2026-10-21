<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('jalur_pendaftarans', function (Blueprint $table) {
            $table->id(); // Ini otomatis membuat bigint UNSIGNED AUTO_INCREMENT primary key
            $table->string('nama_jalur')->unique(); // varchar(255) dan UNIQUE key
            $table->timestamps(); // Ini otomatis membuat created_at dan updated_at
        });

        // Mengisi data awal (Seeding) sesuai dump SQL Anda
        DB::table('jalur_pendaftarans')->insert([
            ['nama_jalur' => 'Jalur Reguler', 'created_at' => now(), 'updated_at' => now()],
            ['nama_jalur' => 'Jalur Ekstensi', 'created_at' => now(), 'updated_at' => now()],
            ['nama_jalur' => 'Pindahan', 'created_at' => now(), 'updated_at' => now()],
            ['nama_jalur' => 'Jalur S2 Manajemen', 'created_at' => now(), 'updated_at' => now()],
            ['nama_jalur' => 'Jalur RPL', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jalur_pendaftarans');
    }
};