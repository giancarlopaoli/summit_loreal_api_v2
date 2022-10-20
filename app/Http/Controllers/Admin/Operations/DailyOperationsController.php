<?php

namespace App\Http\Controllers\Admin\Operations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Operation;

class DailyOperationsController extends Controller
{
    public function daily_operations(Request $request) {

        $date = isset($request->date) ? $request->date : Carbon::now()->format('Y-m-d');
        $month = Carbon::now()->month;
        $year = Carbon::now()->year;
        
        $indicators = Operation::selectRaw("date(operation_date) as date, sum(amount) as total_amount, count(id) as num_operations")
            ->selectRaw("(select sum(op1.amount) from operations op1 where month(op1.operation_date) = $month and year(op1.operation_date) = $year and op1.operation_status_id in (6,7)) as monthly_amount")
            ->selectRaw("(select count(op1.amount) from operations op1 where month(op1.operation_date) = $month and year(op1.operation_date) = $year and op1.operation_status_id in (6,7)) as monthly_operations")
            ->whereRaw("date(operation_date) = '$date' and operation_status_id in (6,7) ")
            //->groupByRaw('amount')
            ->get();

        $graphs = null;

        


        return response()->json([
            'success' => true,
            'data' => [
                'indicators' => $indicators,
                'graphs' => $graphs,
                /*'graphs' => $graphs,
                'graphs' => $graphs,*/
                'now' => $date
            ]
        ]);

    }
}
