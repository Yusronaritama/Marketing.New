<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Str;

// IMPORT MODEL
use App\Models\MasterUser;
use App\Models\ProgramStudi; 
use App\Models\SlikBilling; 
use App\Models\DataMahasiswaFinal;
use App\Models\PendaftarGetNim;
use App\Models\BerkasProfesi; 
use App\Models\BiodataProfesi; 
use App\Models\MasterMoodle;

class DashboardProfesiController extends Controller
{
    public function resumeData()
    {
        $user = Auth::user();
        
        // Tarik data dari ketiga tabel
        $biodata = \App\Models\BiodataProfesi::where('user_id', $user->id)->first();
        $berkas  = \App\Models\BerkasProfesi::where('user_id', $user->id)->first();
        
        // ==========================================================
        // CEK STATUS CBT & WAWANCARA (SUMBER: TABEL USERS)
        // ==========================================================
        $statusCBT = $user->status_cbt; 
        $isCbtSelesai = !empty($statusCBT);

        // [PERBAIKAN] Ejaan typo dan normalisasi spasi/huruf kecil
        $statusWawancara = $user->status_wawancara; 
        $statusWawancaraLower = strtolower(trim($statusWawancara ?? ''));
        
        // Dianggap selesai kalau ada isinya DAN bukan 'belum diuji'
        $isWawancaraSelesai = (!empty($statusWawancaraLower) && $statusWawancaraLower !== 'belum diuji');

        // Status Kelulusan Berdasarkan Wawancara
        $statusKelulusan = 'MENUNGGU'; 
        
        if ($isWawancaraSelesai) {
            if ($statusWawancaraLower === 'lulus') {
                $statusKelulusan = 'LULUS';
            } elseif ($statusWawancaraLower === 'tidak lulus') {
                $statusKelulusan = 'TIDAK_LULUS';
            }
        }
        $historyLunas = \App\Models\SlikBilling::where('user_id', $user->id)
            ->whereRaw('LOWER(status) IN (?, ?)', ['paid', 'lunas'])
            ->first();
        
        $isBayarLunas = $historyLunas ? true : false;
        
        // Lempar semua variabelnya ke halaman blade resume
        return view('mahasiswa.profesi.resume', compact(
            'user', 
            'biodata', 
            'berkas', 
            'isCbtSelesai', 
            'statusWawancara',
            'isWawancaraSelesai',
            'statusKelulusan',
            'isBayarLunas'
        ));
    }
    
