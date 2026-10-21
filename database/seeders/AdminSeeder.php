<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $admins = [
            [
                'id' => 198,
                'name' => 'Admin Akademik',
                'email' => 'akademik@ubpkarawang.ac.id',
                'password' => Hash::make('admin123'), // Ganti dengan password yang diinginkan
                'phone' => '081234567891',
                'bio' => 'Admin bagian akademik PMB UBPKarawang',
                'country' => 'Indonesia',
                'city' => 'Karawang',
                'postal_code' => '-',
                'role' => 'admin_akademik',
                'wa_reminder_sent' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 199,
                'name' => 'Admin Farmasi',
                'email' => 'farmasi@ubpkarawang.ac.id',
                'password' => Hash::make('admin123'), // Ganti dengan password yang diinginkan
                'phone' => '081234567892',
                'bio' => 'Admin bagian farmasi PMB UBPKarawang',
                'country' => 'Indonesia',
                'city' => 'Karawang',
                'postal_code' => '-',
                'role' => 'admin_farmasi',
                'wa_reminder_sent' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($admins as $admin) {
            User::updateOrCreate(['email' => $admin['email']], $admin);
        }
    }
}