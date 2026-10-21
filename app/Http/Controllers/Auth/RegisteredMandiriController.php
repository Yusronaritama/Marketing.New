<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\MasterUser;
use App\Models\AkunPendaftarDefault;
use App\Services\WhatsAppService;
use App\Services\MandiriApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\MasterEreg;
use Illuminate\Support\Facades\Mail;
use App\Mail\AkunPendaftarMail;

use Illuminate\View\View;
use Exception;
use Carbon\Carbon;

class RegisteredMandiriController extends Controller
{
    public function create(): View
    {
        $today = date('Y-m-d');
        
        $semuaGelombang = DB::table('gelombang')
            ->orderBy('tanggal_mulai', 'asc')
            ->get();

        $isPendaftaranOpen = DB::table('gelombang')
            ->where('tanggal_mulai', '<=', $today)
            ->where('tanggal_selesai', '>=', $today)
            ->exists();

        // BYPASS SEMENTARA: Agar form tidak tertutup saat testing/opsi diaktifkan
        $isPendaftaranOpen = true;

        return view('auth.test', [
            'is_maintenance' => $this->isSystemMaintenance(),
            'pendaftaran_tutup' => !$isPendaftaranOpen,
            'daftarGelombang' => $semuaGelombang, 
            'maintenance_message' => '⚠ Sistem pendaftaran sedang dalam pemeliharaan.'
        ]);
    }

    private function isSystemMaintenance(): bool
    {
        return env('APP_MAINTENANCE') === 'true';
    }

    public function checkDuplicate(Request $request)
    {
        $email = $request->email;
        $phone = $request->phone;

        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        } elseif (str_starts_with($phone, '08')) {
            $phone = '62' . substr($phone, 2);
        }

        $existing = User::where('email', $email)
                        ->orWhere('phone', $phone)
                        ->first();

        if ($existing) {
            Auth::login($existing);
            $request->session()->regenerate();

            return response()->json([
                'is_duplicate' => true,
                'message'      => 'Akun Anda sudah terdaftar. Mengarahkan Anda kembali ke Dashboard...',
                'redirect'     => route('mahasiswa.va.dashboard')
            ]);
        }