    public function index()
    {
        $user = Auth::user();
        
        // ==========================================================
        // AMBIL DATA BIODATA PROFESI
        // ==========================================================
        $biodata = BiodataProfesi::where('user_id', $user->id)->first();
        
        // --- VARIABLE DEFAULT FLOW PROGRAM PROFESI ---
        $currentStep = 1; 
        
        // Status Spesifik Profesi
        $isBerkasLengkap = false; 
        $statusBerkas    = 'belum_upload'; // belum_upload, belum, proses, lulus, tolak
        $catatanBerkas   = null;
        
        $isUjianSelesai  = false;
        $statusKelulusan = 'MENUNGGU'; 
        $isBayarLunas    = false;
        
        $activeBill      = null;
        $namaProfesi     = 'Program Profesi Apoteker'; 

        // ==========================================================
        // FLAG KUNCI UI (Gembok Pendaftaran Profesi)
        // ==========================================================
        $isPendaftaranBuka = true; 
        
        // ==========================================================
        // 0. CEK STATUS PEMBAYARAN AWAL (Biaya Pendaftaran)
        // ==========================================================
        $masterUser = MasterUser::where('user_id', $user->id)->first();
        if (!$masterUser || $masterUser->va_status === 'pending') {
            return redirect()->route('mahasiswa.va.dashboard')
                ->with('warning', "Silahkan selesaikan biaya pendaftaran {$namaProfesi} terlebih dahulu.");
        }

        // ==========================================================
        // NARIK DATA MASTER MOODLE & CEK CBT / WAWANCARA
        // ==========================================================
        $moodle = MasterMoodle::where('master_user_id', $masterUser->id)->first();
        
        $statusCBT = $user->status_cbt; 
        $isCbtSelesai = !empty($statusCBT); 
        
        // [PERBAIKAN] Panggil nama kolom yang benar: status_wawancara
        $statusWawancara = $user->status_wawancara; 
        $statusWawancaraLower = strtolower(trim($statusWawancara ?? ''));
        
        // Dianggap selesai kalau ada isinya DAN bukan 'belum diuji'
        $isWawancaraSelesai = (!empty($statusWawancaraLower) && $statusWawancaraLower !== 'belum diuji');

        // ==========================================================
        // 1. CEK UPLOAD BERKAS PROFESI (STEP 1)
        // ==========================================================
        $berkas = BerkasProfesi::where('user_id', $user->id)->first();
        if ($berkas) {
            $statusBerkas  = $berkas->status_verifikasi; 
            $catatanBerkas = $berkas->catatan_verifikasi;
            
            if ($berkas->status_berkas === 'dikirim' || $statusBerkas === 'lulus') {
                $isBerkasLengkap = true;
            }
        }

        // ==========================================================
        // 2. CEK NAMA PROFESI DARI TABEL PROGRAM STUDI
        // ==========================================================
        $kodeProdi = $masterUser->kode_prodi ?? '00000'; // Fallback kode apoteker jika kosong
        $prodi = ProgramStudi::where('kode_prodi', $kodeProdi)->first();
        
        if ($prodi) {
            $namaProfesi = $prodi->nama_prodi;
        }
        
        // ==========================================================
        // STATUS KELULUSAN (LANGSUNG DARI STATUS WAWANCARA USER)
        // ==========================================================
        $statusKelulusan = 'MENUNGGU'; 
        $isUjianSelesai = false;

        // Cek apakah beneran udah diuji
        if ($isWawancaraSelesai) {
            if ($statusWawancaraLower === 'lulus') {
                $statusKelulusan = 'LULUS';
                $isUjianSelesai = true;
            } elseif ($statusWawancaraLower === 'tidak lulus') {
                $statusKelulusan = 'TIDAK_LULUS';
                $isUjianSelesai = true;
            }
        }

        // ==========================================================
        // 3. CEK STATUS PEMBAYARAN UKT PROFESI (STEP 4)
        // ==========================================================
        $historyLunas = SlikBilling::where('user_id', $user->id)
                                   ->whereRaw('LOWER(status) IN (?, ?)', ['paid', 'lunas'])
                                   ->first();

        if ($historyLunas) {
            $isBayarLunas = true;
        }

        $activeBill = SlikBilling::where('user_id', $user->id)
                                 ->where('is_active', 1) 
                                 ->first();
                                   
        if ($activeBill && $activeBill->isExpired() && $activeBill->status !== 'paid') {
            if ($activeBill->status !== 'expired') {
                $activeBill->update(['status' => 'expired']);
            }
        }

        // ==========================================================
        // 4. CEK DATA FINAL & AKUN NIM (STEP 5 & 6)
        // ==========================================================
        $dataFinalRecord = DataMahasiswaFinal::where('user_id', $user->id)->first();
        $isDataFinalAdaDiDB = ($dataFinalRecord && $dataFinalRecord->status_data == 'completed');

        $dataAkun = PendaftarGetNim::where('user_id', $user->id)->first();
        $akunSudahAda = ($dataAkun && !empty($dataAkun->nim));

        // ==========================================================
        // 5. PENENTUAN FLOW STEP UNTUK UI (SINKRONISASI TOTAL)
        // ==========================================================
        // Default $currentStep = 1
        
        // Buka Step 2 (CBT) jika berkas lulus
        if ($berkas && $berkas->status_verifikasi === 'lulus') {
            $currentStep = 2; 
        }
        
        // Buka Step 3 (Wawancara) jika Step 2 udah kebuka DAN Ujian CBT udah selesai
        if ($currentStep == 2 && $isCbtSelesai) {
            $currentStep = 3;
        }
        
        // Buka Step 4 (Bayar UKT) jika Step 3 kebuka DAN status_wawacara bernilai LULUS/LOLOS
        if ($currentStep == 3 && $statusKelulusan === 'LULUS') {
            $currentStep = 4;
        }
        
        // Buka Step 5 (Selesai/Finalisasi) jika Step 4 udah kebuka DAN UKT udah lunas
        if ($currentStep == 4 && $isBayarLunas) {
            $currentStep = 5;
        }

        
        return view('mahasiswa.profesi.dashboard', compact(
            'user', 
            'currentStep', 
            'biodata',
            'moodle', 
            'statusCBT',
            'isCbtSelesai',
            'statusBerkas',
            'catatanBerkas',
            'isBerkasLengkap',
            'statusWawancara',
            'isWawancaraSelesai',
            'isUjianSelesai',
            'statusKelulusan', 
            'isBayarLunas', 
            'activeBill',
            'isDataFinalAdaDiDB',
            'dataAkun',
            'akunSudahAda',
            'namaProfesi',
            'isPendaftaranBuka'
        ));
    }

    public function checkStatusBerkas()
    {
        $user = Auth::user();
        $berkas = BerkasProfesi::where('user_id', $user->id)->first();

        $isBerkasLengkap = false;
        $statusBerkas = 'belum_upload';
        $catatanBerkas = null; 

        if ($berkas) {
            $statusBerkas = $berkas->status_verifikasi;
            $catatanBerkas = $berkas->catatan_verifikasi; 

            if ($berkas->status_berkas === 'dikirim' || $statusBerkas === 'lulus') {
                $isBerkasLengkap = true;
            }
        }

        return response()->json([
            'isBerkasLengkap' => $isBerkasLengkap,
            'statusBerkas'    => $statusBerkas,
            'catatanBerkas'   => $catatanBerkas 
        ]);
    }
}