<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\WhatsAppService;

class WhatsAppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('whatsapp', function ($app) {
            return new WhatsAppService();
        });
        
        $this->mergeConfigFrom(
            __DIR__.'/../../config/whatsapp.php', 'whatsapp'
        );
    }

    public function boot()
    {
        // Publish config
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/whatsapp.php' => config_path('whatsapp.php'),
            ], 'whatsapp-config');
            
            // Register commands
            $this->commands([
                \App\Console\Commands\CheckWhatsAppStatus::class,
            ]);
        }

        // Load routes
        $this->loadRoutesFrom(__DIR__.'/../../routes/whatsapp.php');
    }
}