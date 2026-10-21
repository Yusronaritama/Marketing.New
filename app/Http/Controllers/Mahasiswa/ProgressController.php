<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\MasterEreg;
use App\Models\Biodata;
use App\Models\MasterUser;       
use App\Models\MasterMoodle;


class ProgressController extends Controller
{
    /**
     * Cek kelengkapan data mahasiswa
     */
    public function checkProgress()
    {
        $user = Auth::user();
        
        if (!$user) {
            return $this->getDefaultProgressData();
        }

        $userId = $user->id;
        $biodata = Biodata::where('user_id', $userId)->first();
        $eregData = MasterEreg::where('user_id', $userId)->first();

        // Inisialisasi variabel status
        $isBiodataComplete = false;
        $isDataDiriComplete = false;
        $isJurusanComplete = false;
        $isBerkasComplete = false;
        $isPendaftaranComplete = false;
        $isUjianComplete = false; // 👈 3. TAMBAHKAN VARIABEL UJIAN
        $currentStep = 1; 

        // Step 1: Login
        $currentStep = 1;

        // Cek kelengkapan biodata
        if ($biodata) {
            $isBiodataComplete = $this->checkBiodataCompleteness($biodata);
            $isDataDiriComplete = $this->checkDataDiriCompleteness($biodata);
        }

        // Cek kelengkapan data dari MasterEreg
        if ($eregData) {
            $isJurusanComplete = $this->checkJurusanCompleteness($eregData);
            $isBerkasComplete = $this->checkBerkasCompleteness($eregData);
            $isPendaftaranComplete = $this->checkPendaftaranCompleteness($eregData, $isBiodataComplete);
            
            
            $masterUser = MasterUser::where('user_id', $userId)->first();
            if ($masterUser) {
                // Cek data di master_moodle_users (dari database dump Anda)
                $moodleData = MasterMoodle::where('master_user_id', $masterUser->id)->first();
                if ($moodleData && $moodleData->attempt_count > 0) {
                    $isUjianComplete = true; 
                }
            }
            // ----------------------------------------------------
            
            // 👈 5. PERBARUI PANGGILAN FUNGSI INI
            $currentStep = $this->determineCurrentStep($eregData, $isBiodataComplete, $isPendaftaranComplete, $isUjianComplete);

        } else {
            if ($biodata && $isBiodataComplete) {
                $currentStep = 2;
            }
        }

        return [
            'currentStep' => $currentStep,
            'isBiodataComplete' => $isBiodataComplete,
            'isUjianComplete' => $isUjianComplete, // 👈 6. TAMBAHKAN INI DI RETURN
            'isDataDiriComplete' => $isDataDiriComplete,
            'isJurusanComplete' => $isJurusanComplete,
            'isBerkasComplete' => $isBerkasComplete,
            'isPendaftaranComplete' => $isPendaftaranComplete,
            'biodata' => $biodata,
            'eregData' => $eregData,
        ];
    }

    /**
     * Tentukan currentStep berdasarkan kelengkapan data
     */
    // 👈 7. GANTI TOTAL FUNGSI LAMA DENGAN FUNGSI BARU INI
    /**
     * Tentukan currentStep berdasarkan kelengkapan data (Updated for Step 6)
     */
    private function determineCurrentStep($eregData, $isBiodataComplete, $isPendaftaranComplete, $isUjianComplete)
    {
        // Default adalah Step 1 (Login)
        $currentStep = 1;

        // --- TEMBOK 1: DATA DIRI & BERKAS ---
        if ($isPendaftaranComplete && $isBiodataComplete) {
            $currentStep = 3; // Lolos ke Step 3 (Ujian)
        } else if ($eregData || $isBiodataComplete) {
            $currentStep = 2; // Masih di Step 2 (Pengisian Data)
        }

        // --- TEMBOK 2: UJIAN SELEKSI ---
        // Jika sudah lolos tahap data diri dan sudah melakukan ujian (attempt > 0)
        if ($currentStep >= 3 && $isUjianComplete) {
            $currentStep = 4; // Lolos ke Step 4 (Pembayaran)
        }

        // --- TEMBOK 3: PEMBAYARAN UKT ---
        // Pengecekan status pembayaran lunas di tabel MasterEreg
        $isLunas = (isset($eregData->status_pembayaran) && strtolower($eregData->status_pembayaran) === 'lunas');
        
        if ($currentStep >= 4 && $isLunas) {
            $currentStep = 5; // Lolos ke Step 5 (Data Final)
        }
        
        // --- TEMBOK 4: DATA FINAL & PORTAL (STEP 6) ---
        // Kita cek tabel 'pendaftar_get_nim' untuk memastikan akun mahasiswa sudah digenerate
        $dataAkun = \Illuminate\Support\Facades\DB::table('pendaftar_get_nim')
                    ->where('user_id', Auth::id())
                    ->first();

        $akunSudahAda = ($dataAkun && !empty($dataAkun->nim));

        // Syarat masuk Step 6: 
        // 1. Status di MasterEreg sudah 'completed' (berarti data final sudah diverifikasi admin)
        // 2. Data NIM sudah mendarat di tabel pendaftar_get_nim
        if (isset($eregData->status) && $eregData->status === 'completed') {
            if ($akunSudahAda) {
                $currentStep = 6; // PORTAL TERBUKA
            } else {
                $currentStep = 5; // Stay di Step 5 jika NIM belum ada (antrean Biro Akademik)
            }
        }

        return $currentStep;
    }


