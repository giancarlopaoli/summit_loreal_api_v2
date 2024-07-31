<?php

namespace App\Http\Controllers\Admin\Supervisors;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Client;
use App\Models\Sector;

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


            ->selectRaw("((year(operation_date)-2000)*12 + MONTH(operation_date)) - ((year(now())-2000)*12 + MONTH(now())-13) as periodo")
            ->whereRaw($executive)
            ->whereRaw("clients.client_status_id in (1,2,3)")
            ->whereRaw('((year(operation_date)-2000)*12 + MONTH(operation_date)) > ((year(now())-2000)*12 + MONTH(now())-13)')
            ->groupByRaw("client_name, client_id,MONTH(operation_date),YEAR(operation_date)")
            ->get();

        $report = Client::select('id')
            ->selectRaw("if(customer_type='PN', concat(name,' ',last_name,' ', mothers_name), name) as client_name")
            ->selectRaw('0 as amount_month_1,0 as amount_month_2,0 as amount_month_3,0 as amount_month_4,0 as amount_month_5,0 as amount_month_6,0 as amount_month_7,0 as amount_month_8,0 as amount_month_9,0 as amount_month_10,0 as amount_month_11,0 as amount_month_12,0 as amount_month_13,0 as comission_month_1,0 as comission_month_2,0 as comission_month_3,0 as comission_month_4,0 as comission_month_5,0 as comission_month_6,0 as comission_month_7,0 as comission_month_8,0 as comission_month_9,0 as comission_month_10,0 as comission_month_11,0 as comission_month_12,0 as comission_month_13')
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

    // Sectores económicos por cluster
    public function sectors_clusters(Request $request) {

        $clusters = Sector::select('id','name','cluster')
            ->orderBy("cluster")
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'clusters' => $clusters
            ]
        ]);
    }

    // Reporte ventas por Sectores económicos
    public function sectors_sales(Request $request) {
        
        $clusters_yearly = DB::table('operations as ops')
            ->join('clients as cl','cl.id','=','ops.client_id')
            ->join('sectors as sc','sc.id','=','cl.sector_id')
            ->selectRaw("sum(amount) as amount, sum(comission_amount) as comission_amount, sc.cluster, year(ops.operation_date) as year")
            ->where('ops.operation_date','>','2022-01-01')
            ->where('cl.customer_type','PJ')
            ->where('cl.type','Cliente')
            ->whereIn('ops.operation_status_id', [6,7])
            ->groupByRaw("sc.cluster, year(ops.operation_date)")
            ->get();

        $clusters_monthly= DB::table('operations as ops')
            ->join('clients as cl','cl.id','=','ops.client_id')
            ->join('sectors as sc','sc.id','=','cl.sector_id')
            ->selectRaw("sum(amount) as amount, sum(comission_amount) as comission_amount, sc.cluster, year(ops.operation_date) as year, month(ops.operation_date) as month")
            ->where('ops.operation_date','>','2023-01-01')
            ->where('cl.customer_type','PJ')
            ->where('cl.type','Cliente')
            ->whereIn('ops.operation_status_id', [6,7])
            ->groupByRaw("sc.cluster, month(ops.operation_date), year(ops.operation_date)")
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'clusters_monthly' => $clusters_monthly,
                'clusters_yearly' => $clusters_yearly,
            ]
        ]);
    }

    // Reporte ventas por Sectores económicos
    public function sectors_clients(Request $request) {
        $val = Validator::make($request->all(), [
            'sector_id' => 'required|exists:sectors,id'
        ]);
        if($val->fails()) return response()->json($val->messages());

        $clients = Client::select('id','name','last_name','document_type_id','document_number','sector_id')
            ->where('customer_type', 'PJ')
            ->where('type','Cliente')
            ->where('sector_id', $request->sector_id)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'clients' => $clients
            ]
        ]);
    }
    
}
