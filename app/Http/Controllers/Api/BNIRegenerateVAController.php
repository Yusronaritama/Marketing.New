<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator; // ✅ TAMBAHKAN INI
use App\Models\MasterUser;
use App\Models\User;
use App\Services\BNIApiService;
use Carbon\Carbon;

class BNIRegenerateVAController extends Controller
{
    protected $bniService;

    public function __construct(BNIApiService $bniService)
    {
        $this->bniService = $bniService;
    }

    /**
     * API Endpoint untuk regenerate VA dari Node.js service - TANPA CHECK EXPIRED
     */
    public function regenerateVA(Request $request)
{
    Log::info('🔄 API Regenerate VA called - DETAILED', [
        'request' => $request->all(),
        'ip' => $request->ip(),
        'user_agent' => $request->userAgent(),
        'timestamp' => now()->toISOString()
    ]);

    // Validation
    $validator = Validator::make($request->all(), [
        'user_id' => 'required|integer',
        'trx_id' => 'required|string',
        'virtual_account' => 'required|string|size:16',
    ]);

    if ($validator->fails()) {
        Log::warning('❌ Validation failed', [
            'errors' => $validator->errors()->toArray(),
            'request' => $request->all()
        ]);
        
        return response()->json([
            'status' => 'error',
            'message' => 'Validation failed',
            'errors' => $validator->errors()
        ], 400);
    }

    try {
        // Cari master user dengan user_id DAN VA number
        $masterUser = MasterUser::where('user_id', $request->user_id)
            ->where('va_number', $request->virtual_account)
            ->first();

        // Fallback: cari hanya dengan user_id jika tidak ketemu
        if (!$masterUser) {
            Log::warning('⚠️ Master user not found by VA, trying user_id only', [
                'user_id' => $request->user_id,
                'va' => $request->virtual_account,
                'trx_id_request' => $request->trx_id
            ]);
            
            $masterUser = MasterUser::where('user_id', $request->user_id)
                ->orderBy('created_at', 'desc')
                ->first();
        }

        if (!$masterUser) {
            Log::warning('❌ Master user not found at all', [
                'user_id' => $request->user_id,
                'trx_id_request' => $request->trx_id,
                'request_va' => $request->virtual_account
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Master user data not found'
            ], 404);
        }

        // Get user details
        $user = User::find($masterUser->user_id);
        if (!$user) {
            Log::error('❌ User not found', [
                'user_id' => $masterUser->user_id,
                'master_user_id' => $masterUser->id
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'User not found'
            ], 404);
        }

        Log::info('🔍 Processing VA regeneration', [
            'user_id' => $user->id,
            'name' => $user->name,
            'current_va' => $masterUser->va_number,
            'current_trx' => $masterUser->trx_id,
            'va_status' => $masterUser->va_status,
            'paid_at' => $masterUser->paid_at,
            'va_expired_at' => $masterUser->va_expired_at,
            'va_amount' => $masterUser->va_amount,
            'request_source' => $request->get('source', 'unknown')
        ]);

        // Check if VA is pending and not paid
        if ($masterUser->va_status !== 'pending' || $masterUser->paid_at) {
            Log::warning('⚠️ Cannot regenerate VA', [
                'va_status' => $masterUser->va_status,
                'paid_at' => $masterUser->paid_at
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'VA cannot be regenerated. Status: ' . $masterUser->va_status
            ], 400);
        }

        // Amount calculation
        $amount = $masterUser->va_amount ?: 300000;
        
        // Call BNI API service
        Log::info('📞 Calling BNI API service for regeneration');
        $result = $this->bniService->regenerateVirtualAccount(
            $user, 
            $amount, 
            null, // promo code
            $masterUser->trx_id
        );

        if (!$result || !isset($result['status']) || $result['status'] !== true) {
            $error = $this->bniService->getLastError() ?? 'Unknown error from BNI API';
            
            Log::error('❌ VA regeneration failed', [
                'user_id' => $user->id,
                'error' => $error,
                'bni_result' => $result
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to regenerate VA: ' . $error
            ], 500);
        }

        // Update database
        // Update database
DB::beginTransaction();
try {
    // Simpan data lama
    $oldVa = $masterUser->va_number;
    $oldTrx = $masterUser->trx_id;
    $oldAmount = $masterUser->va_amount;
    $oldExpired = $masterUser->va_expired_at;
    
    // Update dengan data baru dari BNI - UBAH KE 60 MENIT
    $masterUser->va_number = $result['virtual_account'];
    $masterUser->trx_id = $result['trx_id'] ?? $masterUser->trx_id;
    $masterUser->va_amount = $result['amount'] ?? $amount;
    $masterUser->va_expired_at = now('UTC')->addHours(1); // ⚠️ UBAH INI: 60 menit
    $masterUser->va_status = 'pending';
    $masterUser->updated_at = now();
    
    $masterUser->save();
    
    DB::commit();

    Log::info('✅ VA regenerated successfully (60 minutes expiry)', [
        'user_id' => $user->id,
        'operation' => $result['operation'] ?? 'unknown',
        'old_va' => $oldVa,
        'new_va' => $masterUser->va_number,
        'old_trx' => $oldTrx,
        'new_trx' => $masterUser->trx_id,
        'old_amount' => $oldAmount,
        'new_amount' => $masterUser->va_amount,
        'new_expired_utc' => $masterUser->va_expired_at,
        'new_expired_wib' => Carbon::parse($masterUser->va_expired_at)->timezone('Asia/Jakarta')->toDateTimeString(),
        'expired_in_minutes' => 60,
        'bni_response' => $result,
        'va_changed' => $oldVa !== $masterUser->va_number
    ]);

    return response()->json([
        'status' => 'success',
        'message' => 'Virtual Account regenerated successfully (expired in 60 minutes)',
        'data' => [
            'virtual_account' => $masterUser->va_number,
            'trx_id' => $masterUser->trx_id,
            'amount' => number_format($masterUser->va_amount, 2, '.', ''),
            'datetime_expired' => $masterUser->va_expired_at,
            'expired_at' => $masterUser->va_expired_at,
            'expired_at_wib' => Carbon::parse($masterUser->va_expired_at)->timezone('Asia/Jakarta')->toDateTimeString(),
            'expired_in_minutes' => 60, // Tambahkan info expired dalam menit
            'va_status' => $masterUser->va_status,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_email' => $user->email,
            'user_phone' => $user->phone,
            'bni_operation' => $result['operation'] ?? 'createBilling',
            'old_va' => $oldVa,
            'va_changed' => $oldVa !== $masterUser->va_number
        ]
    ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('💥 Database update failed after VA regeneration', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'bni_result' => $result
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'VA generated at BNI but database update failed: ' . $e->getMessage()
            ], 500);
        }

    } catch (\Exception $e) {
        Log::error('💥 API Regenerate VA Exception', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'request' => $request->all()
        ]);
        
        return response()->json([
            'status' => 'error',
            'message' => 'Internal server error: ' . $e->getMessage()
        ], 500);
    }
}
    /**
     * Get list of ALL pending users (tanpa filter expired) - SESUAI PERMINTAAN NODE.JS
     */
    public function getPendingUsers(Request $request)
    {
        try {
            Log::info('📋 Getting ALL pending users for VA regeneration (no expiry filter)');
            
            $query = MasterUser::select([
                    'master_users.id as master_id',
                    'master_users.user_id',
                    'master_users.trx_id',
                    'master_users.va_number',
                    'master_users.va_status',
                    'master_users.va_amount',
                    'master_users.va_expired_at',
                    'master_users.gelombang',
                    'master_users.promo_code_used',
                    'master_users.discount_percentage',
                    'master_users.discount_amount',
                    'master_users.original_amount',
                    'master_users.paid_at',
                    'master_users.created_at',
                    'master_users.updated_at',
                    'users.name',
                    'users.email',
                    'users.phone',
                    'users.created_at as user_created_at'
                ])
                ->join('users', 'master_users.user_id', '=', 'users.id')
                ->where('master_users.va_status', 'pending')
                ->whereNull('master_users.paid_at')
                ->where('master_users.va_number', '!=', '')
                ->whereNotNull('master_users.va_number')
                // Batasi hanya user dalam 30 hari terakhir
                ->where('master_users.created_at', '>', now()->subDays(30))
                ->orderBy('master_users.created_at', 'ASC')
                ->limit($request->get('limit', 20));

            $users = $query->get();

            // Mark all users as needing regeneration (since no expiry check)
            $users = $users->map(function ($user) {
                $user->needs_regeneration = true;
                $user->regeneration_reason = 'Auto regeneration requested';
                $user->va_expired_at_wib = $user->va_expired_at 
                    ? Carbon::parse($user->va_expired_at)->timezone('Asia/Jakarta')->toDateTimeString()
                    : null;
                
                return $user;
            });

            Log::info('✅ Found pending users', [
                'count' => $users->count(),
                'current_time' => now()->toDateTimeString(),
                'note' => 'No expiry filter applied - all pending users will be regenerated'
            ]);

            // Log beberapa contoh untuk debugging
            if ($users->count() > 0) {
                $examples = $users->take(3)->map(function ($user) {
                    return [
                        'user_id' => $user->user_id,
                        'name' => $user->name,
                        'va_number' => $user->va_number,
                        'va_expired_at' => $user->va_expired_at,
                        'va_expired_at_wib' => $user->va_expired_at_wib,
                        'va_amount' => $user->va_amount,
                        'phone' => $user->phone
                    ];
                });
                
                Log::debug('📝 Example pending users:', ['examples' => $examples->toArray()]);
            }

            return response()->json([
                'status' => 'success',
                'count' => $users->count(),
                'note' => 'All pending users will be regenerated (no expiry check)',
                'data' => $users
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Error getting pending users', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get pending users: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API untuk mendapatkan detail single user untuk testing
     */
    public function getUserDetail(Request $request, $userId)
    {
        try {
            Log::info('🔍 Getting user detail', ['user_id' => $userId]);
            
            $masterUser = MasterUser::with('user')
                ->where('user_id', $userId)
                ->first();

            if (!$masterUser) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'master_user' => $masterUser,
                    'user' => $masterUser->user,
                    'current_time' => now()->toDateTimeString(),
                    'va_expired_at' => $masterUser->va_expired_at,
                    'va_expired_at_wib' => $masterUser->va_expired_at 
                        ? Carbon::parse($masterUser->va_expired_at)->timezone('Asia/Jakarta')->toDateTimeString()
                        : null
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Error getting user detail', [
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * API untuk test koneksi dan status
     */
    public function healthCheck(Request $request)
    {
        try {
            $dbStatus = DB::connection()->getPdo() ? 'connected' : 'disconnected';
            
            $totalPending = MasterUser::where('va_status', 'pending')->count();
            $totalUsers = MasterUser::count();
            
            // Get count by status
            $statusCounts = MasterUser::select('va_status', DB::raw('count(*) as count'))
                ->groupBy('va_status')
                ->get()
                ->pluck('count', 'va_status');

            return response()->json([
                'status' => 'success',
                'service' => 'BNI VA Regeneration API',
                'timestamp' => now()->toISOString(),
                'timestamp_wib' => now('Asia/Jakarta')->toISOString(),
                'database' => $dbStatus,
                'stats' => [
                    'total_users' => $totalUsers,
                    'pending_users' => $totalPending,
                    'status_distribution' => $statusCounts,
                    'regeneration_mode' => 'Auto (no expiry check)'
                ],
                'endpoints' => [
                    'regenerate_va' => '/api/bni/regenerate-va (POST)',
                    'pending_users' => '/api/bni/pending-users (GET)',
                    'user_detail' => '/api/bni/user/{id} (GET)'
                ],
                'note' => 'VA regeneration now ignores expiry dates and regenerates on request'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Health check failed: ' . $e->getMessage(),
                'timestamp' => now()->toISOString()
            ], 500);
        }
    }
    public function regenerateVAOneMinute(Request $request)
{
    Log::info('⏱️ API Regenerate VA 1 MINUTE called', [
        'request' => $request->all(),
        'ip' => $request->ip(),
        'timestamp' => now()->toISOString()
    ]);

    // Tambahkan flag untuk expired 1 menit
    $request->merge(['force_1min_expiry' => true]);
    
    // Panggil method utama dengan parameter khusus
    return $this->regenerateVA($request);
}


public function checkBNIStatus(Request $request)
{
    try {
        Log::info('🔍 Checking BNI API connection status');
        
        $testPayload = [
            'type' => 'getBilling',
            'client_id' => env('BNI_CLIENT_ID'),
            'trx_id' => 'TEST-' . time(),
        ];

        $encryptedPayload = \App\Libraries\Bnienc::encrypt(
            $testPayload, 
            env('BNI_CLIENT_ID'), 
            env('BNI_SECRET_KEY')
        );
        
        $startTime = microtime(true);
        
        $response = Http::timeout(15)
            ->asJson()
            ->post(env('BNI_API_URL') . '/api/v1/billing/', [
                'client_id' => env('BNI_CLIENT_ID'),
                'data' => $encryptedPayload,
            ]);
        
        $endTime = microtime(true);
        $responseTime = round(($endTime - $startTime) * 1000, 2); // ms

        if ($response->successful()) {
            $result = $response->json();
            
            return response()->json([
                'status' => 'success',
                'message' => 'BNI API is accessible',
                'data' => [
                    'api_status' => 'online',
                    'response_time_ms' => $responseTime,
                    'bni_response_status' => $result['status'] ?? 'unknown',
                    'recommended_timeout' => '30 seconds',
                    'note' => 'BNI API response time: ' . $responseTime . 'ms'
                ]
            ]);
        } else {
            return response()->json([
                'status' => 'warning',
                'message' => 'BNI API might be slow or down',
                'data' => [
                    'api_status' => 'slow',
                    'response_time_ms' => $responseTime,
                    'http_status' => $response->status(),
                    'recommended_action' => 'Increase timeout or retry later',
                    'note' => 'BNI API response time too long: ' . $responseTime . 'ms'
                ]
            ]);
        }

    } catch (\Exception $e) {
        Log::error('❌ BNI API check failed', ['error' => $e->getMessage()]);
        
        return response()->json([
            'status' => 'error',
            'message' => 'BNI API connection failed',
            'error' => $e->getMessage(),
            'data' => [
                'api_status' => 'offline',
                'recommended_action' => 'Check BNI credentials and network',
                'note' => 'BNI API mungkin sedang maintenance'
            ]
        ]);
    }
}
}