<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WhatsAppService;

class CheckWhatsAppStatus extends Command
{
    protected $signature = 'whatsapp:status';
    protected $description = 'Check WhatsApp connection status';

    public function handle()
    {
        $whatsapp = app(WhatsAppService::class);
        
        $status = $whatsapp->checkStatus();
        $isReady = $whatsapp->isReady();
        
        $this->info('WhatsApp Status Check');
        $this->line(str_repeat('-', 50));
        
        if ($status['success']) {
            $this->info('✅ Server WhatsApp aktif');
            
            $statusData = $status['status']['data'] ?? [];
            
            $this->info("Connected: " . ($statusData['ready'] ? 'Yes' : 'No'));
            $this->info("Authenticated: " . ($statusData['authenticated'] ? 'Yes' : 'No'));
            
            if (isset($statusData['myNumber'])) {
                $this->info("Number: " . $statusData['myNumber']);
            }
            
            if (isset($statusData['info'])) {
                $this->info("Platform: " . ($statusData['info']['platform'] ?? 'Unknown'));
            }
            
        } else {
            $this->error('❌ Server WhatsApp tidak aktif');
            $this->error("Error: " . ($status['error'] ?? 'Unknown error'));
        }
        
        $this->line(str_repeat('-', 50));
        
        return $isReady ? 0 : 1;
    }
}