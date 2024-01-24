<?php

namespace App\Http\Controllers\Admin\Operations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Executive;
use App\Models\Operation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ExecutivesController extends Controller
{
    //
    public function comissions(Request $request) {
        $val = Validator::make($request->all(), [
            'month' => 'nullable|numeric',
            'year' => 'nullable|numeric',
        ]);
        if($val->fails()) return response()->json($val->messages());

        $month = (isset($request->month)) ? $request->month : Carbon::now()->month;
        $year = (isset($request->year)) ? $request->year : Carbon::now()->year;

        $executives = Executive::select('id','type','comission','years')
            ->selectRaw("coalesce((select sum(round(ov.amount/if(ov.type='Interbancaria',if(ov.currency_id=1,exchange_rate,1),1),2)) from operations_view ov where (ov.executive_id = executives.id or ov.executive2_id = executives.id) and year(ov.operation_date) = $year and month(ov.operation_date) = $month),0) as volume")
            ->selectRaw("coalesce((select count(ov.amount) from operations_view ov where (ov.executive_id = executives.id or ov.executive2_id = executives.id) and year(ov.operation_date) = $year and month(ov.operation_date) = $month),0) as num_operations")
            ->selectRaw("coalesce((select sum(round(ov.comission_amount*if(ov.type='Interbancaria',if(ov.currency_id=2,exchange_rate,1),1),2)) from operations_view ov where (ov.executive_id = executives.id or ov.executive2_id = executives.id) and year(ov.operation_date) = $year and month(ov.operation_date) = $month),0) as billex_comission")
            ->selectRaw("coalesce((select sum(round(ov.comission_amount*if(ov.type='Interbancaria',if(ov.currency_id=2,exchange_rate,1),1)*if(ov.executive_id = executives.id,ov.executive_comission,0),2)) + sum(round(ov.comission_amount*if(ov.type='Interbancaria',if(ov.currency_id=2,exchange_rate,1),1)*if(ov.executive2_id = executives.id,ov.executive2_comission,0),2)) from operations_view ov where (ov.executive_id = executives.id or ov.executive2_id = executives.id) and year(ov.operation_date) = $year and month(ov.operation_date) = $month),0) as executive_comission")
            ->with('user:id,name,last_name,email')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'executives' => $executives
            ]
        ]);
    }

    public function comission_detail(Request $request, Executive $executive) {
        $val = Validator::make($request->all(), [
            'month' => 'nullable|numeric',
            'year' => 'nullable|numeric',
        ]);
        if($val->fails()) return response()->json($val->messages());

        $month = (isset($request->month)) ? $request->month : Carbon::now()->month;
        $year = (isset($request->year)) ? $request->year : Carbon::now()->year;
        $executive_id = $executive->id;

        $operations = DB::table('operations_view')
            ->select('id','operation_date','client_name','type','amount','comission_spread')
            ->selectRaw("(if(type='Interbancaria', round(comission_amount*(if(currency_id=2,exchange_rate,1)),2), comission_amount)) as comission_amount")
            ->selectRaw("if(currency_id = 1, 'S/','$') as currency_sign")
            ->selectRaw("round(if(executive_id = $executive_id, executive_comission, 0) + if(executive2_id = $executive_id, executive2_comission, 0))*100,2) as executive_comission_percentage")
            
            ->selectRaw("round(if(executive_id = $executive_id, executive_comission, 0)*(if(type='Interbancaria', round(comission_amount*(if(currency_id=2,exchange_rate,1)),2), comission_amount)),2) + round(if(executive2_id = $executive_id, executive2_comission, 0)*(if(type='Interbancaria', round(comission_amount*(if(currency_id=2,exchange_rate,1)),2), comission_amount)),2) as executive_comission")

            ->whereRaw("(executive_id = $executive_id or executive2_id = $executive_id)")
            ->whereRaw("year(operation_date) = $year and month(operation_date) = $month")
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'operations' => $operations
            ]
        ]);
    }
}
