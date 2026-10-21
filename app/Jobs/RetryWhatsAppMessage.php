<?php

// app/Jobs/RetryWhatsAppMessage.php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\WhatsAppService;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class RetryWhatsAppMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $userId;
    protected $type;
    protected $payload;

    public function __construct($userId, $type, $payload)
    {
        $this->userId = $userId;
        $this->type = $type;
        $this->payload = $payload;
    }

    public function handle()
    {
        try {
            $user = User::find($this->userId);
            
            if (!$user) {
                Log::warning('User tidak ditemukan untuk retry WhatsApp', [
                    'user_id' => $this->userId
                ]);
                return;
            }
            
            $whatsappService = new WhatsAppService();
            
            // Kirim pesan berdasarkan type
            $result = match($this->type) {
                'registration_info' => $whatsappService->sendRegistrationInfo(
                    $user,
                    $this->payload
                ),
                default => null
            };
            
            if ($result && $result['success']) {
                // Update log
                DB::table('whatsapp_logs')->insert([
                    'user_id' => $this->userId,
                    'type' => $this->type,
                    'message' => 'Retry berhasil',
                    'status' => 'sent',
                    'sent_at' => Carbon::now(),
                    'created_at' => Carbon::now()
                ]);
                
                // Hapus dari retry queue
                DB::table('whatsapp_retry_queue')
                    ->where('user_id', $this->userId)
                    ->where('type', $this->type)
                    ->delete();
            }
            
        } catch (\Exception $e) {
            Log::error('Gagal retry WhatsApp', [
                'user_id' => $this->userId,
                'error' => $e->getMessage()
            ]);
            
            // Increment attempts
            DB::table('whatsapp_retry_queue')
                ->where('user_id', $this->userId)
                ->where('type', $this->type)
                ->increment('attempts', 1, [
                    'next_attempt_at' => Carbon::now()->addMinutes(30), // Next retry in 30 minutes
                    'updated_at' => Carbon::now()
                ]);
        }
    }
}