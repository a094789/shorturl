<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // 每週執行一次短網址回收，回收90天前過期且點擊次數少於5次的短網址
        $schedule->command('shorturl:recycle --days=90 --min-clicks=5')
                 ->weekly()
                 ->sundays()
                 ->at('23:00')
                 ->appendOutputTo(storage_path('logs/shorturl-recycle.log'));
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