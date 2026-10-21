<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TestController extends Controller
{
    private $moodleUrl = 'https://elearning2.ubpkarawang.ac.id/webservice/rest/server.php';
    private $moodleToken = '9d812708c38116dbbe03864ccb860b52';

    /**
     * Display test page for retrieving program study data
     */
    public function testProdi()
    {
        return view('admin.test-prodi');
    }

    /**
     * Test function to get user attempts and prodi data
     */
    public function getProdiData(Request $request)
    {
        $request->validate([
            'id_pendaftaran' => 'required|integer'
        ]);

        $idPendaftaran = $request->id_pendaftaran;

        try {
            // Step 1: Get no_ujian data
            $noUjian = \DB::table('no_ujian')
                        ->where('id_pendaftar', $idPendaftaran)
                        ->first();

            if (!$noUjian) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data no ujian tidak ditemukan'
                ], 404);
            }

            if (empty($noUjian->id_elearning)) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID E-learning tidak ditemukan'
                ], 404);
            }

            // Step 2: Get user attempts from Moodle
            $moodleResponse = $this->getUserAttempts($noUjian->id_elearning);

            if (isset($moodleResponse['exception'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error dari Moodle: ' . ($moodleResponse['message'] ?? 'Unknown error'),
                    'error_code' => $moodleResponse['errorcode'] ?? 'unknown'
                ], 400);
            }

            // Step 3: Get pendaftaran data for prodi information
            $pendaftaran = \DB::table('pendaftaran')
                            ->where('id', $idPendaftaran)
                            ->first();

            if (!$pendaftaran) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pendaftaran tidak ditemukan'
                ], 404);
            }

            // Step 4: Process the data
            $hasil = $moodleResponse['attempts'][0]['sumgrades'] ?? 0;
            
            // Get prodi data dengan nama dari database
            $prodiData = $this->getProdiInformation($pendaftaran);

            return response()->json([
                'success' => true,
                'data' => [
                    'id_pendaftaran' => $idPendaftaran,
                    'id_elearning' => $noUjian->id_elearning,
                    'nilai_tes' => $hasil,
                    'prodi_pilihan' => $prodiData,
                    'attempt_data' => $moodleResponse['attempts'][0] ?? null,
                    'status' => $this->determineStatus($hasil, $prodiData, $idPendaftaran)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user attempts from Moodle
     */
    private function getUserAttempts($idElearning)
    {
        $params = [
            'wstoken' => $this->moodleToken,
            'wsfunction' => 'mod_quiz_get_user_attempts',
            'moodlewsrestformat' => 'json',
            'quizid' => 1197,
            'userid' => $idElearning
        ];

        $response = Http::asForm()->post($this->moodleUrl, $params);
        
        return $response->json();
    }

    /**
     * Get program study information dengan nama dari database
     */
    private function getProdiInformation($pendaftaran)
    {
        // Cari nama prodi dari database berdasarkan kode
        $prodiPilihan1 = $this->getNamaProdiFromDatabase($pendaftaran->programstudi_dipilih);
        $prodiPilihan2 = $this->getNamaProdiFromDatabase($pendaftaran->programstudi_dipilihdua);

        return [
            'pilihan_1' => [
                'kode' => $pendaftaran->programstudi_dipilih,
                'nama' => $prodiPilihan1['nama'] ?? 'Tidak Diketahui',
                'jenjang' => $prodiPilihan1['jenjang'] ?? '-',
                'batas_nilai' => $this->getNilaiBatas($pendaftaran->programstudi_dipilih)
            ],
            'pilihan_2' => [
                'kode' => $pendaftaran->programstudi_dipilihdua,
                'nama' => $prodiPilihan2['nama'] ?? 'Tidak Diketahui',
                'jenjang' => $prodiPilihan2['jenjang'] ?? '-',
                'batas_nilai' => $this->getNilaiBatas($pendaftaran->programstudi_dipilihdua)
            ]
        ];
    }

    /**
     * Get nama prodi dari database berdasarkan kode
     */
    private function getNamaProdiFromDatabase($kodeProdi)
    {
        // Coba cari di tabel prodi (sesuaikan dengan struktur database Anda)
        $prodi = \DB::table('prodi')
                    ->where('kode_prodi', $kodeProdi)
                    ->orWhere('id_prodi', $kodeProdi)
                    ->first();

        if ($prodi) {
            return [
                'nama' => $prodi->nama_prodi ?? $prodi->nama ?? 'Tidak Diketahui',
                'jenjang' => $prodi->jenjang ?? $prodi->degree ?? '-'
            ];
        }

        // Jika tidak ditemukan di tabel prodi, coba tabel lain
        $programStudi = \DB::table('program_studi')
                          ->where('kode', $kodeProdi)
                          ->orWhere('id', $kodeProdi)
                          ->first();

        if ($programStudi) {
            return [
                'nama' => $programStudi->nama_prodi ?? $programStudi->nama ?? 'Tidak Diketahui',
                'jenjang' => $programStudi->jenjang ?? $programStudi->degree ?? '-'
            ];
        }

        // Jika masih tidak ditemukan, coba tabel jurusan
        $jurusan = \DB::table('jurusan')
                     ->where('kode_jurusan', $kodeProdi)
                     ->orWhere('kode', $kodeProdi)
                     ->first();

        if ($jurusan) {
            return [
                'nama' => $jurusan->nama_jurusan ?? $jurusan->nama ?? 'Tidak Diketahui',
                'jenjang' => $jurusan->jenjang ?? '-'
            ];
        }

        // Default fallback
        return [
            'nama' => 'Tidak Diketahui',
            'jenjang' => '-'
        ];
    }

    /**
     * Get nilai batas untuk prodi
     */
    private function getNilaiBatas($kodeProdi)
    {
        $nilaiBatas = [
            '21201' => 17,
            '26201' => 17,
            '48201' => 1,
            '55201' => 17,
            '57201' => 17,
            '61201' => 20,
            '62201' => 17,
            '73201' => 20,
            '74201' => 17,
            '86206' => 17,
            '87205' => 5,
            '86230' => 5,
        ];

        return $nilaiBatas[$kodeProdi] ?? 0;
    }

    /**
     * Determine status based on score and prodi criteria
     */
    private function determineStatus($nilai, $prodiData, $idPendaftaran)
    {
        $p1Batas = $prodiData['pilihan_1']['batas_nilai'];
        $p2Batas = $prodiData['pilihan_2']['batas_nilai'];

        if ($nilai > $p1Batas) {
            return [
                'status' => 'LULUS',
                'pilihan' => 'Pilihan 1',
                'prodi_kode' => $prodiData['pilihan_1']['kode'],
                'prodi_nama' => $prodiData['pilihan_1']['nama'],
                'prodi_jenjang' => $prodiData['pilihan_1']['jenjang'],
                'batas_nilai' => $p1Batas,
                'nilai_anda' => $nilai
            ];
        } elseif ($nilai > $p2Batas) {
            return [
                'status' => 'LULUS', 
                'pilihan' => 'Pilihan 2',
                'prodi_kode' => $prodiData['pilihan_2']['kode'],
                'prodi_nama' => $prodiData['pilihan_2']['nama'],
                'prodi_jenjang' => $prodiData['pilihan_2']['jenjang'],
                'batas_nilai' => $p2Batas,
                'nilai_anda' => $nilai
            ];
        } else {
            return [
                'status' => 'TIDAK LULUS',
                'pilihan' => '-',
                'prodi_kode' => '-',
                'prodi_nama' => '-',
                'batas_nilai_p1' => $p1Batas,
                'batas_nilai_p2' => $p2Batas,
                'nilai_anda' => $nilai,
                'keterangan' => 'Nilai tidak memenuhi batas minimum kedua pilihan'
            ];
        }
    }

    /**
     * Test Moodle connection
     */
    public function testMoodleConnection()
    {
        try {
            $params = [
                'wstoken' => $this->moodleToken,
                'wsfunction' => 'core_webservice_get_site_info',
                'moodlewsrestformat' => 'json'
            ];

            $response = Http::asForm()->post($this->moodleUrl, $params);
            $data = $response->json();

            if (isset($data['exception'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Koneksi Moodle gagal: ' . ($data['message'] ?? 'Unknown error')
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Koneksi Moodle berhasil',
                'data' => [
                    'sitename' => $data['sitename'] ?? '',
                    'username' => $data['username'] ?? '',
                    'userid' => $data['userid'] ?? ''
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Koneksi gagal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get list of available prodi codes with names from database
     */
    public function getProdiCodes()
    {
        try {
            // Coba ambil dari tabel prodi
            $prodiList = \DB::table('prodi')
                           ->select('kode_prodi as kode', 'nama_prodi as nama', 'jenjang')
                           ->get();

            if ($prodiList->isEmpty()) {
                // Jika tidak ada, coba dari tabel program_studi
                $prodiList = \DB::table('program_studi')
                               ->select('kode', 'nama_prodi as nama', 'jenjang')
                               ->get();
            }

            if ($prodiList->isEmpty()) {
                // Jika masih tidak ada, coba dari tabel jurusan
                $prodiList = \DB::table('jurusan')
                               ->select('kode_jurusan as kode', 'nama_jurusan as nama', 'jenjang')
                               ->get();
            }

            $formattedProdi = [];
            foreach ($prodiList as $prodi) {
                $formattedProdi[$prodi->kode] = [
                    'nama' => $prodi->nama,
                    'jenjang' => $prodi->jenjang ?? '-'
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $formattedProdi
            ]);

        } catch (\Exception $e) {
            // Fallback ke hardcoded list jika terjadi error
            $prodiCodes = [
                '21201' => ['nama' => 'Teknik Informatika', 'jenjang' => 'S1'],
                '26201' => ['nama' => 'Sistem Informasi', 'jenjang' => 'S1'],
                '48201' => ['nama' => 'Manajemen', 'jenjang' => 'S1'],
                '55201' => ['nama' => 'Akuntansi', 'jenjang' => 'S1'],
                '57201' => ['nama' => 'Ilmu Hukum', 'jenjang' => 'S1'],
                '61201' => ['nama' => 'Ilmu Komunikasi', 'jenjang' => 'S1'],
                '62201' => ['nama' => 'Administrasi Publik', 'jenjang' => 'S1'],
                '73201' => ['nama' => 'Desain Komunikasi Visual', 'jenjang' => 'S1'],
                '74201' => ['nama' => 'Perhotelan', 'jenjang' => 'S1'],
                '86206' => ['nama' => 'Teknik Elektro', 'jenjang' => 'S1'],
                '87205' => ['nama' => 'Agribisnis', 'jenjang' => 'S1'],
                '86230' => ['nama' => 'Teknik Mesin', 'jenjang' => 'S1']
            ];

            return response()->json([
                'success' => true,
                'data' => $prodiCodes,
                'note' => 'Menggunakan data fallback'
            ]);
        }
    }

    /**
     * Search prodi by code (endpoint untuk pencarian individual)
     */
    public function searchProdi($kodeProdi)
    {
        try {
            $prodiData = $this->getNamaProdiFromDatabase($kodeProdi);

            return response()->json([
                'success' => true,
                'data' => [
                    'kode' => $kodeProdi,
                    'nama' => $prodiData['nama'],
                    'jenjang' => $prodiData['jenjang']
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mencari prodi: ' . $e->getMessage()
            ], 500);
        }
    }
}