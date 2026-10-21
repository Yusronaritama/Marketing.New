<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Str;

class AdminProdiSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        
        // 1. Ambil semua data program studi dari database
        $programStudis = DB::table('program_studi')->get();

        foreach ($programStudis as $prodi) {
            // Format slug nama prodi untuk email (contoh: teknik-informatika)
            $slugProdi = Str::slug($prodi->nama_prodi, '');
            
            // 2. Definisi Data Admin Prodi
            $adminData = [
                'name' => 'Admin ' . $prodi->nama_prodi,
                'email' => $slugProdi . '@ubpkarawang.ac.id',
                'email_verified_at' => $now,
                'password' => Hash::make('Pmb2025'), // Password default
                'phone' => '08' . rand(111111111, 999999999), // Dummy phone atau sesuaikan
                'bio' => 'Admin Program Studi ' . $prodi->nama_prodi . ' PMB UBPKarawang',
                'role' => 'admin_' . Str::slug($prodi->nama_prodi, '_'), // Hasil: admin_teknik_informatika
                'created_at' => $now,
                'updated_at' => $now,
            ];

            // 3. Cek apakah user sudah ada berdasarkan email
            $existingUser = DB::table('users')->where('email', $adminData['email'])->first();

            if (!$existingUser) {
                DB::table('users')->insert($adminData);
                $this->command->info("User Admin {$prodi->nama_prodi} berhasil ditambahkan.");
            } else {
                // Update jika sudah ada (opsional, agar data tetap sinkron)
                DB::table('users')
                    ->where('email', $adminData['email'])
                    ->update($adminData);
                $this->command->info("User Admin {$prodi->nama_prodi} berhasil diupdate.");
            }
        }
    }
}