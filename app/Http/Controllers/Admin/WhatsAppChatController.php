<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class WhatsAppChatController extends Controller
{
    private $nodeServerUrl;

    public function __construct()
    {
        $this->nodeServerUrl = config('app.node_server_url', 'http://localhost:3000');
    }

    /**
     * Dashboard untuk melihat dan mengelola chat
     */
    public function chatDashboard()
    {
        return view('admin.whatsapp.chat-dashboard');
    }

    /**
     * Mengirim pesan ke chat tertentu - DIPERBAIKI
     */
    public function sendMessage(Request $request): JsonResponse
    {
        try {
            Log::info('📤 Attempting to send message', $request->all());

            $validated = $request->validate([
                'chat_id' => 'required|string',
                'message' => 'required|string',
                'type' => 'nullable|string|in:text,image,document,audio,video'
            ]);

            Log::info('✅ Validation passed', $validated);

            // PERBAIKAN: Gunakan field yang sesuai dengan Node.js API
            $url = $this->nodeServerUrl . '/api/whatsapp/send-message';
            Log::info('🔗 Sending to URL: ' . $url);

            // PERBAIKAN: Kirim data dengan format yang diharapkan Node.js
            $payload = [
                'number' => $validated['chat_id'], // Node.js expect 'number' bukan 'chat_id'
                'message' => $validated['message'],
                'type' => $validated['type'] ?? 'text'
            ];

            Log::info('📦 Payload to Node.js:', $payload);

            $response = Http::timeout(15)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ])
                ->post($url, $payload);

            Log::info('📨 HTTP Response Status: ' . $response->status());
            Log::info('📨 HTTP Response Body: ' . $response->body());

            if ($response->successful()) {
                $result = $response->json();
                Log::info('✅ Message sent successfully', $result);
                
                // Simpan pesan outgoing ke local storage
                $this->saveOutgoingMessage($validated['chat_id'], $validated['message'], $result['data']['messageId'] ?? null);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Pesan berhasil dikirim',
                    'data' => $result
                ]);
            }

            // Jika response tidak successful, log error details
            $errorDetails = [
                'status' => $response->status(),
                'body' => $response->body(),
                'headers' => $response->headers()
            ];
            
            Log::error('❌ HTTP Request failed', $errorDetails);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim pesan ke server WhatsApp: ' . $response->body(),
                'error' => $response->body(),
                'status_code' => $response->status()
            ], 500);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('❌ Validation error sending message', $e->errors());
            
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('❌ Error sending message: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error sending message: ' . $e->getMessage()
            ], 500);
        }
    }

        public function testConnection(): JsonResponse
    {
        try {
            Log::info('🔗 Testing connection to Node.js server: ' . $this->nodeServerUrl);

            $response = Http::timeout(5)->get($this->nodeServerUrl . '/api/health');
            
            if ($response->successful()) {
                $health = $response->json();
                Log::info('✅ Node.js server is healthy', $health);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Connected to Node.js server',
                    'data' => $health
                ]);
            }

            Log::error('❌ Node.js server health check failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Node.js server is not responding properly. Status: ' . $response->status(),
                'status' => $response->status()
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Connection test failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Cannot connect to Node.js server: ' . $e->getMessage()
            ]);
        }
    }
    public function getChats(Request $request): JsonResponse
    {
        try {
            Log::info('🔄 Fetching chats from Node.js server');

            $url = $this->nodeServerUrl . '/api/whatsapp/chats';
            Log::info('🔗 Request URL: ' . $url);

            $response = Http::timeout(10)
                ->withHeaders(['Accept' => 'application/json'])
                ->get($url);
            
            Log::info('📨 Chats response status: ' . $response->status());

            if ($response->successful()) {
                $chatsData = $response->json();
                Log::info('✅ Chats fetched successfully', [
                    'count' => count($chatsData['data']['chats'] ?? []),
                    'data_structure' => array_keys($chatsData)
                ]);
                
                return response()->json([
                    'success' => true,
                    'data' => $chatsData['data'] ?? ['chats' => []],
                    'total' => count($chatsData['data']['chats'] ?? [])
                ]);
            }

            // Fallback ke data local jika server tidak merespon
            Log::warning('⚠️ Using local chat data fallback. Status: ' . $response->status());
            $localChats = $this->getLocalChats();
            
            return response()->json([
                'success' => true,
                'data' => $localChats,
                'total' => count($localChats['chats'] ?? []),
                'source' => 'local_fallback',
                'message' => 'Using local chat data. Node.js server returned: ' . $response->status()
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Error getting chats: ' . $e->getMessage());
            
            $localChats = $this->getLocalChats();
            
            return response()->json([
                'success' => false,
                'data' => $localChats,
                'message' => 'Error fetching chats: ' . $e->getMessage(),
                'source' => 'error_fallback'
            ]);
        }
    }

      public function getChatHistory(Request $request, string $chatId): JsonResponse
    {
        try {
            $limit = $request->get('limit', 50);
            
            Log::info("🔄 Fetching chat history for {$chatId}, limit: {$limit}");

            $url = $this->nodeServerUrl . '/api/whatsapp/chats/' . urlencode($chatId) . '/messages';
            Log::info("🔗 Request URL: {$url}");

            $response = Http::timeout(10)
                ->withHeaders(['Accept' => 'application/json'])
                ->get($url, [
                    'limit' => $limit
                ]);
            
            Log::info('📨 Chat history response status: ' . $response->status());

            if ($response->successful()) {
                $messagesData = $response->json();
                Log::info("✅ Chat history fetched successfully for {$chatId}", [
                    'count' => count($messagesData['data']['messages'] ?? [])
                ]);
                
                return response()->json([
                    'success' => true,
                    'data' => $messagesData['data'] ?? ['messages' => []],
                    'chat_id' => $chatId,
                    'total' => count($messagesData['data']['messages'] ?? [])
                ]);
            }

            // Fallback ke data local
            Log::warning("⚠️ Using local chat history fallback for {$chatId}. Status: " . $response->status());
            $localMessages = $this->getLocalChatHistory($chatId, $limit);
            
            return response()->json([
                'success' => true,
                'data' => $localMessages,
                'chat_id' => $chatId,
                'source' => 'local_fallback',
                'message' => 'Using local message history. Node.js server returned: ' . $response->status()
            ]);

        } catch (\Exception $e) {
            Log::error("❌ Error getting chat history for {$chatId}: " . $e->getMessage());
            
            $localMessages = $this->getLocalChatHistory($chatId, $request->get('limit', 50));
            
            return response()->json([
                'success' => false,
                'data' => $localMessages,
                'chat_id' => $chatId,
                'message' => 'Error fetching chat history: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Mengirim media ke chat tertentu
     */
    public function sendMedia(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'chat_id' => 'required|string',
                'file' => 'required|file|mimes:jpg,jpeg,png,gif,pdf,doc,docx,txt,mp3,mp4,m4a|max:10240', // 10MB
                'caption' => 'nullable|string'
            ]);

            $file = $request->file('file');
            $caption = $validated['caption'] ?? '';

            // Upload file ke temporary storage
            $tempPath = $file->store('temp/whatsapp');
            $fullPath = storage_path('app/' . $tempPath);

            $response = Http::timeout(30)
                ->attach('file', fopen($fullPath, 'r'), $file->getClientOriginalName())
                ->post($this->nodeServerUrl . '/api/whatsapp/send-media', [
                    'chat_id' => $validated['chat_id'],
                    'caption' => $caption,
                    'mime_type' => $file->getMimeType(),
                    'filename' => $file->getClientOriginalName()
                ]);

            // Hapus temporary file
            Storage::delete($tempPath);

            if ($response->successful()) {
                $result = $response->json();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Media berhasil dikirim',
                    'data' => $result
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim media ke server'
            ], 500);

        } catch (\Exception $e) {
            Log::error('Error sending media: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error sending media: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mencari pesan dalam chat
     */
    public function searchMessages(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'chat_id' => 'nullable|string',
                'query' => 'required|string|min:2',
                'limit' => 'nullable|integer|min:1|max:100'
            ]);

            $response = Http::timeout(10)->get($this->nodeServerUrl . '/api/whatsapp/search-messages', [
                'chat_id' => $validated['chat_id'] ?? null,
                'query' => $validated['query'],
                'limit' => $validated['limit'] ?? 20
            ]);

            if ($response->successful()) {
                $results = $response->json();
                
                return response()->json([
                    'success' => true,
                    'data' => $results,
                    'query' => $validated['query'],
                    'total' => count($results['messages'] ?? [])
                ]);
            }

            // Fallback ke pencarian local
            $localResults = $this->searchLocalMessages($validated['query'], $validated['chat_id'] ?? null, $validated['limit'] ?? 20);
            
            return response()->json([
                'success' => true,
                'data' => $localResults,
                'query' => $validated['query'],
                'source' => 'local_fallback'
            ]);

        } catch (\Exception $e) {
            Log::error('Error searching messages: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error searching messages: ' . $e->getMessage(),
                'data' => ['messages' => []]
            ]);
        }
    }

    public function getChatStats(Request $request): JsonResponse
    {
        try {
            Log::info('📊 Fetching chat stats');

            $response = Http::timeout(10)
                ->withHeaders(['Accept' => 'application/json'])
                ->get($this->nodeServerUrl . '/api/whatsapp/chat-stats');
            
            Log::info('📨 Stats response status: ' . $response->status());

            if ($response->successful()) {
                $statsData = $response->json();
                
                return response()->json([
                    'success' => true,
                    'data' => $statsData['data'] ?? $this->getDefaultChatStats()
                ]);
            }

            // Fallback ke statistik local
            Log::warning('⚠️ Using local chat stats fallback');
            $localStats = $this->getLocalChatStats();
            
            return response()->json([
                'success' => true,
                'data' => $localStats,
                'source' => 'local_fallback'
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Error getting chat stats: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error getting chat stats: ' . $e->getMessage(),
                'data' => $this->getDefaultChatStats()
            ]);
        }
    }

    /**
     * Menandai chat sebagai sudah dibaca
     */
    public function markAsRead(Request $request, string $chatId): JsonResponse
    {
        try {
            $response = Http::timeout(10)->post($this->nodeServerUrl . '/api/whatsapp/chats/' . $chatId . '/read');
            
            if ($response->successful()) {
                $result = $response->json();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Chat ditandai sebagai sudah dibaca',
                    'data' => $result
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Gagal menandai chat sebagai sudah dibaca'
            ], 500);

        } catch (\Exception $e) {
            Log::error('Error marking chat as read: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error marking chat as read: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Archive chat
     */
    public function archiveChat(Request $request, string $chatId): JsonResponse
    {
        try {
            $response = Http::timeout(10)->post($this->nodeServerUrl . '/api/whatsapp/chats/' . $chatId . '/archive');
            
            if ($response->successful()) {
                $result = $response->json();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Chat berhasil diarsipkan',
                    'data' => $result
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengarsipkan chat'
            ], 500);

        } catch (\Exception $e) {
            Log::error('Error archiving chat: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error archiving chat: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Endpoint untuk menerima update pesan dari Node.js bot
     */
    public function handleMessageUpdate(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'event' => 'required|string',
                'chat_id' => 'required|string',
                'message_id' => 'required|string',
                'sender' => 'required|string',
                'message' => 'nullable|string',
                'type' => 'required|string',
                'timestamp' => 'required|string',
                'has_media' => 'boolean',
                'is_group' => 'boolean',
                'is_from_me' => 'boolean'
            ]);

            Log::info('WhatsApp message update received:', $validated);

            // Simpan pesan ke local storage
            $this->saveIncomingMessage($validated);

            // Broadcast event ke frontend jika diperlukan (WebSocket/Pusher)
            $this->broadcastMessageEvent($validated);

            return response()->json([
                'success' => true,
                'message' => 'Message update processed',
                'received_at' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            Log::error('Error handling message update: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error processing message update: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getLocalChats(): array
    {
        try {
            if (Storage::disk('local')->exists('whatsapp/chats.json')) {
                $content = Storage::disk('local')->get('whatsapp/chats.json');
                $data = json_decode($content, true) ?? ['chats' => []];
                Log::info('📁 Loaded local chats', ['count' => count($data['chats'])]);
                return $data;
            }
        } catch (\Exception $e) {
            Log::error('❌ Error getting local chats: ' . $e->getMessage());
        }
        
        Log::info('📁 No local chats found, returning empty');
        return ['chats' => []];
    }
    /**
     * Ambil history chat dari local storage
     */
    private function getLocalChatHistory(string $chatId, int $limit = 50, int $offset = 0): array
    {
        try {
            $filename = 'whatsapp/messages/' . $this->sanitizeFilename($chatId) . '.json';
            
            if (Storage::disk('local')->exists($filename)) {
                $content = Storage::disk('local')->get($filename);
                $messages = json_decode($content, true) ?? [];
                
                // Pagination
                $paginatedMessages = array_slice($messages, $offset, $limit);
                
                Log::info("📁 Loaded local chat history for {$chatId}", [
                    'total' => count($messages),
                    'returned' => count($paginatedMessages)
                ]);
                
                return [
                    'messages' => $paginatedMessages,
                    'total' => count($messages),
                    'has_more' => ($offset + $limit) < count($messages)
                ];
            }
        } catch (\Exception $e) {
            Log::error("❌ Error getting local chat history for {$chatId}: " . $e->getMessage());
        }
        
        Log::info("📁 No local chat history found for {$chatId}");
        return ['messages' => [], 'total' => 0, 'has_more' => false];
    }
    private function saveOutgoingMessage(string $chatId, string $message, ?string $messageId = null): void
    {
        try {
            $filename = 'whatsapp/messages/' . $this->sanitizeFilename($chatId) . '.json';
            $existingMessages = [];
            
            if (Storage::disk('local')->exists($filename)) {
                $content = Storage::disk('local')->get($filename);
                $existingMessages = json_decode($content, true) ?? [];
            }
            
            $newMessage = [
                'id' => $messageId ?? 'local_' . uniqid(),
                'chat_id' => $chatId,
                'sender' => 'bot',
                'message' => $message,
                'type' => 'text',
                'timestamp' => now()->toISOString(),
                'is_from_me' => true,
                'direction' => 'outgoing',
                'status' => 'sent'
            ];
            
            array_push($existingMessages, $newMessage);
            
            // Simpan maksimal 1000 pesan per chat
            if (count($existingMessages) > 1000) {
                $existingMessages = array_slice($existingMessages, -1000);
            }
            
            Storage::disk('local')->put($filename, json_encode($existingMessages, JSON_PRETTY_PRINT));
            
            Log::info('💾 Saved outgoing message to local storage', [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'total_messages' => count($existingMessages)
            ]);
            
        } catch (\Exception $e) {
            Log::error('❌ Error saving outgoing message: ' . $e->getMessage());
        }
    }


    /**
     * Simpan pesan incoming ke local storage
     */
    private function saveIncomingMessage(array $messageData): void
    {
        try {
            $chatId = $messageData['chat_id'];
            $filename = 'whatsapp/messages/' . $this->sanitizeFilename($chatId) . '.json';
            $existingMessages = [];
            
            if (Storage::disk('local')->exists($filename)) {
                $content = Storage::disk('local')->get($filename);
                $existingMessages = json_decode($content, true) ?? [];
            }
            
            $messageData['direction'] = 'incoming';
            $messageData['received_at'] = now()->toISOString();
            
            array_push($existingMessages, $messageData);
            
            // Simpan maksimal 1000 pesan per chat
            if (count($existingMessages) > 1000) {
                $existingMessages = array_slice($existingMessages, -1000);
            }
            
            Storage::disk('local')->put($filename, json_encode($existingMessages, JSON_PRETTY_PRINT));
            
            // Update chat list
            $this->updateChatList($chatId, $messageData);
            
        } catch (\Exception $e) {
            Log::error('Error saving incoming message: ' . $e->getMessage());
        }
    }

    /**
     * Update daftar chat
     */
    private function updateChatList(string $chatId, array $messageData): void
    {
        try {
            $chats = $this->getLocalChats();
            $chatExists = false;
            
            // Cek apakah chat sudah ada
            foreach ($chats['chats'] as &$chat) {
                if ($chat['id'] === $chatId) {
                    $chat['last_message'] = $messageData['message'] ?? '';
                    $chat['last_message_time'] = $messageData['timestamp'];
                    $chat['unread_count'] = ($chat['unread_count'] ?? 0) + 1;
                    $chatExists = true;
                    break;
                }
            }
            
            // Jika chat baru, tambahkan
            if (!$chatExists) {
                $chats['chats'][] = [
                    'id' => $chatId,
                    'name' => $this->extractChatName($chatId),
                    'last_message' => $messageData['message'] ?? '',
                    'last_message_time' => $messageData['timestamp'],
                    'unread_count' => 1,
                    'is_group' => $messageData['is_group'] ?? false,
                    'created_at' => now()->toISOString()
                ];
            }
            
            Storage::disk('local')->put('whatsapp/chats.json', json_encode($chats, JSON_PRETTY_PRINT));
            
        } catch (\Exception $e) {
            Log::error('Error updating chat list: ' . $e->getMessage());
        }
    }

    /**
     * Pencarian pesan di local storage
     */
    private function searchLocalMessages(string $query, ?string $chatId = null, int $limit = 20): array
    {
        $results = [];
        
        try {
            if ($chatId) {
                // Cari di chat tertentu
                $filename = 'whatsapp/messages/' . $this->sanitizeFilename($chatId) . '.json';
                if (Storage::disk('local')->exists($filename)) {
                    $content = Storage::disk('local')->get($filename);
                    $messages = json_decode($content, true) ?? [];
                    
                    foreach ($messages as $message) {
                        if (stripos($message['message'] ?? '', $query) !== false) {
                            $results[] = $message;
                            if (count($results) >= $limit) break;
                        }
                    }
                }
            } else {
                // Cari di semua chat
                $chats = $this->getLocalChats();
                
                foreach ($chats['chats'] as $chat) {
                    $filename = 'whatsapp/messages/' . $this->sanitizeFilename($chat['id']) . '.json';
                    if (Storage::disk('local')->exists($filename)) {
                        $content = Storage::disk('local')->get($filename);
                        $messages = json_decode($content, true) ?? [];
                        
                        foreach ($messages as $message) {
                            if (stripos($message['message'] ?? '', $query) !== false) {
                                $results[] = $message;
                                if (count($results) >= $limit) break;
                            }
                        }
                        
                        if (count($results) >= $limit) break;
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Error searching local messages: ' . $e->getMessage());
        }
        
        return ['messages' => $results, 'total' => count($results)];
    }

    /**
     * Ambil statistik chat dari local storage
     */
    private function getLocalChatStats(): array
    {
        try {
            $chats = $this->getLocalChats();
            $totalMessages = 0;
            $groupChats = 0;
            $privateChats = 0;
            $todayMessages = 0;
            
            foreach ($chats['chats'] as $chat) {
                if ($chat['is_group'] ?? false) {
                    $groupChats++;
                } else {
                    $privateChats++;
                }
                
                // Hitung pesan hari ini (sederhana)
                $filename = 'whatsapp/messages/' . $this->sanitizeFilename($chat['id']) . '.json';
                if (Storage::disk('local')->exists($filename)) {
                    $content = Storage::disk('local')->get($filename);
                    $messages = json_decode($content, true) ?? [];
                    $totalMessages += count($messages);
                    
                    // Hitung pesan hari ini
                    foreach ($messages as $message) {
                        if (isset($message['timestamp']) && Carbon::parse($message['timestamp'])->isToday()) {
                            $todayMessages++;
                        }
                    }
                }
            }
            
            return [
                'total_chats' => count($chats['chats']),
                'group_chats' => $groupChats,
                'private_chats' => $privateChats,
                'total_messages' => $totalMessages,
                'today_messages' => $todayMessages,
                'last_updated' => now()->toISOString()
            ];
            
        } catch (\Exception $e) {
            Log::error('Error getting local chat stats: ' . $e->getMessage());
            return $this->getDefaultChatStats();
        }
    }

    /**
     * Statistik default
     */
    private function getDefaultChatStats(): array
    {
        return [
            'total_chats' => 0,
            'group_chats' => 0,
            'private_chats' => 0,
            'total_messages' => 0,
            'today_messages' => 0,
            'last_updated' => now()->toISOString()
        ];
    }

    /**
     * Broadcast message event (untuk real-time updates)
     */
    private function broadcastMessageEvent(array $messageData): void
    {
        // Implementasi WebSocket/Pusher bisa ditambahkan di sini
        // Contoh: Pusher::trigger('whatsapp-channel', 'new-message', $messageData);
    }

    /**
     * Sanitize filename untuk storage
     */
    private function sanitizeFilename(string $filename): string
    {
        return preg_replace('/[^a-zA-Z0-9-_]/', '_', $filename);
    }

    /**
     * Extract chat name dari chat ID
     */
    private function extractChatName(string $chatId): string
    {
        // Format: 62812xxxxxxx@c.us atau 62812xxxxxxx-162534xxxx@g.us
        if (strpos($chatId, '-') !== false && strpos($chatId, '@g.us') !== false) {
            return 'Group Chat';
        }
        
        return 'Personal Chat';
    }
}