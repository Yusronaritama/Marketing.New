<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\JurusanPassingGrade;

class JurusanPassingGradeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jurusanData = [
            [
                'kode_jurusan' => '61201',
                'nama_jurusan' => 'Manajemen',
                'passing_grade' => 5.00,
                'kuota' => 100,
                'status' => 'active'
            ],
        ];

        foreach ($jurusanData as $jurusan) {
            JurusanPassingGrade::updateOrCreate(
                ['kode_jurusan' => $jurusan['kode_jurusan']],
                $jurusan
            );
        }

        $this->command->info('✅ Data jurusan dan passing grade berhasil disimpan ke database!');
        $this->command->info('📊 Total jurusan: ' . count($jurusanData));
    }
}