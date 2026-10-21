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
        if (!Schema::hasTable('master_periode')) {
            Schema::create('master_periode', function (Blueprint $table) {
                $table->id();
                $table->string('kode', 10)->unique();
                $table->string('nama', 100);
                $table->string('tahun', 4)->nullable();
                $table->string('semester', 20)->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });

            $now = now();
            DB::table('master_periode')->insert([
                ['kode' => '20251', 'nama' => '20251 (Semester Ganjil 2025)', 'tahun' => '2025', 'semester' => 'Ganjil', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['kode' => '20261', 'nama' => '20261 (Semester Ganjil 2026)', 'tahun' => '2026', 'semester' => 'Ganjil', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['kode' => '20262', 'nama' => '20262 (Semester Genap 2026)',  'tahun' => '2026', 'semester' => 'Genap',  'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['kode' => '20271', 'nama' => '20271 (Semester Ganjil 2027)', 'tahun' => '2027', 'semester' => 'Ganjil', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['kode' => '20272', 'nama' => '20272 (Semester Genap 2027)',  'tahun' => '2027', 'semester' => 'Genap',  'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_periode');
    }
};
