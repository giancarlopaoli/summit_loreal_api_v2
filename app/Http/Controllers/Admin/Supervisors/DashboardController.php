<?php

namespace App\Http\Controllers\Admin\Supervisors;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Client;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Operation;
use App\Models\OperationStatus;
use Illuminate\Database\Eloquent\Builder;

class DashboardController extends Controller
{
    // Dashboard
    public function dashboard(Request $request) {

        /*$indicators = DB::table('monthly_operations_view')
            ->selectRaw("sum(amount) as total_volume, sum(operations_number) as num_operations, round(sum(comission_amount),2) as total_comissions")
            ->selectRaw("(select count(distinct client_id) from operations_view where type in ('Compra','Venta')) as unique_clients")
            ->whereIn("type", ['Compra','Venta'])
            ->first();

        $graphs = DB::table('monthly_operations_view')
            ->selectRaw("year, sum(amount) as volume, sum(operations_number) as num_operations, round(sum(comission_amount),2) as comissions")
            ->selectRaw("(select count(distinct client_id) from operations_view where type in ('Compra','Venta') and year(operation_date) = year) as unique_clients")
            ->whereIn("type", ['Compra','Venta'])
            ->groupByRaw("year")
            ->orderByRaw('year desc')
            ->limit(7)
            ->get();

        $monthly_indicators = DB::table('monthly_operations_view')
            ->selectRaw("year,month, sum(amount) as volume, sum(operations_number) as num_operations, round(sum(comission_amount),2) as comissions, round(100*sum(if(type='Compra',amount,0))/sum(amount),2) as rate_buying, round(100*sum(if(type='Venta',amount,0))/sum(amount),2) as rate_selling")
            ->selectRaw("coalesce((select sg.goal from sales_goals sg where sg.year = monthly_operations_view.year and sg.month =monthly_operations_view.month),0) as sales_goal")
            ->selectRaw("(select sum(ov.amount) from operations_view ov where ov.customer_type = 'PJ' and year(ov.operation_date) = year and month(ov.operation_date) = month ) as volume_pj")
            ->selectRaw("(select sum(ov.amount) from operations_view ov where ov.customer_type = 'PN' and year(ov.operation_date) = year and month(ov.operation_date) = month ) as volume_pn")
            ->selectRaw("(select count(ov.amount) from operations_view ov where ov.customer_type = 'PJ' and year(ov.operation_date) = year and month(ov.operation_date) = month ) as num_operations_pj")
            ->selectRaw("(select count(ov.amount) from operations_view ov where ov.customer_type = 'PN' and year(ov.operation_date) = year and month(ov.operation_date) = month ) as num_operations_pn")
            ->whereIn("type", ['Compra','Venta'])
            ->whereRaw("((year-2000)*12 + month) >= ((year(now()) - 2000 )*12 + month(now()) -6)")
            ->groupByRaw("year, month")
            ->orderByRaw('year asc, month')
            ->limit(7)
            ->get();*/

        $vendor_indicators = Operation::selectRaw("year(operation_date) as year,month(operation_date) as month")
            ->whereIn("type", ['Compra','Venta'])
            ->whereRaw("((year(operation_date)-2000)*12 + month(operation_date)) >= ((year(now()) - 2000 )*12 + month(now()) -6)")
            ->groupByRaw("year(operation_date), month(operation_date)")
            ->orderByRaw('year(operation_date) asc, month(operation_date)')
            ->limit(7)
            ->get();


        return response()->json([
            'success' => true,
            'data' => [ 
                /*'global_indicators' => [
                    $indicators
                ],
                'ghaphs' => [
                    'year' => $graphs->pluck('year'),
                    'volume' => $graphs->pluck('volume'),
                    'comissions' => $graphs->pluck('comissions'),
                    'num_operations' => $graphs->pluck('num_operations'),
                    'unique_clients' => $graphs->pluck('unique_clients'),
                ],*/
                'monthly_indicators' => [
                    $vendor_indicators
                ]
            ]
        ]);
    }
}
