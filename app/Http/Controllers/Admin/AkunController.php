<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\BniConfig;
use App\Models\MasterUser;
use App\Models\PromoCode;
use App\Services\BNIApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules;
use Exception;
use Carbon\Carbon;

class AkunController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::orderBy('created_at', 'desc')->paginate(10);
        return view('admin.akun.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.akun.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        set_time_limit(60);
        
        // Pisahkan payload promo code dari data utama
        $registerData = $request->except(['promo_code']);
        Log::info('📥 [ADMIN] Menerima request buat akun baru', $registerData);

        // Validasi input - UBAH VALIDASI GELOMBANG MENJADI INTEGER
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'regex:/^08[0-9]{8,11}$/'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'string', 'in:admin,mahasiswa_baru'],
            'promo_code' => ['nullable', 'string', 'max:255'],
            'gelombang' => ['nullable', 'integer', 'min:1', 'max:12'], // UBAH KE INTEGER
        ]);

        // ✅ VALIDASI PROMO CODE
        $promoCode = null;
        $discountPercentage = 0;
        $promoCodeUsed = null;

        if (!empty($validated['promo_code'])) {
            $promoCode = PromoCode::where('code', $validated['promo_code'])->first();
            
            if ($promoCode) {
                if (!$promoCode->isValid()) {
                    return redirect()->back()->withInput()->with('error', 'Kode promo tidak valid atau sudah kadaluarsa.');
                }
                
                $discountPercentage = $promoCode->discount;
                $promoCodeUsed = $promoCode->code;
                
                Log::info('🎫 [ADMIN] Promo code divalidasi', [
                    'code' => $promoCode->code,
                    'discount' => $promoCode->discount . '%',
                    'current_used_count' => $promoCode->used_count
                ]);
            } else {
                return redirect()->back()->withInput()->with('error', 'Kode promo tidak ditemukan.');
            }
        }

        // ✅ TENTUKAN GELOMBANG (gunakan dari input atau otomatis) - KONVERSI KE INTEGER
        $gelombangInput = $validated['gelombang'] ?? null;
        $gelombang = $gelombangInput ? (int)$gelombangInput : $this->determineGelombang();
        
        Log::info('🎯 [ADMIN] Menentukan gelombang', [
            'gelombang_input' => $gelombangInput,
            'gelombang_digunakan' => $gelombang,
            'bulan_sekarang' => Carbon::now()->month
        ]);

        DB::beginTransaction();

        try {
            // ✅ BUAT USER - PASTIKAN GELOMBANG INTEGER
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => '62' . substr($validated['phone'], 1),
                'password' => Hash::make($validated['password']),
                'role' => $validated['role'],
                'promo_code_used' => $promoCodeUsed,
                'gelombang' => $gelombang, // INTEGER
            ]);
            Log::info('✅ [ADMIN] User berhasil dibuat', [
                'id' => $user->id,
                'role' => $user->role,
                'gelombang' => $gelombang,
                'promo_code_used' => $promoCodeUsed
            ]);

            // ✅ JIKA ROLE MAHASISWA, BUAT VA DI BNI
            if ($validated['role'] === 'mahasiswa_baru') {
                $this->createVAForMahasiswa($user, $promoCode, $discountPercentage, $gelombang, $promoCodeUsed);
            } else {
                Log::info('ℹ️ [ADMIN] User admin dibuat tanpa VA');
            }

            DB::commit();

            return redirect()->route('admin.akun.index')
                ->with('success', $this->getSuccessMessage($user, $promoCode, $discountPercentage, $gelombang));

        } catch (Exception $e) {
            DB::rollBack();
            
            // ✅ ROLLBACK PROMO CODE USAGE JIKA GAGAL
            if ($promoCode && $promoCode->used_count > 0) {
                $promoCode->decrement('used_count');
                Log::info('🔄 [ADMIN] Rollback promo code used_count', [
                    'code' => $promoCode->code,
                    'new_used_count' => $promoCode->used_count
                ]);
            }
            
            Log::error('💥 [ADMIN] Pembuatan akun gagal', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Pembuatan akun gagal: ' . $e->getMessage());
        }
    }

    /**
     * Buat VA untuk user mahasiswa_baru
     */
    private function createVAForMahasiswa($user, $promoCode, $discountPercentage, $gelombang, $promoCodeUsed)
    {
        $bniConfig = BniConfig::firstOrFail();
        $originalAmount = $bniConfig->amount;
        
        // ✅ KONDISI UNTUK MENENTUKAN AMOUNT
        if ($promoCode) {
            $amountForBNI = $this->calculateDiscountedAmount($originalAmount, $discountPercentage);
            $discountedAmount = $amountForBNI;
            Log::info('💰 [ADMIN] Menggunakan amount setelah diskon untuk BNI', [
                'original' => $originalAmount,
                'discount' => $discountPercentage . '%',
                'amount_for_bni' => $amountForBNI,
                'note' => 'Promo code TIDAK dikirim ke BNI'
            ]);
        } else {
            $amountForBNI = $originalAmount;
            $discountedAmount = $originalAmount;
            Log::info('💰 [ADMIN] Menggunakan amount asli untuk BNI', [
                'amount_for_bni' => $amountForBNI
            ]);
        }

        // ✅ TENTUKAN STATUS BERDASARKAN DISKON
        $isFreePromo = $promoCode && $discountPercentage == 100;
        $vaStatus = $isFreePromo ? 'PAID' : 'PENDING';
        $masterVaStatus = $isFreePromo ? 'paid' : 'pending';

        Log::info('🎯 [ADMIN] Menentukan status pembayaran', [
            'is_free_promo' => $isFreePromo ? 'Ya' : 'Tidak',
            'discount_percentage' => $discountPercentage . '%',
            'va_status' => $vaStatus,
            'master_va_status' => $masterVaStatus
        ]);

        // ✅ PASTIKAN AMOUNT TIDAK 0 UNTUK BNI
        if ($amountForBNI <= 0) {
            $amountForBNI = 1000;
            Log::warning('⚠️ [ADMIN] Amount 0, set ke minimum amount untuk BNI', [
                'new_amount' => $amountForBNI
            ]);
        }

        $maxRetry = 3;
        $attempt = 0;
        $vaResponse = null;
        $bniService = new BNIApiService();

        // ✅ BUAT VA DI BNI
        do {
            $attempt++;
            Log::info("💰 [ADMIN] Mencoba membuat VA, percobaan #$attempt", [
                'user_id' => $user->id,
                'amount' => $amountForBNI,
                'gelombang' => $gelombang,
                'promo_applied' => $promoCode ? 'Ya' : 'Tidak'
            ]);
            
            $vaResponse = $bniService->createBilling($user, $amountForBNI);

            if (!$vaResponse) {
                throw new Exception("Gagal membuat Virtual Account BNI pada percobaan #$attempt.");
            }

            // Jika BNI return Duplicate Billing ID, generate ulang ID dan retry
            if (($vaResponse['status_code'] ?? '') === '105') {
                Log::warning('⚠️ [ADMIN] Duplicate Billing ID, retry...', ['attempt' => $attempt]);
                continue;
            }

            break;

        } while ($attempt < $maxRetry);

        if (!$vaResponse || empty($vaResponse['virtual_account'])) {
            throw new Exception('Gagal membuat Virtual Account BNI setelah beberapa percobaan.');
        }

        Log::info('✅ [ADMIN] VA berhasil dibuat di BNI', [
            'va_number' => $vaResponse['virtual_account'],
            'trx_id' => $vaResponse['trx_id'],
            'amount' => $amountForBNI,
            'gelombang' => $gelombang,
            'promo_code_saved_in_user' => $promoCodeUsed
        ]);

        // ✅ SIMPAN DATA KE MASTER_USERS - PASTIKAN GELOMBANG INTEGER
        $masterUserData = [
            'plain_password' => $vaResponse['password'],
            'password'       => Hash::make($vaResponse['password']),
            'trx_id'         => $vaResponse['trx_id'],
            'va_number'      => $vaResponse['virtual_account'],
            'va_status'      => $masterVaStatus,
            'va_amount'      => $amountForBNI,
            'gelombang'      => $gelombang, // INTEGER
            
            // ✅ INFORMASI PROMO UNTUK TRACKING
            'original_amount' => $originalAmount,
            'discount_amount' => $promoCode ? ($originalAmount - $discountedAmount) : 0,
            'promo_code_used' => $promoCodeUsed,
            'discount_percentage' => $discountPercentage,
            'final_amount' => $discountedAmount,
            
            'va_expired_at'  => $vaResponse['datetime_expired'],
        ];

        // ✅ TAMBAH INFORMASI TANGGAL BAYAR JIKA STATUS PAID
        if ($isFreePromo) {
            $masterUserData['paid_at'] = Carbon::now();
            $masterUserData['payment_method'] = 'free_promo_100';
            Log::info('🎉 [ADMIN] Status otomatis PAID untuk promo 100%');
        }

        $masterUser = MasterUser::updateOrCreate(
            ['user_id' => $user->id],
            $masterUserData
        );

        // ✅ UPDATE USED_COUNT JIKA PROMO CODE VALID
        if ($promoCode) {
            $promoCode->increment('used_count');
            Log::info('✅ [ADMIN] Promo code used_count diupdate', [
                'code' => $promoCode->code,
                'new_used_count' => $promoCode->used_count,
                'saved_in_user_table' => $promoCodeUsed
            ]);
        }

        // ✅ UPDATE USER UTAMA DENGAN DATA VA - PASTIKAN GELOMBANG INTEGER
        $userUpdateData = [
            'va_number' => $masterUser->va_number,
            'va_expired_at' => $masterUser->va_expired_at,
            'va_amount' => $amountForBNI,
            'gelombang' => $gelombang, // INTEGER
            
            // ✅ INFORMASI PROMO UNTUK DISPLAY
            'original_amount' => $originalAmount,
            'discount_amount' => $promoCode ? ($originalAmount - $discountedAmount) : 0,
            'promo_code_used' => $promoCodeUsed,
            'final_amount' => $discountedAmount,
            
            'va_status' => $vaStatus,
        ];

        // ✅ TAMBAH INFORMASI TANGGAL BAYAR JIKA STATUS PAID
        if ($isFreePromo) {
            $userUpdateData['paid_at'] = Carbon::now();
        }

        $user->update($userUpdateData);

        // ✅ REGISTRASIKAN KE MOODLE
        app(\App\Http\Controllers\Moodle\MoodleSyncController::class)
            ->registerSingleUserToMoodle($masterUser);

        Log::info('🗂️ [ADMIN] Data user dan VA disimpan lengkap', [
            'user_id' => $user->id,
            'va_number' => $masterUser->va_number,
            'va_status' => $masterUser->va_status,
            'gelombang' => $masterUser->gelombang,
            'using_promo' => $promoCode ? 'Ya' : 'Tidak',
            'is_free' => $isFreePromo ? 'Ya' : 'Tidak'
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = User::findOrFail($id);
        return view('admin.akun.edit', compact('user'));
    }

   /**
 * Update the specified resource in storage.
 */
public function update(Request $request, string $id)
{
    $user = User::findOrFail($id);
    $masterUser = MasterUser::where('user_id', $user->id)->first();

    // Validasi input
    $validated = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $id],
        'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        'role' => ['required', 'string', 'in:admin,mahasiswa_baru'],
        'phone' => ['nullable', 'string', 'max:20'],
        'gelombang' => ['nullable', 'integer', 'min:1', 'max:12'],
        'bio' => ['nullable', 'string', 'max:500'],
        'va_number' => ['nullable', 'string', 'max:255'],
        'va_amount' => ['nullable', 'numeric', 'min:0'],
        'va_status' => ['nullable', 'string', 'in:pending,active,expired,paid,failed'],
        'va_expired_at' => ['nullable', 'date'],
        'promo_code_used' => ['nullable', 'string', 'max:50'],
        'trx_id' => ['nullable', 'string', 'max:255'],
        'plain_password' => ['nullable', 'string', 'max:255'],
    ]);

    DB::beginTransaction();

    try {
        // Update data user
        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'phone' => $validated['phone'],
            'gelombang' => (int)$validated['gelombang'],
            'bio' => $validated['bio'],
            'va_number' => $validated['va_number'],
            'va_amount' => $validated['va_amount'],
            'va_status' => $validated['va_status'],
            'va_expired_at' => $validated['va_expired_at'],
            'promo_code_used' => $validated['promo_code_used'],
        ];

        // Jika password diisi, update password user dan master user
        if ($request->filled('password')) {
            $newPassword = $request->password;
            $updateData['password'] = Hash::make($newPassword);

            // Update master user jika ada
            if ($masterUser) {
                $masterUser->update([
                    'plain_password' => $newPassword,
                    'password' => Hash::make($newPassword)
                ]);
            }
        }

        $user->update($updateData);

        // Update master user data jika ada
        if ($masterUser) {
            $masterUserData = [
                'trx_id' => $validated['trx_id'],
                'va_number' => $validated['va_number'],
                'va_status' => $validated['va_status'],
                'va_amount' => $validated['va_amount'],
                'gelombang' => (int)$validated['gelombang'],
                'promo_code_used' => $validated['promo_code_used'],
                'va_expired_at' => $validated['va_expired_at'],
            ];

            // Update plain password jika diisi secara manual
            if ($request->filled('plain_password') && !$request->filled('password')) {
                $masterUserData['plain_password'] = $validated['plain_password'];
                $masterUserData['password'] = Hash::make($validated['plain_password']);
            }

            $masterUser->update($masterUserData);
        }

        DB::commit();

        return redirect()->route('admin.akun.index')
            ->with('success', 'Akun berhasil diperbarui!');

    } catch (Exception $e) {
        DB::rollBack();
        
        Log::error('💥 [ADMIN] Update akun gagal', [
            'user_id' => $id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        return redirect()
            ->back()
            ->withInput()
            ->with('error', 'Update akun gagal: ' . $e->getMessage());
    }
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.akun.index')
                ->with('error', 'Tidak dapat menghapus akun sendiri!');
        }

        $user->delete();

        return redirect()->route('admin.akun.index')
            ->with('success', 'Akun berhasil dihapus!');
    }

    /**
     * Tentukan gelombang berdasarkan bulan pendaftaran - RETURN INTEGER
     */
    private function determineGelombang(): int
    {
        $currentMonth = Carbon::now()->month;
        
        // Mapping bulan ke angka gelombang (integer)
        $gelombangMapping = [
            1 => 1,  // January -> Gelombang 1
            2 => 2,  // February -> Gelombang 2
            3 => 3,  // March -> Gelombang 3
            4 => 4,  // April -> Gelombang 4
            5 => 5,  // May -> Gelombang 5
            6 => 6,  // June -> Gelombang 6
            7 => 7,  // July -> Gelombang 7
            8 => 8,  // August -> Gelombang 8
            9 => 9,  // September -> Gelombang 9
            10 => 10, // October -> Gelombang 10
            11 => 11, // November -> Gelombang 11
            12 => 12, // December -> Gelombang 12
        ];

        return $gelombangMapping[$currentMonth] ?? 1;
    }

    /**
     * Hitung amount setelah diskon
     */
    private function calculateDiscountedAmount($originalAmount, $discountPercentage)
    {
        if ($discountPercentage <= 0) {
            return $originalAmount;
        }
        
        $discountAmount = ($originalAmount * $discountPercentage) / 100;
        $discountedAmount = $originalAmount - $discountAmount;
        
        // Pastikan tidak minus dan bulatkan ke bawah
        return max(0, floor($discountedAmount));
    }

    /**
     * Generate success message - FORMAT GELOMBANG UNTUK DISPLAY
     */
    private function getSuccessMessage($user, $promoCode, $discountPercentage, $gelombang)
    {
        $message = "Akun {$user->role} berhasil dibuat! ";
        
        if ($user->role === 'mahasiswa_baru') {
            $message .= "Virtual Account telah dibuat. ";
            
            if ($promoCode && $discountPercentage == 100) {
                $message .= "🎉 Status pembayaran: LUNAS (Diskon 100%). ";
            } elseif ($promoCode) {
                $message .= "Diskon {$discountPercentage}% diterapkan. ";
            }
            
            $message .= "Gelombang: " . $this->formatGelombang($gelombang);
        } else {
            $message .= "Gelombang: " . $this->formatGelombang($gelombang);
        }

        return $message;
    }

    /**
     * Format gelombang integer ke string untuk display
     */
    private function formatGelombang($gelombangInt): string
    {
        return "Gelombang " . $gelombangInt;
    }

    /**
     * Validasi promo code via AJAX - PERBAIKI RETURN GELOMBANG
     */
    public function validatePromoCode(Request $request)
    {
        $request->validate([
            'promo_code' => 'required|string'
        ]);

        $promoCode = PromoCode::where('code', $request->promo_code)->first();

        if (!$promoCode) {
            return response()->json([
                'valid' => false,
                'message' => 'Kode promo tidak ditemukan'
            ]);
        }

        if (!$promoCode->isValid()) {
            return response()->json([
                'valid' => false,
                'message' => 'Kode promo tidak valid atau sudah kadaluarsa'
            ]);
        }

        $bniConfig = BniConfig::first();
        $originalAmount = $bniConfig->amount;
        $discountedAmount = $this->calculateDiscountedAmount($originalAmount, $promoCode->discount);

        // ✅ Tentukan status berdasarkan diskon
        $isFreePromo = $promoCode->discount == 100;
        $vaStatus = $isFreePromo ? 'PAID' : 'PENDING';

        // ✅ Tentukan amount yang akan digunakan untuk BNI
        $amountForBNI = $discountedAmount > 0 ? $discountedAmount : 1000;

        // ✅ Tentukan gelombang (integer)
        $gelombang = $this->determineGelombang();

        return response()->json([
            'valid' => true,
            'promo_code' => [
                'id' => $promoCode->id,
                'code' => $promoCode->code,
                'discount' => $promoCode->discount,
                'description' => $promoCode->description
            ],
            'pricing' => [
                'original_amount' => $originalAmount,
                'discounted_amount' => $discountedAmount,
                'discount_amount' => $originalAmount - $discountedAmount,
                'amount_for_bni' => $amountForBNI,
                'formatted_original' => 'Rp ' . number_format($originalAmount, 0, ',', '.'),
                'formatted_discounted' => 'Rp ' . number_format($discountedAmount, 0, ',', '.'),
                'formatted_saving' => 'Rp ' . number_format($originalAmount - $discountedAmount, 0, ',', '.'),
                'formatted_bni_amount' => 'Rp ' . number_format($amountForBNI, 0, ',', '.'),
                'is_free' => $isFreePromo
            ],
            'status_info' => [
                'va_status' => $vaStatus,
                'is_paid' => $isFreePromo
            ],
            'gelombang_info' => [
                'gelombang' => $gelombang,
                'gelombang_display' => $this->formatGelombang($gelombang), // Format untuk display
                'bulan_sekarang' => Carbon::now()->monthName,
                'message' => "Gelombang pendaftaran: " . $this->formatGelombang($gelombang)
            ],
            'note' => $isFreePromo ? 
                '🎉 GRATIS 100%! Status pembayaran otomatis LUNAS.' : 
                'VA akan dibuat dengan amount terdiskon: Rp ' . number_format($discountedAmount, 0, ',', '.')
        ]);
    }
}