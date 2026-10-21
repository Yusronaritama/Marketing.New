<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class add_UserAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
                $now = Carbon::now();

        $admins = [ 
              [
                'name' => 'Admin Keuangan',
                'email' => 'keuangan@ubpkarawang.ac.id',
                'email_verified_at' => $now,
                'password' => Hash::make('Pmb2025'),
                'remember_token' => null,
                'created_at' => $now,
                'updated_at' => $now,
                'phone' => '085759995505',
                'bio' => 'Admin bagian keuangan PMB UBPKarawang',
                'country' => 'Indonesia',
                'city' => 'Karawang',
                'postal_code' => '-',
                'role' => 'admin_keuangan',
                'gelombang' => null,
                'promo_code_used' => null,
                'va_number' => null,
                'va_amount' => null,
                'original_amount' => null,
                'discount_amount' => 0.00,
                'va_expired_at' => null,
                'va_status' => null,
            ],
        ];
    }
}
