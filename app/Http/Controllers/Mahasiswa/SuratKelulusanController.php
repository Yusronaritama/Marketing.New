<?php
namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use PDF; // Facade DomPDF

use App\Models\MasterEreg;
use App\Models\MasterMoodleLulus; 
use App\Models\MasterUser;
use App\Models\FarmasiVerification;

class SuratKelulusanController extends Controller
{
    public function downloadSurat()
    {
        $user = Auth::user();

        // 1. AMBIL DATA
        $dataDiri   = MasterEreg::where('user_id', $user->id)->first();
        $masterUser = MasterUser::where('user_id', $user->id)->first();
        
        $dataLulus = null;
        if ($masterUser) {
            $dataLulus = MasterMoodleLulus::where('master_user_id', $masterUser->id)->first();
        }

        // ==========================================================
        // LOGIKA PENENTUAN PRODI (SINKRONISASI DENGAN DASHBOARD)
        // ==========================================================
        $prodiFinal = 'PRODI BELUM DISET';

        if ($dataLulus) {
            $prodiLulusMentah = strtoupper($dataLulus->nama_prodi_lulus);
            $prodiFinal = ucwords(strtolower($prodiLulusMentah)); // Default jika non-farmasi

            // Jika dia anak Farmasi, cek status tahap 2 kesehatannya
            if (str_contains($prodiLulusMentah, 'FARMASI')) {
                $verifFarmasi = FarmasiVerification::where('user_id', $user->id)->first();
                
                if ($verifFarmasi) {
                    $statusTahap2 = strtolower($verifFarmasi->status_verifikasi);
                    
                    // Jika ditolak tahap 2, timpa nama prodi jadi Pilihan 2
                    if (in_array($statusTahap2, ['rejected', 'ditolak', 'tidak lulus', 'gagal'])) {
                        $pilihan2 = $verifFarmasi->prodi_pilihan2 ?? ($dataDiri->jurusan2 ?? '-');
                        
                        if (!empty($pilihan2) && $pilihan2 !== '-' && strtolower($pilihan2) !== 'tidak memilih') {
                            
                            
                            $dataProdi = \App\Models\ProgramStudi::withoutGlobalScopes()
                                            ->where('kode_prodi', $pilihan2)
                                            ->first();
                                            
                            if ($dataProdi) {
                                // Jika ketemu, pakai nama lengkap prodinya
                                $prodiFinal = ucwords(strtolower($dataProdi->nama_prodi));
                            } else {
                                // Fallback aman jika kode tidak ditemukan
                                $prodiFinal = ucwords(strtolower($pilihan2));
                            }
                            
                        } else {
                            $prodiFinal = 'TIDAK LULUS (Gagal Tahap 2 & Tidak Ada Pilihan 2)';
                        }
                    }
                }
            }
        }

        // 2. SIAPKAN VARIABEL
        $nama      = $dataDiri->nama ?? $user->name;
        $nim       = $dataDiri->trx_id ?? '-';
        $prodi     = $prodiFinal; // Gunakan prodi yang sudah divalidasi logic Farmasi
        $lunasSelisih = \App\Models\SlikBilling::where('user_id', $user->id)
            ->where('order_id', 'like', '%UKT-SELISIH%')
            ->whereRaw('LOWER(status) IN (?, ?)', ['paid', 'lunas'])
            ->latest()
            ->first();

        if ($lunasSelisih) {
            // Tarik prodi baru dari DB berdasarkan kode_prodi tagihan selisih
            $prodiBaruLunas = \App\Models\ProgramStudi::where('kode_prodi', $lunasSelisih->kode_prodi)->first();
            
            // Timpa nama prodi dengan prodi yang baru dilunasi
            if ($prodiBaruLunas) {
                $prodi = ucwords(strtolower($prodiBaruLunas->nama_prodi));
            } else {
                // Fallback kalau entah kenapa DB prodi error
                $prodi = ucwords(strtolower($lunasSelisih->nama_jurusan)); 
            }
        }
        
        Carbon::setLocale('id');
        $tanggal   = Carbon::now()->translatedFormat('d F Y');

        // 3. KONVERSI GAMBAR KE BASE64 (Agar Anti-Gagal di PDF)
        $logoBase64 = $this->imageToBase64(public_path('images/logo_ubp.png'));
        $ttdBase64  = $this->imageToBase64(public_path('images/ttd-removebg-preview.png')); 
        $capBase64  = $this->imageToBase64(public_path('images/cap-removebg-preview.png'));

        // 4. GENERATE PDF
        $pdf = PDF::loadView('mahasiswa.surat.template_lulus', compact(
            'nama', 'nim', 'prodi', 'tanggal', 'logoBase64', 'ttdBase64', 'capBase64'
        ));

        // Set ukuran kertas A4 Potrait
        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream('Surat_Kelulusan_' . $nim . '.pdf');
    }

    // Helper Function untuk Gambar
    private function imageToBase64($path) {
        if (!file_exists($path)) {
            return null; // Return null jika gambar tidak ada
        }
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        return 'data:image/' . $type . ';base64,' . base64_encode($data);
    }
}