        return response()->json(['is_duplicate' => false]);
    }

    public function store(Request $request)
    {
        set_time_limit(60);
    
        if ($this->isSystemMaintenance()) {
            return response()->json(['status' => 'error', 'message' => 'Sistem maintenance.'], 503);
        }
    
        // 1. Validasi Inputan Form
        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'email'          => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
            'phone'          => ['required', 'regex:/^08[0-9]{8,11}$/'],
            'type'           => ['required', 'in:reguler,s2,apoteker'], 
            'program_study'  => ['required_if:type,s2'],
            'is_alumni'      => ['nullable', 'in:1,0'],
            'nim'            => ['nullable', 'string', 'max:50'], 
            'promo_code'     => ['nullable', 'string', 'max:50'],
        ]);
    
        $isOpen = false;
        $gelombangSaatIni = 1;
        $gelombangNama = 'Umum';
    
        // 2. Cek Jadwal
        if ($validated['type'] === 'apoteker') {
            $activeGel = $this->getActiveGelombangForApoteker();
                
            if ($activeGel) {
                $isOpen = true;
                $gelombangSaatIni = $activeGel->id; 
                $gelombangNama = $activeGel->nama_gelombang; 
            }
        } else {
            $activeGel = DB::table('master_gelombangs')
                ->where('is_active', 1)
                ->first();
                
            if ($activeGel) {
                $isOpen = true;
                $gelombangSaatIni = $activeGel->angka_gelombang;
                $gelombangNama = 'Gelombang ' . $gelombangSaatIni;
            }
        }
    
        if (!$isOpen) {
            // BYPASS SEMENTARA: Agar Anda bisa mendaftar meskipun di DB sedang ditutup
            $isOpen = true;
            $gelombangSaatIni = 1;
            $gelombangNama = 'Umum';
            
            // return response()->json([
            //     'status' => 'error', 
            //     'message' => 'Pendaftaran untuk jalur ini saat ini sedang ditutup atau di luar jadwal.'
            // ], 403);
        }
    
        // 3. Olah Nomor HP & Password
        $cleanPhone     = preg_replace('/\D/', '', $validated['phone']);
        $formattedPhone = '62' . substr($validated['phone'], 1);
        $phonePassword  = 'PMB' . substr($cleanPhone, -8);
        $plainPassword  = $phonePassword;
    
        $nim = null;
        if ($validated['type'] === 'apoteker' && $request->input('is_alumni') == '1') {
            $nim = !empty($validated['nim']) ? $validated['nim'] : null;
        }
    
        // 4. Logika Nominal Biaya Original
        if ($validated['type'] === 's2') {
            $prodi = DB::table('program_studi')->where('kode_prodi', $validated['program_study'])->first();
            $registrationFee = $prodi->biaya_pendaftaran ?? 500000;
            $namaJalur       = "Jalur S2 " . ($prodi->nama_prodi ?? 'Manajemen');
        } elseif ($validated['type'] === 'apoteker') {
            $prodiApoteker = DB::table('program_studi')->where('kode_prodi', '00000')->first();
    
            if ($request->input('is_alumni') == '1') {
                $registrationFee = $prodiApoteker->pendaftaran_alumni_ubp ?? 750000;
                $namaJalur       = "Jalur Profesi Apoteker (Alumni UBP)";
            } else {
                $registrationFee = $prodiApoteker->biaya_pendaftaran ?? 1000000;
                $namaJalur       = "Jalur Profesi Apoteker (Non-Alumni)";
            }
        } else {
            $registrationFee = 300000;
            $namaJalur       = "Jalur Reguler";
        }
    
        $originalAmount     = $registrationFee;
        $discountAmount     = 0;
        $discountPercentage = 0;
        $appliedPromo       = null;
        $isFree             = false;
    
        // 5. VALIDASI & LOGIKA KODE PROMO
        if ($request->filled('promo_code')) {
            // Cek apakah jalur mengizinkan promo
            if (!in_array($validated['type'], ['reguler', 'apoteker'])) {
                return response()->json(['status' => 'error', 'message' => 'Jalur ini tidak mendukung penggunaan kode promo.'], 400);
            }

            $promo = DB::table('promo_codes')->where('code', strtoupper($request->promo_code))->first();
    
            // Validasi ketat jika kode promo tidak memenuhi syarat
            if (!$promo) {
                return response()->json(['status' => 'error', 'message' => 'Kode promo tidak ditemukan.'], 404);
            }
            if ($promo->is_active != 1) {
                return response()->json(['status' => 'error', 'message' => 'Kode promo sudah tidak aktif.'], 400);
            }
            if ($promo->expired_at && Carbon::parse($promo->expired_at)->isPast()) {
                return response()->json(['status' => 'error', 'message' => 'Kode promo sudah kedaluwarsa.'], 400);
            }
            if ($promo->usage_limit !== null && $promo->used_count >= $promo->usage_limit) {
                return response()->json(['status' => 'error', 'message' => 'Batas penggunaan/kuota kode promo telah habis.'], 400);
            }

            // Terapkan Diskon
            $appliedPromo       = $promo->code;
            $discountPercentage = (int) $promo->discount;
    
            $discountAmount  = (int) round(($originalAmount * $discountPercentage) / 100);
            $registrationFee = max(0, $originalAmount - $discountAmount);
    
            // Jika diskon 100% atau tagihan menjadi 0
            if ($discountPercentage >= 100 || $registrationFee <= 0) {
                $registrationFee = 0;
                $discountAmount  = $originalAmount;
                $isFree          = true;
            }
        }
    
        DB::beginTransaction();
        try {
            // 6. Buat User Utama
            Log::info("Mendaftar dengan tipe: " . $validated['type']);
            $user = User::create([
                'name'     => $validated['name'],
                'email'    => $validated['email'],
                'phone'    => $formattedPhone,
                'password' => Hash::make($plainPassword),
                'role'     => 'mahasiswa_baru',
                'nim'      => $nim,
                'profesi'  => ($validated['type'] === 'apoteker') ? 'Apoteker Farmasi' : null,
            ]);
    
            // ==============================================================
            // 7. Proses VA Mandiri (DIPERBARUI)
            // ==============================================================
            $mandiriService = new MandiriApiService();
            
            // Trik: API Mandiri akan error jika tagihan Rp 0. 
            // Jadi jika $isFree (gratis), kita kirim $originalAmount ke Mandiri 
            // HANYA untuk mendapatkan VA Number yang valid.
            $apiAmount = $isFree ? $originalAmount : $registrationFee;
            
            $vaResponse = $mandiriService->createBilling($user, $apiAmount, null, 'PENDAFTARAN_PMB');
    
            if (!$vaResponse) {
                throw new Exception("Gagal mendapatkan Virtual Account Mandiri.");
            }
    
            // Ambil data asli dari response Bank Mandiri
            $vaNumber  = $vaResponse['virtual_account'];
            $trxId     = $vaResponse['trx_id'];
            $vaExpired = $vaResponse['expired_at'];
            
            // JIKA MENGGUNAKAN PROMO 100%, PAKSA STATUS LOKAL JADI "PAID"
            $vaStatus  = $isFree ? 'paid' : 'pending'; 
            // ==============================================================
    
            // 8. Simpan ke master_users
            $masterUser = MasterUser::create([
                'user_id'             => $user->id,
                'gelombang'           => $gelombangSaatIni,
                'password'            => Hash::make($plainPassword),
                'plain_password'      => $plainPassword,
                'trx_id'              => $trxId,        // Asli dari Mandiri
                'va_number'           => $vaNumber,     // Asli dari Mandiri
                'va_status'           => $vaStatus,     // Otomatis "paid" jika gratis
                'promo_code_used'     => $appliedPromo,
                'va_amount'           => $registrationFee, // Disimpan Rp 0 jika gratis
                'original_amount'     => $originalAmount,
                'discount_amount'     => $discountAmount,
                'discount_percentage' => $discountPercentage,
                'va_expired_at'       => $vaExpired,
            ]);
    
            if ($appliedPromo) {
                DB::table('promo_codes')->where('code', $appliedPromo)->increment('used_count');
            }
    
            // 9. Simpan ke master_ereg
            DB::table('master_ereg')->insert([
                'user_id'           => $user->id,
                'nama'              => $user->name,
                'whatsapp'          => $user->phone,
                'gmail'             => $user->email,
                'jalur_pendaftaran' => $namaJalur . " (" . $gelombangNama . ")",
                'jurusan1'          => ($validated['type'] === 's2') ? $validated['program_study'] : (($validated['type'] === 'apoteker') ? '00000' : null),
                'status'            => $vaStatus, // Otomatis "paid" jika gratis
                'step_completed'    => 1,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
    
            // 10. Simpan ke akun default
            AkunPendaftarDefault::updateOrCreate(
                ['email' => $user->email],
                [
                    'name'           => $user->name,
                    'phone'          => $user->phone,
                    'va_number'      => $vaNumber, // Asli dari Mandiri
                    'va_status'      => $vaStatus, // Otomatis "paid" jika gratis
                    'password_plain' => $plainPassword,
                ]
            );
    
            DB::commit();
            Auth::login($user);
            $this->syncExternalServices($user, $masterUser, $plainPassword);
    
            return response()->json([
                'status'     => 'success',
                'message'    => 'Pendaftaran Berhasil!',
                'va_number'  => $vaNumber,
                'va_status'  => $vaStatus,
                'is_free'    => $isFree,
                'amount'     => $registrationFee,
                'user'       => $user->name,
                'redirect'   => route('mahasiswa.va.dashboard')
            ]);
    
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Registration Error: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()], 500);
        }
    }

    private function syncExternalServices($user, $masterUser, $password)
    {
        try {
            $wa = new WhatsAppService();
            $wa->sendCompleteLoginInfo($user, $masterUser);
        } catch (Exception $e) {
            Log::error('WA Error: ' . $e->getMessage());
        }

        try {
            if (!empty($user->email)) {
                $username = $masterUser->trx_id; 
                $va_number = $masterUser->va_number;
                Mail::to($user->email)->send(new AkunPendaftarMail($username, $password, $va_number));
                Log::info("📧 Email info pendaftaran berhasil dikirim ke: {$user->email}");
            }
        } catch (Exception $e) {
            Log::error('Email Error: ' . $e->getMessage());
        }
    }

    private function getActiveGelombangForApoteker()
    {
        $today = Carbon::today()->format('Y-m-d');
        
        return DB::table('gelombang')
            ->where('tanggal_mulai', '<=', $today)
            ->where('tanggal_selesai', '>=', $today)
            ->first();
    }

    public function validatePromo(Request $request)
    {
        $request->validate([
            'promo_code' => 'required|string',
            'type'       => 'nullable|string',
            'is_alumni'  => 'nullable|string'
        ]);

        // Ganti pengecekan awal agar bisa memberikan pesan error yang spesifik (tidak di-chain dengan where)
        $promo = DB::table('promo_codes')
            ->where('code', strtoupper($request->promo_code))
            ->first();

        // VALIDASI KETAT
        if (!$promo) {
            return response()->json(['valid' => false, 'message' => 'Kode promo tidak ditemukan.']);
        }
        if ($promo->is_active != 1) {
            return response()->json(['valid' => false, 'message' => 'Kode promo sudah tidak aktif.']);
        }
        if ($promo->expired_at && Carbon::parse($promo->expired_at)->isPast()) {
            return response()->json(['valid' => false, 'message' => 'Kode promo sudah kedaluwarsa.']);
        }
        if ($promo->usage_limit !== null && $promo->used_count >= $promo->usage_limit) {
            return response()->json(['valid' => false, 'message' => 'Kuota penggunaan kode promo habis.']);
        }

        $type = $request->input('type', 'reguler');
        if ($type === 'apoteker') {
            $prodiApoteker = DB::table('program_studi')->where('kode_prodi', '00000')->first();
            if ($request->input('is_alumni') == '1') {
                $originalPrice = $prodiApoteker->pendaftaran_alumni_ubp ?? 750000;
            } else {
                $originalPrice = $prodiApoteker->biaya_pendaftaran ?? 1000000;
            }
        } elseif ($type === 's2') {
            $originalPrice = 500000;
        } else {
            $originalPrice = 300000; 
        }

        $discountPct    = (int) $promo->discount;
        $discountAmount = (int) round(($originalPrice * $discountPct) / 100);
        $finalPrice     = max(0, $originalPrice - $discountAmount);
        
        // Pengecekan 100% atau Lunas
        $isPaid         = ($discountPct >= 100 || $finalPrice <= 0);

        return response()->json([
            'valid'        => true,
            'promo_code'  => [
                'code'        => $promo->code,
                'discount'    => $discountPct,
                'description' => $promo->description ?? '',
            ],
            'status_info' => ['is_paid' => $isPaid],
            'pricing'     => [
                'formatted_original'   => 'Rp ' . number_format($originalPrice,  0, ',', '.'),
                'formatted_discounted' => 'Rp ' . number_format($finalPrice,     0, ',', '.'),
                'formatted_saving'     => 'Rp ' . number_format($discountAmount, 0, ',', '.'),
            ],
        ]);
    }
}