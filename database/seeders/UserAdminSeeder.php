<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UserAdminSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $admins = [
            [
                'id' => 1,
                'name' => 'Admin Akademik',
                'email' => 'akademik@ubpkarawang.ac.id',
                'email_verified_at' => $now,
                'password' => Hash::make('Pmb2025'),
                'created_at' => $now,
                'updated_at' => $now,
                'phone' => '081234567891',
                'bio' => 'Admin bagian akademik PMB UBPKarawang',
                'country' => 'Indonesia',
                'city' => 'Karawang',
                'role' => 'admin_akademik',
                // Kolom di bawah ini dihapus karena menyebabkan error 'Column Not Found'
                // 'gelombang' => null, 
                // 'promo_code_used' => null,
                // ... dst
            ],
            [
                'id' => 2,
                'name' => 'Admin Farmasi',
                'email' => 'farmasi@ubpkarawang.ac.id',
                'email_verified_at' => $now,
                'password' => Hash::make('Pmb2025'),
                'created_at' => $now,
                'updated_at' => $now,
                'phone' => '081234567892',
                'bio' => 'Admin bagian farmasi PMB UBPKarawang',
                'country' => 'Indonesia',
                'city' => 'Karawang',
                'role' => 'admin_farmasi',
            ],
        ];

        foreach ($admins as $admin) {
            $existingUser = DB::table('users')
                ->where('email', $admin['email'])
                ->orWhere('id', $admin['id'])
                ->first();
            
            if (!$existingUser) {
                DB::table('users')->insert($admin);
                $this->command->info("User {$admin['name']} (ID: {$admin['id']}) berhasil dibuat.");
            } else {
                $this->command->warn("User dengan ID {$admin['id']} atau Email {$admin['email']} sudah ada.");
            }
        }
    }
}