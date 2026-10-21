<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\BniConfig;
use App\Models\MasterUser;
use App\Models\PromoCode;
use App\Models\AkunPendaftarDefault; // ✅ Import Model Baru
use App\Exports\RegistrantsExport;    // ✅ Import Export Class
use App\Services\WhatsAppService;
use App\Services\BNIApiService;
use Illuminate\Support\Facades\Mail;
use App\Mail\RegistrationSuccessMail; 
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Exception;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel; // ✅ Import Facade Excel

class RegisteredUserController extends Controller
{
    public function view(): View
    {
        $bniCredentialsValid = $this->checkBniCredentials();
        return view('auth.register', [
            'bni_credentials_valid' => $bniCredentialsValid,
            'bni_error_message' => !$bniCredentialsValid ? 
                '⚠ Sistem pembayaran sedang dalam maintenance. Silakan coba lagi nanti atau hubungi admin.' : null
        ]);
    }
    
    public function create(): View
    {
        $bniCredentialsValid = $this->checkBniCredentials();
        return view('auth.register', [
            'bni_credentials_valid' => $bniCredentialsValid,
            'bni_error_message' => !$bniCredentialsValid ? 
                '⚠ Sistem pembayaran sedang dalam maintenance. Silakan coba lagi nanti atau hubungi admin.' : null
        ]);
    }
    
    public function store(Request $request): RedirectResponse
    {
        set_time_limit(60);
        
        if (!$this->checkBniCredentials()) {
            Log::error('❌ Kredensial BNI tidak lengkap - Registrasi dibatalkan');
            return redirect()->back()->withInput()->with('error', '❌ Sistem pembayaran sedang maintenance.');
        }

        // ✅ CEK DUPLIKASI
        $duplicateCheck = $this->checkForDuplicateRegistration($request);
        if ($duplicateCheck['is_duplicate']) {
            $redirectUrl = $duplicateCheck['action'] === 'redirect_to_login' ? route('login') : back();
            return redirect($redirectUrl)->with('warning', $duplicateCheck['message'])->withInput();
        }

        // ✅ VALIDASI
        $validated = $request->validate([
    'name' => ['required', 'string', 'max:255'],
    'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
    'phone' => ['required', 'regex:/^08[0-9]{8,11}$/'],
    'promo_code' => ['nullable', 'string', 'max:255'],
], [
    'phone.regex' => 'Format nomor telepon harus diawali 08 dan berjumlah 10-13 digit.',
]);

$phonePassword = $this->generatePasswordFromPhone($validated['phone']);
$promoCode = null;
$discountPercentage = 0;
$promoCodeUsed = null;

// ✅ VALIDASI KODE PROMO SECARA EKSPLISIT
if (!empty($validated['promo_code'])) {
    $promoCode = PromoCode::where('code', $validated['promo_code'])->first();

    // Jika kode tidak ditemukan ATAU tidak aktif/kadaluarsa
    if (!$promoCode || !$promoCode->isValid()) {
        return redirect()->back()
            ->withInput()
            ->withErrors(['promo_code' => 'Kode promo tidak valid, sudah kadaluarsa, atau kuota habis.']);
    }

    $discountPercentage = $promoCode->discount;
    $promoCodeUsed = $promoCode->code;
}

        $gelombang = $this->determineGelombang();
        DB::beginTransaction();

        try {
            // ✅ BUAT USER
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => '62' . substr($validated['phone'], 1),
                'password' => Hash::make($phonePassword),
                'role' => 'mahasiswa_baru',
                'promo_code_used' => $promoCodeUsed,
            ]);
            
            $bniConfig = BniConfig::firstOrFail();
            $originalAmount = $bniConfig->amount;
            $amountForBNI = $promoCode ? $this->calculateDiscountedAmount($originalAmount, $discountPercentage) : $originalAmount;

            // ✅ BUAT VA BNI
            $bniService = new BNIApiService();
            $vaResponse = $bniService->createBilling($user, $amountForBNI, 0);

            if (!$vaResponse || empty($vaResponse['virtual_account'])) {
                throw new Exception("Gagal membuat Virtual Account BNI.");
            }

