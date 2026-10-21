<?php 

namespace Database\Seeders; 

use Illuminate\Database\Seeder; 
use Illuminate\Support\Facades\DB; 
use Carbon\Carbon; 

class PendaftarGetNimSeeder extends Seeder 
{ 
    /** * Run the database seeds. 
     * * @return void 
     */ 
    public function run() 
    { 
        $pendaftarData = [ 
            [ 
                'nim' => '25416261114053', 
                'nama_mahasiswa' => 'Kusnoto', 
                'email_mhs' => 'mm25.kusnoto@mhs.ubpkarawang.ac.id', 
                'password_sementara' => 'ubp2026', 
                'kode_prodi' => '61114', // Tambahkan kode_prodi di sini
                'created_at' => Carbon::now(), 
                'updated_at' => Carbon::now(), 
            ], 
            [ 
                'nim' => '25416261114052', 
                'nama_mahasiswa' => 'Nia Rosdiana', 
                'email_mhs' => 'mm25.niarosdiana@mhs.ubpkarawang.ac.id', 
                'password_sementara' => 'ubp2026', 
                'kode_prodi' => '61114', // Tambahkan kode_prodi di sini
                'created_at' => Carbon::now(), 
                'updated_at' => Carbon::now(), 
            ], 
        ]; 

        // Insert data ke tabel pendaftar_get_nim 
        foreach ($pendaftarData as $data) { 
            
            $user = DB::table('users')->where('name', $data['nama_mahasiswa'])->first(); 
            $dataMhs = DB::table('data_mahasiswa_finals')->where('nama_mahasiswa', $data['nama_mahasiswa'])->first(); 

            DB::table('pendaftar_get_nim')->insert([ 
                'nim' => $data['nim'], 
                'nama_mahasiswa' => $data['nama_mahasiswa'], 
                'email_mhs' => $data['email_mhs'], 
                'password_sementara' => $data['password_sementara'], 
                
                'kode_prodi' => $data['kode_prodi'], // Masukkan ke dalam proses insert
                
                'user_id' => $user ? $user->id : null, 
                'data_mahasiswa_final_id' => $dataMhs ? $dataMhs->id : null, 
                'created_at' => $data['created_at'], 
                'updated_at' => $data['updated_at'], 
            ]); 
        } 
    } 
}