<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;   // Untuk mengambil data user yang login
use Illuminate\Support\Facades\Http;   // Ini adalah "Guzzle" versi Laravel
use App\Models\SlikBilling;            // Model baru yang kita buat
use App\Models\MasterEreg;             // Untuk mengambil data pendaftaran (seperti No. HP)
use Illuminate\Support\Str;            // Untuk membuat ID unik (Order ID)
use Illuminate\Validation\ValidationException; // Untuk menangani error

class MandiriPaymentController extends Controller
{
    /**
     * Fungsi #1: Membuat Tagihan (Create Bill)
     * Ini dipanggil saat user menekan tombol "Lanjutkan ke Pembayaran".
     */
    public function createVa(Request $request)
    {
        $user = Auth::user();
        // Ambil data pendaftaran (pastikan relasi/nama model ini benar)
        $eregData = MasterEreg::where('user_id', $user->id)->firstOrFail();

        // 1. Validasi data dari Form (Tambahkan aturan lain jika perlu)
        $validated = $request->validate([
            'tenor_pembayaran' => 'required|string',
            'ukuran_almamater' => 'required|string',
        ]);
        $totalTagihan = $this->calculateTotal(
            $validated['tenor_pembayaran'],
            $validated['ukuran_almamater']
        ); 

        // 3. Buat ID unik untuk transaksi ini
        $orderId = 'PMB-UKT-' . Str::uuid(); // Menghasilkan: PMB-UKT-a1b2c3d4-...

        // 4. Siapkan data (payload) untuk API SLIK
        // Formatnya kita tiru dari 'custom_api_test'
        $payload = [
            'user' => [
                'name'      => $user->name,
                'email'     => $user->email,
                'handphone' => $eregData->phone, // PASTIKAN NAMA KOLOM INI BENAR ('phone' atau 'whatsapp'?)
                'nim'       => $user->nim ?? $eregData->id, // Pakai NIM jika ada, jika tidak pakai ID pendaftar
            ],
            'total_bayar' => $totalTagihan,
            'datetime_expired' => now()->addMonth()->toIso8601String(), // Tagihan kadaluarsa 24 jam
            'bill_id' => $orderId, // Kita kirim ID unik kita
        ];

        // 5. Simpan "karcis" tagihan ke DB kita SEBELUM menembak API
        $billing = SlikBilling::create([
            'user_id' => $user->id,
            'order_id' => $orderId,
            'amount' => $totalTagihan,
            'status' => 'pending', // Status awal
            'slik_request_payload' => $payload, // Simpan apa yang kita kirim (untuk debug)
        ]);

        // 6. Tembak API SLIK
        $url = 'https://slik.ubpkarawang.ac.id/api/mandiri/create_va_pmb';

        try {
            $response = Http::timeout(20)->post($url, $payload); // Timeout 20 detik

            // 7. Tangani Balasan dari SLIK
            if ($response->failed()) {
                // Gagal (server SLIK error 500, 404, dll)
                $billing->update(['status' => 'failed']); // Update DB kita
                return back()->with('error', 'Server pembayaran sedang bermasalah. (Code: ' . $response->status() . ')');
            }

            $data = $response->json(); // Ubah balasan JSON jadi array

            // 8. Cek balasan SUKSES dari SLIK
            // (Saya tebak balasannya punya 'status' => 'success')
            if (isset($data['status']) && $data['status'] === 'success') { 
                
                // 9. UPDATE DB kita dengan nomor VA
                $billing->update([
                    // SESUAIKAN 'data.va_number' DENGAN BALASAN JSON ASLI DARI SLIK
                    'virtual_account' => $data['data']['virtual_account'] ?? 'ERROR-PARSE-VA', 
                    'slik_response_data' => $data, // Simpan balasan lengkap
                ]);

                // 10. Lempar user ke halaman instruksi
                return redirect()->route('mahasiswa.pembayaran.show', $billing->order_id);
            
            } else {
                // API SLIK merespon, tapi bilang "GAGAL"
                $billing->update(['status' => 'failed']);
                return back()->with('error', 'Gagal membuat tagihan VA: ' . ($data['message'] ?? 'Error tidak diketahui dari SLIK.'));
            }

        } catch (\Exception $e) {
            // Gagal konek (Server SLIK mati, URL salah, timeout)
            $billing->update(['status' => 'failed']); // Update DB kita
            return back()->with('error', 'Tidak dapat terhubung ke server pembayaran. Coba lagi nanti.');
        }
    }

    /**
     * Fungsi #2: Menampilkan Halaman Instruksi
     * Ini dipanggil setelah VA berhasil dibuat.
     */
    public function showVa($orderId)
    {
        // Ambil data tagihan dari DB kita, pastikan milik user yang login
        $transaction = SlikBilling::where('order_id', $orderId)
                                  ->where('user_id', Auth::id())
                                  ->firstOrFail(); // Gagal jika tagihan bukan miliknya
        
        // Tampilkan view baru yang akan kita buat nanti
        return view('mahasiswa.pembayaran.show', compact('transaction'));
    }
    public function checkStatus($orderId)
    {
        $tagihan = SlikBilling::where('order_id', $orderId)
                              ->where('user_id', Auth::id())
                              ->first();

        if (!$tagihan) {
            return response()->json(['status' => 'not_found'], 404);
        }

        // JIKA LUNAS, KIRIM SEMUA DATANYA
        if ($tagihan->status === 'paid') {
            return response()->json([
                'status' => 'paid',
                'transaction' => $tagihan // Kirim objek transaksi lengkap
            ]);
        }
        
        // Jika masih pending
        return response()->json(['status' => $tagihan->status]);
    }
    /**
     * Menghitung total tagihan di sisi server (backend).
     * JANGAN PERNAH percaya total bayar yang dikirim dari frontend.
     */
    private function calculateTotal($tenor, $almamater)
    {
        // 1. Tentukan harga (HARUS SAMA DENGAN DI JS ANDA)
        $hargaUktSemester = 5000000;
        $hargaUktTahun = 10000000;
        $hargaUktCicilan = 1000000;
        $hargaAlmamater = 150000;

        // 2. Hitung Tagihan UKT
        $tagihanUkt = 0;
        if ($tenor === 'semester') {
            $tagihanUkt = $hargaUktSemester;
        } elseif ($tenor === 'tahun') {
            $tagihanUkt = $hargaUktTahun;
        } elseif ($tenor === 'cicilan') {
            $tagihanUkt = $hargaUktCicilan;
        }

        // 3. Hitung Tagihan Almamater
        $tagihanAlmamater = 0;
        if ($almamater !== 'tidak_beli' && $almamater !== '') {
            $tagihanAlmamater = $hargaAlmamater;
        }
        
        // 4. Kembalikan totalnya
        return $tagihanUkt + $tagihanAlmamater;
    }
}