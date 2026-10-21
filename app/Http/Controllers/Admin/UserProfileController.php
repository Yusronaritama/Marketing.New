<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MasterUser;
use App\Models\MasterMoodle;
use App\Models\User;
use App\Models\PaymentLog;
use App\Models\BNICallbackLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\AkunPendaftarDefault;
use Carbon\Carbon;

class UserProfileController extends Controller
{
    public function __construct()
    {
        view()->share('user', auth()->user());
    }

public function index(Request $request)
{
    $search = $request->get('search');

    $profiles = MasterUser::with('user')
        ->when($search, function ($query) use ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })->orWhere('va_number', 'like', "%{$search}%");
        })
        ->paginate(15)
        ->appends(['search' => $search]); // penting agar URL pagination ikut bawa parameter search

    return view('admin.user-profiles.userallprofile', compact('profiles'));
}

    public function show($id)
    {
        $profile = MasterUser::with('user')->findOrFail($id);
        return view('admin.userprofile_show', compact('profile'));
    }
public function updateProfile(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20' // Tambahkan validasi phone
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            // 1. Cari data master_user untuk mendapatkan user_id
            $masterUser = MasterUser::findOrFail($id);
            
            // 2. Cari data user asli di tabel users
            $user = User::findOrFail($masterUser->user_id);
            
            // Cek apakah email sudah dipakai user lain
            $emailExists = User::where('email', $request->email)
                               ->where('id', '!=', $user->id)
                               ->exists();
                               
            if ($emailExists) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Email tersebut sudah digunakan oleh pengguna lain.'
                ], 422);
            }

            // 3. Update data di tabel users
            $user->name = $request->name;
            $user->email = $request->email;
            $user->phone = $request->phone; // Tambahkan update phone
            $user->save();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Profile berhasil diperbarui!',
                'data' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal update profile', ['error' => $e->getMessage(), 'user_id' => $id]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }
    // 🔹 Fungsi untuk ambil data user via AJAX
    public function showAjax($id)
{
    try {
        $masterUser = MasterUser::with('user')->findOrFail($id);
        $masterMoodle = MasterMoodle::where('master_user_id', $id)->first();
        
        // Ambil data dari tabel default pendaftar (backup awal)
        $pendaftarDefault = AkunPendaftarDefault::where('email', $masterUser->user->email)->first();

        return response()->json([
            'status' => 'success',
            'master_user' => [
                'name' => $masterUser->user->name ?? '-',
                'trx_id' => $masterUser->trx_id ?? '-', 
                'email' => $masterUser->user->email ?? '-',
                'phone' => $masterUser->user->phone ?? '-',
                'va_number' => $masterUser->va_number ?? '-',
                'va_status' => $masterUser->va_status ?? '-',
                'created_at' => $masterUser->created_at ? $masterUser->created_at->format('d M Y H:i') : '-',
                
                // 1. Password saat pendaftaran pertama kali
                'password_awal' => $pendaftarDefault->password_plain ?? 'Tidak Ditemukan',
                
                // 2. Password terbaru (dari tabel master_users yang Anda tambahkan tadi)
                'plain_password' => $masterUser->plain_password ?? '-', 
            ],
            'master_moodle' => $masterMoodle ? [
                'username' => $masterMoodle->username ?? '-',
                'status' => $masterMoodle->status ?? 'not_registered',
                'enrol_status' => $masterMoodle->enrol_status ?? 'not_enrolled',
                'moodle_user_id' => $masterMoodle->moodle_user_id ?? '-',
            ] : null,
        ]);

        } catch (\Exception $e) {
            Log::error('Gagal mengambil data user AJAX', [
                'error' => $e->getMessage(),
                'user_id' => $id
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data user atau moodle.',
                'error_detail' => $e->getMessage(),
            ], 500);
        }
    }

    // 🔹 FITUR BARU: Hapus User dan Nonaktifkan VA - GUNAKAN POST
    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();
        
        try {
            // Cari data MasterUser
            $masterUser = MasterUser::with('user')->findOrFail($id);
            $userId = $masterUser->user_id;
            $vaNumber = $masterUser->va_number;
            $userName = $masterUser->user->name ?? 'N/A';
            $userEmail = $masterUser->user->email ?? 'N/A';
            
            // 1. Nonaktifkan VA terlebih dahulu
            $this->deactivateVirtualAccount($masterUser);
            
            // 2. Hapus data terkait - PERBAIKAN DI SINI
            $this->deleteRelatedData($masterUser, $userId);
            
            // 3. Hapus MasterUser
            $masterUser->delete();
            
            // 4. Hapus User
            $user = User::find($userId);
            if ($user) {
                $user->delete();
            }
            
            DB::commit();
            
            // Log aktivitas
            Log::info('User berhasil dihapus', [
                'admin_id' => auth()->id(),
                'admin_name' => auth()->user()->name,
                'deleted_user_id' => $userId,
                'deleted_user_name' => $userName,
                'deleted_user_email' => $userEmail,
                'va_number' => $vaNumber,
                'ip_address' => $request->ip(),
                'timestamp' => now()
            ]);
            
            // Kirim notifikasi ke admin
            $this->sendDeletionNotification($userName, $userEmail, $vaNumber);
            
            if ($request->ajax()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'User berhasil dihapus dan VA dinonaktifkan.',
                    'user_id' => $userId,
                    'user_name' => $userName,
                    'va_number' => $vaNumber
                ]);
            }
            
            return redirect()->route('admin.userprofile.index')
                ->with('success', 'User berhasil dihapus dan VA dinonaktifkan.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Gagal menghapus user', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $id,
                'admin_id' => auth()->id()
            ]);
            
            if ($request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gagal menghapus user: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Gagal menghapus user: ' . $e->getMessage());
        }
    }

    // 🔹 Method untuk nonaktifkan VA
    private function deactivateVirtualAccount(MasterUser $masterUser)
    {
        try {
            // Cek ENUM values dari database terlebih dahulu
            $validStatuses = $this->getValidVaStatusesFromDB();
            
            // Tentukan status yang akan digunakan
            if (in_array('inactive', $validStatuses)) {
                $newStatus = 'inactive';
            } elseif (in_array('expired', $validStatuses)) {
                $newStatus = 'expired';
            } elseif (in_array('paid', $validStatuses)) {
                $newStatus = 'paid';
            } else {
                $newStatus = $validStatuses[0] ?? 'pending';
            }
            
            Log::info('Mencoba menonaktifkan VA', [
                'va_number' => $masterUser->va_number,
                'selected_status' => $newStatus,
                'valid_statuses' => $validStatuses
            ]);
            
            // Update status VA
            $masterUser->va_status = $newStatus;
            $masterUser->va_expired_at = Carbon::now();
            $masterUser->deactivated_at = Carbon::now();
            $masterUser->deactivated_by = auth()->id();
            $masterUser->deactivation_reason = 'User deleted by admin';
            $masterUser->save();
            
            Log::info('VA dinonaktifkan', [
                'va_number' => $masterUser->va_number,
                'user_id' => $masterUser->user_id,
                'admin_id' => auth()->id(),
                'new_status' => $newStatus
            ]);
            
        } catch (\Exception $e) {
            Log::error('Gagal menonaktifkan VA', [
                'va_number' => $masterUser->va_number,
                'error' => $e->getMessage(),
                'error_code' => $e->getCode()
            ]);
            throw $e;
        }
    }
    
    // 🔹 Helper method untuk mendapatkan ENUM values dari database
    private function getValidVaStatusesFromDB()
    {
        try {
            $result = DB::select("SHOW COLUMNS FROM master_users WHERE Field = 'va_status'");
            
            if (!empty($result)) {
                $type = $result[0]->Type;
                // Extract ENUM values
                preg_match("/^enum\(\'(.*)\'\)$/", $type, $matches);
                
                if (isset($matches[1])) {
                    return explode("','", $matches[1]);
                }
            }
            
            // Default values jika tidak bisa mendapatkan dari DB
            return ['pending', 'active', 'paid', 'partial'];
            
        } catch (\Exception $e) {
            Log::error('Gagal mengambil ENUM values', ['error' => $e->getMessage()]);
            return ['pending', 'active', 'paid', 'partial'];
        }
    }

    // 🔹 Method untuk menghapus data terkait - PERBAIKAN UTAMA DI SINI
    private function deleteRelatedData(MasterUser $masterUser, $userId)
    {
        try {
            // Hapus data MasterMoodle
            MasterMoodle::where('master_user_id', $masterUser->id)->delete();
            
            // Hapus PaymentLog
            PaymentLog::where('user_id', $userId)
                ->orWhere('master_user_id', $masterUser->id)
                ->delete();
            
            // PERBAIKAN: Cek apakah tabel BNICallbackLog memiliki kolom deleted_at
            $hasDeletedAtColumn = $this->checkIfTableHasColumn('bni_callback_logs', 'deleted_at');
            
            if ($hasDeletedAtColumn) {
                // Jika ada kolom deleted_at, gunakan soft delete
                BNICallbackLog::where('user_id', $userId)
                    ->orWhere('master_user_id', $masterUser->id)
                    ->update([
                        'deleted_at' => now(),
                        'deletion_note' => 'User account deleted by admin: ' . auth()->user()->name
                    ]);
            } else {
                // Jika tidak ada kolom deleted_at, gunakan hard delete atau tambahkan kolom lain
                BNICallbackLog::where('user_id', $userId)
                    ->orWhere('master_user_id', $masterUser->id)
                    ->update([
                        'status' => 'deleted',
                        'deletion_note' => 'User account deleted by admin: ' . auth()->user()->name,
                        'updated_at' => now()
                    ]);
            }
            
            Log::info('Data terkait berhasil dihapus', [
                'user_id' => $userId,
                'master_user_id' => $masterUser->id,
                'va_number' => $masterUser->va_number,
                'has_deleted_at_column' => $hasDeletedAtColumn
            ]);
            
        } catch (\Exception $e) {
            Log::error('Gagal menghapus data terkait', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    // 🔹 Helper method untuk cek apakah tabel memiliki kolom tertentu
    private function checkIfTableHasColumn($tableName, $columnName)
    {
        try {
            $result = DB::select("SHOW COLUMNS FROM {$tableName} WHERE Field = ?", [$columnName]);
            return !empty($result);
        } catch (\Exception $e) {
            Log::warning('Gagal mengecek kolom tabel', [
                'table' => $tableName,
                'column' => $columnName,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    // 🔹 Method untuk mengirim notifikasi ke admin
    private function sendDeletionNotification($userName, $userEmail, $vaNumber)
    {
        try {
            // Cek apakah WhatsAppService ada
            if (class_exists(\App\Services\WhatsAppService::class)) {
                $whatsappService = app(\App\Services\WhatsAppService::class);
                $adminPhones = env('WHATSAPP_ADMIN_PHONES', '6281234567890');
                
                if ($adminPhones) {
                    $adminPhones = explode(',', $adminPhones);
                    
                    $message = "🚨 *USER DIHAPUS & VA DINONAKTIFKAN*\n\n"
                             . "⚠️ User telah dihapus dari sistem\n\n"
                             . "👤 *Nama:* {$userName}\n"
                             . "📧 *Email:* {$userEmail}\n"
                             . "🏦 *VA Number:* {$vaNumber}\n"
                             . "👨‍💼 *Dihapus oleh:* " . auth()->user()->name . "\n"
                             . "⏰ *Waktu:* " . now()->format('d/m/Y H:i:s') . "\n\n"
                             . "✅ VA telah dinonaktifkan.";
                    
                    foreach ($adminPhones as $adminPhone) {
                        $formattedPhone = $this->formatPhoneForWhatsApp($adminPhone);
                        if ($formattedPhone) {
                            $whatsappService->sendMessage($formattedPhone, $message);
                        }
                    }
                }
            }
            
        } catch (\Exception $e) {
            Log::error('Gagal mengirim notifikasi penghapusan', [
                'error' => $e->getMessage()
            ]);
        }
    }

    // 🔹 Helper method untuk format nomor WhatsApp
    private function formatPhoneForWhatsApp($phone)
    {
        // Hapus semua karakter non-digit
        $cleanPhone = preg_replace('/\D/', '', $phone);
        
        if (empty($cleanPhone) || strlen($cleanPhone) < 10) {
            return null;
        }
        
        // Format ke 62xxx
        if (substr($cleanPhone, 0, 1) === '0') {
            return '62' . substr($cleanPhone, 1);
        } elseif (substr($cleanPhone, 0, 2) === '62') {
            return $cleanPhone;
        } elseif (substr($cleanPhone, 0, 1) === '8') {
            return '62' . $cleanPhone;
        }
        
        return $cleanPhone;
    }

    // 🔹 Method untuk bulk delete
    public function bulkDestroy(Request $request)
{
    // Validasi dengan pesan error kustom agar tahu ID mana yang bermasalah
    $validator = Validator::make($request->all(), [
        'user_ids' => 'required|array',
        'user_ids.*' => 'required' // Kita validasi manual di dalam loop saja untuk keamanan
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'message' => 'Data yang dikirim tidak valid.',
            'errors' => $validator->errors()
        ], 422);
    }
    
    $deletedCount = 0;
    $failedUsers = [];
    
    foreach ($request->user_ids as $userId) {
        try {
            DB::beginTransaction();
            
            // Cari berdasarkan ID primer di tabel master_users
            $masterUser = MasterUser::find($userId);
            
            if (!$masterUser) {
                // Jika tidak ketemu di ID, coba cari di kolom user_id (opsional, jaga-jaga salah kirim ID)
                $masterUser = MasterUser::where('user_id', $userId)->first();
            }

            if (!$masterUser) {
                $failedUsers[] = ['user_id' => $userId, 'error' => 'Data tidak ditemukan di database.'];
                DB::rollBack();
                continue;
            }
            
            $currentUserId = $masterUser->user_id;

            // 1. Nonaktifkan VA (BNI/Lainnya)
            if (method_exists($this, 'deactivateVirtualAccount')) {
                $this->deactivateVirtualAccount($masterUser);
            }
            
            // 2. Hapus data terkait (Moodle, dll)
            if (method_exists($this, 'deleteRelatedData')) {
                $this->deleteRelatedData($masterUser, $currentUserId);
            }
            
            // 3. Hapus MasterUser
            $masterUser->delete();
            
            // 4. Hapus User Utama
            $user = User::find($currentUserId);
            if ($user) {
                $user->delete();
            }
            
            DB::commit();
            $deletedCount++;
            
        } catch (\Exception $e) {
            DB::rollBack();
            $failedUsers[] = ['user_id' => $userId, 'error' => $e->getMessage()];
        }
    }
    
    return response()->json([
        'status' => 'success',
        'message' => "Berhasil menghapus {$deletedCount} user.",
        'failed_count' => count($failedUsers),
        'failed_users' => $failedUsers
    ]);
}

    // 🔹 Method untuk cek status VA yang valid
    public function getValidVaStatuses()
    {
        try {
            $validStatuses = $this->getValidVaStatusesFromDB();
            
            return response()->json([
                'status' => 'success',
                'valid_statuses' => $validStatuses
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    // 🔹 Method untuk statistik
    public function getStats()
    {
        try {
            $totalUsers = MasterUser::count();
            $activeUsers = MasterUser::where('va_status', 'active')->count();
            $paidUsers = MasterUser::where('va_status', 'paid')->count();
            $inactiveUsers = MasterUser::where('va_status', 'inactive')->orWhere('va_status', 'expired')->count();
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'total_users' => $totalUsers,
                    'active_users' => $activeUsers,
                    'paid_users' => $paidUsers,
                    'inactive_users' => $inactiveUsers
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Gagal mengambil statistik user', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil statistik'
            ], 500);
        }
    }
}