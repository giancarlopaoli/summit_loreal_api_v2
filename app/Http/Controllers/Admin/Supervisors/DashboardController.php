<?php

namespace App\Http\Controllers\Admin\Supervisors;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Client;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\SalesGoals;
use App\Models\Operation;
use App\Models\OperationStatus;
use Illuminate\Database\Eloquent\Builder;

class DashboardController extends Controller
{
    // Dashboard
    public function dashboard(Request $request) {

        $indicators = DB::table('monthly_operations_view')
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
            ->selectRaw("coalesce((select sum(ov.amount) from operations ov where ov.operation_status_id in (1,2,3,4,5) and year(ov.operation_date) = year and month(ov.operation_date) = month ),0) as volume_in_progress")
            ->selectRaw("coalesce((select sum(ov.comission_amount) from operations ov where ov.operation_status_id in (1,2,3,4,5) and year(ov.operation_date) = year and month(ov.operation_date) = month ),0) as comission_in_progress")

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
            ->get();

        /*$vendor_indicators = Operation::selectRaw("year(operation_date) as year,month(operation_date) as month")
            ->whereIn("type", ['Compra','Venta'])
            ->whereRaw("((year(operation_date)-2000)*12 + month(operation_date)) >= ((year(now()) - 2000 )*12 + month(now()) -6)")
            ->groupByRaw("year(operation_date), month(operation_date)")
            ->orderByRaw('year(operation_date) asc, month(operation_date)')
            ->limit(7)
            ->get();*/


        /*$daily_indicators = Operation::selectRaw("day(operation_date) as day")
            ->selectRaw("(select sum(amount) from operations_view as ov where month(ov.operation_date) = month(operations.operation_date) and year(ov.operation_date) = year(operations.operation_date) and day(ov.operation_date) = day(operations.operation_date) and ov.type in ('Compra','Venta')) as volume")
*/
            /*->selectRaw("(select sum(comission_amount) from operations_view as ov where month(ov.operation_date) = month(now()) and year(ov.operation_date) = year(now()) and day(ov.operation_date) = day(operations.operation_date) and ov.type in ('Compra','Venta')) as comissions")

            ->selectRaw("(select count(amount) from operations_view as ov where month(ov.operation_date) = month(now()) and year(ov.operation_date) = year(now()) and day(ov.operation_date) = day(operations.operation_date) and ov.type in ('Compra','Venta')) as num_operations")


            ->selectRaw("coalesce((select daily_goal from sales_goals sg where sg.month = month(now()) and sg.year = year(now())),0) as daily_goal")
            ->selectRaw("coalesce((select daily_goal from sales_goals sg where sg.month = month(now()) and sg.year = year(now())),0) * (@rownum:=coalesce(@rownum,0)+1) as daily_goal_accumulated")

            ->selectRaw("(select sum(amount) from operations_view as ov where month(ov.operation_date) = month(now()) and year(ov.operation_date) = year(now()) and day(ov.operation_date) <= day(operations.operation_date) and ov.type in ('Compra','Venta'))  as volume_accumulated")*/


            /*->whereIn("type", ['Compra','Venta'])
            ->whereRaw('month(operation_date) = month(now()) and year(operation_date) = year(now())')
            ->groupByRaw("day(operation_date)")
            ->orderByRaw('day(operation_date)')
            ->get();*/



        /*'ventas_diarias' => [
                    'periodo' => $grafico_ventas_diarias->pluck('dia'),
                    'meta_diaria' => $meta_diaria,
                    'meta' => $grafico_ventas_diarias->pluck('contador'),
                    'volumen' => $grafico_ventas_diarias->pluck('monto'),
                    'volumen_acumulado' => $grafico_ventas_diarias->pluck('acumulado'),
                    'volumen_pendiente' => $grafico_ventas_diarias->pluck('pendiente'),
                    'comision' => $grafico_ventas_diarias->pluck('comision'),
                    'comision_acumulado' => $grafico_ventas_diarias->pluck('comision_acumulado'),
                    'comision_pendiente' => $grafico_ventas_diarias->pluck('comision_pendiente'),
                    'nro_operaciones' => $grafico_ventas_diarias->pluck('nro_ops'),
                    'clientes_unicos' => $grafico_ventas_diarias->pluck('clientes_unicos')
                ],*/


        return response()->json([
            'success' => true,
            'data' => [ 
                'global_indicators' => [
                    $indicators
                ],
                'ghaphs' => [
                    'year' => $graphs->pluck('year'),
                    'volume' => $graphs->pluck('volume'),
                    'comissions' => $graphs->pluck('comissions'),
                    'num_operations' => $graphs->pluck('num_operations'),
                    'unique_clients' => $graphs->pluck('unique_clients'),
                ],
                'monthly_indicators' => [
                    'month' => $monthly_indicators->pluck('month'),
                    'year' => $monthly_indicators->pluck('year'),
                    'volume' => $monthly_indicators->pluck('volume'),
                    'volume_in_progress' => $monthly_indicators->pluck('volume_in_progress'),
                    'num_operations' => $monthly_indicators->pluck('num_operations'),
                    'comissions' => $monthly_indicators->pluck('comissions'),
                    'comission_in_progress' => $monthly_indicators->pluck('comission_in_progress'),
                    'rate_buying' => $monthly_indicators->pluck('rate_buying'),
                    'rate_selling' => $monthly_indicators->pluck('rate_selling'),
                    'sales_goal' => $monthly_indicators->pluck('sales_goal'),
                    'volume_pj' => $monthly_indicators->pluck('volume_pj'),
                    'volume_pn' => $monthly_indicators->pluck('volume_pn'),
                    'num_operations_pj' => $monthly_indicators->pluck('num_operations_pj'),
                    'num_operations_pn' => $monthly_indicators->pluck('num_operations_pn'),
                ],
                /*'daily_indicators' => [
                    $daily_indicators
                ]*/
            ]
        ]);
    }

