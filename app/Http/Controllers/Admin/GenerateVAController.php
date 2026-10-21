<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\MasterUser;
use App\Models\ProgramStudi;
use App\Models\SlikBilling;
use App\Models\MasterEreg;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GenerateVAController extends Controller
{
    private $url_slik;

    public function __construct()
    {
        $this->url_slik = 'https://slik.ubpkarawang.ac.id/api/mandiri/';
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        // Query utama: Ambil semua data dari master_ereg
        $query = MasterEreg::with([
            'user.biodata',
            'user.masterUsersVerified',
            'user.masterMoodleLulus'
        ]);

        // Filter berdasarkan kelulusan
        if ($request->has('kelulusan') && $request->kelulusan !== '') {
            if ($request->kelulusan == '1') {
                $query->whereHas('user.masterMoodleLulus');
            } else {
                $query->whereDoesntHave('user.masterMoodleLulus');
            }
        }

        // Filter berdasarkan status verifikasi
        if ($request->has('verifikasi') && $request->verifikasi !== '') {
            if ($request->verifikasi == '1') {
                $query->whereHas('user.masterUsersVerified');
            } else {
                $query->whereDoesntHave('user.masterUsersVerified');
            }
        }

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('gmail', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('jurusan1', 'like', "%{$search}%")
                  ->orWhere('jurusan2', 'like', "%{$search}%")
                  ->orWhereHas('user', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  })
                  ->orWhereHas('user.biodata', function($q) use ($search) {
                      $q->where('nama_lengkap', 'like', "%{$search}%");
                  });
            });
        }

        $masterEregs = $query->orderBy('created_at', 'desc')->paginate(20);

        // Ambil semua user_id dari master_ereg untuk mengambil data VA
        $userIds = $masterEregs->pluck('user_id')->filter()->unique();
        
        // Ambil data VA yang sudah digenerate
        $vaData = SlikBilling::whereIn('user_id', $userIds)
            ->get()
            ->keyBy('user_id');

        // Ambil data program studi untuk mapping
        $prodiMapping = ProgramStudi::aktif()->orderBy('kode_prodi')->get()->keyBy('kode_prodi');

        return view('admin.generate-va.index', compact(
            'masterEregs',
            'vaData',
            'prodiMapping'
        ));
    }

    /**
     * Show the form for creating a new VA
     */
    public function create($id): View
    {
        // Cari data berdasarkan master_ereg_id
        $masterEreg = MasterEreg::with([
            'user.biodata',
            'user.masterUsersVerified',
            'user.masterMoodleLulus'
        ])->findOrFail($id);

        $user = $masterEreg->user;

        if (!$user) {
            abort(404, 'User tidak ditemukan untuk data master ereg ini.');
        }

        // CEK VERIFIKASI
        $masterUserVerified = $user->masterUsersVerified()->first();
        $isVerified = !empty($masterUserVerified);
        
        if (!$isVerified) {
            session()->flash('warning', 'User belum terverifikasi. Pastikan user sudah diverifikasi sebelum generate VA.');
        }

        // Ambil data program studi aktif untuk dropdown
        $prodiList = ProgramStudi::aktif()->orderBy('kode_prodi')->get();

        // Tentukan program studi yang akan ditampilkan di dropdown
        $availableProdi = $this->getAvailableProdi($masterEreg, $prodiList);

        // Siapkan data prodi pilihan untuk ditampilkan di view
        $prodiPilihan = $this->getProdiPilihanData($masterEreg, $prodiList);

        // Cek apakah sudah ada VA aktif
        $existingVA = SlikBilling::where('user_id', $user->id)
            ->where('status', 'pending')
            ->first();

        return view('admin.generate-va.create', compact(
            'masterEreg',
            'user',
            'prodiList',
            'existingVA',
            'availableProdi',
            'prodiPilihan',
            'isVerified',
            'masterUserVerified'
        ));
    }

    /**
     * Store a newly created VA
     */
    public function store(Request $request, $id)
    {
        $request->validate([
            'tenor_pembayaran' => 'required|in:semester,tahun,cicilan',
            'kode_prodi' => 'required|exists:program_studi,kode_prodi',
            'custom_amount' => 'nullable|numeric|min:0',
            'confirm_unverified' => 'nullable|boolean',
        ]);

        // Cari data berdasarkan master_ereg_id
        $masterEreg = MasterEreg::with([
            'user.biodata',
            'user.masterUsersVerified',
            'user.masterMoodleLulus'
        ])->findOrFail($id);

        $user = $masterEreg->user;

        if (!$user) {
            return redirect()->back()
                ->with('error', 'User tidak ditemukan untuk data master ereg ini.');
        }

        // CEK VERIFIKASI
        $masterUserVerified = $user->masterUsersVerified()->first();
        $isVerified = !empty($masterUserVerified);
        
        if (!$isVerified && !$request->has('confirm_unverified')) {
            return redirect()->back()
                ->with('warning', 'User belum terverifikasi. Centang konfirmasi jika ingin tetap melanjutkan.')
                ->withInput();
        }

        // Cek apakah sudah ada VA pending
        $existingVA = SlikBilling::where('user_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if ($existingVA) {
            return redirect()->back()
                ->with('error', 'User sudah memiliki VA yang masih aktif. Batalkan VA sebelumnya untuk membuat yang baru.');
        }

        try {
            DB::beginTransaction();

            // Ambil data program studi dari tabel program_studi
            $prodi = ProgramStudi::where('kode_prodi', $request->kode_prodi)->first();
            if (!$prodi) {
                throw new \Exception('Program studi tidak ditemukan.');
            }

            // Tentukan harga standar berdasarkan tenor dari tabel
            $hargaStandar = $this->getHargaByTenor($prodi, $request->tenor_pembayaran);
            
            // Tentukan harga final: gunakan custom amount jika ada, jika tidak gunakan harga standar
            $totalBayar = $request->custom_amount && $request->custom_amount > 0 
                ? $request->custom_amount 
                : $hargaStandar;
            
            $isCustomAmount = $request->custom_amount && $request->custom_amount > 0 && $request->custom_amount != $hargaStandar;
            
            if ($totalBayar <= 0) {
                throw new \Exception('Harga tidak valid untuk program studi dan tenor yang dipilih. Silakan pilih program studi dan tenor lain.');
            }

            // Generate order ID yang lebih unik
            $orderId = 'VA-' . date('YmdHis') . '-' . $user->id . '-' . Str::random(4);

            // Prepare data untuk API SLIK
            $phone = $masterEreg->phone ?? $user->biodata->no_telepon ?? $user->phone ?? '081234567890';
            // Bersihkan nomor telepon dari karakter non-digit
            $phone = preg_replace('/[^0-9]/', '', $phone);
            // Jika nomor diawali dengan 0, ubah menjadi 62
            if (substr($phone, 0, 1) === '0') {
                $phone = '62' . substr($phone, 1);
            }
            
            $data = [
                'user' => [
                    'name' => $masterEreg->nama ?? $user->biodata->nama_lengkap ?? $user->name,
                    'email' => $masterEreg->gmail ?? $user->email,
                    'handphone' => $phone,
                    'nim' => (string) $user->id,
                ],
                'total_bayar' => (float) $totalBayar,
                'datetime_expired' => now()->addDays(2)->format('Y-m-d H:i:s'),
            ];

            \Log::info('Sending request to SLIK API:', [
                'url' => $this->url_slik . 'create_va_pmb',
                'data' => $data
            ]);

            // Kirim request ke API SLIK
            $response = Http::timeout(60)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post($this->url_slik . 'create_va_pmb', $data);

            \Log::info('SLIK API Response:', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            if (!$response->successful()) {
                throw new \Exception('Gagal terhubung ke API SLIK. Status: ' . $response->status() . ' - ' . $response->body());
            }

            $responseData = $response->json();

            // Cek response
            if (!isset($responseData['data']) && !isset($responseData['va_bni'])) {
                throw new \Exception('API SLIK Error: Response tidak valid - ' . json_encode($responseData));
            }

            // Handle response - SLIK biasanya mengembalikan va_bni langsung
            $vaNumber = $responseData['va_bni'] ?? $responseData['data']['va_bni'] ?? $responseData['virtual_account'] ?? null;
            
            if (empty($vaNumber)) {
                throw new \Exception('API SLIK Error: Nomor VA tidak ditemukan dalam response');
            }

            // Simpan data VA ke database
            $slikBilling = SlikBilling::create([
                'user_id' => $user->id,
                'order_id' => $orderId,
                'virtual_account' => $vaNumber,
                'kode_prodi' => $request->kode_prodi,
                'tenor_pembayaran' => $request->tenor_pembayaran,
                'amount' => $totalBayar,
                'is_custom_amount' => $isCustomAmount,
                'standard_amount' => $hargaStandar,
                'status' => 'pending',
                'slik_request_payload' => json_encode($data),
                'slik_response_data' => json_encode($responseData),
                'datetime_expired' => now()->addDays(2),
            ]);

            // Update user VA data di tabel users (jika ada kolom VA di users)
            if (Schema::hasTable('users')) {
                if (Schema::hasColumn('users', 'va_number')) {
                    $user->update([
                        'va_number' => $vaNumber,
                        'va_amount' => $totalBayar,
                        'va_expired_at' => now()->addDays(2),
                        'va_status' => 'pending',
                    ]);
                }
            }

            // Update juga di master_users jika ada
            $masterUser = MasterUser::where('user_id', $user->id)->first();
            if ($masterUser) {
                $masterUser->update([
                    'va_number' => $vaNumber,
                    'va_amount' => $totalBayar,
                    'va_expired_at' => now()->addDays(2),
                    'va_status' => 'pending',
                ]);
            }

            DB::commit();

            // Buat success message yang informatif
            $message = 'Virtual Account berhasil digenerate: ' . $vaNumber;
            
            if ($isCustomAmount) {
                $message .= ' dengan Custom Amount: Rp ' . number_format($totalBayar, 0, ',', '.') 
                         . ' (Standar: Rp ' . number_format($hargaStandar, 0, ',', '.') . ')';
            } else {
                $message .= ' dengan Amount: Rp ' . number_format($totalBayar, 0, ',', '.');
            }
            
            // Tambahkan info jika user belum terverifikasi
            if (!$isVerified) {
                $message .= ' (User belum terverifikasi)';
            }
            
            return redirect()->route('admin.generate-va.index')
                ->with('success', $message);
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Generate VA Error: ' . $e->getMessage(), [
                'master_ereg_id' => $id,
                'request' => $request->all(),
                'url' => $this->url_slik . 'create_va_pmb',
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Gagal generate Virtual Account: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Cancel VA
     */
    public function destroy($id)
    {
        // Cari berdasarkan master_ereg_id
        $masterEreg = MasterEreg::with(['user'])->findOrFail($id);
        $user = $masterEreg->user;

        if (!$user) {
            return redirect()->back()
                ->with('error', 'User tidak ditemukan untuk data master ereg ini.');
        }

        try {
            DB::beginTransaction();

            // Hapus data VA
            $deleted = SlikBilling::where('user_id', $user->id)
                ->where('status', 'pending')
                ->delete();

            // Reset user VA data
            if (Schema::hasTable('users') && Schema::hasColumn('users', 'va_number')) {
                $user->update([
                    'va_number' => null,
                    'va_amount' => null,
                    'va_expired_at' => null,
                    'va_status' => null,
                ]);
            }

            // Reset juga di master_users
            $masterUser = MasterUser::where('user_id', $user->id)->first();
            if ($masterUser) {
                $masterUser->update([
                    'va_number' => null,
                    'va_amount' => null,
                    'va_expired_at' => null,
                    'va_status' => null,
                ]);
            }

            DB::commit();

            return redirect()->route('admin.generate-va.index')
                ->with('success', 'Virtual Account berhasil dibatalkan.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Cancel VA Error: ' . $e->getMessage(), ['master_ereg_id' => $id]);
            
            return redirect()->back()
                ->with('error', 'Gagal membatalkan Virtual Account: ' . $e->getMessage());
        }
    }

    /**
     * Check VA status
     */
    public function checkStatus($id)
    {
        // Cari berdasarkan master_ereg_id
        $masterEreg = MasterEreg::with(['user'])->findOrFail($id);
        $user = $masterEreg->user;

        if (!$user) {
            return redirect()->back()
                ->with('error', 'User tidak ditemukan untuk data master ereg ini.');
        }

        $slikBilling = SlikBilling::where('user_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if (!$slikBilling) {
            return redirect()->back()
                ->with('error', 'Tidak ada VA pending ditemukan untuk user ini.');
        }

        try {
            // Kirim request ke API SLIK untuk cek status
            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post($this->url_slik . 'get_va_pmb', [
                    'va' => $slikBilling->virtual_account,
                    'user_id' => $user->id,
                ]);

            if ($response->successful()) {
                $responseData = $response->json();
                
                // Update status berdasarkan response
                if (isset($responseData['status']) && $responseData['status'] === 'paid') {
                    DB::beginTransaction();
                    
                    $slikBilling->update([
                        'status' => 'paid',
                        'payment_ntb' => $responseData['payment_ntb'] ?? null,
                        'payment_date' => now(),
                        'datetime_payment' => now(),
                    ]);

                    // Update user VA status
                    if (Schema::hasTable('users') && Schema::hasColumn('users', 'va_status')) {
                        $user->update([
                            'va_status' => 'paid',
                            'paid_at' => now(),
                        ]);
                    }
                    
                    // Update juga di master_users
                    $masterUser = MasterUser::where('user_id', $user->id)->first();
                    if ($masterUser) {
                        $masterUser->update([
                            'va_status' => 'paid',
                            'paid_at' => now(),
                        ]);
                    }
                    
                    // Update prodi di master_ereg berdasarkan prodi yang dipilih saat generate VA
                    if ($slikBilling->kode_prodi) {
                        $masterEreg->update([
                            'jurusan1' => $slikBilling->kode_prodi,
                            'jurusan2' => null,
                        ]);
                    }
                    
                    DB::commit();
                    
                    return redirect()->back()
                        ->with('success', 'Status VA: PAID - Prodi berhasil diupdate ke ' . ($slikBilling->kode_prodi ?? 'tidak ada'));
                } else {
                    $status = $responseData['status'] ?? 'pending';
                    return redirect()->back()
                        ->with('success', 'Status VA: ' . strtoupper($status));
                }
            }

            return redirect()->back()
                ->with('error', 'Gagal memeriksa status VA. Status: ' . $response->status() . ' - ' . $response->body());

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Check VA Status Error: ' . $e->getMessage(), [
                'master_ereg_id' => $id,
                'url' => $this->url_slik . 'get_va_pmb',
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Gagal memeriksa status VA: ' . $e->getMessage());
        }
    }

    /**
     * Get available program studi for dropdown
     */
    private function getAvailableProdi($masterEreg, $prodiList)
    {
        $availableProdi = collect();
        
        // SELALU tampilkan kedua prodi pilihan dari master_ereg (jika ada)
        if ($masterEreg) {
            $this->addProdiFromEreg($availableProdi, $masterEreg, $prodiList);
        }
        
        // Jika user sudah lulus, tambahkan juga prodi lulus (jika berbeda dengan pilihan)
        $user = $masterEreg->user;
        if ($user && $user->masterMoodleLulus && $user->masterMoodleLulus->prodi_lulus) {
            $prodiLulus = $user->masterMoodleLulus->prodi_lulus;
            
            // Cari program studi yang sesuai dengan prodi lulus
            foreach ($prodiList as $prodi) {
                if (($prodi->kode_prodi == $prodiLulus || 
                     strpos($prodi->nama_prodi, $prodiLulus) !== false) && 
                    !$availableProdi->contains('kode_prodi', $prodi->kode_prodi)) {
                    $availableProdi->push($prodi);
                }
            }
        }
        
        // Jika masih kosong, tampilkan semua program studi
        if ($availableProdi->isEmpty()) {
            $availableProdi = collect($prodiList);
        }
        
        return $availableProdi;
    }

    /**
     * Add program studi dari data master_ereg
     */
    private function addProdiFromEreg(&$availableProdi, $masterEreg, $prodiList)
    {
        // Tambahkan prodi pilihan 1
        if ($masterEreg->jurusan1) {
            foreach ($prodiList as $prodi) {
                if ($prodi->kode_prodi == $masterEreg->jurusan1) {
                    $availableProdi->push($prodi);
                    break;
                }
            }
        }
        
        // Tambahkan prodi pilihan 2
        if ($masterEreg->jurusan2) {
            foreach ($prodiList as $prodi) {
                if ($prodi->kode_prodi == $masterEreg->jurusan2 && 
                    !$availableProdi->contains('kode_prodi', $masterEreg->jurusan2)) {
                    $availableProdi->push($prodi);
                    break;
                }
            }
        }
    }

    /**
     * Get prodi pilihan data for display
     */
    private function getProdiPilihanData($masterEreg, $prodiList)
    {
        $prodiPilihan = [
            'pilihan1' => null,
            'pilihan2' => null
        ];

        if ($masterEreg) {
            if ($masterEreg->jurusan1) {
                $prodiPilihan['pilihan1'] = [
                    'kode' => $masterEreg->jurusan1,
                    'nama' => $this->getNamaProdi($masterEreg->jurusan1, $prodiList)
                ];
            }
            
            if ($masterEreg->jurusan2) {
                $prodiPilihan['pilihan2'] = [
                    'kode' => $masterEreg->jurusan2,
                    'nama' => $this->getNamaProdi($masterEreg->jurusan2, $prodiList)
                ];
            }
        }

        return $prodiPilihan;
    }

    /**
     * Get nama program studi dari kode program studi
     */
    private function getNamaProdi($kodeProdi, $prodiList)
    {
        if (!$kodeProdi) return '-';
        
        foreach ($prodiList as $prodi) {
            if ($prodi->kode_prodi == $kodeProdi) {
                return $prodi->nama_prodi;
            }
        }
        
        return $kodeProdi; // Return kode jika nama tidak ditemukan
    }

    /**
     * Get harga berdasarkan tenor
     */
    private function getHargaByTenor($prodi, $tenor)
    {
        switch ($tenor) {
            case 'cicilan':
                return $prodi->harga_cicilan ?? 0;
            case 'semester':
                return $prodi->harga_semester ?? 0;
            case 'tahun':
                return $prodi->harga_tahun ?? 0;
            default:
                return 0;
        }
    }

    /**
     * Get harga untuk AJAX request
     */
    public function getHarga($kodeProdi, $tenor)
    {
        try {
            $prodi = ProgramStudi::where('kode_prodi', $kodeProdi)->first();
            
            if (!$prodi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Program studi tidak ditemukan'
                ]);
            }

            $harga = $this->getHargaByTenor($prodi, $tenor);

            if ($harga <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Harga untuk tenor '{$tenor}' tidak tersedia untuk program studi ini"
                ]);
            }

            // Ambil semua tenor yang tersedia
            $availableTenors = [];
            if ($prodi->harga_cicilan > 0) {
                $availableTenors['cicilan'] = $prodi->harga_cicilan;
            }
            if ($prodi->harga_semester > 0) {
                $availableTenors['semester'] = $prodi->harga_semester;
            }
            if ($prodi->harga_tahun > 0) {
                $availableTenors['tahun'] = $prodi->harga_tahun;
            }

            return response()->json([
                'success' => true,
                'price' => $harga,
                'price_formatted' => 'Rp ' . number_format($harga, 0, ',', '.'),
                'prodi_nama' => $prodi->nama_prodi,
                'tenor' => $tenor,
                'available_tenors' => $availableTenors
            ]);

        } catch (\Exception $e) {
            \Log::error('Get Harga Error: ' . $e->getMessage(), [
                'kode_prodi' => $kodeProdi,
                'tenor' => $tenor
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Test connection to SLIK API
     */
    public function testConnection()
    {
        try {
            $testData = [
                'user' => [
                    'name' => 'Test User',
                    'email' => 'test@example.com',
                    'handphone' => '6281234567890',
                    'nim' => 'TEST123',
                ],
                'total_bayar' => 100000,
                'datetime_expired' => now()->addDays(2)->format('Y-m-d H:i:s'),
            ];

            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post($this->url_slik . 'create_va_pmb', $testData);

            return response()->json([
                'success' => $response->successful(),
                'status' => $response->status(),
                'response' => $response->json(),
                'url' => $this->url_slik . 'create_va_pmb'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'url' => $this->url_slik . 'create_va_pmb'
            ]);
        }
    }

    /**
     * Handle payment callback from SLIK
     */
    public function callbackPayment(Request $request)
    {
        try {
            \Log::info('SLIK CALLBACK PAYMENT RECEIVED', $request->all());

            // Validasi input
            $validated = $request->validate([
                'id_pendaftar' => 'required',
                'no_va' => 'required',
                'payment_ntb' => 'required',
                'trx_id' => 'required',
                'status' => 'required|in:paid,success',
            ]);

            // Cari billing berdasarkan VA
            $billing = SlikBilling::where('virtual_account', $validated['no_va'])->first();

            if (!$billing) {
                return response()->json([
                    'success' => false,
                    'message' => 'VA tidak ditemukan'
                ], 404);
            }

            // Update status pembayaran
            $billing->update([
                'status' => 'paid',
                'payment_ntb' => $validated['payment_ntb'],
                'trx_id' => $validated['trx_id'],
                'datetime_payment' => now(),
            ]);

            // Update juga user di tabel users
            $user = User::find($billing->user_id);
            if ($user && Schema::hasColumn('users', 'va_status')) {
                $user->update([
                    'va_status' => 'paid',
                    'paid_at' => now(),
                ]);
            }

            // Update juga di master_users
            $masterUser = MasterUser::where('user_id', $billing->user_id)->first();
            if ($masterUser) {
                $masterUser->update([
                    'va_status' => 'paid',
                    'paid_at' => now(),
                    'trx_id' => $validated['trx_id'],
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Callback processed successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('SLIK CALLBACK ERROR', [
                'message' => $e->getMessage(),
                'payload' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal error: ' . $e->getMessage()
            ], 500);
        }
    }
}