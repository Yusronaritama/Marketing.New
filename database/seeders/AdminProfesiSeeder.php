<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AdminProfesiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Cek dulu apakah admin profesi sudah ada agar tidak duplikat jika seeder dijalankan berulang
        $exists = DB::table('users')->where('email', 'admin.profesi@ubpkarawang.ac.id')->first();

        if (!$exists) {
            DB::table('users')->insert([
                'name'              => 'admin_profesi',
                'email'             => 'admin.profesi@ubpkarawang.ac.id',
                'nim'               => null,
                'profesi'           => null, // Admin tidak perlu kolom profesi ini, karena ini untuk pendaftar
                'email_verified_at' => Carbon::now(),
                'password'          => Hash::make('admin_profesi123'), // Password default: password123
                'remember_token'    => null,
                'created_at'        => Carbon::now(),
                'updated_at'        => Carbon::now(),
                'phone'             => '081234567899', // Nomor dummy
                'bio'               => 'Admin khusus bagian Pendaftaran Profesi Apoteker UBPKarawang',
                'country'           => 'Indonesia',
                'city'              => 'Karawang',
                'postal_code'       => null,
                'role'              => 'admin_profesi', // ROLE PENTING!
                'promo_code_used'   => null,
                'va_number'         => null,
                'va_amount'         => null,
                'original_amount'   => null,
                'discount_amount'   => 0.00,
                'va_expired_at'     => null,
                'va_status'         => null,
                'wa_reminder_sent'  => 0
            ]);

            $this->command->info('Akun Admin Profesi berhasil dibuat!');
            $this->command->info('Email: admin.profesi@ubpkarawang.ac.id | Password: password123');
        } else {
            $this->command->warn('Akun Admin Profesi sudah ada di database.');
        }
    }
}