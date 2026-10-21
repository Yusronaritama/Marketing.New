<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Log;

class SendWhatsAppMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $maxExceptions = 2;
    public $timeout = 60;
    public $backoff = [30, 60, 120];

    protected $phoneNumber;
    protected $message;
    protected $options;

    public function __construct($phoneNumber, $message, $options = [])
    {
        $this->phoneNumber = $phoneNumber;
        $this->message = $message;
        $this->options = $options;
        
        // Set queue connection
        $this->onConnection(config('whatsapp.queue.connection', 'database'));
        $this->onQueue(config('whatsapp.queue.queue', 'whatsapp'));
    }

    public function handle()
    {
        try {
            Log::info('SendWhatsAppMessage Job - Processing', [
                'phone' => $this->phoneNumber,
                'message_length' => strlen($this->message),
                'attempt' => $this->attempts(),
                'job_id' => $this->job->getJobId()
            ]);
            
            $whatsappService = new WhatsAppService();
            
            $result = $whatsappService->sendMessage(
                $this->phoneNumber,
                $this->message,
                $this->options
            );
            
            if ($result['success']) {
                Log::info('SendWhatsAppMessage Job - Success', [
                    'phone' => $this->phoneNumber,
                    'job_id' => $this->job->getJobId()
                ]);
            } else {
                Log::error('SendWhatsAppMessage Job - Failed', [
                    'phone' => $this->phoneNumber,
                    'error' => $result['error'] ?? 'Unknown error',
                    'job_id' => $this->job->getJobId()
                ]);
                
                // Jika gagal, release job untuk retry
                if ($this->attempts() < $this->tries) {
                    $this->release($this->backoff[$this->attempts() - 1] ?? 30);
                }
            }
            
        } catch (\Exception $e) {
            Log::error('SendWhatsAppMessage Job - Exception', [
                'phone' => $this->phoneNumber,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'job_id' => $this->job->getJobId()
            ]);
            
            if ($this->attempts() < $this->tries) {
                $this->release($this->backoff[$this->attempts() - 1] ?? 30);
            }
        }
    }

    public function failed(\Throwable $exception)
    {
        Log::critical('SendWhatsAppMessage Job - Failed permanently', [
            'phone' => $this->phoneNumber,
            'error' => $exception->getMessage(),
            'job_id' => $this->job->getJobId()
        ]);
    }
}