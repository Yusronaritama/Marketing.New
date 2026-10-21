<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'id' => 166,
                'name' => 'percobaan',
                'email' => 'if23.muhammadnurfadilah@mhs.ubpkarawang.ac.id',
                'password' => Hash::make('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 164,
                'name' => 'Halo',
                'email' => 'halo@gmail.com',
                'password' => Hash::make('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($users as $user) {
            DB::table('users')->updateOrInsert(
                ['id' => $user['id']],
                $user
            );
        }
    }
}