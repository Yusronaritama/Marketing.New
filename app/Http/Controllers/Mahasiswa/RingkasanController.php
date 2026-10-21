<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

// Load Models
use App\Models\MasterEreg;
use App\Models\MasterUser;
use App\Models\MasterMoodle;
use App\Models\MasterMoodleLulus; 
use App\Models\SlikBilling;
use App\Models\Biodata;
use App\Models\DataMahasiswaFinal;
use App\Models\FarmasiVerification; // [TAMBAHAN] Import Model Verifikasi Farmasi
use App\Models\ProgramStudi;        // [TAMBAHAN] Import Model Prodi

class RingkasanController extends Controller
{
    /**
     * Halaman Utama Ringkasan Dashboard
     */
    public function index()
    {
        $user = Auth::user();
        
        // 1. AMBIL DATA UTAMA
        $ereg = MasterEreg::where('user_id', $user->id)->first();
        $biodata = Biodata::where('user_id', $user->id)->first();
        $dataFinal = DataMahasiswaFinal::where('user_id', $user->id)->first();

        // 2. AMBIL STATUS KEUANGAN (BILLING)
        $slikBilling = SlikBilling::where('user_id', $user->id)
                                  ->orderBy('created_at', 'desc')
                                  ->first();
                                  
        $slikBillingStatus = $slikBilling ? $slikBilling->status : 'unpaid';

        // 3. AMBIL STATUS KELULUSAN & JURUSAN
        $masterUser = MasterUser::where('user_id', $user->id)->first();
        $moodleData = null;
        $statusUjian = 'Belum Ujian'; 
        $jurusanDiterima = '-'; // [TAMBAHAN] Inisiasi default jurusan

        if ($masterUser) {
            $moodleData = MasterMoodle::where('master_user_id', $masterUser->id)->first();
            $dataLulus = MasterMoodleLulus::where('master_user_id', $masterUser->id)->first();

            if ($dataLulus) {
                $rawStatus = strtoupper($dataLulus->status_kelulusan);
                $prodiLulusMentah = strtoupper($dataLulus->nama_prodi_lulus);

                if ($rawStatus === 'LULUS') {
                    $statusUjian = 'Lulus';
                    $jurusanDiterima = ucwords(strtolower($prodiLulusMentah)); // Default

                    // ==========================================================
                    // LOGIKA FARMASI (SINKRON DENGAN DASHBOARD)
                    // ==========================================================
                    if (str_contains($prodiLulusMentah, 'FARMASI')) {
                        $verifFarmasi = FarmasiVerification::where('user_id', $user->id)->first();
                        if ($verifFarmasi) {
                            $statusTahap2 = strtolower($verifFarmasi->status_verifikasi);
                            
                            // Jika Gagal Tahap 2
                            if (in_array($statusTahap2, ['rejected', 'ditolak', 'tidak lulus', 'gagal'])) {
                                $pilihan2 = $verifFarmasi->prodi_pilihan2 ?? ($ereg->jurusan2 ?? '-');
                                
                                if (!empty($pilihan2) && $pilihan2 !== '-' && strtolower($pilihan2) !== 'tidak memilih') {
                                    $statusUjian = 'Lulus';
                                    $dataProdi = ProgramStudi::withoutGlobalScopes()->where('kode_prodi', $pilihan2)->first();
                                    $jurusanDiterima = $dataProdi ? ucwords(strtolower($dataProdi->nama_prodi)) : strtoupper($pilihan2);
                                } else {
                                    $statusUjian = 'Tidak Lulus';
                                    $jurusanDiterima = 'Gagal Tahap 2 & Tidak Ada Pilihan 2';
                                }
                            } elseif (!in_array($statusTahap2, ['verified', 'diverifikasi', 'lulus'])) {
                                // Jika masih pending
                                $statusUjian = 'Menunggu Hasil (Kesehatan)';
                                $jurusanDiterima .= ' (Menunggu Tes Kesehatan)';
                            }
                        }
                    }
                    // ==========================================================

                } elseif (in_array($rawStatus, ['TIDAK LULUS', 'GAGAL', 'REJECTED'])) {
                    $statusUjian = 'Tidak Lulus';
                } else {
                    $statusUjian = ucwords(strtolower($dataLulus->status_kelulusan));
                }
            } else {
                if ($moodleData) {
                    $statusUjian = 'Menunggu Hasil';
                }
            }
        }

        // 4. KIRIM KE VIEW
        return view('mahasiswa.ringkasan.index', [
            'user'              => $user,
            'ereg'              => $ereg, 
            'biodata'           => $biodata,
            'dataFinal'         => $dataFinal,
            'moodleData'        => $moodleData,
            'slikBilling'       => $slikBilling,
            'billing'           => $slikBilling,
            'slikBillingStatus' => $slikBillingStatus,
            'statusUjian'       => $statusUjian, 
            'jurusanDiterima'   => $jurusanDiterima, // [TAMBAHAN] Lempar jurusan ke view ringkasan
        ]);
    }

