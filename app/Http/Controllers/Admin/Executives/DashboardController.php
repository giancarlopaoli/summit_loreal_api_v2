<?php

namespace App\Http\Controllers\Admin\Executives;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\ExecutiveGoal;
use App\Models\Lead;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    // Validating if the ruc exists
    public function dashboard(Request $request) {

        $executive_id = (isset($request->executive_id)) ? $request->executive_id : auth()->id();

        // Tablero de indicadores globales
        $tablero_prospectos = Lead::selectRaw("count(id) as nro_prospectos")
            ->selectRaw("(select count(lt.id) from lead_trackings lt inner join leads ld on lt.lead_id = ld.id where ld.executive_id = $executive_id) as seguimiento_prospectos")
            ->where('executive_id', $executive_id)->first();

        $tablero_cartera = Client::join('leads','leads.client_id','=','clients.id')
            ->selectRaw("count(clients.id) as nro_cartera")
            ->selectRaw("(select count(lt.id) from client_trackings lt inner join clients ld on lt.client_id = ld.id where ld.executive_id = $executive_id) as seguimiento_cartera")
            ->where('leads.executive_id', $executive_id)
            ->first();

        
        $grafico_globales = Client::selectRaw("month(registered_at) as mes, year(registered_at) as anio")
            ->selectRaw("(select count(id) from leads where month(created_at) = mes and year(created_at) = anio and executive_id = $executive_id) as nro_prospectos")
            ->selectRaw("(select count(lt.id) from lead_trackings lt inner join leads ld on lt.lead_id = ld.id where ld.executive_id = $executive_id and month(lt.created_at) = mes and year(lt.created_at) = anio) as seguimiento_prospectos")
            ->selectRaw("(select count(lt.id) from client_trackings lt inner join clients ld on lt.client_id = ld.id where ld.executive_id = $executive_id and month(lt.created_at) = mes and year(lt.created_at) = anio) as seguimiento_cartera")

            ->whereRaw('((year(registered_at)-2000)*12 + month(registered_at)) >= (year(now())-2000)*12 + month(now())-6')
            ->groupByRaw("month(registered_at), year(registered_at)")
            ->orderByRaw('year(registered_at), month(registered_at)')
            ->get();

        
        $clientes_conseguidos = Client::selectRaw("month(registered_at) as mes, year(registered_at) as anio")
            ->selectRaw("(select count(cl2.id) from clients cl2 where month(cl2.registered_at) = month(clients.registered_at) and year(cl2.registered_at) = year(clients.registered_at) and cl2.executive_id = $executive_id ) as nro_clientes")
            ->whereRaw('((year(registered_at)-2000)*12 + month(registered_at)) >= (year(CURRENT_TIMESTAMP)-2000)*12 + month(CURRENT_TIMESTAMP)-6')
            ->groupByRaw("month(registered_at), year(registered_at)")
            ->orderByRaw('year(registered_at), month(registered_at)')
            ->get();

        
    

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
        
         /*return response()->json([
            'success' => true,
            'data' => [
                $grafico_ventas
            ]
        ]);*/

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


        /*$spreads_pl = DB::table('Comision')
            ->select('RangoMin', 'RangoMax', 'comision', 'spread', 'spreadAux')
            ->selectRaw("(select sp.Compra*10000 from spread sp where sp.ComisionId = Comision.ComisionId and sp.Estado = 'ACT' and ProvedorliquidezId = 3166) as r4_compra")
            ->selectRaw("(select sp.Venta*10000 from spread sp where sp.ComisionId = Comision.ComisionId and sp.Estado = 'ACT' and ProvedorliquidezId = 3166) as r4_venta")
            ->selectRaw("(select sp.Compra*10000 from spread sp where sp.ComisionId = Comision.ComisionId and sp.Estado = 'ACT' and ProvedorliquidezId = 366) as coril_compra")
            ->selectRaw("(select sp.Venta*10000 from spread sp where sp.ComisionId = Comision.ComisionId and sp.Estado = 'ACT' and ProvedorliquidezId = 366) as coril_venta")
            ->selectRaw("(select sp.Compra*10000 from spread sp where sp.ComisionId = Comision.ComisionId and sp.Estado = 'ACT' and ProvedorliquidezId = 4280) as ripley_compra")
            ->selectRaw("(select sp.Venta*10000 from spread sp where sp.ComisionId = Comision.ComisionId and sp.Estado = 'ACT' and ProvedorliquidezId = 4280) as ripley_venta")
            ->where('Estado','ACT')
            ->get();*/



        return response()->json([
            'success' => true,
            'data' => [ 
                'tablero' => [
                    'prospectos' => $tablero_prospectos->nro_prospectos,
                    'seguimiento_prospectos' => $tablero_prospectos->seguimiento_prospectos,
                    'cartera' => $tablero_cartera->nro_cartera,
                    'seguimiento_cartera' => $tablero_cartera->seguimiento_cartera
                ],
                'graficos_tablero' => [
                    'mes' => $grafico_globales->pluck('mes'),
                    'nro_prospectos' => $grafico_globales->pluck('nro_prospectos'),
                    'seguimiento_prospectos' => $grafico_globales->pluck('seguimiento_prospectos'),
                    'nro_clientes' => $clientes_conseguidos->pluck('nro_clientes'),
                    'seguimiento_cartera' => $grafico_globales->pluck('seguimiento_cartera'),
                ],
                'ventas_mensuales' => [
                    'periodo' => $grafico_ventas->pluck('mes'),
                    'volumen' => $grafico_ventas->pluck('monto'),
                    'comision' => $grafico_ventas->pluck('comision'),
                    'comision_ejecutivo' => $grafico_ventas->pluck('comision_ejecutivo'),
                    'nro_operaciones' => $grafico_ventas->pluck('nro_ops'),
                    'clientes_unicos' => $grafico_ventas->pluck('clientes_unicos'),
                    //'volumen_empresas' => $grafico_ventas->pluck('volumen_empresas'),
                    //'volumen_personas' => $grafico_ventas->pluck('volumen_personas'),
                    //'cuenta_empresas' => $grafico_ventas->pluck('cuenta_empresas'),
                    //'cuenta_personas' => $grafico_ventas->pluck('cuenta_personas'),
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
}
