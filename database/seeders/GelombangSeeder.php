<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GelombangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('gelombang')->insert([
            [
                'nama_gelombang'   => 'Gelombang 1',
                'tanggal_mulai'    => '2026-05-18',
                'tanggal_selesai'  => '2026-06-30',
                'created_at'       => now(),
            ],
            [
                'nama_gelombang'   => 'Gelombang 2',
                'tanggal_mulai'    => '2026-06-08',
                'tanggal_selesai'  => '2026-06-30',
                'created_at'       => now(),
            ],
        ]);
    }
}