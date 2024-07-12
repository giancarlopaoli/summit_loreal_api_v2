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
        $schedule->job(new Jobs\DesactivatingOperationAnalysts)
            ->everyMinute();

        // Pending Operations Send WS Corfid
        $schedule->job(new Jobs\WsCorfidOperations)
            ->everyFifteenMinutes();

        // Pending Detractions
        $schedule->job(new Jobs\PendingDetractions)
            ->hourly()
            ->weekdays()
            ->between('8:50', '18:00');

        // Deactivating Special Exchange Rates
        $schedule->job(new Jobs\DeactivatingSpecialExchangeRates)
            ->everyMinute();

        if(env('APP_ENV') == 'production'){
            // Exchange Rates Datatec Alarm
            $schedule->job(new Jobs\ExchangeRateAlert)
                ->everyTenMinutes()
                ->weekdays()
                ->between('8:50', '13:40');
        }

        // Expiring Negotiated Operations
        $schedule->job(new Jobs\ExpireNegotiatedOperation)
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
