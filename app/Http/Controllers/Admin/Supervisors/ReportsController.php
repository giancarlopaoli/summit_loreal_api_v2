<?php

namespace App\Http\Controllers\Admin\Supervisors;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Client;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    //
    public function new_clients(Request $request) {
        $month = (isset($request->month)) ? $request->month : Carbon::now()->month;
        $year = (isset($request->year)) ? $request->year : Carbon::now()->year;
        $executive = (isset($request->executive_id)) ? " executive_id = " . $request->executive_id : '1';

        $new_clients = Client::select('id','executive_id','registered_at','customer_type')
            ->whereRaw($executive)
            ->selectRaw("if(customer_type='PN', CONCAT(name, ' ', last_name, ' ', mothers_name), name) as client_name")
            ->selectRaw("(select sum(ov.amount) from operations_view ov where ov.client_id = clients.id) as total_volume")
            ->selectRaw("(select count(ov.amount) from operations_view ov where ov.client_id = clients.id) as total_operations")
            ->selectRaw("(select ov.operation_date from operations_view ov where ov.client_id = clients.id order by ov.operation_date desc limit 1) as last_operation")
            ->whereIn('client_status_id', [1,2,3])
            ->whereRaw("(month(registered_at) = $month and year(registered_at) = $year)")
            ->with("executive:id,type","executive.user:id,name,last_name")
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'new_clients' => $new_clients
            ]
        ]);
    }

    public function monthly_sales(Request $request) {
        $executive = (isset($request->executive_id)) ? " clients.executive_id = " . $request->executive_id : '1';

        $values = DB::table('operations_view')
            ->join("clients","clients.id","=","operations_view.client_id")
            ->select("client_id","client_name")
            ->selectRaw("MONTH(operation_date) as month,YEAR(operation_date) as year")
            ->selectRaw("round(sum(if(operations_view.type = 'Interbancaria', if(currency_id = 1, round(amount/exchange_rate,2),amount), amount)),2) as total_amount")
            
            ->selectRaw("round(sum(if(operations_view.type = 'Interbancaria', if(currency_id = 2, round(comission_amount*exchange_rate,2),comission_amount), comission_amount)),2) as total_comission")
            ->selectRaw("round(sum(comission_amount),2) as total_comission")


            ->selectRaw("((year(operation_date)-2000)*12 + MONTH(operation_date)) - ((year(now())-2000)*12 + MONTH(now())-12) as periodo")
            ->whereRaw($executive)
            ->whereRaw("clients.client_status_id in (1,2,3)")
            ->whereRaw('((year(operation_date)-2000)*12 + MONTH(operation_date)) > ((year(now())-2000)*12 + MONTH(now())-12)')
            ->groupByRaw("client_name, client_id,MONTH(operation_date),YEAR(operation_date)")
            ->get();

        $report = Client::select('id')
            ->selectRaw("if(customer_type='PN', concat(name,' ',last_name,' ', mothers_name), name) as client_name")
            ->selectRaw('0 as amount_month_1,0 as amount_month_2,0 as amount_month_3,0 as amount_month_4,0 as amount_month_5,0 as amount_month_6,0 as amount_month_7,0 as amount_month_8,0 as amount_month_9,0 as amount_month_10,0 as amount_month_11,0 as amount_month_12,0 as comission_month_1,0 as comission_month_2,0 as comission_month_3,0 as comission_month_4,0 as comission_month_5,0 as comission_month_6,0 as comission_month_7,0 as comission_month_8,0 as comission_month_9,0 as comission_month_10,0 as comission_month_11,0 as comission_month_12')
            ->whereIn('client_status_id', [1,2,3])
            ->whereRaw($executive)
            ->get();

        foreach ($values as $key => $value) {
            $report->where('id', $value->client_id)->first()->{'amount_month_'."$value->periodo"} = $value->total_amount;
            $report->where('id', $value->client_id)->first()->{'comission_month_'."$value->periodo"} = $value->total_comission;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'report' => $report
            ]
        ]);
    }
    
}