            // ✅ SIMPAN KE MASTER_USERS
            $masterUser = MasterUser::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'password'       => Hash::make($phonePassword),
                    'trx_id'         => $vaResponse['trx_id'],
                    'va_number'      => $vaResponse['virtual_account'],
                    'va_status'      => 'paid',
                    'va_amount'      => $amountForBNI,
                    'gelombang'      => $gelombang,
                    'paid_at'        => Carbon::now(),
                    'datetime_payment' => Carbon::now()->format('Y-m-d H:i:s'),
                ]
            );

            // ✅ SIMPAN KE TABEL akun_pendaftar_default
            AkunPendaftarDefault::create([
                'name'           => $user->name,
                'email'          => $user->email,
                'phone'          => $user->phone,
                'va_number'      => $vaResponse['virtual_account'],
                'password_plain' => $phonePassword, // Simpan password asli
            ]);

            if ($promoCode) $promoCode->increment('used_count');

            DB::commit();

            // ✅ EKSPOR KE EXCEL (Setelah Commit)
            try {
                Excel::store(new RegistrantsExport, 'public/pendaftar.xlsx');
                Log::info('📊 File Excel diperbarui secara otomatis.');
            } catch (Exception $e) {
                Log::error('❌ Gagal update Excel: ' . $e->getMessage());
            }

            // ✅ INTEGRASI MOODLE & NOTIFIKASI
            $this->syncExternalServices($user, $masterUser, $phonePassword);

            event(new Registered($user));
            Auth::login($user);

            return redirect()->route('mahasiswa.va.dashboard');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('💥 Registrasi gagal: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Registrasi gagal: ' . $e->getMessage());
        }
    }

    private function syncExternalServices($user, $masterUser, $password) {
        // Moodle Sync
        try {
            Http::timeout(10)->post('http://103.23.198.160:3009/api/moodle/register', [
                'name' => $user->name, 'email' => $user->email, 'phone' => $user->phone, 'va_number' => (string)$masterUser->va_number
            ]);
        } catch (Exception $e) { Log::error('Moodle Error: '.$e->getMessage()); }

        // WhatsApp
        try {
            $wa = new WhatsAppService();
            $wa->sendCompleteLoginInfo($user, $masterUser);
        } catch (Exception $e) { Log::error('WA Error: '.$e->getMessage()); }

        // Email
        try {
            Mail::to($user->email)->send(new RegistrationSuccessMail($user, $masterUser, $password));
        } catch (Exception $e) { Log::error('Email Error: '.$e->getMessage()); }
    }

    // --- Helper Methods (Kopi dari kode Anda sebelumnya) ---
    private function generatePasswordFromPhone(string $phone): string {
        $cleanPhone = preg_replace('/\D/', '', $phone);
        return 'PMB' . substr($cleanPhone, -8);
    }

    private function checkBniCredentials(): bool {
        return !empty(env('BNI_CLIENT_ID')) && !empty(env('BNI_SECRET_KEY'));
    }

    private function determineGelombang(): int {
        $m = Carbon::now()->month;
        return ($m == 12 || $m == 1) ? 1 : $m;
    }

    private function calculateDiscountedAmount($original, $percent) {
        return max(1000, floor($original - ($original * $percent / 100)));
    }

    private function checkForDuplicateRegistration(Request $request): array {
        $email = $request->email;
        $phone = '62' . substr($request->phone, 1);
        $existing = User::where('email', $email)->orWhere('phone', $phone)->first();
        if ($existing) {
            return ['is_duplicate' => true, 'message' => 'Akun sudah terdaftar.', 'action' => 'redirect_to_login'];
        }
        return ['is_duplicate' => false];
    }
    public function validatePromo(Request $request)
{
    $request->validate([
        'promo_code' => 'required|string',
    ]);

    $promo = PromoCode::where('code', $request->promo_code)->first();

    // 1. Cek apakah kode ada
    if (!$promo) {
        return response()->json([
            'valid' => false,
            'message' => 'Kode promo tidak ditemukan.'
        ]);
    }

    // 2. Cek apakah aktif, expired, atau limit habis (asumsi ada method isValid() di Model)
    if (!$promo->isValid()) {
        return response()->json([
            'valid' => false,
            'message' => 'Kode promo sudah tidak berlaku atau kuota habis.'
        ]);
    }

    // 3. Ambil harga asli dari config BNI
    $bniConfig = BniConfig::first();
    $originalAmount = $bniConfig ? $bniConfig->amount : 0;
    
    // Hitung diskon
    $discountedAmount = $this->calculateDiscountedAmount($originalAmount, $promo->discount);
    
    // Tentukan apakah ini promo gratis 100%
    $isPaid = ($promo->discount >= 100);

    return response()->json([
        'valid' => true,
        'promo_code' => [
            'code' => $promo->code,
            'description' => $promo->description,
            'discount' => $promo->discount
        ],
        'pricing' => [
            'original' => $originalAmount,
            'discounted' => $discountedAmount,
            'formatted_original' => 'Rp ' . number_format($originalAmount, 0, ',', '.'),
            'formatted_discounted' => 'Rp ' . number_format($discountedAmount, 0, ',', '.')
        ],
        'status_info' => [
            'is_paid' => $isPaid
        ]
    ]);
}
}