    /**
    * Cek kelengkapan data biodata
    */
    private function checkBiodataCompleteness($biodata)
    {
        if (!$biodata) {
            return false;
        }

        $requiredFields = [
            'nama_lengkap', 'alamat', 'tanggal_lahir', 'nama_ibu',
            'nama_ayah', 'jenjang_pendidikan', 'asal_sekolah',
            'jurusan_sekolah', 'agama', 'tempat_tinggal'
        ];

        foreach ($requiredFields as $field) {
            // 👈 8. PERBAIKI BUG 'EMPTY()' TERAKHIR ANDA
            if (is_null($biodata->$field)) { // GANTI DARI 'empty()'
                return false;
            }
        }
        return true;
    }

    /**
    * Cek kelengkapan data diri (field penting)
    */
    private function checkDataDiriCompleteness($biodata)
    {
        if (!$biodata) {
            return false;
        }

        $criticalFields = [
            'nama_lengkap', 'alamat', 'tanggal_lahir',
            'nama_ibu', 'nama_ayah', 'asal_sekolah', 'agama'
        ];

        foreach ($criticalFields as $field) {
            if (is_null($biodata->$field)) { // Anda sudah benar di sini
                return false;
            }
        }
        return true;
    }

    /**
     * Cek kelengkapan pemilihan jurusan
     */
    private function checkJurusanCompleteness($ereg)
    {
        $jurusanFields = ['jurusan1', 'jurusan_pilihan_1', 'program_studi', 'jurusan'];
        foreach ($jurusanFields as $field) {
            if (isset($ereg->$field) && !empty($ereg->$field)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Cek kelengkapan berkas
     */
    private function checkBerkasCompleteness($ereg)
    {
        $berkasFields = ['file_kk', 'file_ijazah', 'berkas_ktp', 'berkas_ijazah', 'berkas_foto'];
        $completedBerkas = 0;
        foreach ($berkasFields as $field) {
            if (isset($ereg->$field) && !empty($ereg->$field)) {
                $completedBerkas++;
            }
        }
        return $completedBerkas >= 2;
    }

    /**
     * Cek kelengkapan pendaftaran
     */
    private function checkPendaftaranCompleteness($ereg, $isBiodataComplete)
    {
        $completionIndicators = [
            'status' => 'completed', 'status_pendaftaran' => 'completed', 
            'is_complete' => true, 'is_final' => true,
        ];
        foreach ($completionIndicators as $field => $value) {
            if (isset($ereg->$field) && $ereg->$field == $value) {
                return true;
            }
        }
        return $this->checkDataDiriCompletenessFromEreg($ereg) && 
               $this->checkJurusanCompleteness($ereg) && 
               $this->checkBerkasCompleteness($ereg) &&
               $isBiodataComplete;
    }

    /**
     * Cek data diri dari MasterEreg (jika ada)
     */
    private function checkDataDiriCompletenessFromEreg($ereg)
    {
        $criticalFields = ['nama', 'whatsapp', 'gmail'];
        foreach ($criticalFields as $field) {
            if (is_null($ereg->$field) || $ereg->$field === '') { // Anda sudah benar di sini
                return false;
            }
        }
        return true;
    }

    /**
     * Data default ketika user tidak ditemukan
     */
    private function getDefaultProgressData()
    {
        return [
            'currentStep' => 1,
            'isBiodataComplete' => false,
            'isUjianComplete' => false, // 👈 9. TAMBAHKAN INI DI DATA DEFAULT JUGA
            'isDataDiriComplete' => false,
            'isJurusanComplete' => false,
            'isBerkasComplete' => false,
            'isPendaftaranComplete' => false,
            'biodata' => null,
            'eregData' => null,
        ];
    }
    
    // ... (sisa fungsi apiProgress dan checkBiodataStatus Anda biarkan saja) ...
    // ...
    
    public function getProgressData()
    {
        $progress = $this->checkProgress();
        return $progress;
    }

    public function apiProgress()
    {
        try {
            $progress = $this->checkProgress();
            
            return response()->json([
                'success' => true,
                'data' => $progress
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get progress data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function checkBiodataStatus()
    {
        $user = Auth::user();
        
        if (!$user) {
            return [
                'exists' => false,
                'complete' => false,
                'message' => 'User tidak ditemukan'
            ];
        }

        $biodata = Biodata::where('user_id', $user->id)->first();

        if (!$biodata) {
            return [
                'exists' => false,
                'complete' => false,
                'message' => 'Biodata belum dibuat'
            ];
        }

        $isComplete = $this->checkBiodataCompleteness($biodata);

        return [
            'exists' => true,
            'complete' => $isComplete,
            'data' => $biodata,
            'message' => $isComplete ? 'Biodata lengkap' : 'Biodata belum lengkap'
        ];
    }
}