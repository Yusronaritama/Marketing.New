<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\MasterUser;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Log;
use Exception;

class TestController extends Controller
{
    public function testRegistrationWhatsApp(Request $request)
    {
        // LOG AWAL SANGAT DETAIL
        Log::channel('daily')->info('═══════════════════════════════════════════');
        Log::channel('daily')->info('🚀 START: WhatsApp Registration Test');
        Log::channel('daily')->info('📅 Time: ' . now()->toDateTimeString());
        Log::channel('daily')->info('📝 Request IP: ' . $request->ip());
        Log::channel('daily')->info('🔗 URL: ' . $request->fullUrl());
        Log::channel('daily')->info('═══════════════════════════════════════════');
        
        try {
            Log::info('🔧 [TEST CONTROLLER] Memulai test WhatsApp registration');
            
            // ============================================
            // 1. DAPATKAN USER UNTUK TEST
            // ============================================
            $userId = $request->input('user_id', 1);
            $testPhone = $request->input('phone');
            
            Log::info('🔍 Mencari user untuk test:', [
                'user_id_param' => $userId,
                'phone_param' => $testPhone
            ]);
            
            $user = null;
            $masterUser = null;
            
            if ($testPhone) {
                Log::info('📱 Test dengan nomor telepon langsung:', ['phone' => $testPhone]);
                
                // Format nomor telepon
                $cleanPhone = preg_replace('/\D/', '', $testPhone);
                $formattedPhone = '62' . substr($cleanPhone, -11);
                
                Log::info('📞 Format nomor telepon:', [
                    'original' => $testPhone,
                    'clean' => $cleanPhone,
                    'formatted' => $formattedPhone
                ]);
                
                // Cari user berdasarkan nomor telepon
                $user = User::where('phone', $formattedPhone)->first();
                
                if (!$user) {
                    Log::info('👤 User tidak ditemukan, membuat dummy user');
                    // Buat user dummy untuk test
                    $user = new User();
                    $user->id = 9999; // ID dummy
                    $user->name = 'User Test ' . date('His');
                    $user->email = 'test' . date('His') . '@test.com';
                    $user->phone = $formattedPhone;
                    $user->password = bcrypt('test123');
                    $user->role = 'mahasiswa_baru';
                    
                    Log::info('📝 Dummy user created:', [
                        'id' => $user->id,
                        'name' => $user->name,
                        'phone' => $user->phone
                    ]);
                }
            } else {
                Log::info('🔍 Mencari user dari database');
                // Ambil user dengan ID tertentu atau yang terakhir
                $user = User::find($userId);
                if (!$user) {
                    Log::info('⚠ User dengan ID tidak ditemukan, mengambil yang terakhir');
                    $user = User::latest()->first();
                }
            }
            
            if (!$user) {
                Log::error('❌ Tidak ada user di database');
                return response()->json([
                    'success' => false,
                    'error' => 'User tidak ditemukan',
                    'suggestion' => 'Buat user dulu atau gunakan parameter ?phone=08xxxx',
                    'available_users' => User::count()
                ], 404);
            }
            
            Log::info('✅ User ditemukan:', [
                'id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
                'email' => $user->email
            ]);
            
            // Dapatkan atau buat master user
            $masterUser = MasterUser::where('user_id', $user->id)->first();
            if (!$masterUser) {
                Log::info('📝 MasterUser tidak ditemukan, membuat dummy');
                $masterUser = new MasterUser();
                $masterUser->user_id = $user->id;
                $masterUser->trx_id = 'TEST' . date('YmdHis');
                $masterUser->va_number = '98841' . rand(10000, 99999);
                $masterUser->va_amount = 250000;
                $masterUser->va_status = 'pending';
                $masterUser->va_expired_at = now()->addDays(1);
                $masterUser->gelombang = 1;
                
                Log::info('📊 Dummy MasterUser created:', [
                    'trx_id' => $masterUser->trx_id,
                    'va_number' => $masterUser->va_number,
                    'amount' => $masterUser->va_amount
                ]);
            } else {
                Log::info('✅ MasterUser ditemukan:', [
                    'trx_id' => $masterUser->trx_id,
                    'va_number' => $masterUser->va_number,
                    'va_status' => $masterUser->va_status
                ]);
            }
            
            // ============================================
            // 2. INISIALISASI WHATSAPP SERVICE
            // ============================================
            Log::info('🔌 Menginisialisasi WhatsAppService...');
            $whatsappService = new WhatsAppService();
            
            Log::info('📡 WhatsApp Service initialized', [
                'server_url' => $whatsappService->serverUrl,
                'timeout' => $whatsappService->timeout,
                'constructor_called' => true
            ]);
            
            // ============================================
            // 3. TEST KONEKSI SERVER - DETAILED
            // ============================================
            Log::info('🔄 Testing WhatsApp server connection (detailed)...');
            
            // Test method sederhana dulu
            $simpleConnection = $whatsappService->testConnection();
            Log::info('🔗 Simple connection test:', ['success' => $simpleConnection]);
            
            // Test detail
            $connection = $whatsappService->testConnectionDetailed();
            
            Log::info('📊 Detailed connection results:', [
                'overall_success' => $connection['success'] ?? false,
                'health_endpoint' => $connection['results']['health']['success'] ?? false,
                'status_endpoint' => $connection['results']['status']['success'] ?? false,
                'send_endpoint' => $connection['results']['send_get']['success'] ?? false
            ]);
            
            if (!$connection['success']) {
                Log::error('❌ WhatsApp server connection failed', $connection);
                return response()->json([
                    'success' => false,
                    'message' => 'WhatsApp server tidak terhubung',
                    'connection_test' => $connection,
                    'user_info' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'phone' => $user->phone
                    ]
                ], 500);
            }
            
            // ============================================
            // 4. TEST FORMAT PHONE NUMBER
            // ============================================
            Log::info('📱 Testing phone number formatting...');
            try {
                $formattedPhone = $whatsappService->formatPhoneNumber($user->phone);
                Log::info('✅ Phone formatted successfully:', [
                    'original' => $user->phone,
                    'formatted' => $formattedPhone
                ]);
            } catch (Exception $e) {
                Log::error('❌ Phone format error:', ['error' => $e->getMessage()]);
                return response()->json([
                    'success' => false,
                    'error' => 'Format nomor error: ' . $e->getMessage()
                ], 400);
            }
            
            // ============================================
            // 5. TEST 1: PESAN SEDERHANA
            // ============================================
            Log::info('📤 TEST 1: Mengirim pesan sederhana...');
            $simpleMessage = "🔔 *TEST PENDATAFTARAN PMB* 🔔\n\n" .
                            "Halo {$user->name},\n" .
                            "Ini adalah test pengiriman WhatsApp.\n" .
                            "Waktu: " . date('d/m/Y H:i:s') . "\n\n" .
                            "Jika pesan ini diterima, sistem WhatsApp berfungsi.";
            
            Log::info('📝 Simple message prepared:', [
                'length' => strlen($simpleMessage),
                'preview' => substr($simpleMessage, 0, 100)
            ]);
            
            $simpleResult = $whatsappService->sendMessage($user->phone, $simpleMessage);
            
            Log::info('📨 Simple message result:', [
                'success' => $simpleResult['success'] ?? false,
                'error' => $simpleResult['error'] ?? 'No error',
                'message_id' => $simpleResult['data']['data']['messageId'] ?? 'N/A',
                'status_code' => $simpleResult['status_code'] ?? 'N/A'
            ]);
            
            // Tunggu 3 detik
            sleep(3);
            
            // ============================================
            // 6. TEST 2: USERNAME DAN PASSWORD
            // ============================================
            Log::info('🔑 TEST 2: Mengirim username & password...');
            
            // Generate password dari nomor HP (8 digit terakhir)
            $phoneDigits = preg_replace('/\D/', '', $user->phone);
            $passwordDigits = substr($phoneDigits, -8);
            $password = 'PMB' . $passwordDigits;
            
            Log::info('🔐 Password generated:', [
                'original_phone' => $user->phone,
                'phone_digits' => $phoneDigits,
                'last_8_digits' => $passwordDigits,
                'password' => $password
            ]);
            
            // Buat pesan username dan password
            $usernamePasswordMessage = "🔐 *INFORMASI LOGIN AKUN* 🔐\n\n" .
                                      "Halo *{$user->name}*,\n\n" .
                                      "Berikut adalah informasi login akun Anda:\n\n" .
                                      "📋 *DETAIL AKUN*\n" .
                                      "┌ Nama Lengkap: {$user->name}\n" .
                                      "├ Email: {$user->email}\n" .
                                      "├ Nomor HP: {$user->phone}\n" .
                                      "├ Username: `{$masterUser->trx_id}`\n" .
                                      "└ Password: `{$password}`\n\n" .
                                      "💳 *INFORMASI PEMBAYARAN*\n" .
                                      "┌ Virtual Account: `{$masterUser->va_number}`\n" .
                                      "├ Jumlah: Rp " . number_format($masterUser->va_amount, 0, ',', '.') . "\n" .
                                      "├ Status: " . strtoupper($masterUser->va_status) . "\n" .
                                      "└ Batas Waktu: " . ($masterUser->va_expired_at ? $masterUser->va_expired_at->format('d/m/Y H:i') : '24 jam') . "\n\n" .
                                      "⚠️ *PETUNJUK PENGGUNAAN*\n" .
                                      "1. Login di: " . url('/login') . "\n" .
                                      "2. Gunakan username & password di atas\n" .
                                      "3. Lakukan pembayaran sebelum batas waktu\n" .
                                      "4. Jangan bagikan password ke siapapun!\n\n" .
                                      "📞 *BANTUAN*\n" .
                                      "Jika ada kendala, hubungi admin kami.\n\n" .
                                      "Terima kasih,\n" .
                                      "Tim PMB " . config('app.name');
            
            Log::info('📝 Username/password message prepared:', [
                'length' => strlen($usernamePasswordMessage),
                'preview' => substr($usernamePasswordMessage, 0, 150)
            ]);
            
            $usernameResult = $whatsappService->sendMessage($user->phone, $usernamePasswordMessage);
            
            Log::info('📨 Username/password result:', [
                'success' => $usernameResult['success'] ?? false,
                'error' => $usernameResult['error'] ?? 'No error',
                'message_id' => $usernameResult['data']['data']['messageId'] ?? 'N/A',
                'username_sent' => $masterUser->trx_id,
                'password_sent' => $password
            ]);
            
            // Tunggu 3 detik
            sleep(3);
            
            // ============================================
            // 7. TEST 3: PESAN LENGKAP (sendCompleteLoginInfo)
            // ============================================
            Log::info('🎯 TEST 3: Mengirim pesan lengkap via sendCompleteLoginInfo...');
            
            $completeResult = $whatsappService->sendCompleteLoginInfo($user, $masterUser);
            
            Log::info('📨 Complete message result:', [
                'success' => $completeResult['success'] ?? false,
                'error' => $completeResult['error'] ?? 'No error',
                'message_id' => $completeResult['data']['data']['messageId'] ?? 'N/A',
                'method' => 'sendCompleteLoginInfo'
            ]);
            
            // ============================================
            // 8. BUAT RESPONSE JSON DETAILED
            // ============================================
            $responseData = [
                'success' => true,
                'timestamp' => now()->toDateTimeString(),
                'request_info' => [
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ],
                'server_info' => [
                    'url' => $whatsappService->serverUrl,
                    'connection_status' => $connection['success'] ? 'Connected' : 'Disconnected',
                    'whatsapp_ready' => $whatsappService->isReady(),
                    'health_check' => $simpleConnection
                ],
                'user_info' => [
                    'id' => $user->id ?? 'dummy',
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'formatted_phone' => $formattedPhone ?? $whatsappService->formatPhoneNumber($user->phone),
                    'password_generated' => $password,
                    'is_dummy_user' => !isset($user->id) || $user->id == 9999
                ],
                'account_info' => [
                    'username' => $masterUser->trx_id,
                    'va_number' => $masterUser->va_number,
                    'va_amount' => $masterUser->va_amount,
                    'formatted_amount' => 'Rp ' . number_format($masterUser->va_amount, 0, ',', '.'),
                    'va_status' => $masterUser->va_status,
                    'va_expired' => $masterUser->va_expired_at?->format('d/m/Y H:i'),
                    'gelombang' => $masterUser->gelombang,
                    'is_dummy_master' => !isset($masterUser->id)
                ],
                'test_results' => [
                    'connection_test' => [
                        'success' => $connection['success'],
                        'details' => $connection['results'] ?? []
                    ],
                    'simple_message_test' => [
                        'success' => $simpleResult['success'] ?? false,
                        'error' => $simpleResult['error'] ?? null,
                        'message_id' => $simpleResult['data']['data']['messageId'] ?? null,
                        'timestamp' => $simpleResult['data']['data']['timestamp'] ?? null,
                        'status_code' => $simpleResult['status_code'] ?? null
                    ],
                    'username_password_test' => [
                        'success' => $usernameResult['success'] ?? false,
                        'error' => $usernameResult['error'] ?? null,
                        'message_id' => $usernameResult['data']['data']['messageId'] ?? null,
                        'timestamp' => $usernameResult['data']['data']['timestamp'] ?? null,
                        'status_code' => $usernameResult['status_code'] ?? null,
                        'credentials_sent' => [
                            'username' => $masterUser->trx_id,
                            'password' => $password
                        ]
                    ],
                    'complete_message_test' => [
                        'success' => $completeResult['success'] ?? false,
                        'error' => $completeResult['error'] ?? null,
                        'message_id' => $completeResult['data']['data']['messageId'] ?? null,
                        'timestamp' => $completeResult['data']['data']['timestamp'] ?? null,
                        'status_code' => $completeResult['status_code'] ?? null
                    ]
                ],
                'summary' => [
                    'total_tests' => 3,
                    'successful_tests' => 
                        ($simpleResult['success'] ? 1 : 0) + 
                        ($usernameResult['success'] ? 1 : 0) + 
                        ($completeResult['success'] ? 1 : 0),
                    'failed_tests' => 
                        (!$simpleResult['success'] ? 1 : 0) + 
                        (!$usernameResult['success'] ? 1 : 0) + 
                        (!$completeResult['success'] ? 1 : 0),
                    'overall_status' => ($simpleResult['success'] && $usernameResult['success']) ? 'PASS' : 'FAIL',
                    'execution_time' => microtime(true) - LARAVEL_START
                ]
            ];
            
            Log::channel('daily')->info('═══════════════════════════════════════════');
            Log::channel('daily')->info('✅ END: WhatsApp Registration Test');
            Log::channel('daily')->info('📊 Summary: ' . $responseData['summary']['overall_status']);
            Log::channel('daily')->info('✅ Successful: ' . $responseData['summary']['successful_tests']);
            Log::channel('daily')->info('❌ Failed: ' . $responseData['summary']['failed_tests']);
            Log::channel('daily')->info('⏱️ Execution time: ' . round($responseData['summary']['execution_time'], 2) . 's');
            Log::channel('daily')->info('═══════════════════════════════════════════');
            
            return response()->json($responseData);
            
        } catch (Exception $e) {
            Log::channel('daily')->error('💥 CRITICAL ERROR in WhatsApp test:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'time' => now()->toDateTimeString()
            ]);
            