    public function sales_progress(Request $request) {
        $month = (isset($request->month)) ? $request->month : Carbon::now()->month;
        $year = (isset($request->year)) ? $request->year : Carbon::now()->year;


        $goal_progress = DB::table('goals_achievement')
            ->select('operation_executive_id','operation_month', 'operation_year','progress','goal')
            ->selectRaw(" round(achievement,4) as achievement, if( ((operation_executive_id = 2801 or operation_executive_id = 2811) and $year = 2023),0.05, comission_achieved ) as comission_achieved")
            ->selectRaw("(select sum(round( ov.comission_amount*ov.executive_comission ,2))  from operations_view ov where ov.executive_id = goals_achievement.operation_executive_id and month(ov.operation_date) = goals_achievement.operation_month and year(ov.operation_date) = goals_achievement.operation_year) as comission_earned")
            ->selectRaw("(select concat(name,' ',last_name) from users where users.id = goals_achievement.operation_executive_id) as executive_name")
            ->where('operation_executive_id','!=',null)
            ->whereRaw(" operation_month = $month and operation_year = $year")
            ->get();

        /*$cumplimiento_meta_mensual = DB::connection('mysql')->table('goals_achievement')
            ->select('operation_executive_id','operation_month', 'operation_year','avance','goal')
            ->selectRaw(" round(cumplimiento,4) as cumplimiento, if( ((operation_executive_id = 2801 or operation_executive_id = 2811) and $year = 2023),0.05, comission_achieved ) as comission_achieved")
            ->selectRaw("(select sum(round( ov.comission*ov.executive_comission ,2))  from operations_view ov where ov.executive_id = goals_achievement.operation_executive_id and month(ov.creation_date) = goals_achievement.operation_month and year(ov.creation_date) = goals_achievement.operation_year) as comission_earned")
            ->whereRaw(" operation_month = $month and operation_year = $year")
            ->orderByRaw('operation_year asc, operation_month')
            ->get();

        $users = DB::table('Usuario')
            ->whereIn('UsuarioId', $cumplimiento_meta_mensual->pluck('operation_executive_id'))
            ->select('UsuarioId as executive_id', 'Nombres', 'Apellidos')
            ->get();*/

        return response()->json([
            'success' => true,
            'data' => [
                'cumplimiento_meta' => $goal_progress
            ]
        ]);
    }
}
