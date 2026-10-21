<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BNICallbackService;
use App\Models\User;
use App\Models\MasterUser;
use App\Models\BNICallbackLog;
use App\Models\PaymentLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BNIController extends Controller
{
    protected $bniService;

    public function __construct(BNICallbackService $bniService)
    {
        $this->bniService = $bniService;
    }

    /**
     * Display BNI callback management page
     */
    public function index(Request $request)
    {
        // Statistik
        $stats = [
            'total_callback' => BNICallbackLog::count(),
            'today_callback' => BNICallbackLog::whereDate('created_at', today())->count(),
            'processed' => BNICallbackLog::where('status', 'processed')->count(),
            'failed' => BNICallbackLog::where('status', 'failed')->count(),
            'new_payments' => PaymentLog::where('created_at', '>=', now()->subDays(7))->count(),
        ];
        
        $stats['processed_percentage'] = $stats['total_callback'] > 0 
            ? round(($stats['processed'] / $stats['total_callback']) * 100, 2) 
            : 0;
        $stats['failed_percentage'] = $stats['total_callback'] > 0 
            ? round(($stats['failed'] / $stats['total_callback']) * 100, 2) 
            : 0;

        // Data untuk tab
        $callbackLogs = BNICallbackLog::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(15, ['*'], 'callback_page');
            
        $paymentLogs = PaymentLog::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(15, ['*'], 'payment_page');
            
        $users = User::with('masterUser')
            ->whereHas('masterUser')
            ->select('users.*')
            ->addSelect([
                'va_number' => MasterUser::select('va_number')
                    ->whereColumn('user_id', 'users.id')
                    ->limit(1),
                'va_amount' => MasterUser::select('va_amount')
                    ->whereColumn('user_id', 'users.id')
                    ->limit(1),
                'va_status' => MasterUser::select('va_status')
                    ->whereColumn('user_id', 'users.id')
                    ->limit(1),
                'payment_amount' => MasterUser::select('payment_amount')
                    ->whereColumn('user_id', 'users.id')
                    ->limit(1),
                'paid_at' => MasterUser::select('paid_at')
                    ->whereColumn('user_id', 'users.id')
                    ->limit(1),
                'va_expired_at' => MasterUser::select('va_expired_at')
                    ->whereColumn('user_id', 'users.id')
                    ->limit(1),
            ])
            ->orderBy('users.created_at', 'desc')
            ->paginate(15, ['*'], 'users_page');

        return view('admin.bni.index', compact('stats', 'callbackLogs', 'paymentLogs', 'users'));
    }

    /**
     * Set all users as paid (Bulk Paid)
     */
    /**
 * Set all users as paid (Bulk Paid)
 */
public function bulkPaid(Request $request)
{
    $request->validate([
        'payment_method' => 'required|string',
        'payment_date' => 'required|date',
    ]);

    DB::beginTransaction();
    try {
        $paymentAmount = $request->input('payment_amount');
        $paymentMethod = $request->input('payment_method');
        $paymentDate = Carbon::parse($request->input('payment_date'));
        $sendNotification = $request->boolean('send_notification', true);
        $updateUserStatus = $request->boolean('update_user_status', true);

        // Get all users with master users
        $users = User::has('masterUser')->with('masterUser')->get();
        $processed = 0;
        $errors = [];

        foreach ($users as $user) {
            try {
                $masterUser = $user->masterUser;
                
                // Skip if already paid
                if ($masterUser->va_status === 'paid') {
                    Log::info("User {$user->id} sudah dibayar, skip", [
                        'user_id' => $user->id,
                        'va_status' => $masterUser->va_status,
                    ]);
                    continue;
                }
                
                // Determine payment amount
                $amount = $paymentAmount ?: $masterUser->va_amount;
                
                // Update master user
                $masterUser->update([
                    'va_status' => 'paid',
                    'payment_amount' => $amount,
                    'payment_method' => $paymentMethod,
                    'paid_at' => $paymentDate,
                    'datetime_payment' => $paymentDate->format('Y-m-d H:i:s'),
                    'va_expired_at' => null,
                    'updated_at' => now(),
                ]);

                // Update user if requested
                if ($updateUserStatus) {
                    $user->update([
                        'va_status' => 'PAID',
                        'paid_at' => $paymentDate,
                        'payment_method' => $paymentMethod,
                    ]);
                }

                // Create payment log
                PaymentLog::create([
                    'user_id' => $user->id,
                    'master_user_id' => $masterUser->id,
                    'virtual_account' => $masterUser->va_number ?? null,
                    'trx_id' => $masterUser->trx_id ?? null,
                    'trx_amount' => $masterUser->va_amount ?? 0,
                    'payment_amount' => $amount,
                    'datetime_payment' => $paymentDate->format('Y-m-d H:i:s'),
                    'status' => 'paid',
                    'payment_data' => json_encode([
                        'bulk_paid' => true,
                        'admin_id' => auth()->id(),
                        'payment_method' => $paymentMethod,
                    ]),
                ]);

                // Send WhatsApp notification if requested
                if ($sendNotification) {
                    try {
                        $this->bniService->sendBulkPaidNotification($user, $amount, $paymentMethod, $paymentDate);
                    } catch (\Exception $notifError) {
                        Log::warning("Gagal mengirim notifikasi untuk user {$user->id}", [
                            'error' => $notifError->getMessage(),
                        ]);
                        // Continue processing, jangan stop karena notifikasi gagal
                    }
                }

                $processed++;
                
            } catch (\Exception $e) {
                $errors[] = "User {$user->id} ({$user->name}): " . $e->getMessage();
                Log::error("Bulk paid error for user {$user->id}", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'user' => $user->id,
                ]);
            }
        }

        DB::commit();

        $message = "Berhasil mengubah {$processed} users menjadi PAID.";
        if (!empty($errors)) {
            $errorCount = count($errors);
            $message .= " Terjadi {$errorCount} error: " . implode(', ', array_slice($errors, 0, 5));
            if ($errorCount > 5) {
                $message .= " (dan " . ($errorCount - 5) . " error lainnya)";
            }
        }

        Log::info('Bulk paid completed', [
            'processed' => $processed,
            'errors' => count($errors),
            'admin_id' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => $message,
            'processed' => $processed,
            'errors' => $errors,
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        
        Log::error('Bulk paid failed', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'line' => $e->getLine(),
            'file' => $e->getFile(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Gagal memproses bulk paid: ' . $e->getMessage(),
        ], 500);
    }
}
    /**
     * Force set a single user as paid
     */
    public function forcePaid(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'payment_method' => 'required|string',
            'payment_amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
        ]);

        DB::beginTransaction();
        try {
            $user = User::with('masterUser')->findOrFail($request->user_id);
            
            if (!$user->masterUser) {
                throw new \Exception('User tidak memiliki data master user');
            }

            $masterUser = $user->masterUser;
            $paymentDate = Carbon::parse($request->input('payment_date'));
            
            // Update master user
            $masterUser->update([
                'va_status' => 'paid',
                'payment_amount' => $request->payment_amount,
                'payment_method' => $request->payment_method,
                'paid_at' => $paymentDate,
                'datetime_payment' => $paymentDate->format('Y-m-d H:i:s'),
                'payment_ntb' => $request->input('payment_ntb'),
                'va_expired_at' => null,
                'updated_at' => now(),
            ]);

            // Update user
            $user->update([
                'va_status' => 'PAID',
                'paid_at' => $paymentDate,
                'payment_method' => $request->payment_method,
            ]);

            // Create payment log
            PaymentLog::create([
                'user_id' => $user->id,
                'master_user_id' => $masterUser->id,
                'virtual_account' => $masterUser->va_number,
                'trx_id' => $masterUser->trx_id,
                'trx_amount' => $masterUser->va_amount,
                'payment_amount' => $request->payment_amount,
                'payment_ntb' => $request->input('payment_ntb'),
                'datetime_payment' => $paymentDate->format('Y-m-d H:i:s'),
                'status' => 'paid',
                'payment_data' => json_encode([
                    'forced_paid' => true,
                    'admin_id' => auth()->id(),
                    'payment_method' => $request->payment_method,
                ]),
            ]);

            // Send WhatsApp notification if requested
            if ($request->boolean('send_notification', true)) {
                $this->bniService->sendForcePaidNotification(
                    $user, 
                    $request->payment_amount, 
                    $request->payment_method, 
                    $paymentDate
                );
            }

            DB::commit();

            Log::info('Force paid completed', [
                'user_id' => $user->id,
                'admin_id' => auth()->id(),
                'amount' => $request->payment_amount,
            ]);

            return response()->json([
                'success' => true,
                'message' => "User {$user->name} berhasil di-set sebagai PAID",
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'va_status' => 'paid',
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Force paid failed', [
                'error' => $e->getMessage(),
                'user_id' => $request->user_id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Sync payment manually
     */
    public function syncPayment(Request $request)
    {
        $request->validate([
            'virtual_account' => 'required_without:trx_id|string',
            'trx_id' => 'required_without:virtual_account|string',
            'payment_amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
        ]);

        try {
            // Find master user
            $masterUser = MasterUser::when($request->virtual_account, function($q) use ($request) {
                return $q->where('va_number', $request->virtual_account);
            })
            ->when($request->trx_id, function($q) use ($request) {
                return $q->orWhere('trx_id', $request->trx_id);
            })
            ->first();

            if (!$masterUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Virtual account atau TRX ID tidak ditemukan',
                ], 404);
            }

            // Process payment using BNI service
            $paymentData = [
                'virtual_account' => $masterUser->va_number,
                'trx_id' => $masterUser->trx_id,
                'trx_amount' => $masterUser->va_amount,
                'payment_amount' => $request->payment_amount,
                'datetime_payment' => $request->payment_date,
                'payment_ntb' => $request->input('payment_ntb'),
                'cumulative_payment_amount' => $request->payment_amount,
            ];

            // Create a fake callback log for manual sync
            $callbackLog = BNICallbackLog::create([
                'client_id' => env('BNI_CLIENT_ID'),
                'virtual_account' => $masterUser->va_number,
                'trx_id' => $masterUser->trx_id,
                'trx_amount' => $masterUser->va_amount,
                'payment_amount' => $request->payment_amount,
                'datetime_payment' => $request->payment_date,
                'status' => 'manual_sync',
                'raw_data' => json_encode(['manual_sync' => true]),
                'decrypted_data' => json_encode($paymentData),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Process the payment
            $result = $this->bniService->processManualPayment($masterUser, $paymentData);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Payment berhasil di-sync',
                    'data' => $result,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal sync payment: ' . ($result['error'] ?? 'Unknown error'),
                ], 400);
            }

        } catch (\Exception $e) {
            Log::error('Sync payment failed', [
                'error' => $e->getMessage(),
                'data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal sync payment: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Test WhatsApp notification
     */
    public function testWhatsApp(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'type' => 'required|in:paid,partial,duplicate',
            'amount' => 'required|numeric|min:0',
        ]);

        try {
            $user = User::findOrFail($request->user_id);
            
            // Use the existing test method from BNI service
            $result = $this->bniService->testWhatsAppNotification(
                $user->id, 
                $request->type, 
                $request->amount
            );

            // Send to admin if requested
            if ($request->boolean('send_to_admin', false)) {
                $this->bniService->sendAdminNotification($user, null, $request->amount);
            }

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'] ?? 'Test notification sent',
                'data' => $result['data'] ?? null,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim test notification: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get callback details
     */
    public function getCallbackDetails(Request $request)
    {
        $request->validate(['id' => 'required|exists:bni_callback_logs,id']);
        
        $log = BNICallbackLog::findOrFail($request->id);
        
        return response()->json([
            'success' => true,
            'data' => [
                'raw_data' => json_decode($log->raw_data, true),
                'decrypted_data' => json_decode($log->decrypted_data, true),
                'status' => $log->status,
                'created_at' => $log->created_at,
                'user' => $log->user ? [
                    'id' => $log->user->id,
                    'name' => $log->user->name,
                    'email' => $log->user->email,
                ] : null,
            ],
        ]);
    }

    /**
     * Get user payment details
     */
    public function getUserPayments(Request $request)
    {
        $request->validate(['user_id' => 'required|exists:users,id']);
        
        $user = User::with(['masterUser', 'paymentLogs'])->findOrFail($request->user_id);
        
        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'va_status' => $user->va_status,
                ],
                'master_user' => $user->masterUser ? [
                    'va_number' => $user->masterUser->va_number,
                    'trx_id' => $user->masterUser->trx_id,
                    'va_amount' => $user->masterUser->va_amount,
                    'va_status' => $user->masterUser->va_status,
                    'payment_amount' => $user->masterUser->payment_amount,
                    'payment_method' => $user->masterUser->payment_method,
                    'paid_at' => $user->masterUser->paid_at,
                    'va_expired_at' => $user->masterUser->va_expired_at,
                ] : null,
                'payment_logs' => $user->paymentLogs->map(function($log) {
                    return [
                        'id' => $log->id,
                        'virtual_account' => $log->virtual_account,
                        'trx_id' => $log->trx_id,
                        'payment_amount' => $log->payment_amount,
                        'status' => $log->status,
                        'datetime_payment' => $log->datetime_payment,
                        'created_at' => $log->created_at,
                    ];
                }),
            ],
        ]);
    }

    /**
     * Delete user (admin only)
     */
    public function deleteUser(Request $request)
    {
        if (!auth()->user()->hasRole('super_admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $request->validate(['user_id' => 'required|exists:users,id']);
        
        DB::beginTransaction();
        try {
            $user = User::findOrFail($request->user_id);
            $userName = $user->name;
            
            // Delete related records
            if ($user->masterUser) {
                $user->masterUser->delete();
            }
            
            PaymentLog::where('user_id', $user->id)->delete();
            BNICallbackLog::where('user_id', $user->id)->delete();
            
            // Delete user
            $user->delete();
            
            DB::commit();
            
            Log::warning('User deleted by admin', [
                'user_id' => $request->user_id,
                'admin_id' => auth()->id(),
            ]);
            
            return response()->json([
                'success' => true,
                'message' => "User {$userName} berhasil dihapus",
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus user: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send WhatsApp to specific user
     */
    public function sendWhatsApp(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'type' => 'sometimes|in:paid,partial,duplicate',
        ]);

        try {
            $user = User::findOrFail($request->user_id);
            
            // Send test notification
            $result = $this->bniService->testWhatsAppNotification(
                $user->id, 
                $request->input('type', 'paid')
            );

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'] ?? 'WhatsApp sent',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim WhatsApp: ' . $e->getMessage(),
            ], 500);
        }
    }
}