    /**
     * Simpan Ukuran Almamater
     */
    public function simpanAlmamater(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'ukuran_almamater' => 'required|string|in:S,M,L,XL,XXL,XXXL,4XL,5XL,6XL,JUMBO',
        ], [
            'ukuran_almamater.required' => 'Silakan pilih ukuran almamater terlebih dahulu.',
            'ukuran_almamater.in'       => 'Ukuran yang dipilih tidak valid.',
        ]);

        $user = Auth::user();

        // 2. Cari Data Master Ereg User Tersebut
        $ereg = MasterEreg::where('user_id', $user->id)->first();

        // 3. Update Data
        if ($ereg) {
            $ereg->update([
                'ukuran_almamater' => $request->ukuran_almamater
            ]);

            return redirect()->back()->with('success', 'Ukuran Almamater (' . $request->ukuran_almamater . ') berhasil disimpan!');
        }

        return redirect()->back()->with('error', 'Data registrasi tidak ditemukan.');
    }
    
    /**
     * Halaman Khusus Print Manifest
     */
    public function printManifest()
    {
        $user = Auth::user();

        // 1. AMBIL DATA UTAMA
        $ereg = MasterEreg::where('user_id', $user->id)->firstOrFail();
        $biodata = Biodata::where('user_id', $user->id)->first();
        $dataFinal = DataMahasiswaFinal::where('user_id', $user->id)->first();

        // 2. LOGIKA STATUS PEMBAYARAN
        $billing = SlikBilling::where('user_id', $user->id)
                            ->whereIn('status', ['paid', 'lunas'])
                            ->orderBy('created_at', 'desc')
                            ->first();
        
        $slikBillingStatus = $billing ? 'paid' : 'unpaid';
        $masterUser = MasterUser::where('user_id', $user->id)->first();
        
        // --- SET DEFAULT VALUE ---
        $statusUjian = 'Belum Ujian';
        $jurusanDiterima = '-'; 

        if ($masterUser) {
            // Ambil Data dari Tabel MasterMoodleLulus
            $dataLulus = MasterMoodleLulus::where('master_user_id', $masterUser->id)->first();

            if ($dataLulus) {
                $rawStatus = strtoupper($dataLulus->status_kelulusan);
                $prodiLulusMentah = strtoupper($dataLulus->nama_prodi_lulus);

                if ($rawStatus === 'LULUS') {
                    $statusUjian = 'Lulus';
                    $jurusanDiterima = ucwords(strtolower($prodiLulusMentah)); // Default

                    // ==========================================================
                    // LOGIKA FARMASI (SINKRON DENGAN PRINT MANIFEST)
                    // ==========================================================
                    if (str_contains($prodiLulusMentah, 'FARMASI')) {
                        $verifFarmasi = FarmasiVerification::where('user_id', $user->id)->first();
                        if ($verifFarmasi) {
                            $statusTahap2 = strtolower($verifFarmasi->status_verifikasi);
                            
                            // Jika Gagal Tahap 2
                            if (in_array($statusTahap2, ['rejected', 'ditolak', 'tidak lulus', 'gagal'])) {
                                $pilihan2 = $verifFarmasi->prodi_pilihan2 ?? ($ereg->jurusan2 ?? '-');
                                
                                if (!empty($pilihan2) && $pilihan2 !== '-' && strtolower($pilihan2) !== 'tidak memilih') {
                                    $statusUjian = 'Lulus';
                                    $dataProdi = ProgramStudi::withoutGlobalScopes()->where('kode_prodi', $pilihan2)->first();
                                    $jurusanDiterima = $dataProdi ? ucwords(strtolower($dataProdi->nama_prodi)) : strtoupper($pilihan2);
                                } else {
                                    $statusUjian = 'Tidak Lulus';
                                    $jurusanDiterima = '-'; 
                                }
                            }
                        }
                    }
                    // ==========================================================

                } elseif (in_array($rawStatus, ['TIDAK LULUS', 'GAGAL', 'REJECTED'])) {
                    $statusUjian = 'Tidak Lulus';
                    $jurusanDiterima = '-';
                } else {
                    $statusUjian = ucwords(strtolower($dataLulus->status_kelulusan));
                    $jurusanDiterima = '-';
                }
            } else {
                $moodleData = MasterMoodle::where('master_user_id', $masterUser->id)->first();
                if ($moodleData) {
                    $statusUjian = 'Menunggu Hasil';
                }
            }
        }

        // 4. RETURN VIEW KHUSUS PRINT
        return view('mahasiswa.ringkasan.print', compact(
            'user', 
            'ereg', 
            'biodata', 
            'dataFinal', 
            'billing', 
            'statusUjian', 
            'slikBillingStatus',
            'jurusanDiterima'
        ));
    }
}