            Log::error('💥 Error in WhatsApp test:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null,
                'timestamp' => now()->toDateTimeString(),
                'request_info' => [
                    'url' => $request->fullUrl(),
                    'ip' => $request->ip()
                ]
            ], 500);
        }
    }
    
    /**
     * Test cepat hanya username & password
     */
    public function testUsernamePasswordOnly(Request $request)
    {
        Log::channel('daily')->info('🚀 [QUICK TEST] Username Password Test');
        
        try {
            $phone = $request->input('phone');
            $username = $request->input('username', 'TEST' . date('YmdHis'));
            
            if (!$phone) {
                return response()->json([
                    'success' => false,
                    'error' => 'Parameter phone diperlukan',
                    'example' => '/test/username-password?phone=081234567890&username=20250001'
                ], 400);
            }
            
            Log::info('📱 Quick test untuk nomor:', ['phone' => $phone]);
            
            // Generate password dari nomor HP
            $phoneDigits = preg_replace('/\D/', '', $phone);
            $passwordDigits = substr($phoneDigits, -8);
            $password = 'PMB' . $passwordDigits;
            
            Log::info('🔐 Password generated:', [
                'phone' => $phone,
                'password' => $password
            ]);
            
            $whatsappService = new WhatsAppService();
            
            // Buat pesan simple
            $message = "🔐 *INFORMASI LOGIN* 🔐\n\n" .
                      "Username: `{$username}`\n" .
                      "Password: `{$password}`\n\n" .
                      "Gunakan kredensial di atas untuk login.\n\n" .
                      "Test Time: " . date('d/m/Y H:i:s');
            
            Log::info('📝 Quick message prepared:', [
                'length' => strlen($message),
                'preview' => substr($message, 0, 100)
            ]);
            
            $result = $whatsappService->sendMessage($phone, $message);
            
            $response = [
                'success' => $result['success'] ?? false,
                'phone' => $phone,
                'formatted_phone' => $whatsappService->formatPhoneNumber($phone),
                'credentials' => [
                    'username' => $username,
                    'password' => $password
                ],
                'result' => $result,
                'timestamp' => now()->toDateTimeString()
            ];
            
            Log::channel('daily')->info('📨 Quick test result:', $response);
            
            return response()->json($response);
            
        } catch (Exception $e) {
            Log::channel('daily')->error('💥 Quick test error:', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}