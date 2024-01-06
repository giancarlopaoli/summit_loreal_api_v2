<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\OperationsAnalyst;
use App\Models\OperationsAnalystLog;
use App\Models\OperationHistory;
use Carbon\Carbon;

class DesactivatingOperationAnalysts implements ShouldQueue
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
        $analysts = OperationsAnalyst::where("status", "Activo")->get();

        foreach ($analysts as $key => $value) {

            if(Carbon::now()->format('H:i:00') == $value->end_time){
                $value->update([
                    "status" => "Inactivo",
                    "updated_at" => Carbon::now()
                ]);

                OperationsAnalystLog::create([
                    'operations_analyst_id' => $value->id,
                    'online' => false
                ]);
            }
        }
    }
}
