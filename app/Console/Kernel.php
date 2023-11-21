<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel {
    protected function schedule(Schedule $schedule): void
    {
         $schedule->command('app:check-order-hours')->everySecond();
         $schedule->command('app:check-order-day')->everySecond();
         $schedule->command('app:check-order-hours')->everyTenMinutes();
         $schedule->command('app:modified-day-notify')->everyTenMinutes();
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
