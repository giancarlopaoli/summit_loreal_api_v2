<?php

namespace App\Http\Controllers\Admin\Operations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Client;
use App\Models\Operation;
use App\Models\ClientStatus;
use Illuminate\Support\Facades\Validator;

class OperationsTimesController extends Controller
{
    //
    public function dashboard(Request $request) {
        $val = Validator::make($request->all(), [
            'month' => 'required|numeric',
            'year' => 'required|numeric'
        ]);
        if($val->fails()) return response()->json($val->messages());

        $month = $request->month;
        $year = $request->year;

        $tablero = Client::select('id','name')
            ->where('type', 'PL')
            ->where('client_status_id', ClientStatus::where('name', 'Activo')->first()->id)
            ->selectRaw("(Select count(*) from operations op where op.client_id = clients.id and MONTH(op.operation_date) = $month and YEAR(op.operation_date) = $year and op.operation_status_id = 7) as num_ops")
            ->get();

        /*$tablero = DB::table('Cliente')
            ->select('ClienteId','NonmbresRazonSocial')
            ->selectRaw("(Select count(*) from Operacion op where op.ClienteId = Cliente.ClienteId and MONTH(op.FechaOperacion) = $month and YEAR(op.FechaOperacion) = $year and op.EstadoId ='FSF' and FechaOperacion >='2022-09-12') as nro_ops")
            
            ->selectRaw("(Select AVG(DATEDIFF(minute, op.FechaCorreoPL, op2.FecDeposito)) from Operacion op 
                inner join OperacionEmparejar on OperacionEmparejar.OperacionEmparejado = op.OperacionId
                inner join Operacion op2 on op2.OperacionId = OperacionEmparejar.OperacionEmparejador
                where op2.ClienteId = Cliente.ClienteId and MONTH(op.FechaOperacion) = $month and YEAR(op.FechaOperacion) = $year and op.EstadoId in ('FAC','FSF','PFA') and op.FechaOperacion >='2022-09-12') as tiempo_promedio")

            ->selectRaw("(Select min(DATEDIFF(minute, op.FechaCorreoPL, op2.FecDeposito)) from Operacion op 
                inner join OperacionEmparejar on OperacionEmparejar.OperacionEmparejado = op.OperacionId
                inner join Operacion op2 on op2.OperacionId = OperacionEmparejar.OperacionEmparejador
                where op2.ClienteId = Cliente.ClienteId and MONTH(op.FechaOperacion) = $month and YEAR(op.FechaOperacion) = $year and op.EstadoId in ('FAC','FSF','PFA') and op.FechaOperacion >='2022-09-12') as tiempo_minimo")

            ->selectRaw("(Select max(DATEDIFF(minute, op.FechaCorreoPL, op2.FecDeposito)) from Operacion op 
                inner join OperacionEmparejar on OperacionEmparejar.OperacionEmparejado = op.OperacionId
                inner join Operacion op2 on op2.OperacionId = OperacionEmparejar.OperacionEmparejador
                where op2.ClienteId = Cliente.ClienteId and MONTH(op.FechaOperacion) = $month and YEAR(op.FechaOperacion) = $year and op.EstadoId in ('FAC','FSF','PFA') and op.FechaOperacion >='2022-09-12') as tiempo_maximo")

            ->whereIn('ClienteId', [1884,366,2815,3166,4280])
            ->get();*/

        /*$grafico_tablero = DB::table('Operacion as op')
            ->join('OperacionEmparejar', 'OperacionEmparejar.OperacionEmparejado', '=', 'op.OperacionId')
            ->whereIn('op.EstadoId', ['FAC','FSF','PFA'])
            ->whereRaw("MONTH(op.FechaOperacion) = $month and YEAR(op.FechaOperacion) = $year")
            ->where('op.FechaOperacion', '>=', '2022-09-12')
            ->selectRaw("day(op.FechaOperacion) as dia")
            
            ->selectRaw("(Select COALESCE(AVG(DATEDIFF(minute, op1.FechaCorreoPL, op2.FecDeposito)),0) from Operacion op1 
                inner join OperacionEmparejar on OperacionEmparejar.OperacionEmparejado = op1.OperacionId
                inner join Operacion op2 on op2.OperacionId = OperacionEmparejar.OperacionEmparejador
                where op2.ClienteId = 366 and day(op.FechaOperacion) = day(op1.FechaOperacion) and MONTH(op1.FechaOperacion) = $month and year(op1.FechaOperacion) = $year and op1.EstadoId in ('FAC','FSF','PFA') ) as tiempo_coril")

            ->selectRaw("(Select COALESCE(AVG(DATEDIFF(minute, op1.FechaCorreoPL, op2.FecDeposito)),0) from Operacion op1 
                inner join OperacionEmparejar on OperacionEmparejar.OperacionEmparejado = op1.OperacionId
                inner join Operacion op2 on op2.OperacionId = OperacionEmparejar.OperacionEmparejador
                where op2.ClienteId = 1884 and day(op.FechaOperacion) = day(op1.FechaOperacion) and MONTH(op1.FechaOperacion) = $month and year(op1.FechaOperacion) = $year and op1.EstadoId in ('FAC','FSF','PFA') ) as tiempo_caja")

            ->selectRaw("(Select COALESCE(AVG(DATEDIFF(minute, op1.FechaCorreoPL, op2.FecDeposito)),0) from Operacion op1 
                inner join OperacionEmparejar on OperacionEmparejar.OperacionEmparejado = op1.OperacionId
                inner join Operacion op2 on op2.OperacionId = OperacionEmparejar.OperacionEmparejador
                where op2.ClienteId = 2815 and day(op.FechaOperacion) = day(op1.FechaOperacion) and MONTH(op1.FechaOperacion) = $month and year(op1.FechaOperacion) = $year and op1.EstadoId in ('FAC','FSF','PFA') ) as tiempo_mibanco")

            ->selectRaw("(Select COALESCE(AVG(DATEDIFF(minute, op1.FechaCorreoPL, op2.FecDeposito)),0) from Operacion op1 
                inner join OperacionEmparejar on OperacionEmparejar.OperacionEmparejado = op1.OperacionId
                inner join Operacion op2 on op2.OperacionId = OperacionEmparejar.OperacionEmparejador
                where op2.ClienteId = 3166 and day(op.FechaOperacion) = day(op1.FechaOperacion) and MONTH(op1.FechaOperacion) = $month and year(op1.FechaOperacion) = $year and op1.EstadoId in ('FAC','FSF','PFA') ) as tiempo_renta4")

            ->selectRaw("(Select COALESCE(AVG(DATEDIFF(minute, op1.FechaCorreoPL, op2.FecDeposito)),0) from Operacion op1 
                inner join OperacionEmparejar on OperacionEmparejar.OperacionEmparejado = op1.OperacionId
                inner join Operacion op2 on op2.OperacionId = OperacionEmparejar.OperacionEmparejador
                where op2.ClienteId = 4280 and day(op.FechaOperacion) = day(op1.FechaOperacion) and MONTH(op1.FechaOperacion) = $month and year(op1.FechaOperacion) = $year and op1.EstadoId in ('FAC','FSF','PFA') ) as tiempo_ripley")

            ->groupByRaw("day(op.FechaOperacion)")
            ->orderByRaw('day(op.FechaOperacion)')
            ->get();*/

        return response()->json([
            'success' => true,
            'data' => [ 
                'tablero' => $tablero,
                /*'graficos_tablero' => [
                    'dia' => $grafico_tablero->pluck('dia'),
                    'coril' => $grafico_tablero->pluck('tiempo_coril'),
                    'caja' => $grafico_tablero->pluck('tiempo_caja'),
                    'mibanco' => $grafico_tablero->pluck('tiempo_mibanco'),
                    'renta4' => $grafico_tablero->pluck('tiempo_renta4'),
                    'renta4' => $grafico_tablero->pluck('tiempo_ripley')
                ],*/
            ]
        ]);
    }
}
