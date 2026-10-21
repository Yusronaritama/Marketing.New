<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AdminKeuanganSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $adminKeuangan = [
            'name' => 'Admin Keuangan',
            'email' => 'keuangan@ubpkarawang.ac.id',
            'email_verified_at' => $now,
            'password' => Hash::make('Pmb2025'),
            'remember_token' => null,
            'created_at' => $now,
            'updated_at' => $now,
            'phone' => '085759995505',
            'bio' => 'Admin bagian keuangan PMB UBPKarawang',
            'role' => 'admin_keuangan',
            // Hanya kolom yang benar-benar ada di tabel users
        ];

        // Hapus kolom yang tidak ada di tabel users
        $columnsToRemove = ['country', 'city', 'postal_code', 'gelombang', 'promo_code_used', 'va_number', 'va_amount', 'original_amount', 'discount_amount', 'va_expired_at', 'va_status'];
        
        foreach ($columnsToRemove as $column) {
            if (array_key_exists($column, $adminKeuangan)) {
                unset($adminKeuangan[$column]);
            }
        }

        // Cek apakah user sudah ada
        $existingUser = DB::table('users')->where('email', $adminKeuangan['email'])->first();
        
        if (!$existingUser) {
            DB::table('users')->insert($adminKeuangan);
            $this->command->info("Admin Keuangan berhasil ditambahkan.");
        } else {
            // Update user yang sudah ada
            DB::table('users')
                ->where('email', $adminKeuangan['email'])
                ->update($adminKeuangan);
            $this->command->info("Admin Keuangan berhasil diupdate.");
        }
    }
}