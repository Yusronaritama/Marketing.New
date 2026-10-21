<?php

namespace App\Http\Controllers\Mahasiswa\profesi\payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

// MODELS
use App\Models\MasterEreg;
use App\Models\MasterUser;
use App\Models\ProgramStudi;
use App\Models\SlikBilling;
use App\Models\BiodataProfesi;

// SERVICE
use App\Services\UktMandiriApi;

class ReviewPaymentProfesiController extends Controller
{
    protected $mandiriService;

    // Biaya tambahan jika memilih metode LUNAS
    const BIAYA_TAMBAHAN_LUNAS = 18700000;
    
    
    

    public function __construct(UktMandiriApi $mandiriService)
    {
        $this->mandiriService = $mandiriService;
    }

    /**
     * Menampilkan halaman Review Tagihan & Form Ukuran Almamater
     */
    public function index()
    {
        $user = Auth::user();

        // 1. CEK KELULUSAN WAWANCARA
        $statusKelulusan = strtolower(trim($user->status_kelulusan ?? $user->status_wawancara ?? ''));
        if (!in_array($statusKelulusan, ['lulus', 'lolos'])) {
            return redirect()->route('profesi.dashboard')
                ->with('error', 'Anda harus lulus tahap wawancara terlebih dahulu untuk membayar UKT.');
        }

        // 2. CEK JIKA ADA TAGIHAN AKTIF
        $activeBill = SlikBilling::where('user_id', $user->id)
                                 ->where('is_active', 1)
                                 ->where('status', 'pending')
                                 ->first();

        if ($activeBill && !$activeBill->isExpired()) {
            return redirect()->route('profesi.reviewpayment.show', $activeBill->order_id);
        }

        // 3. AMBIL HARGA DARI DATABASE
        $prodiProfesi = ProgramStudi::where('jenjang', 'Profesi')
                                    ->orWhere('nama_prodi', 'like', '%Apoteker%')
                                    ->first();

        // Narik murni dari database tanpa hardcode
        $hargaSemester = $prodiProfesi ? $prodiProfesi->harga_semester : 0;
        
        $hargaLunas = $hargaSemester + self::BIAYA_TAMBAHAN_LUNAS;

        return view('mahasiswa.profesi.payment.reviewpayment', compact(
            'user',
            'prodiProfesi',
            'hargaSemester',
            'hargaLunas'
        ));
    }

