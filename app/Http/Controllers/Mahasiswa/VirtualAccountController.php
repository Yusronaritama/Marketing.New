<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\MasterUser;
use App\Services\MandiriApiService;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class VirtualAccountController extends Controller
{
    protected $mandiriService;

    public function __construct(MandiriApiService $mandiriService)
    {
        $this->mandiriService = $mandiriService;
    }

    // ===========================================================================
    // HELPER: Parse tanggal secara aman — mengembalikan Carbon atau null
    // ===========================================================================
    private function parseDate($value): ?Carbon
    {
        if ($value === null || $value === '') return null;
        if ($value instanceof Carbon) return $value;
        try {
            return Carbon::parse($value);
        } catch (\Exception $e) {
            return null;
        }
    }

    // ===========================================================================
    // HELPER: Hitung sisa waktu dari field expired
    // ===========================================================================
    private function calcTimeRemaining($expiredAt): array
    {
        $expired = $this->parseDate($expiredAt);

        if (!$expired) {
            return ['time_remaining' => null, 'is_expiring_soon' => false, 'is_expired' => false];
        }

        $now = now();

        if ($now->gt($expired)) {
            return ['time_remaining' => 'EXPIRED', 'is_expiring_soon' => false, 'is_expired' => true];
        }

        $diff = $now->diff($expired);
        $timeRemaining = $diff->days > 0
            ? $diff->days . ' hari ' . $diff->h . ' jam'
            : $diff->h . ' jam ' . $diff->i . ' menit';

        $isExpiringSoon = $expired->diffInHours($now) <= 2;

        return ['time_remaining' => $timeRemaining, 'is_expiring_soon' => $isExpiringSoon, 'is_expired' => false];
    }

    // ===========================================================================
    // dashboard
    // ===========================================================================
    public function dashboard()
    {
        $user   = Auth::user();
        $master = MasterUser::where('user_id', $user->id)->first();

        if (!$master) {
            return view('mahasiswa.va.dashboard', [
                'user'           => null,
                'master'         => null,
                'can_regenerate' => false,
                'error_message'  => 'Data Virtual Account tidak ditemukan. Silakan hubungi administrator.'
            ]);
        }

        $vaHistory = $this->getVAHistory($user->id);
        $this->fillUserFromMaster($user, $master);

        // Jika VA SUDAH LUNAS (paid)
        if ($master->va_status === 'paid') {
            // REDIRECT OTOMATIS: Cek apakah user adalah profesi apoteker
            if ($user->profesi === 'Apoteker Farmasi') {
                return redirect()->route('profesi.dashboard')
                    ->with('success', 'Pembayaran berhasil. Selamat datang di portal pendaftaran profesi!');
            }

            // Jika bukan profesi, tetap tampilkan view dashboard VA (atau Anda bisa redirect ke route mahasiswa biasa)
            return view('mahasiswa.va.dashboard', [
                'user'             => $user,
                'master'           => $master,
                'va_history'       => $vaHistory,
                'time_remaining'   => 'Pembayaran Selesai',
                'is_expiring_soon' => false,
                'is_expired'       => false,
                'can_regenerate'   => false,
                'trx_id_kept'      => $master->trx_id,
            ]);
        }

        // ... Sisa kode dashboard (jika VA pending/expired) tetap sama ...
        ['time_remaining' => $timeRemaining, 'is_expiring_soon' => $isExpiringSoon, 'is_expired' => $isExpired]
            = $this->calcTimeRemaining($master->va_expired_at);

        if (empty($master->va_number) || empty($master->va_expired_at) || empty($master->va_amount)) {
            return view('mahasiswa.va.dashboard', [
                'user'             => null,
                'master'           => $master,
                'va_history'       => $vaHistory,
                'time_remaining'   => $timeRemaining,
                'is_expiring_soon' => $isExpiringSoon,
                'is_expired'       => $isExpired,
                'can_regenerate'   => true,
                'error_message'    => 'Gagal membuat Virtual Account saat registrasi. Silakan generate VA baru.'
            ]);
        }

        return view('mahasiswa.va.dashboard', [
            'user'             => $user,
            'master'           => $master,
            'va_history'       => $vaHistory,
            'time_remaining'   => $timeRemaining,
            'is_expiring_soon' => $isExpiringSoon,
            'is_expired'       => $isExpired,
            'can_regenerate'   => $this->mandiriService->canRegenerateVA($master),
            'trx_id_kept'      => $master->trx_id,
        ]);
    }

    // ===========================================================================
    // autoLogin (Jika Anda pakai tombol Lanjut Login di view)
    // ===========================================================================
    public function autoLogin(Request $request)
    {
        $user   = Auth::user();
        $master = MasterUser::where('user_id', $user->id)->first();

        if (!$master) {
            return redirect()->route('mahasiswa.va.dashboard')
                ->with('error', 'Data Virtual Account tidak ditemukan.');
        }

        if ($master->va_status !== 'paid') {
            Log::warning('Percobaan akses dashboard tanpa bayar', ['user_id' => $user->id]);
            return redirect()->route('mahasiswa.va.dashboard')
                ->with('warning', 'Status pembayaran Anda masih pending. Silakan selesaikan pembayaran terlebih dahulu.');
        }

        // REDIRECT OTOMATIS BERDASARKAN PROFESI
        if ($user->profesi === 'Apoteker Farmasi') {
            return redirect()->route('profesi.dashboard')
                ->with('success', 'Login berhasil. Selamat datang di portal profesi!');
        }

        return redirect()->route('mahasiswa.dashboard')
            ->with('success', 'Login berhasil. Selamat datang di portal mahasiswa!');
    }
    // ===========================================================================
    // regenerateVA
    // ===========================================================================
    public function regenerateVA(Request $request)
    {
        $request->validate(['confirm' => 'required|accepted']);

        $user   = Auth::user();
        $master = MasterUser::where('user_id', $user->id)->first();

        if (!$master) {
            return back()->with('error', 'Data master user tidak ditemukan.');
        }

        if (!$this->mandiriService->canRegenerateVA($master)) {
            return back()->with('warning', 'Pembayaran Anda sudah lunas atau VA masih aktif.');
        }

        $this->mandiriService->deleteVA($master->trx_id, $master->va_number);

        $result = $this->mandiriService->regenerateVirtualAccount($user, $master->va_amount);

        if (!$result || !($result['status'] ?? false)) {
            return back()->with('error', 'Gagal memperbarui Virtual Account ke Bank Mandiri.');
        }

        $master->update([
            'trx_id'         => $result['trx_id'],
            'va_number'      => $result['virtual_account'],
            'va_expired_at'  => $result['datetime_expired'],
            'va_status'      => 'pending',
        ]);

        return redirect()->route('mahasiswa.va.dashboard')
            ->with('success', 'Virtual Account Mandiri Berhasil Diperbarui!');
    }

    // ===========================================================================
    // checkStatus (AJAX) — baris 240 yang error ada di sini
    // ===========================================================================
    public function checkStatus(Request $request)
    {
        $user   = Auth::user();
        $master = MasterUser::where('user_id', $user->id)->first();

        if (!$master) {
            return response()->json(['status' => 'error', 'message' => 'Data tidak ditemukan'], 404);
        }

        ['time_remaining' => $timeRemaining, 'is_expiring_soon' => $isExpiringSoon, 'is_expired' => $isExpired]
            = $this->calcTimeRemaining($master->va_expired_at);

        $vaHistory = $this->getVAHistory($user->id);

        // FIX: gunakan parseDate() sebelum ->format() agar tidak error jika string
        $expiredFormatted = $this->parseDate($master->va_expired_at)?->format('d/m/Y H:i');
        $paymentFormatted = $this->parseDate($master->payment_date)?->format('d/m/Y H:i');

        return response()->json([
            'status' => 'success',
            'data'   => [
                'va_status'        => $master->va_status,
                'va_number'        => $master->va_number,
                'va_amount'        => $master->va_amount,
                'va_expired_at'    => $expiredFormatted,
                'payment_date'     => $paymentFormatted,
                'payment_amount'   => $master->payment_amount,
                'gelombang'        => $master->gelombang,
                'trx_id'           => $master->trx_id,
                'can_regenerate'   => $this->mandiriService->canRegenerateVA($master),
                'is_expired'       => $isExpired,
                'is_expiring_soon' => $isExpiringSoon,
                'time_remaining'   => $timeRemaining,
                'history_count'    => count($vaHistory),
                'trx_id_history'   => $this->getTrxIdHistory($vaHistory),
            ]
        ]);
    }

    // ===========================================================================
    // autoLogin
    // ===========================================================================
    // public function autoLogin(Request $request)
    // {
    //     $user   = Auth::user();
    //     $master = MasterUser::where('user_id', $user->id)->first();

    //     if (!$master) {
    //         return redirect()->route('mahasiswa.va.dashboard')
    //             ->with('error', 'Data Virtual Account tidak ditemukan.');
    //     }

    //     if ($master->va_status !== 'paid') {
    //         Log::warning('Percobaan akses dashboard tanpa bayar', ['user_id' => $user->id]);
    //         return redirect()->route('mahasiswa.va.dashboard')
    //             ->with('warning', 'Status pembayaran Anda masih pending. Silakan selesaikan pembayaran terlebih dahulu.');
    //     }

    //     return redirect()->route('mahasiswa.dashboard')
    //         ->with('success', 'Login berhasil. Selamat datang di portal mahasiswa!');
    // }

    // ===========================================================================
    // history
    // ===========================================================================
    public function history()
    {
        $user      = Auth::user();
        $vaHistory = $this->getVAHistory($user->id);
        $master    = MasterUser::where('user_id', $user->id)->first();

        return view('mahasiswa.va.history', [
            'va_history'      => $vaHistory,
            'current_trx_id'  => $master->trx_id ?? null,
            'current_va'      => $master->va_number ?? null,
            'history_count'   => count($vaHistory),
        ]);
    }

    // ===========================================================================
    // details
    // ===========================================================================
    public function details()
    {
        $user   = Auth::user();
        $master = MasterUser::where('user_id', $user->id)->first();

        if (!$master) {
            return redirect()->route('mahasiswa.va.dashboard')
                ->with('error', 'Data Virtual Account tidak ditemukan.');
        }

        ['time_remaining' => $timeRemaining, 'is_expiring_soon' => $isExpiringSoon, 'is_expired' => $isExpired]
            = $this->calcTimeRemaining($master->va_expired_at);

        // FIX: gunakan parseDate() untuk semua field tanggal
        $expiredParsed = $this->parseDate($master->va_expired_at);
        $paymentParsed = $this->parseDate($master->payment_date);
        $createdParsed = $this->parseDate($master->created_at);
        $updatedParsed = $this->parseDate($master->updated_at);

        $vaDetails = [
            'va_number'           => $master->va_number,
            'va_number_formatted' => $this->formatVANumber($master->va_number),
            'trx_id'              => $master->trx_id,
            'amount'              => 'Rp ' . number_format($master->va_amount, 0, ',', '.'),
            'status'              => $this->getStatusLabel($master->va_status),
            'status_color'        => $this->getStatusColor($master->va_status),
            'expired_at'          => $expiredParsed ? $expiredParsed->format('d F Y H:i') : '-',
            'expired_at_full'     => $expiredParsed ? $expiredParsed->format('d/m/Y H:i:s') : '-',
            'time_remaining'      => $timeRemaining,
            'is_expiring_soon'    => $isExpiringSoon,
            'is_expired'          => $isExpired,
            'payment_date'        => $paymentParsed ? $paymentParsed->format('d F Y H:i') : '-',
            'payment_amount'      => $master->payment_amount
                ? 'Rp ' . number_format($master->payment_amount, 0, ',', '.') : '-',
            'gelombang'           => $master->gelombang ?? 'Belum ditentukan',
            'promo_code'          => $master->promo_code_used ?? '-',
            'original_amount'     => $master->original_amount
                ? 'Rp ' . number_format($master->original_amount, 0, ',', '.') : null,
            'discount_amount'     => $master->discount_amount
                ? 'Rp ' . number_format($master->discount_amount, 0, ',', '.') : null,
            'discount_percentage' => $master->discount_percentage
                ? $master->discount_percentage . '%' : null,
            'created_at'          => $createdParsed ? $createdParsed->format('d F Y') : '-',
            'updated_at'          => $updatedParsed ? $updatedParsed->format('d F Y H:i') : '-',
        ];

        $generationCount = DB::table('master_users_va_history')
            ->where('user_id', $user->id)
            ->whereIn('operation', ['regenerate', 'createBilling'])
            ->count();

        return view('mahasiswa.va.details', [
            'va_details'       => $vaDetails,
            'generation_count' => $generationCount,
            'can_regenerate'   => $this->mandiriService->canRegenerateVA($master),
            'master'           => $master,
        ]);
    }

    // ===========================================================================
    // uploadProof
    // ===========================================================================
    public function uploadProof(Request $request)
    {
        $request->validate([
            'payment_proof' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $user   = Auth::user();
        $master = MasterUser::where('user_id', $user->id)->first();

        if ($request->hasFile('payment_proof')) {
            if ($master->payment_proof) {
                Storage::disk('public')->delete($master->payment_proof);
            }

            $path = $request->file('payment_proof')->store('proofs', 'public');
            $master->update(['payment_proof' => $path]);

            return back()->with('success', 'Bukti pembayaran berhasil diunggah. Admin akan segera memverifikasi.');
        }

        return back()->with('error', 'Gagal mengunggah file.');
    }

    // ===========================================================================
    // downloadDetails
    // ===========================================================================
    public function downloadDetails()
    {
        $user   = Auth::user();
        $master = MasterUser::where('user_id', $user->id)->first();

        if (!$master) {
            return back()->with('error', 'Data Virtual Account tidak ditemukan.');
        }

        return view('mahasiswa.va.pdf', [
            'title'        => 'Virtual Account Details - ' . $user->name,
            'date'         => date('d/m/Y H:i'),
            'user'         => $user,
            'master'       => $master,
            'va_formatted' => $this->formatVANumber($master->va_number),
        ]);
    }

    // ===========================================================================
    // PRIVATE HELPERS
    // ===========================================================================

    /**
     * Salin field VA dari master ke objek user agar Blade bisa akses $user->va_*
     */
    private function fillUserFromMaster($user, $master): void
    {
        $user->trx_id              = $master->trx_id              ?? null;
        $user->va_number           = $master->va_number           ?? null;
        $user->va_status           = $master->va_status           ?? null;
        $user->va_amount           = $master->va_amount           ?? null;
        $user->va_expired_at       = $master->va_expired_at       ?? null;
        $user->payment_date        = $master->payment_date        ?? null;
        $user->payment_amount      = $master->payment_amount      ?? null;
        $user->gelombang           = $master->gelombang           ?? null;
        $user->promo_code_used     = $master->promo_code_used     ?? null;
        $user->original_amount     = $master->original_amount     ?? null;
        $user->discount_amount     = $master->discount_amount     ?? null;
        $user->discount_percentage = $master->discount_percentage ?? null;
        $user->plain_password      = $master->plain_password      ?? null; 
    }

    private function getVAHistory($userId)
    {
        try {
            return DB::table('master_users_va_history')
                ->where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($item) {
                    return [
                        'id'         => $item->id,
                        'trx_id'     => $item->trx_id,
                        'va_number'  => $item->va_number,
                        'operation'  => $this->getOperationLabel($item->operation),
                        'old_data'   => $item->old_data ? json_decode($item->old_data, true) : null,
                        'created_at' => Carbon::parse($item->created_at)->format('d/m/Y H:i:s'),
                        'time_ago'   => Carbon::parse($item->created_at)->diffForHumans(),
                    ];
                });
        } catch (\Exception $e) {
            Log::error('Error get VA history: ' . $e->getMessage());
            return collect();
        }
    }

    private function getOperationLabel($operation): string
    {
        return [
            'create'        => 'Pembuatan VA',
            'update'        => 'Update VA',
            'regenerate'    => 'Regenerate VA',
            'close'         => 'Penutupan VA',
            'createBilling' => 'Pembuatan Billing',
            'updateBilling' => 'Update Billing',
            'inquiry'       => 'Cek Status',
        ][$operation] ?? $operation;
    }

    private function getTrxIdHistory($vaHistory): array
    {
        $trxIds = [];
        foreach ($vaHistory as $history) {
            if (!in_array($history['trx_id'], $trxIds)) {
                $trxIds[] = $history['trx_id'];
            }
        }
        return $trxIds;
    }

    private function logVAHistory($userId, $trxId, $vaNumber, $operation, $oldData = null): void
    {
        try {
            DB::table('master_users_va_history')->insert([
                'user_id'    => $userId,
                'trx_id'     => $trxId,
                'va_number'  => $vaNumber,
                'operation'  => $operation,
                'old_data'   => $oldData ? json_encode($oldData) : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            Log::info('📝 VA history logged', [
                'user_id'   => $userId,
                'trx_id'    => $trxId,
                'operation' => $operation,
                'va_number' => $vaNumber,
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Error logging VA history: ' . $e->getMessage());
        }
    }

    private function formatVANumber($vaNumber): string
    {
        if (strlen((string) $vaNumber) === 16) {
            return substr($vaNumber, 0, 4) . ' '
                 . substr($vaNumber, 4, 4) . ' '
                 . substr($vaNumber, 8, 4) . ' '
                 . substr($vaNumber, 12, 4);
        }
        return (string) $vaNumber;
    }

    private function getStatusLabel($status): string
    {
        return [
            'pending'   => 'Menunggu Pembayaran',
            'paid'      => 'Sudah Dibayar',
            'expired'   => 'Kadaluarsa',
            'cancelled' => 'Dibatalkan',
            'failed'    => 'Gagal',
        ][$status] ?? ucfirst((string) $status);
    }

    private function getStatusColor($status): string
    {
        return [
            'pending'   => 'warning',
            'paid'      => 'success',
            'expired'   => 'danger',
            'cancelled' => 'secondary',
            'failed'    => 'danger',
        ][$status] ?? 'secondary';
    }
}