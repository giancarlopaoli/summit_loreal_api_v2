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

        $indicators = DB::table('operations_view')
            ->selectRaw("sum(amount) as total_volume, count(amount) as num_operations, round(sum(comission_amount),2) as total_comissions")
            ->selectRaw("count(distinct client_id) as unique_clients")
            ->whereIn("type", ['Compra','Venta'])
            ->first();

        $graphs = DB::table('operations_view')
            ->selectRaw("year(operation_date) as year, sum(amount) as volume, count(amount) as num_operations, round(sum(comission_amount),2) as comissions")
            ->selectRaw("count(distinct client_id) as unique_clients")
            ->whereIn("type", ['Compra','Venta'])
            ->groupByRaw("year(operation_date)")
            ->orderByRaw('year(operation_date) desc')
            ->limit(7)
            ->get();


        $monthly_indicators = Operation::join("clients","clients.id","=","operations.client_id")
            ->selectRaw("month(operation_date) as month,year(operation_date) as year")
            ->selectRaw("sum(amount) as volume, count(amount) as num_operations, round(sum(comission_amount),2) as comissions")
            ->selectRaw("round(100*sum(if(operations.type='Compra',amount,0))/sum(amount),2) as rate_buying")
            ->selectRaw("round(100*sum(if(operations.type='Venta',amount,0))/sum(amount),2) as rate_selling")

            ->selectRaw("coalesce((select sum(amount) from operations op where month(op.operation_date) = month(operations.operation_date) and year(op.operation_date) = year(operations.operation_date) and op.operation_status_id in (2,3,4,5) and op.type in ('Compra','Venta') and op.client_id not in (select id from clients where type = 'PL')),0) as volume_in_progress")

            ->selectRaw("coalesce((select sum(comission_amount) from operations op where month(op.operation_date) = month(operations.operation_date) and year(op.operation_date) = year(operations.operation_date) and op.operation_status_id in (2,3,4,5) and op.type in ('Compra','Venta') and op.client_id not in (select id from clients where type = 'PL')),0) as comission_in_progress")

            ->selectRaw("coalesce((select sg.goal from sales_goals sg where sg.year = year(operations.operation_date) and sg.month = month(operations.operation_date)),0) as sales_goal")

            ->selectRaw(" sum(if(clients.customer_type='PJ', operations.amount,0)) as volume_pj")
            ->selectRaw(" sum(if(clients.customer_type='PN', operations.amount,0)) as volume_pn")
            ->selectRaw(" sum(if(clients.customer_type='PJ', 1,0)) as num_operations_pj")
            ->selectRaw(" sum(if(clients.customer_type='PN', 1,0)) as num_operations_pn")

            ->selectRaw("(select sum(ov.amount) from operations ov where ov.client_id = 366 and year(ov.operation_date) = year(operations.operation_date) and month(ov.operation_date) = month(operations.operation_date) and ov.operation_status_id = 7 ) as volume_coril")
            ->selectRaw("(select sum(ov.amount) from operations ov where ov.client_id = 2815 and year(ov.operation_date) = year(operations.operation_date) and month(ov.operation_date) = month(operations.operation_date) and ov.operation_status_id = 7 ) as volume_mibanco")
            ->selectRaw("(select sum(ov.amount) from operations ov where ov.client_id = 3166 and year(ov.operation_date) = year(operations.operation_date) and month(ov.operation_date) = month(operations.operation_date) and ov.operation_status_id = 7 ) as volume_renta4")
            ->selectRaw("(select sum(ov.amount) from operations ov where ov.client_id = 4280 and year(ov.operation_date) = year(operations.operation_date) and month(ov.operation_date) = month(operations.operation_date) and ov.operation_status_id = 7 ) as volume_ripley")
            ->selectRaw("(select sum(ov.amount) from operations ov where ov.client_id = 4540 and year(ov.operation_date) = year(operations.operation_date) and month(ov.operation_date) = month(operations.operation_date) and ov.operation_status_id = 7 ) as volume_cajatru")

            ->whereIn("operations.type", ['Compra','Venta'])
            ->whereIn("operation_status_id", [6,7,8])
            //->whereNotIn("client_id", Client::where('type', 'PL')->get()->pluck('id') )
            ->where("clients.type", "Cliente")
            ->whereRaw("((year(operation_date)-2000)*12 + month(operation_date)) >= ((year(now()) - 2000 )*12 + month(now()) -6)")
            ->groupByRaw("month(operation_date), year(operation_date)")
            ->orderByRaw('year(operation_date) asc, month(operation_date)')
            ->limit(7)
            ->get();


        $daily_indicators = Operation::selectRaw("day(operation_date) as dia")
            ->selectRaw("coalesce((select daily_goal from sales_goals sg where sg.month = month(now()) and sg.year = year(now())),0) as daily_goal")
            ->selectRaw("( sum(amount)) as volume")
            
            ->selectRaw("coalesce((select sum(amount) from operations op where month(op.operation_date) = month(operations.operation_date) and year(op.operation_date) = year(operations.operation_date) and day(op.operation_date) <= day(operations.operation_date) and op.operation_status_id in (6,7,8) and op.type in ('Compra','Venta') and op.client_id not in (select id from clients where type = 'PL')),0) as accumulated_volume")

            ->selectRaw("coalesce((select sum(amount) from operations op where month(op.operation_date) = month(operations.operation_date) and year(op.operation_date) = year(operations.operation_date) and day(op.operation_date) <= day(operations.operation_date) and op.operation_status_id in (2,3,4,5) and op.type in ('Compra','Venta') and op.client_id not in (select id from clients where type = 'PL')),0) as volume_in_progress")

            ->selectRaw("( sum(comission_amount)) as comission_amount")
            ->selectRaw("coalesce((select sum(comission_amount) from operations op where month(op.operation_date) = month(operations.operation_date) and year(op.operation_date) = year(operations.operation_date) and day(op.operation_date) <= day(operations.operation_date) and op.operation_status_id in (6,7,8) and op.type in ('Compra','Venta') and op.client_id not in (select id from clients where type = 'PL')),0) as accumulated_comission")

            ->selectRaw("coalesce((select sum(comission_amount) from operations op where month(op.operation_date) = month(operations.operation_date) and year(op.operation_date) = year(operations.operation_date) and day(op.operation_date) <= day(operations.operation_date) and op.operation_status_id in (2,3,4,5) and op.type in ('Compra','Venta') and op.client_id not in (select id from clients where type = 'PL')),0) as comission_in_progress")
            ->selectRaw("( count(amount)) as num_operations")
            ->selectRaw("( count(distinct client_id)) as unique_clients")

            ->whereIn("operations.type", ['Compra','Venta'])
            ->whereIn("operation_status_id", [6,7,8])
            ->whereNotIn("client_id", Client::where('type', 'PL')->get()->pluck('id') )
            ->whereRaw('month(operation_date) = month(now()) and year(operation_date) = year(now())')
            ->groupByRaw("day(operation_date)")
            ->orderByRaw('day(operation_date)')
            ->get();



        $top_clients = Operation::join('clients', 'clients.id', '=', 'operations.client_id')
            ->whereIn('operation_status_id', [6,7,8])
            ->where('clients.type', 'Cliente')
            ->whereIn('operations.type', ['Compra','Venta'])
            ->where('customer_type', 'PJ')
            ->selectRaw('SUBSTRING(clients.name,1,20) as client_name,sum(comission_amount) as comissions,sum(amount) as volume, count(amount) as num_operations')
            ->whereRaw("(select op.operation_date from operations op where op.client_id = clients.id order by op.id desc limit 1) >= DATE_SUB(now(), INTERVAL 6 MONTH)")
            ->groupByRaw("clients.name")
            ->orderByRaw('sum(comission_amount) desc')
            ->havingRaw('count(amount) > 10 ')
            ->limit(10)
            ->get();


