<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

// Models
use App\Models\User;
use App\Models\MasterEreg;
use App\Models\ProgramStudi;
use App\Models\SlikBilling;
use App\Models\MasterMoodleLulus;

// Services
use App\Services\UktMandiriApi;

class AdminProdiController extends Controller
{
    protected $mandiriService;

    public function __construct(UktMandiriApi $mandiriService)
    {
        $this->mandiriService = $mandiriService;
    }

    /**
     * Export data ke Excel
     */
    public function export(Request $request)
    {
        $fileName = 'rekap_pindah_prodi_' . date('Ymd_His') . '.xlsx';
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\UbahProdiExport($request), $fileName);
    }

    /**
     * Tampilkan halaman cari/list user untuk ubah prodi
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        // Query data dengan fitur pencarian
        $users = MasterEreg::with('user')
            ->when($search, function ($query) use ($search) {
                $query->where('nama', 'like', "%{$search}%")
                      ->orWhere('user_id', 'like', "%{$search}%")
                      ->orWhereHas('user', function ($q) use ($search) {
                          $q->where('email', 'like', "%{$search}%")
                            ->orWhereHas('slikBilling', function($qSlik) use ($search) {
                                $qSlik->where('order_id', 'like', "%{$search}%")
                                      ->orWhere('virtual_account', 'like', "%{$search}%");
                            });
                      });
            })
            ->paginate(50)
            ->appends(['search' => $search]); 

        // Tambahkan logic menarik data nominal yang lunas & tagihan selisih
        foreach ($users as $ereg) {
            // Nominal pembayaran normal yang sudah LUNAS (bukan selisih)
            $pembayaranLunas = SlikBilling::where('user_id', $ereg->user_id)
                ->where('order_id', 'not like', '%SELISIH%')
                ->whereIn('status', ['paid', 'settlement', 'success'])
                ->sum('amount');
                
            $ereg->nominal_dibayar = $pembayaranLunas;

            // VA Selisih (Ambil yang terbaru, entah pending atau paid)
            $tagihanSelisih = SlikBilling::where('user_id', $ereg->user_id)
                ->where('order_id', 'like', '%SELISIH%')
                ->orderBy('id', 'desc')
                ->first();

            $ereg->tagihan_selisih = $tagihanSelisih;
        }

        $prodis = ProgramStudi::where('status_prodi', 1)->get();

        return view('admin.ubah-prodi.index', compact('users', 'prodis', 'search'));
    }

    public function updateProdi(Request $request, $userId)
    {
        $request->validate([
            'kode_prodi_baru' => 'required|exists:program_studi,kode_prodi',
            'nominal_selisih' => 'nullable|numeric|min:0'
        ]);

        $user = User::findOrFail($userId);
        $ereg = MasterEreg::where('user_id', $user->id)->firstOrFail();
        $prodiBaru = ProgramStudi::where('kode_prodi', $request->kode_prodi_baru)->firstOrFail();

        DB::beginTransaction();

        try {
            $selisih = $request->nominal_selisih ?? 0;
            $butuhBayarSelisih = $selisih > 0;

            $oldBilling = SlikBilling::where('user_id', $user->id)
                                     ->orderBy('id', 'desc')
                                     ->first();
            
            $tenor = $oldBilling ? strtolower($oldBilling->tenor_pembayaran) : 'semester';
            $infoTagihan = "";

            if ($butuhBayarSelisih) {
                $expiredDate = Carbon::now()->addDays(7); 
                $namaUntukBank = substr("SELISIH " . ($ereg->nama ?? $user->name), 0, 50);

                $result = $this->mandiriService->createBilling($user, $selisih, $namaUntukBank, $expiredDate);

                if (!$result) {
                    DB::rollBack();
                    return redirect()->back()->with('error', 'Gagal koneksi ke Bank saat membuat VA selisih. Coba lagi.');
                }

                $timestamp = now()->format('YmdHis');
                $orderId = 'UKT-SELISIH-' . $user->id . '-' . $timestamp;

                SlikBilling::create([
                    'user_id'            => $user->id,
                    'master_ereg_id'     => $ereg->id,
                    'order_id'           => $orderId,
                    'trx_id'             => $result['trx_id'],
                    'virtual_account'    => $result['virtual_account'],
                    'amount'             => $result['amount'],
                    'status'             => 'pending',
                    'kode_prodi'         => $prodiBaru->kode_prodi,
                    'nama_jurusan'       => $prodiBaru->nama_prodi,
                    'tenor_pembayaran'   => $tenor,
                    'datetime_expired'   => $result['expired_at'],
                    'slik_response_data' => json_encode($result['response_raw'] ?? []),
                    'customer_name'      => "SELISIH_PRODI:" . $user->id,
                    'description'        => 'Kekurangan pindah prodi ke ' . $prodiBaru->nama_prodi
                ]);

                $infoTagihan = "Tagihan selisih sebesar Rp " . number_format($selisih, 0, ',', '.') . " telah dibuat. Prodi otomatis berubah SETELAH mahasiswa melunasi VA ini.";
            
            } else {
                $ereg->update([
                    'jurusan_lulusan' => $prodiBaru->kode_prodi
                ]);

                $masterUser = \App\Models\MasterUser::where('user_id', $user->id)->first();
                if ($masterUser) {
                    MasterMoodleLulus::where('master_user_id', $masterUser->id)
                        ->update([
                            'prodi_lulus'      => $prodiBaru->kode_prodi,
                            'nama_prodi_lulus' => $prodiBaru->nama_prodi
                        ]);
                }

                $infoTagihan = "Prodi berhasil diubah langsung tanpa penambahan tagihan VA.";
            }

            DB::commit();
            return redirect()->back()->with('success', $infoTagihan);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error Ubah Prodi Manual: " . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    /**
     * FITUR BARU: Membatalkan/Menghapus VA Selisih Pindah Prodi yang masih pending
     */
    public function batalVaSelisih($billingId)
    {
        DB::beginTransaction();
        try {
            $billing = SlikBilling::where('id', $billingId)
                ->where('status', 'pending')
                ->where('order_id', 'like', '%SELISIH%')
                ->firstOrFail();

            // Opsional: Panggil API Mandiri untuk melidasi/menutup bill jika service uktMandiriApi menyediakan method terkait,
            // contoh: $this->mandiriService->cancelBilling($billing->trx_id);

            // Update status billing di lokal database agar tidak aktif
            $billing->update([
                'status'    => 'cancel'
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Virtual Account selisih berhasil dihapus/dibatalkan.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error Batal VA Selisih: " . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal membatalkan Virtual Account: ' . $e->getMessage());
        }
    }
}