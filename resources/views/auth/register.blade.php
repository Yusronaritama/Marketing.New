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

use Illuminate\View\View;
use Exception;
use Carbon\Carbon;

class RegisteredMandiriController extends Controller
{
    public function create(): View
    {
        return view('auth.test', [
            'is_maintenance' => $this->isSystemMaintenance(),
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

        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'email'          => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
            'phone'          => ['required', 'regex:/^08[0-9]{8,11}$/'],
            'type'           => ['required', 'in:reguler,s2'],
            'program_study'  => ['required_if:type,s2'],
            'promo_code'     => ['nullable', 'string', 'max:20'],
        ]);

        $cleanPhone    = preg_replace('/\D/', '', $validated['phone']);
        $phonePassword = 'PMB' . substr($cleanPhone, -8);
        $formattedPhone = '62' . substr($validated['phone'], 1);

        // 1. Tentukan Nominal Awal
        if ($validated['type'] === 's2') {
            $prodi = DB::table('program_studi')
                        ->where('kode_prodi', $validated['program_study'])
                        ->first();

            $registrationFee = $prodi->biaya_pendaftaran ?? 500000;
            $namaJalur       = "Jalur S2 " . ($prodi->nama_prodi ?? 'Manajemen');
        } else {
            $registrationFee = 300000;
            $namaJalur       = "Jalur Reguler";
        }

        $originalAmount     = $registrationFee;
        $discountAmount     = 0;
        $discountPercentage = 0;
        $appliedPromo       = null;
        $isFree             = false;

        // 2. Logika Promo Code
        if ($validated['type'] === 'reguler' && $request->filled('promo_code')) {
            $promo = DB::table('promo_codes')
                ->where('code', strtoupper($request->promo_code))
                ->where('is_active', 1)
                ->where('expired_at', '>', now())
                ->first();

            if ($promo && $promo->usage_limit > $promo->used_count) {
                $appliedPromo       = $promo->code;
                $discountPercentage = $promo->discount;

                $discountAmount  = (int) round(($originalAmount * $discountPercentage) / 100);
                $registrationFee = max(0, $originalAmount - $discountAmount);

                if ($discountPercentage >= 100 || $registrationFee <= 0) {
                    $registrationFee = 0;
                    $discountAmount  = $originalAmount;
                    $isFree          = true;
                }
            }
        }

        DB::beginTransaction();
        try {
            // 3. Buat User Utama
            $user = User::create([
                'name'     => $validated['name'],
                'email'    => $validated['email'],
                'phone'    => $formattedPhone,
                'password' => Hash::make($phonePassword),
                'role'     => 'mahasiswa_baru',
            ]);

            // 4. Proses VA Mandiri — selalu hit API, termasuk saat amount 0 (promo 100%)
            $mandiriService = new MandiriApiService();
            $vaResponse     = $mandiriService->createBilling($user, $registrationFee, null, 'PENDAFTARAN_PMB');

            if (!$vaResponse) {
                throw new Exception("Gagal mendapatkan Virtual Account Mandiri.");
            }

            $vaNumber  = $vaResponse['virtual_account'];
            $trxId     = $vaResponse['trx_id'];
            $vaExpired = $vaResponse['expired_at'];

            // Jika promo 100%, VA & TRX tetap dari Mandiri — status langsung paid
            $vaStatus = $isFree ? 'paid' : 'pending';

            // 5. Simpan ke master_users
            $masterUser = MasterUser::create([
                'user_id'             => $user->id,
                'gelombang'           => 3,
                'password'            => Hash::make($phonePassword),
                'plain_password'      => $phonePassword,
                'trx_id'              => $trxId,
                'va_number'           => $vaNumber,
                'va_status'           => $vaStatus,
                'promo_code_used'     => $appliedPromo,
                'va_amount'           => $registrationFee,
                'original_amount'     => $originalAmount,
                'discount_amount'     => $discountAmount,
                'discount_percentage' => $discountPercentage,
                'va_expired_at'       => $vaExpired,
            ]);

            // Update kuota promo
            if ($appliedPromo) {
                DB::table('promo_codes')
                    ->where('code', $appliedPromo)
                    ->increment('used_count');
            }

            // 6. Simpan ke master_ereg
            DB::table('master_ereg')->insert([
                'user_id'           => $user->id,
                'nama'              => $user->name,
                'phone'             => $user->phone,
                'gmail'             => $user->email,
                'jalur_pendaftaran' => $namaJalur,
                'jurusan1'          => ($validated['type'] === 's2') ? $validated['program_study'] : null,
                'status'            => $isFree ? 'paid' : 'pending',
                'step_completed'    => 1,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            // 7. Simpan ke akun default
            AkunPendaftarDefault::create([
                'name'           => $user->name,
                'email'          => $user->email,
                'phone'          => $user->phone,
                'va_number'      => $vaNumber,
                'va_status'      => $vaStatus,
                'password_plain' => $phonePassword,
            ]);

            DB::commit();
            Auth::login($user);
            $this->syncExternalServices($user, $masterUser, $phonePassword);

            $message = 'Pendaftaran berhasil';
            if ($appliedPromo && $isFree) {
                $message = 'Pendaftaran berhasil! Biaya pendaftaran GRATIS dengan kode promo.';
            } elseif ($appliedPromo) {
                $message = 'Pendaftaran berhasil dengan diskon kode promo.';
            }

            return response()->json([
                'status'     => 'success',
                'message'    => $message,
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
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
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
    }

    /**
     * Validasi kode promo — dipakai oleh frontend secara real-time.
     *
     * Response shape yang dikonsumsi oleh showPromoSuccess() di blade:
     * {
     *   valid: true,
     *   promo_code: { code, discount, description },
     *   status_info: { is_paid: bool },   // is_paid = true jika diskon 100%
     *   pricing: {
     *     formatted_original,
     *     formatted_discounted,
     *     formatted_saving
     *   }
     * }
     */
    public function validatePromo(Request $request)
    {
        $request->validate([
            'promo_code' => 'required|string'
        ]);

        $promo = DB::table('promo_codes')
            ->where('code', strtoupper($request->promo_code))
            ->where('is_active', 1)
            ->where('expired_at', '>', now())
            ->first();

        if (!$promo) {
            return response()->json([
                'valid'   => false,
                'message' => 'Kode promo tidak ditemukan atau sudah kedaluwarsa.'
            ]);
        }

        if ($promo->used_count >= $promo->usage_limit) {
            return response()->json([
                'valid'   => false,
                'message' => 'Kuota kode promo sudah habis.'
            ]);
        }

        $originalPrice  = 300000; // harga reguler default
        $discountPct    = (int) $promo->discount;
        $discountAmount = (int) round(($originalPrice * $discountPct) / 100);
        $finalPrice     = max(0, $originalPrice - $discountAmount);
        $isPaid         = ($discountPct >= 100 || $finalPrice <= 0);

        // Jika 100%, pastikan angka konsisten
        if ($isPaid) {
            $discountAmount = $originalPrice;
            $finalPrice     = 0;
        }

        return response()->json([
            'valid'       => true,
            // 'promo_code' = key baru (blade baru)
            // 'promo'      = key lama (blade production) — keduanya dikirim agar kompatibel
            'promo_code'  => [
                'code'        => $promo->code,
                'discount'    => $discountPct,
                'description' => $promo->description ?? '',
            ],
            'promo'       => [
                'code'        => $promo->code,
                'discount'    => $discountPct,
                'description' => $promo->description ?? '',
            ],
            'status_info' => [
                'is_paid' => $isPaid,
            ],
            'pricing'     => [
                'formatted_original'   => 'Rp ' . number_format($originalPrice,  0, ',', '.'),
                'formatted_discounted' => 'Rp ' . number_format($finalPrice,     0, ',', '.'),
                'formatted_saving'     => 'Rp ' . number_format($discountAmount, 0, ',', '.'),
                // alias key lama agar blade production tetap jalan
                'original'             => 'Rp ' . number_format($originalPrice,  0, ',', '.'),
                'final'                => 'Rp ' . number_format($finalPrice,     0, ',', '.'),
                'saving'               => 'Rp ' . number_format($discountAmount, 0, ',', '.'),
            ],
        ]);
    }
}