/*        return response()->json([
            'test' => true,
            'data' => $top_clients
        ]);*/

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
                'monthly_indicators' =>  [
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
                    'volume_coril' => $monthly_indicators->pluck('volume_coril'),
                    'volume_mibanco' => $monthly_indicators->pluck('volume_mibanco'),
                    'volume_renta4' => $monthly_indicators->pluck('volume_renta4'),
                    'volume_ripley' => $monthly_indicators->pluck('volume_ripley'),
                    'volume_cajatru' => $monthly_indicators->pluck('volume_cajatru'),
                ],
                'daily_indicators' => [
                    'period' => $daily_indicators->pluck('dia'),
                    'daily_goal' => $daily_indicators->pluck('daily_goal'),
                    'volume' => $daily_indicators->pluck('volume'),
                    'accumulated_volume' => $daily_indicators->pluck('accumulated_volume'),
                    'volume_in_progress' => $daily_indicators->pluck('volume_in_progress'),
                    'comission_amount' => $daily_indicators->pluck('comission_amount'),
                    'accumulated_comission' => $daily_indicators->pluck('accumulated_comission'),
                    'comission_in_progress' => $daily_indicators->pluck('comission_in_progress'),
                    'num_operations' => $daily_indicators->pluck('num_operations'),
                    'unique_clients' => $daily_indicators->pluck('unique_clients'),
                ],
                'top_cliente' => [
                    'client' => $top_clients->pluck('client_name'),
                    'volume' => $top_clients->pluck('volume'),
                    'comissions' => $top_clients->pluck('comissions'),
                    'num_operations' => $top_clients->pluck('num_operations')
                ],
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

    public function executive_summary(Request $request) {
        $year = (isset($request->year)) ? $request->year : Carbon::now()->year;


        $tabla = DB::table('operations_view as operations')
            ->selectRaw("month(operation_date) as mes, $year as year, count(*) as num_operations, sum(operations.amount) as volume, coalesce(round(sum(operations.amount)/count(*),0),0) as avg_ticket, sum(operations.comission_amount) as comision")

            ->selectRaw(" coalesce((select eg.goal from sales_goals eg where eg.month = mes and eg.year = $year limit 1),0) as goal ") 


            ->selectRaw(" round(100*coalesce(sum(operations.amount) / coalesce((select eg.goal from sales_goals eg where eg.month = mes and eg.year = $year limit 1),0),0),0) as goal_achieved ")    

            ->selectRaw(" round(100*coalesce(sum(operations.comission_amount)/sum(operations.amount*operations.exchange_rate),0),2) as spread")

            ->selectRaw(" if( coalesce((select sum(ov.amount) from operations_view ov where month(ov.operation_date) +1 = mes and year(ov.operation_date) = $year),0) = 0, 0, sum(operations.amount) - coalesce((select sum(ov.amount) from operations_view ov where month(ov.operation_date) +1 = mes and year(ov.operation_date) = $year),0)) as var_volume")


            ->selectRaw(" count(distinct client_id) as unique_clients")
            ->selectRaw(" coalesce((select count(cl.id) from clients cl where month(cl.registered_at) = mes and year(cl.registered_at) = year),0) as new_clients")

            ->selectRaw(" (select sum(ov.comission_amount) from operations_view ov where month(ov.operation_date) <= mes and year(ov.operation_date) = year) as accumulated_comissions")

            ->whereRaw("year(operation_date) = $year")
            ->whereIn("operations.type", ['Compra','Venta'])
            ->groupByRaw("month(operation_date)")
            ->orderByRaw('month(operation_date)')
            ->get();

        $total = DB::table('operations_view as operations')
            ->selectRaw("$year as year")
            ->selectRaw(" count(*) as num_operations")
            ->selectRaw(" sum(operations.amount) as volume")
            ->selectRaw(" coalesce(round(sum(operations.amount)/count(*),0),0) as avg_ticket")

            ->selectRaw(" coalesce((select sum(eg.goal) from sales_goals eg where eg.year = $year limit 1),0) as goal ") 

            ->selectRaw(" round(100*coalesce(sum(operations.amount) / coalesce((select sum(eg.goal) from sales_goals eg where eg.year = $year limit 1),0),0),0) as goal_achieved ")

            ->selectRaw(" sum(operations.comission_amount) as comision")
            ->selectRaw(" round(100*coalesce(sum(operations.comission_amount)/sum(operations.amount*operations.exchange_rate),0),2) as spread")

            ->selectRaw(" count(distinct client_id) as unique_clients")
            ->selectRaw(" coalesce((select count(cl.id) from clients cl where year(cl.registered_at) = year),0) as new_clients")

            ->whereRaw("year(operation_date) = $year")
            ->whereIn("operations.type", ['Compra','Venta'])
            ->groupByRaw("year(operation_date)")
            ->get();

        $positive_variation = DB::table('operations_view')
            ->selectRaw("client_name")

            ->selectRaw(" coalesce(sum(if( (year(operation_date)*12 + month(operation_date)) = (year(now())*12 + month(now()) - 1),amount,0))) as  current_amount")

            ->selectRaw(" coalesce(sum(if( (year(operation_date)*12 + month(operation_date)) = (year(now())*12 + month(now()) - 2),amount,0))) as  previous_amount")

            ->selectRaw(" coalesce(sum(if( (year(operation_date)*12 + month(operation_date)) = (year(now())*12 + month(now()) - 1),amount,0))) - coalesce(sum(if( (year(operation_date)*12 + month(operation_date)) = (year(now())*12 + month(now()) - 2),amount,0))) as difference")

            ->whereRaw("((year(operation_date)-2000)*12 + MONTH(operation_date)) >= ((year(CURRENT_TIMESTAMP)-2000)*12 + MONTH(CURRENT_TIMESTAMP)-2)")
            ->groupByRaw("client_name")
            ->orderByRaw("difference desc")
            ->havingRaw("difference > 0")
            ->limit(20)
            ->get();

        $negative_variation = DB::table('operations_view')
            ->selectRaw("client_name")

            ->selectRaw(" coalesce(sum(if( (year(operation_date)*12 + month(operation_date)) = (year(now())*12 + month(now()) - 1),amount,0))) as  current_amount")

            ->selectRaw(" coalesce(sum(if( (year(operation_date)*12 + month(operation_date)) = (year(now())*12 + month(now()) - 2),amount,0))) as  previous_amount")

            ->selectRaw(" coalesce(sum(if( (year(operation_date)*12 + month(operation_date)) = (year(now())*12 + month(now()) - 1),amount,0))) - coalesce(sum(if( (year(operation_date)*12 + month(operation_date)) = (year(now())*12 + month(now()) - 2),amount,0))) as difference")

            ->whereRaw("((year(operation_date)-2000)*12 + MONTH(operation_date)) >= ((year(CURRENT_TIMESTAMP)-2000)*12 + MONTH(CURRENT_TIMESTAMP)-2)")
            ->groupByRaw("client_name")
            ->orderByRaw("difference asc")
            ->havingRaw("difference < 0")
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'table' => $tabla,
                'total' => $total,
                'positive_variation' => $positive_variation,
                'negative_variation' => $negative_variation
            ]
        ]);
    }
}