    /**
     * Membuat Virtual Account (VA) via Mandiri API Service
     */
    public function createVa(Request $request)
    {
        $user = Auth::user();

        // 1. CEK TAGIHAN AKTIF
        $existingBill = SlikBilling::where('user_id', $user->id)
                                   ->where('is_active', 1)
                                   ->whereIn('status', ['pending', 'paid', 'lunas'])
                                   ->first();

        if ($existingBill) {
            if (in_array($existingBill->status, ['paid', 'lunas'])) {
                return redirect()->route('profesi.reviewpayment.show', $existingBill->order_id)
                                 ->with('success', 'Tagihan Anda sudah lunas.');
            }
            return redirect()->route('profesi.reviewpayment.show', $existingBill->order_id)
                             ->with('error', 'Anda masih memiliki tagihan aktif. Harap selesaikan pembayaran sebelumnya.');
        }

        // 2. VALIDASI INPUT
        $validated = $request->validate([
            'ukuran_almamater'   => 'required|string|in:S,M,L,XL,XXL',
            'metode_pembayaran'  => 'required|string|in:semester,lunas',
        ]);

        // Simpan ukuran almamater
        BiodataProfesi::updateOrCreate(
            ['user_id' => $user->id],
            ['ukuran_almamater' => $validated['ukuran_almamater']]
        );

        // 3. HITUNG NOMINAL BERDASARKAN METODE
        $prodiProfesi = ProgramStudi::where('jenjang', 'Profesi')
                                    ->orWhere('nama_prodi', 'like', '%Apoteker%')
                                    ->first();

        // Narik murni dari database tanpa hardcode
        $hargaSemester = $prodiProfesi ? $prodiProfesi->harga_semester : 0;

        $amount = ($validated['metode_pembayaran'] === 'lunas')
                    ? $hargaSemester + self::BIAYA_TAMBAHAN_LUNAS
                    : $hargaSemester;

        // Validasi jika harga di database ternyata 0 atau belum diset
        if ($amount <= 0 || $hargaSemester <= 0) {
            return back()->with('error', 'Biaya UKT profesi belum dikonfigurasi di database. Hubungi Admin.');
        }

        // 4. REQUEST KE API BANK MANDIRI
        $expiredDate = Carbon::now()->endOfMonth();
        $ereg        = MasterEreg::where('user_id', $user->id)->first();
        $namaUntukBank = $ereg->nama ?? $user->name;

        $result = $this->mandiriService->createBilling($user, $amount, $namaUntukBank, $expiredDate);

        if (!$result) {
            return redirect()->back()->with('error', 'Gagal terkoneksi ke server Bank (Timeout). Silakan coba kembali.');
        }

        // 5. SIMPAN KE DATABASE
        $masterUser       = MasterUser::where('user_id', $user->id)->first();
        $cleanUserTrxId   = $masterUser ? trim($masterUser->trx_id) : 'PRF-' . $user->id;
        $orderId          = 'UKT-' . $cleanUserTrxId . '-' . now()->format('YmdHis');
        $namaBersih       = strtoupper(substr(trim($namaUntukBank), 0, 50));

        try {
            $newBilling = SlikBilling::create([
                'user_id'            => $user->id,
                'master_ereg_id'     => $ereg->id ?? null,
                'order_id'           => $orderId,
                'trx_id'             => $result['trx_id'],
                'virtual_account'    => $result['virtual_account'],
                'amount'             => $result['amount'],
                'status'             => 'pending',
                'is_active'          => 1,
                'kode_prodi'         => $prodiProfesi->kode_prodi ?? 'PRF',
                'nama_jurusan'       => $prodiProfesi->nama_prodi ?? 'Profesi Apoteker',
                'tenor_pembayaran'   => $validated['metode_pembayaran'], // 'semester' atau 'lunas'
                'datetime_expired'   => $result['expired_at'],
                'slik_response_data' => json_encode($result['response_raw'] ?? []),
                'customer_name'      => "REGISTRASI_PMB:" . $cleanUserTrxId . '-' . $namaBersih,
                'nama_user'          => $user->name,
            ]);

            return redirect()->route('profesi.reviewpayment.show', $newBilling->order_id);

        } catch (\Exception $e) {
            Log::error('Gagal simpan billing profesi: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menyimpan data tagihan ke database.');
        }
    }

    /**
     * Menampilkan Halaman Invoice Detail VA Mandiri
     */
    public function showVa($orderId)
    {
        $transaction = SlikBilling::where('order_id', $orderId)
                                  ->where('user_id', Auth::id())
                                  ->firstOrFail();

        return view('mahasiswa.profesi.payment.show_payment', compact('transaction'));
    }

    /**
     * Cek Status Transaksi untuk Real-time Polling JavaScript
     */
    public function checkStatus($orderId)
    {
        $tagihan = SlikBilling::where('order_id', $orderId)
                              ->where('user_id', Auth::id())
                              ->first();

        if (!$tagihan) {
            return response()->json([
                'success' => false,
                'message' => 'Tagihan tidak ditemukan',
                'status'  => 'not_found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Status tagihan berhasil diambil',
            'status'  => $tagihan->status,
        ], 200);
    }
    
    public function cancelVa($orderId)
    {
        $tagihan = SlikBilling::where('order_id', $orderId)
                              ->where('user_id', Auth::id())
                              ->first();
        if (!$tagihan) {
            return redirect()->route('profesi.reviewpayment')
                             ->with('error', 'Tagihan tidak ditemukan.');
        }
        if (in_array($tagihan->status, ['paid', 'lunas'])) {
            return redirect()->route('profesi.reviewpayment.show', $orderId)
                             ->with('error', 'Tagihan yang sudah lunas tidak dapat dibatalkan.');
        }
        if ($tagihan->status !== 'pending') {
            return redirect()->route('profesi.reviewpayment.show', $orderId)
                             ->with('error', 'Hanya tagihan berstatus pending yang dapat dibatalkan.');
        }
        try {
            Log::info('VA dihapus oleh user', [
                'user_id'  => Auth::id(),
                'order_id' => $orderId,
                'metode'   => $tagihan->tenor_pembayaran,
                'amount'   => $tagihan->amount,
            ]);
            $tagihan->delete();
            return redirect()->route('profesi.reviewpayment')
                             ->with('success', 'Tagihan berhasil dihapus. Silakan pilih metode pembayaran yang baru.');
        } catch (\Exception $e) {
            Log::error('Gagal hapus VA: ' . $e->getMessage());
            return redirect()->back()
                             ->with('error', 'Gagal menghapus tagihan. Silakan coba lagi.');
        }
    }
}