<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Operation;
use Carbon\Carbon;

class ExpireNegotiatedOperation implements ShouldQueue
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
        Operation::where('class', 'Programada')
            ->where('operation_status_id', 1)
            ->where('negotiated_expired_date', '<=', Carbon::now())
            ->update([
                'operation_status_id' => 10,
                'updated_at' => Carbon::now()
            ]);
    }
}
