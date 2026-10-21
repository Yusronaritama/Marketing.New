<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
protected function schedule(Schedule $schedule)
{
    
    $schedule->command('pmb:reset-expired')
                 ->dailyAt('03:00')
                 ->withoutOverlapping();
    
    $schedule->command('billing:expire')
                 ->daily() 
                 ->withoutOverlapping();
                 
    $schedule->command('quota:sync')
             ->everyMinute()
             ->withoutOverlapping(); 
             
    $schedule->command('quota:recalculate')
                 ->hourly()
                 ->withoutOverlapping();
                 
    $schedule->command('pmb:sync-moodle-lulus')
                ->withoutOverlapping()
                ->hourly();
                
    $schedule->command('pmb:sync-slik --limit=50 --delay=500')
             ->cron('*/20 * * * *')
             ->withoutOverlapping();
}

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
