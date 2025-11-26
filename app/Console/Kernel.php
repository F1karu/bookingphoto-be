<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Models\Booking;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule)
    {
        
        $schedule->call(function () {
            Booking::where('booking_status', 'IN_PROGRESS')
                ->whereDate('date', '<', now()->toDateString())
                ->update(['booking_status' => 'COMPLETED']);
        })->daily();

        $schedule->command('payment:expire')->everyMinute();
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
