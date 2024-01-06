<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Jobs;
use App\Models\Configuration;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $configurations = new Configuration();

        // Deactivating vendor spreads at market close
        $schedule->job(new Jobs\SpreadCloseMarket)
            ->weekdays()->at($configurations->get_value('MARKETCLOSE'));

        // Deactivating vendor spreads at the end of the day
        $schedule->job(new Jobs\SpreadCloseMarket)
            ->weekdays()->at("18:30");

        // Activating Operation Analysts
        $schedule->job(new Jobs\ActivatingOperationAnalysts)
            ->everyMinute();

        // Deactivating Operation Analysts
        $schedule->job(new Jobs\DesactivatingOperationAnalysts
        )
            ->everyMinute();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
