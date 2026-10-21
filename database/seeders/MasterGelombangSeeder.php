<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MasterGelombangSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $data = [
            [
                'nama_gelombang' => 'Gelombang 1',
                'angka_gelombang' => 1,
                'is_active' => 0, // Tidak aktif
                'tanggal_mulai' => '2025-10-01',
                'tanggal_selesai' => '2025-11-30',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nama_gelombang' => 'Gelombang 2',
                'angka_gelombang' => 2,
                'is_active' => 0,
                'tanggal_mulai' => '2025-12-01',
                'tanggal_selesai' => '2026-01-31',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nama_gelombang' => 'Gelombang 3',
                'angka_gelombang' => 3,
                'is_active' => 0,
                'tanggal_mulai' => '2026-02-01',
                'tanggal_selesai' => '2026-03-31',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nama_gelombang' => 'Gelombang 4',
                'angka_gelombang' => 4,
                'is_active' => 0,
                'tanggal_mulai' => '2026-04-01',
                'tanggal_selesai' => '2026-04-30',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nama_gelombang' => 'Gelombang 5',
                'angka_gelombang' => 5,
                'is_active' => 1, // <--- INI YANG AKTIF
                'tanggal_mulai' => '2026-05-01',
                'tanggal_selesai' => '2026-06-30',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('master_gelombangs')->insert($data);
    }
}