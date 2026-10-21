<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('program_studi', function (Blueprint $table) {
            $table->id();
            $table->string('kode_prodi', 10)->unique(); // Kode prodi: 21201, 26201, dll.
            $table->string('nama_prodi'); // Nama program studi
            $table->string('jenjang', 20); // Jenjang: D3, S1, S2, dll.
            $table->string('fakultas')->nullable(); // Fakultas
            $table->decimal('passing_grade', 5, 2)->default(0); // Nilai passing grade
            $table->integer('kuota')->nullable(); // Kuota penerimaan
            $table->integer('daya_tampung')->nullable(); // Daya tampung
            $table->text('deskripsi')->nullable(); // Deskripsi program studi
            $table->string('akreditasi', 10)->nullable(); // Akreditasi: A, B, C
            $table->integer('tahun_berdiri')->nullable(); // Tahun berdiri
            $table->string('email_prodi')->nullable(); // Email program studi
            $table->string('telepon_prodi', 20)->nullable(); // Telepon program studi
            $table->string('website_prodi')->nullable(); // Website program studi
            $table->string('ketua_prodi')->nullable(); // Nama ketua program studi
            $table->boolean('is_aktif')->default(true); // Status aktif/tidak aktif
            $table->integer('urutan_tampil')->default(0); // Urutan tampil (sorting)
            $table->timestamps();

            // Indexes
            $table->index('kode_prodi');
            $table->index('jenjang');
            $table->index('fakultas');
            $table->index('is_aktif');
            $table->index('urutan_tampil');
        });

        // Insert data program studi default
        $this->seedProgramStudi();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_studi');
    }

    /**
     * Seed data program studi default
     */
    private function seedProgramStudi(): void
    {
        $programStudi = [
            // Teknik
            [
                'kode_prodi' => '21201',
                'nama_prodi' => 'Teknik Mesin',
                'jenjang' => 'S1',
                'fakultas' => 'Teknik',
                'passing_grade' => 17,
                'akreditasi' => 'A',
                'is_aktif' => true,
                'urutan_tampil' => 1,
            ],
            [
                'kode_prodi' => '26201',
                'nama_prodi' => 'Teknik Elektro',
                'jenjang' => 'S1',
                'fakultas' => 'Teknik',
                'passing_grade' => 17,
                'akreditasi' => 'A',
                'is_aktif' => true,
                'urutan_tampil' => 2,
            ],
            [
                'kode_prodi' => '61201',
                'nama_prodi' => 'Teknik Informatika',
                'jenjang' => 'S1',
                'fakultas' => 'Teknik',
                'passing_grade' => 20,
                'akreditasi' => 'A',
                'is_aktif' => true,
                'urutan_tampil' => 3,
            ],
            [
                'kode_prodi' => '62201',
                'nama_prodi' => 'Sistem Informasi',
                'jenjang' => 'S1',
                'fakultas' => 'Teknik',
                'passing_grade' => 17,
                'akreditasi' => 'A',
                'is_aktif' => true,
                'urutan_tampil' => 4,
            ],

            // Ekonomi dan Bisnis
            [
                'kode_prodi' => '55201',
                'nama_prodi' => 'Akuntansi',
                'jenjang' => 'S1',
                'fakultas' => 'Ekonomi dan Bisnis',
                'passing_grade' => 17,
                'akreditasi' => 'A',
                'is_aktif' => true,
                'urutan_tampil' => 5,
            ],
            [
                'kode_prodi' => '57201',
                'nama_prodi' => 'Manajemen',
                'jenjang' => 'S1',
                'fakultas' => 'Ekonomi dan Bisnis',
                'passing_grade' => 17,
                'akreditasi' => 'A',
                'is_aktif' => true,
                'urutan_tampil' => 6,
            ],

            // Kesehatan
            [
                'kode_prodi' => '48201',
                'nama_prodi' => 'Administrasi Rumah Sakit',
                'jenjang' => 'D3',
                'fakultas' => 'Ilmu Kesehatan',
                'passing_grade' => 1,
                'akreditasi' => 'B',
                'is_aktif' => true,
                'urutan_tampil' => 7,
            ],

            // Hukum dan Sosial
            [
                'kode_prodi' => '73201',
                'nama_prodi' => 'Ilmu Hukum',
                'jenjang' => 'S1',
                'fakultas' => 'Hukum',
                'passing_grade' => 20,
                'akreditasi' => 'A',
                'is_aktif' => true,
                'urutan_tampil' => 8,
            ],
            [
                'kode_prodi' => '74201',
                'nama_prodi' => 'Ilmu Administrasi Negara',
                'jenjang' => 'S1',
                'fakultas' => 'Ilmu Sosial dan Politik',
                'passing_grade' => 17,
                'akreditasi' => 'B',
                'is_aktif' => true,
                'urutan_tampil' => 9,
            ],

            // Pendidikan
            [
                'kode_prodi' => '86206',
                'nama_prodi' => 'Pendidikan Bahasa Inggris',
                'jenjang' => 'S1',
                'fakultas' => 'Keguruan dan Ilmu Pendidikan',
                'passing_grade' => 17,
                'akreditasi' => 'A',
                'is_aktif' => true,
                'urutan_tampil' => 10,
            ],
            [
                'kode_prodi' => '87205',
                'nama_prodi' => 'Bimbingan dan Konseling',
                'jenjang' => 'S1',
                'fakultas' => 'Keguruan dan Ilmu Pendidikan',
                'passing_grade' => 5,
                'akreditasi' => 'B',
                'is_aktif' => true,
                'urutan_tampil' => 11,
            ],
            [
                'kode_prodi' => '86230',
                'nama_prodi' => 'Pendidikan Guru Sekolah Dasar',
                'jenjang' => 'S1',
                'fakultas' => 'Keguruan dan Ilmu Pendidikan',
                'passing_grade' => 5,
                'akreditasi' => 'A',
                'is_aktif' => true,
                'urutan_tampil' => 12,
            ],
        ];

        foreach ($programStudi as $prodi) {
            DB::table('program_studi')->insert(array_merge($prodi, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
};