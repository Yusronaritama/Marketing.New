<?php

// app/Console/Commands/CheckBniConnection.php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BNIApiService;

class CheckBniConnection extends Command
{
    protected $signature = 'bni:check';
    protected $description = 'Check BNI API connection and IP whitelist';

    public function handle()
    {
        $this->info('🔍 Checking BNI API Connection...');
        
        // Get server IP
        $externalIp = @file_get_contents('https://api.ipify.org') ?: 'Unknown';
        $this->line("Server External IP: <fg=cyan>{$externalIp}</>");
        
        $bniService = new BNIApiService();
        
        // Check credentials
        $this->line("\n📋 Checking BNI Credentials:");
        $this->table(
            ['Parameter', 'Status'],
            [
                ['Client ID', config('services.bni.client_id') ? '✅ Set' : '❌ Empty'],
                ['Secret Key', config('services.bni.secret_key') ? '✅ Set' : '❌ Empty'],
                ['API URL', config('services.bni.api_url') ?: '❌ Empty'],
            ]
        );
        
        // Test connection
        $this->line("\n🔗 Testing API Connection...");
        $isValid = $bniService->validateCredentials();
        
        if ($isValid) {
            $this->info('✅ BNI API Connection: SUCCESS');
            $this->line('   IP is whitelisted and credentials are valid.');
        } else {
            $this->error('❌ BNI API Connection: FAILED');
            $this->line("   Error: " . $bniService->getLastError());
            $this->line("\n⚠  Action Required:");
            $this->line("   1. Login to BNI E-Collection Dashboard");
            $this->line("   2. Go to Settings > IP Whitelist");
            $this->line("   3. Add this IP: <fg=cyan>{$externalIp}</>");
            $this->line("   4. Wait 5-10 minutes for propagation");
            $this->line("   5. Run this command again");
        }
        
        return $isValid ? 0 : 1;
    }
}