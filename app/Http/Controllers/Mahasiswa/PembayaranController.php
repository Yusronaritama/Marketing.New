<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Database\QueryException;

// MODELS
use App\Models\User;
use App\Models\MasterEreg;
use App\Models\MasterUser;
use App\Models\MasterMoodle;
use App\Models\MasterMoodleLulus;
use App\Models\ProgramStudi;
use App\Models\SlikBilling;
use App\Models\Biodata;
use App\Services\UktMandiriApi;

class PembayaranController extends Controller
{
    
    protected $mandiriService;
    protected $url_pmb;

    public function __construct(UktMandiriApi $mandiriService)
    {
        $this->mandiriService = $mandiriService;
        
        $this->url_pmb = config('app.url');
        if (substr($this->url_pmb, -1) !== '/') {
            $this->url_pmb .= '/';
        }
    }

    /**
     * Menampilkan halaman pembayaran UKT (Step 4)
     */
    public function index()
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login')->with('error', 'Sesi Anda telah berakhir. Silakan login kembali.');
        }

        // 1. CARI TAGIHAN AKTIF (is_active = 1)
        $activeBilling = SlikBilling::where('user_id', $user->id)
                                    ->where('is_active', 1) 
                                    ->first();

        // 2. JIKA ADA, CEK EXPIRED & REDIRECT
        if ($activeBilling) {
            // Cek Expired (Hanya update teks status, is_active biarkan 1)
            if ($activeBilling->isExpired() && $activeBilling->status !== 'paid') {
                if ($activeBilling->status !== 'expired') {
                    $activeBilling->update(['status' => 'expired']);
                }
            }
            return redirect()->route('pembayaran.show', $activeBilling->order_id);
        }
        
        // 3. JIKA TIDAK ADA, TAMPILKAN FORM PILIHAN
        $ereg = MasterEreg::where('user_id', $user->id)->first();
        if (!$ereg) {
            return redirect()->route('mahasiswa.dashboard')->with('error', 'Data pendaftaran tidak ditemukan.');
        }

        $jurusanDiterima = "Menunggu Hasil Ujian"; 
        $moodleUsername  = '-';
        $gelombang       = '-';
        $namaLengkap     = $ereg->nama ?? $user->name;

        // Default Harga
        $dataHarga = [
            'semester' => 0,
            'tahun' => 0,
            'cicilan' => 0,
            'lunas' => 0,
        ];

        $jalur = $ereg->jalur_pendaftaran ?? '';
        $showSemester = Str::contains(strtolower($jalur), 's2');
        
        $masterUser = MasterUser::where('user_id', $user->id)->first();
        
        if ($masterUser) {
            $gelombang = $masterUser->gelombang ?? '-';

            $akunMoodle = MasterMoodle::where('master_user_id', $masterUser->id)->first();
            if ($akunMoodle) {
                $moodleUsername = $akunMoodle->username;
            }

            $dataLulus = MasterMoodleLulus::where('master_user_id', $masterUser->id)->first();

            if ($dataLulus) {
                if ($dataLulus->status_kelulusan === 'LULUS') {
                    
                    $jurusanDiterima = $dataLulus->nama_prodi_lulus;

                    // ==========================================================
                    // LOGIKA FARMASI (SINKRONISASI HARGA UKT TAMPILAN)
                    // ==========================================================
                    if (str_contains(strtoupper($jurusanDiterima), 'FARMASI')) {
                        $verifFarmasi = \App\Models\FarmasiVerification::where('user_id', $user->id)->first();
                        if ($verifFarmasi) {
                            $statusTahap2 = strtolower($verifFarmasi->status_verifikasi);
                            
                            // Jika Ditolak Tahap 2, Ganti Harga Menggunakan Pilihan 2
                            if (in_array($statusTahap2, ['rejected', 'ditolak', 'tidak lulus', 'gagal'])) {
                                $pilihan2 = $verifFarmasi->prodi_pilihan2 ?? ($ereg->jurusan2 ?? '-');
                                if (!empty($pilihan2) && $pilihan2 !== '-' && strtolower($pilihan2) !== 'tidak memilih') {
                                    $dataProdi2 = ProgramStudi::withoutGlobalScopes()->where('kode_prodi', $pilihan2)->first();
                                    if ($dataProdi2) {
                                        $jurusanDiterima = $dataProdi2->nama_prodi;
                                    }
                                } else {
                                    $jurusanDiterima = "Tidak Lulus Seleksi";
                                }
                            }
                        }
                    }
                    // ==========================================================

                    if ($jurusanDiterima !== "Tidak Lulus Seleksi") {
                        $prodi = ProgramStudi::where('nama_prodi', $jurusanDiterima)->first();

                        if ($prodi) {
                            // [TAMBAHAN BARU] CEK BEASISWA KELUARGA UBP
                            if ($user->kel_beasiswa == 1) {
                                // Hanya isi nominal tahun, yang lain di-0-kan biar UI bisa nge-lock
                                $dataHarga['semester'] = 0;
                                $dataHarga['tahun']    = $prodi->beasiswa_kel_ubp;
                                $dataHarga['cicilan']  = 0;
                                $dataHarga['lunas']    = $prodi->beasiswa_kel_ubp;
                            } else {
                                // Logic Normal
                                $dataHarga['semester'] = $prodi->harga_semester;
                                $dataHarga['tahun']    = ($prodi->harga_semester * 2) - 1000000;
                                $dataHarga['cicilan']  = $prodi->harga_cicilan;
                            }
                        } else {
                            Log::warning("Data ProgramStudi tidak ditemukan untuk nama: {$jurusanDiterima}");
                        }
                    }
                } elseif ($dataLulus->status_kelulusan === 'TIDAK LULUS') {
                    $jurusanDiterima = "Tidak Lulus Seleksi";
                }
            }
        }

        return view('mahasiswa.pembayaran.generate-payment.index', compact(
            'user', 
            'jurusanDiterima', 
            'dataHarga',
            'moodleUsername', 
            'gelombang',        
            'namaLengkap',
            'showSemester'
        ));
    }

    /**
     * Fungsi untuk membuat Virtual Account (VA)
     */
    public function createVA(Request $request)
    {
        $request->validate(['tenor_pembayaran' => 'required|string']);
        $user = Auth::user();
        $ereg = MasterEreg::where('user_id', $user->id)->first();
        $existingBill = SlikBilling::where('user_id', $user->id)
                                   ->where('is_active', 1)
                                   ->whereIn('status', ['pending', 'paid', 'lunas'])
                                   ->first();

        // JIKA KETEMU TAGIHAN AKTIF
        if ($existingBill) {
            // Jika sudah lunas -> Redirect Sukses
            if ($existingBill->status == 'paid' || $existingBill->status == 'lunas') {
                return redirect()->route('pembayaran.show', $existingBill->order_id)
                                 ->with('success', 'Anda sudah melakukan pembayaran. Tidak perlu membuat tagihan baru.');
            }
            // Jika masih pending -> Redirect Warning
            return redirect()->route('pembayaran.show', $existingBill->order_id)
                             ->with('error', 'Anda masih memiliki tagihan aktif. Harap selesaikan atau batalkan tagihan sebelumnya.');
        }
        
        // --- 1. AMBIL DATA MASTER USER & ID REGISTRASI ---
        $masterUser = \App\Models\MasterUser::where('user_id', $user->id)->first();
        if (!$masterUser) return redirect()->back()->with('error', 'Data user tidak valid.');
        $userTrxId = $masterUser->trx_id; 
        if (!$userTrxId) return redirect()->back()->with('error', 'ID Registrasi tidak ditemukan.');
        // --- 2. VALIDASI KELULUSAN ---
        $dataLulus = \App\Models\MasterMoodleLulus::where('master_user_id', $masterUser->id)->first();
        if (!$dataLulus || $dataLulus->status_kelulusan !== 'LULUS') {
            return redirect()->back()->with('error', 'Anda belum dinyatakan lulus.');
        }

        $namaProdiLulus = $dataLulus->nama_prodi_lulus;

        // ==========================================================
        // LOGIKA FARMASI (SINKRONISASI BIAYA API VA BANK)
        // ==========================================================
        if (str_contains(strtoupper($namaProdiLulus), 'FARMASI')) {
            $verifFarmasi = \App\Models\FarmasiVerification::where('user_id', $user->id)->first();
            if ($verifFarmasi) {
                $statusTahap2 = strtolower($verifFarmasi->status_verifikasi);
                
                if (in_array($statusTahap2, ['rejected', 'ditolak', 'tidak lulus', 'gagal'])) {
                    $pilihan2 = $verifFarmasi->prodi_pilihan2 ?? ($ereg->jurusan2 ?? '-');
                    
                    if (!empty($pilihan2) && $pilihan2 !== '-' && strtolower($pilihan2) !== 'tidak memilih') {
                        $dataProdi2 = \App\Models\ProgramStudi::withoutGlobalScopes()->where('kode_prodi', $pilihan2)->first();
                        if ($dataProdi2) {
                            $namaProdiLulus = $dataProdi2->nama_prodi;
                        } else {
                            return redirect()->back()->with('error', 'Gagal membuat tagihan: Data Prodi Pilihan Kedua tidak ditemukan di sistem.');
                        }
                    } else {
                        return redirect()->back()->with('error', 'Gagal membuat tagihan: Anda tidak lulus Tahap 2 dan tidak memiliki Pilihan Kedua.');
                    }
                } elseif (!in_array($statusTahap2, ['verified', 'diverifikasi', 'lulus'])) {
                    return redirect()->back()->with('error', 'Status Tes Kesehatan Farmasi Anda masih Pending. Belum diizinkan untuk membayar UKT.');
                }
            }
        }
        // ==========================================================

        $prodi = \App\Models\ProgramStudi::where('nama_prodi', $namaProdiLulus)->first();
        if (!$prodi) return redirect()->back()->with('error', 'Data Prodi tidak ditemukan.');
        // --- 3. HITUNG NOMINAL ---
        $amount = 0;
        
        // [TAMBAHAN BARU] CEK BEASISWA KELUARGA UBP
        if ($user->kel_beasiswa == 1) {
            // Paksa nominal pakai harga beasiswa dan paksa tenor jadi 'tahun'
            $amount = $prodi->beasiswa_kel_ubp;
            $request->merge(['tenor_pembayaran' => 'lunas']);
        } else {
            // Logic Normal
            switch ($request->tenor_pembayaran) {
                case 'semester': $amount = $prodi->harga_semester; break;
                case 'tahun':    $amount = ($prodi->harga_semester * 2) - 1000000; break;
                case 'cicilan':  $amount = $prodi->harga_cicilan; break;
            }
        }
        
        if ($amount <= 0) return redirect()->back()->with('error', 'Biaya UKT / Beasiswa belum disetting.');
        $expiredDate = Carbon::now()->endOfMonth();
        // --- 4. REQUEST KE BANK (SERVICE) ---
        $namaUntukBank = $ereg->nama ?? $user->name; 
        $result = $this->mandiriService->createBilling($user, $amount, $namaUntukBank, $expiredDate);

        if (!$result) {
             return redirect()->back()->with('error', 'Gagal koneksi ke Bank (Timeout). Silakan coba lagi.');
        }
        // --- 5. SIMPAN KE DATABASE ---
        if ($result) {
            $timestamp = now()->format('YmdHis');
            $cleanUserTrxId = trim($masterUser->trx_id); 
            $orderId = 'UKT-' . $cleanUserTrxId . '-' . $timestamp;
            $namaMhs = $ereg->nama ?? $user->name;
            $namaBersih = strtoupper(substr(trim($namaMhs), 0, 50));
            try {
                $newBilling = SlikBilling::create([
                    'user_id'            => $user->id,
                    'master_ereg_id'     => $ereg->id ?? null, // Pakai Null Coalescing biar aman
                    'order_id'           => $orderId,
                    'trx_id'             => $result['trx_id'],
                    'virtual_account'    => $result['virtual_account'],
                    'amount'             => $result['amount'],
                    'status'             => 'pending',
                    'is_active'          => 1,
                    'kode_prodi'         => $prodi->kode_prodi,
                    'nama_jurusan'       => $prodi->nama_prodi,
                    'tenor_pembayaran'   => $request->tenor_pembayaran,
                    'datetime_expired'   => $result['expired_at'],
                    'slik_response_data' => json_encode($result['response_raw'] ?? []), 
                    'customer_name'      => "REGISTRASI_PMB:" . $cleanUserTrxId . '-' . $namaBersih
                ]);

                return redirect()->route('pembayaran.show', ['order_id' => $newBilling->order_id]);
            } catch (\Exception $e) {
                // Log::error($e->getMessage());
                return redirect()->back()->with('error', 'Gagal menyimpan tagihan ke database.');
            }
        }
        return redirect()->back()->with('error', 'Gagal membuat tagihan (API Error). Coba lagi nanti.');
    }



    /**
     * Menampilkan halaman instruksi pembayaran
     */
    public function showInstruksi($order_id)
    {
        $user = Auth::user();
        
        $transaction = SlikBilling::where('order_id', $order_id)
                                  ->where('user_id', $user->id)
                                  ->firstOrFail(); 

        // LOGIKA CEK EXPIRED
        if ($transaction->isExpired()) {
            if ($transaction->status !== 'expired') {
                $transaction->update(['status' => 'expired']);
            }
        }

        $ereg = MasterEreg::where('user_id', $user->id)->first();

        return view('mahasiswa.pembayaran.show', compact('transaction', 'ereg'));
    }

    /**
     * METHOD UNTUK CETAK BUKTI BAYAR
     */
    public function printReceipt($order_id)
    {
        $user = Auth::user();

        // Cari Tagihan
        $bill = SlikBilling::where('order_id', $order_id)
                           ->where('user_id', $user->id)
                           ->firstOrFail();

        // Ambil Biodata
        $biodata = Biodata::where('user_id', $user->id)->first();

        // Mapping Metode
        $metode = 'Pembayaran UKT';
        if ($bill->tenor_pembayaran) {
            if (stripos($bill->tenor_pembayaran, 'cicilan') !== false) {
                $metode = 'Cicilan UKT (Bertahap)';
            } elseif (stripos($bill->tenor_pembayaran, 'tahun') !== false) {
                $metode = 'Lunas 1 Tahun';
            } elseif (stripos($bill->tenor_pembayaran, 'semester') !== false) {
                $metode = 'Lunas 1 Semester';
            } elseif (stripos($bill->tenor_pembayaran, 'lunas') !== false) {
                $metode = 'Lunas (Beasiswa Keluarga UBP)'; // <--- JADI SPESIFIK GINI
            }
            
        }

        return view('mahasiswa.pembayaran.receipt', compact('bill', 'user', 'biodata', 'metode'));
    }

    /**
     * Simpan Ukuran Almamater
     */
    public function saveAlmamater(Request $request)
    {
        // Validasi di-update dengan tambahan 4XL, 5XL, dan 6XL
        $request->validate([
            'ukuran_almamater' => 'required|string|in:S,M,L,XL,XXL,XXXL,4XL,5XL,6XL',
        ]);

        $user = Auth::user();
        $ereg = MasterEreg::where('user_id', $user->id)->first();

        if ($ereg) {
            $ereg->update([
                'ukuran_almamater' => $request->ukuran_almamater
            ]);
            return redirect()->back()->with('success_almamater', 'Ukuran almamater (' . $request->ukuran_almamater . ') berhasil disimpan!');
        }

        return redirect()->back()->with('error_almamater', 'Gagal menyimpan ukuran.');
    }

    /**
     * API Status Pembayaran (Untuk Polling JS)
     */
    public function getPaymentStatus($order_id)
    {
        $user = Auth::user(); 
        $transaction = SlikBilling::where('order_id', $order_id)
                                  ->where('user_id', $user->id)
                                  ->first();

        if (!$transaction) {
            return response()->json(['status' => 'not_found'], 404);
        }

        return response()->json([
            'status' => $transaction->status, 
        ]);
    }

    /**
     * Callback / Webhook Pembayaran
     */
    
    
    /**
     * Reset Tagihan (Manual Cancel)
     */
    public function resetTagihan($order_id)
    {
        $user = Auth::user();
        
        // Cari data tagihan
        $billing = SlikBilling::where('order_id', $order_id)
                              ->where('user_id', $user->id)
                              ->firstOrFail();

        // 1. Panggil Service Hapus VA di sisi Bank (Wajib biar API Bank bersih)
        $this->mandiriService->deleteVA($billing->trx_id, $billing->virtual_account);

        // 2. Hapus permanen data tagihan dari Database
        $billing->delete();

        // 3. Kembalikan user ke halaman pembuatan tagihan
        return redirect()->route('pembayaran.ukt')
                         ->with('success', 'Tagihan sebelumnya berhasil dihapus. Silakan buat tagihan baru.');
    }
    
    public function checkStatus($order_id)
    {
        // Kita cari tagihan berdasarkan Order ID
        $billing = SlikBilling::where('order_id', $order_id)->first();

        if (!$billing) {
            return response()->json([
                'success' => false,
                'message' => 'Tagihan tidak ditemukan',
                'status'  => 'not_found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'status'  => $billing->status, 
            'message' => 'Status berhasil diambil'
        ]);
    }
    
    
}