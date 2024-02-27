<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\SpecialExchangeRate;
use Carbon\Carbon;

class DeactivatingSpecialExchangeRates implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //

        SpecialExchangeRate::where('active', true)
            ->where('finished_at', '<=', Carbon::now())
            ->update([
                'active' => false,
                'updated_at' => Carbon::now()
            ]);
    }
}
