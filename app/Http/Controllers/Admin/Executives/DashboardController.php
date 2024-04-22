<?php

namespace App\Http\Controllers\Admin\Executives;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\ExecutiveGoal;
use App\Models\Lead;
use App\Models\Operation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    // Dashboard
    public function dashboard(Request $request) {

        $executive_id = (isset($request->executive_id)) ? $request->executive_id : auth()->id();
        $year = (isset($request->year)) ? $request->year : Carbon::now()->year;

        // Tablero de indicadores globales
        $tablero_cartera = DB::table('operations_view')
            ->selectRaw(" (select count(id) from clients where executive_id = $executive_id and client_status_id = 3) as total_clients")
            ->selectRaw("sum(amount) as total_amount")
            ->selectRaw("sum(comission_amount) as total_comissions")
            ->selectRaw("(select count( distinct op.client_id) from operations_view op where op.executive_id = $executive_id) as unique_clients")
            ->selectRaw(" coalesce((select count(cl.id) from clients cl where year(cl.registered_at) = year(now()) and cl.executive_id = $executive_id),0) as new_clients")

            ->where('executive_id', $executive_id)
            ->first();

        $grafico_globales = DB::table('operations_view')
            ->selectRaw("year(operation_date) as year")
            ->selectRaw("sum(if(executive_id =$executive_id, amount,0)) as volume")
            ->selectRaw("sum(if(executive_id =$executive_id, comission_amount,0)) as comission")

            ->selectRaw("sum(if(executive_id =$executive_id, 1,0)) as unique_clients")

            ->selectRaw(" coalesce((select count(distinct op.client_id) from operations_view op where op.executive_id = $executive_id and year(op.operation_date) = year),0) as unique_clients")

            ->selectRaw(" coalesce((select count(cl.id) from clients cl where year(cl.registered_at) = year and cl.executive_id = $executive_id),0) as new_clients")

            ->groupByRaw("year(operation_date)")
            ->orderByRaw('year(operation_date)')
            ->get();

        /*return response()->json([
            'success' => true,
            'data' => [
                $tablero_cartera,
                $grafico_globales
            ]
        ]);*/

        // Operaciones Mensuales
        $grafico_ventas = DB::table('operations_view')
            ->selectRaw("year(operation_date) as anio, month(operation_date) as mes, sum(amount) as monto, count(amount) as nro_ops, sum(comission_amount) as comision, count(distinct client_id) as clientes_unicos, sum(round(comission_amount*if(executive_id =$executive_id, executive_comission,0),2)) + sum(round(comission_amount*if(executive2_id =$executive_id, executive2_comission,0),2)) as comision_ejecutivo")
            ->selectRaw("sum(if(type = 'Compra', amount,0)) as volumen_compra")
            ->selectRaw("sum(if(type = 'Venta', amount,0)) as volumen_venta")
            ->selectRaw("coalesce((select eg.goal from executive_goals eg where eg.executive_id = $executive_id  and eg.month = mes and eg.year = anio), 0) as meta")
            ->whereRaw(" (executive_id = $executive_id or executive2_id = $executive_id) and ((year(operation_date)-2000)*12 + MONTH(operation_date)) >= ((year(CURRENT_TIMESTAMP)-2000)*12 + MONTH(CURRENT_TIMESTAMP)-6)")
            ->groupByRaw("year(operations_view.operation_date), month(operations_view.operation_date)")
            ->orderByRaw('year(operation_date) asc, month(operation_date)')
            ->limit(7)
            ->get();

        // Calculo meta diaria
        $meta_diaria = ExecutiveGoal::selectRaw("*, month(now()) as mes, year(now()) as anio")
            ->where('executive_id', $executive_id)
            ->whereRaw("month = month(now()) and year = year(now())")
            ->first();

        $meta_diaria = is_null($meta_diaria) ? 0 : $meta_diaria->daily_goal;

        // Gráfico avance ventas diarias
        $grafico_ventas_diarias = DB::table('operations_view')
            ->selectRaw("day(operation_date) as dia, count(amount) as nro_ops, count(distinct client_id) as clientes_unicos, sum(amount) as monto, sum(comission_amount) as comision, sum(round(comission_amount*if(executive_id =$executive_id, executive_comission,0),2)) + sum(round(comission_amount*if(executive2_id =$executive_id, executive2_comission,0),2)) as comision_ejecutivo, 1*$meta_diaria as meta_diaria")

            ->selectRaw("(select sum(amount) from operations_view op2 where (op2.executive_id = $executive_id or op2.executive2_id = $executive_id) and month(op2.operation_date) = month(CURRENT_TIMESTAMP) and year(op2.operation_date) = year(CURRENT_TIMESTAMP) and day(op2.operation_date) <= dia) as monto_acumulado")

            ->selectRaw("(select sum(comission_amount) from operations_view op2 where (op2.executive_id = $executive_id or op2.executive2_id = $executive_id) and month(op2.operation_date) = month(operation_date) and year(op2.operation_date) = year(CURRENT_TIMESTAMP) and day(op2.operation_date) <= dia) as comision_acumulado")

            ->selectRaw("(select sum(round(comission_amount*if(executive_id =$executive_id, executive_comission,0),2)) + sum(round(comission_amount*if(executive2_id =$executive_id, executive2_comission,0),2)) from operations_view op2 where (op2.executive_id = $executive_id or op2.executive2_id = $executive_id) and month(op2.operation_date) = month(operation_date) and year(op2.operation_date) = year(CURRENT_TIMESTAMP) and day(op2.operation_date) <= dia) as comision_ejecutivo_acumulado")
            
            ->whereRaw("(executive_id = $executive_id or executive2_id = $executive_id) and month(operation_date) = month(CURRENT_TIMESTAMP) and year(operation_date) = year(CURRENT_TIMESTAMP) ")
            ->groupByRaw("day(operation_date)")
            ->orderByRaw('day(operation_date)')
            ->get();


        $cumplimiento_meta = DB::table('goals_achievement')
            ->select('operation_executive_id as executive_id','operation_month as month', 'operation_year as year','progress as operations_amount','goal')
            ->selectRaw(" round(achievement,4) as goal_achieved, if( ((operation_executive_id = 2801 or operation_executive_id = 2811) and year(CURRENT_TIMESTAMP) = 2023),0.05, comission_achieved ) as comission_achieved")
            ->selectRaw("(select sum(round( ov.comission_amount*ov.executive_comission ,2)) from operations_view ov where ov.executive_id = goals_achievement.operation_executive_id and month(ov.operation_date) = goals_achievement.operation_month and year(ov.operation_date) = goals_achievement.operation_year) as comission_earned")
            ->where('operation_executive_id', $executive_id)
            ->whereRaw(" operation_month = month(CURRENT_TIMESTAMP) and operation_year = year(CURRENT_TIMESTAMP)")
            ->first();



        return response()->json([
            'success' => true,
            'data' => [ 
                'tablero' => [
                    'volume' => $tablero_cartera->total_amount,
                    'comissions' => $tablero_cartera->total_comissions,
                    'unique_clients' => $tablero_cartera->unique_clients,
                    'total_clients' => $tablero_cartera->total_clients,
                    'new_clients' => $tablero_cartera->new_clients
                ],
                'graficos_tablero' => [
                    'year' => $grafico_globales->pluck('year'),
                    'volume' => $grafico_globales->pluck('volume'),
                    'comissions' => $grafico_globales->pluck('comission'),
                    'unique_clients' => $grafico_globales->pluck('unique_clients'),
                    'new_clients' => $grafico_globales->pluck('new_clients'),
                ],
                'ventas_mensuales' => [
                    'periodo' => $grafico_ventas->pluck('mes'),
                    'volumen' => $grafico_ventas->pluck('monto'),
                    'comision' => $grafico_ventas->pluck('comision'),
                    'comision_ejecutivo' => $grafico_ventas->pluck('comision_ejecutivo'),
                    'nro_operaciones' => $grafico_ventas->pluck('nro_ops'),
                    'clientes_unicos' => $grafico_ventas->pluck('clientes_unicos'),
                    'volumen_compra' => $grafico_ventas->pluck('volumen_compra'),
                    'volumen_venta' => $grafico_ventas->pluck('volumen_venta'),
                    'meta' => $grafico_ventas->pluck('meta')
                ],
                'ventas_diarias' => [
                    'periodo' => $grafico_ventas_diarias->pluck('dia'),
                    'meta_diaria' => $meta_diaria,
                    'meta' => $grafico_ventas_diarias->pluck('meta_diaria'),
                    'volumen' => $grafico_ventas_diarias->pluck('monto'),
                    'volumen_acumulado' => $grafico_ventas_diarias->pluck('monto_acumulado'),
                    //'volumen_pendiente' => $grafico_ventas_diarias->pluck('pendiente'),
                    'comision' => $grafico_ventas_diarias->pluck('comision'),
                    'comision_ejecutivo' => $grafico_ventas_diarias->pluck('comision_ejecutivo'),
                    'comision_acumulado' => $grafico_ventas_diarias->pluck('comision_acumulado'),
                    //'comision_pendiente' => $grafico_ventas_diarias->pluck('comision_pendiente'),
                    'nro_operaciones' => $grafico_ventas_diarias->pluck('nro_ops'),
                    'clientes_unicos' => $grafico_ventas_diarias->pluck('clientes_unicos')
                ],
                'cumplimiento_meta' => $cumplimiento_meta,
                //'spreads' => $spreads_pl
            ]
        ]);
    }

    public function goal_progress(Request $request) {
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

        return response()->json([
            'success' => true,
            'data' => [
                'cumplimiento_meta' => $goal_progress
            ]
        ]);
    }

    public function executives_summary(Request $request) {
        $executive_id = (isset($request->executive_id)) ? $request->executive_id : auth()->id();
        $year = (isset($request->year)) ? $request->year : Carbon::now()->year;


        $tabla = DB::table('operations_view as operations')
            ->selectRaw("month(operation_date) as mes, $year as year")
            ->selectRaw(" sum(if(operations.executive_id = $executive_id, 1,0)) as num_operations")
            ->selectRaw(" sum(if(operations.executive_id = $executive_id, operations.amount,0)) as volume")
            ->selectRaw(" coalesce(round(sum(if(operations.executive_id = $executive_id, operations.amount,0))/sum(if(operations.executive_id = $executive_id, 1,0)),0),0) as avg_ticket")
            ->selectRaw(" coalesce((select eg.goal from executive_goals eg where eg.month = mes and eg.executive_id = $executive_id and eg.year = $year limit 1),0) as goal ") 

            ->selectRaw(" round(100*coalesce(sum(if(operations.executive_id = $executive_id, operations.amount,0)) / coalesce((select eg.goal from executive_goals eg where eg.month = mes and eg.executive_id = $executive_id and eg.year = $year limit 1),0),0),0) as goal_achieved ")            
            ->selectRaw(" sum(if(operations.executive_id = $executive_id, operations.comission_amount,0)) as comision")
            ->selectRaw(" round(100*coalesce(sum(if(operations.executive_id = $executive_id, operations.comission_amount,0))/sum(if(operations.executive_id = $executive_id, operations.amount*operations.exchange_rate,0)),0),2) as spread")
            ->selectRaw(" if( coalesce((select sum(ov.amount) from operations_view ov where month(ov.operation_date) +1 = mes and year(ov.operation_date) = $year and ov.executive_id = $executive_id),0) = 0, 0, sum(if(operations.executive_id = $executive_id, operations.amount,0)) - coalesce((select sum(ov.amount) from operations_view ov where month(ov.operation_date) +1 = mes and year(ov.operation_date) = $year and ov.executive_id = $executive_id),0)) as var_volume")
            ->selectRaw(" count(distinct if(operations.executive_id = $executive_id, client_id,0)) - 1 as unique_clients")
            ->selectRaw(" coalesce((select count(cl.id) from clients cl where month(cl.registered_at) = mes and year(cl.registered_at) = year and cl.executive_id = $executive_id),0) as new_clients")
            ->selectRaw(" (select sum(ov.comission_amount) from operations_view ov where month(ov.operation_date) <= mes and year(ov.operation_date) = year and ov.executive_id = $executive_id) as accumulated_comissions")

            ->whereRaw("year(operation_date) = $year")
            ->whereIn("operations.type", ['Compra','Venta'])
            ->groupByRaw("month(operation_date)")
            ->orderByRaw('month(operation_date)')
            ->get();

        $total = DB::table('operations_view as operations')
            ->selectRaw("$year as year")
            ->selectRaw(" sum(if(operations.executive_id = $executive_id, 1,0)) as num_operations")
            ->selectRaw(" sum(if(operations.executive_id = $executive_id, operations.amount,0)) as volume")
            ->selectRaw(" coalesce(round(sum(if(operations.executive_id = $executive_id, operations.amount,0))/sum(if(operations.executive_id = $executive_id, 1,0)),0),0) as avg_ticket")
            ->selectRaw(" coalesce((select sum(eg.goal) from executive_goals eg where eg.executive_id = $executive_id and eg.year = $year limit 1),0) as goal ") 

            ->selectRaw(" round(100*coalesce(sum(if(operations.executive_id = $executive_id, operations.amount,0)) / coalesce((select sum(eg.goal) from executive_goals eg where eg.executive_id = $executive_id and eg.year = $year limit 1),0),0),0) as goal_achieved ")            
            ->selectRaw(" sum(if(operations.executive_id = $executive_id, operations.comission_amount,0)) as comision")
            ->selectRaw(" round(100*coalesce(sum(if(operations.executive_id = $executive_id, operations.comission_amount,0))/sum(if(operations.executive_id = $executive_id, operations.amount*operations.exchange_rate,0)),0),2) as spread")
            ->selectRaw(" count(distinct if(operations.executive_id = $executive_id, client_id,0)) - 1 as unique_clients")
            ->selectRaw(" coalesce((select count(cl.id) from clients cl where year(cl.registered_at) = year and cl.executive_id = $executive_id),0) as new_clients")

            ->whereRaw("year(operation_date) = $year")
            ->whereIn("operations.type", ['Compra','Venta'])
            ->groupByRaw("year(operation_date)")
            ->get();

        $positive_variation = DB::table('operations_view')
            ->selectRaw("client_name")

            ->selectRaw(" coalesce(sum(if( (year(operation_date)*12 + month(operation_date)) = (year(now())*12 + month(now()) - 1),amount,0))) as  current_amount")

            ->selectRaw(" coalesce(sum(if( (year(operation_date)*12 + month(operation_date)) = (year(now())*12 + month(now()) - 2),amount,0))) as  previous_amount")

            ->selectRaw(" coalesce(sum(if( (year(operation_date)*12 + month(operation_date)) = (year(now())*12 + month(now()) - 1),amount,0))) - coalesce(sum(if( (year(operation_date)*12 + month(operation_date)) = (year(now())*12 + month(now()) - 2),amount,0))) as difference")

            ->whereRaw(" (executive_id = $executive_id) and ((year(operation_date)-2000)*12 + MONTH(operation_date)) >= ((year(CURRENT_TIMESTAMP)-2000)*12 + MONTH(CURRENT_TIMESTAMP)-2)")
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

            ->whereRaw(" (executive_id = $executive_id) and ((year(operation_date)-2000)*12 + MONTH(operation_date)) >= ((year(CURRENT_TIMESTAMP)-2000)*12 + MONTH(CURRENT_TIMESTAMP)-2)")

            ->whereRaw(" (executive_id = $executive_id) and ((year(operation_date)-2000)*12 + MONTH(operation_date)) >= ((year(CURRENT_TIMESTAMP)-2000)*12 + MONTH(CURRENT_TIMESTAMP)-2)")
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
