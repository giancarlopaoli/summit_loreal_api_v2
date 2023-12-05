<?php

namespace App\Http\Controllers\Admin\Operations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Client;
use App\Models\Operation;
use App\Models\OperationStatus;
use App\Models\ClientStatus;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

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
            
            ->selectRaw("(Select AVG(DATEDIFF(minute, op.FechaCorreoPL, op2.FecDeposito)) from Operacion op 
                inner join OperacionEmparejar on OperacionEmparejar.OperacionEmparejado = op.OperacionId
                inner join Operacion op2 on op2.OperacionId = OperacionEmparejar.OperacionEmparejador
                where op2.ClienteId = Cliente.ClienteId and MONTH(op.FechaOperacion) = $month and YEAR(op.FechaOperacion) = $year and op.EstadoId in ('FAC','FSF','PFA') and op.FechaOperacion >='2022-09-12') as tiempo_promedio")

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

    // Reporte tiempos de atención
    public function daily_times(Request $request) {
        $val = Validator::make($request->all(), [
            'status' => 'required|in:Todos,Pendientes,Finalizadas',
            'executives' => 'in:true,false'
        ]);
        if($val->fails()) return response()->json($val->messages());

        ############### Status Filter #############
        $pendientes = OperationStatus::wherein('name', ['Disponible','Pendiente envio fondos','Pendiente fondos contraparte','Contravalor recaudado','Fondos enviados','Pendiente facturar'])->get()->pluck('id');
        $finalizadas = OperationStatus::wherein('name', ['Facturado','Finalizado sin factura'])->get()->pluck('id');
        $todas = OperationStatus::get()->pluck('id');

        if($request->status == 'Pendientes'){
            $status = $pendientes;
        }
        elseif($request->status == 'Finalizadas'){
            $status = $finalizadas;
        }
        else{
            $status = $todas;
        }

        $report = Operation::select('operations.id','operations.code','operations.class','operations.type','operations.client_id','operations.user_id','operations.amount','operations.currency_id','operations.operation_status_id','operations.operation_date')
            ->whereIn('operations.operation_status_id', $status)
            ->whereRaw("date(operations.operation_date) = date(now())")
            ->with('client:id,name,last_name,mothers_name,type,customer_type,executive_id','client.executive:id,type','client.executive.user:id,name,last_name')
            ->with('documents:id,operation_id,type')
            ->whereHas('client', function ($query) {
                $query->where('type', 'Cliente');
            })
            ->with('status:id,name')
            ->with('currency:id,name,sign')
            ->with('matches:id,client_id,operation_status_id','matches.client:id,name,last_name,mothers_name,type,customer_type','matches.status:id,name')
            ->join('clients', 'clients.id', '=','operations.client_id')
            ->join('operation_matches', 'operation_matches.operation_id', '=', 'operations.id')
            ->join('operations as op2', 'op2.id', '=', 'operation_matches.matched_id')
            ->selectRaw("TIMESTAMPDIFF(MINUTE,operations.operation_date, (select od.created_at from operation_documents od where od.operation_id = operations.id and od.type = 'Comprobante' order by id limit 1 )) as voucher_time")
            ->selectRaw("TIMESTAMPDIFF(MINUTE,(select od.created_at from operation_documents od where od.operation_id = operations.id and od.type = 'Comprobante' order by id limit 1 ),operations.funds_confirmation_date) as confirm_funds_time")
            ->selectRaw("TIMESTAMPDIFF(MINUTE,operations.funds_confirmation_date,op2.sign_date) as ops_first_sign_time")
            ->selectRaw("TIMESTAMPDIFF(MINUTE,op2.sign_date,op2.deposit_date) as corfid_first_sign_time")
            ->selectRaw("TIMESTAMPDIFF(MINUTE,op2.deposit_date,op2.funds_confirmation_date) as vendor_funds_time")
            ->selectRaw("TIMESTAMPDIFF(MINUTE,op2.funds_confirmation_date,operations.sign_date) as ops_second_sign_time")
            ->selectRaw("TIMESTAMPDIFF(MINUTE,operations.sign_date,operations.deposit_date) as corfid_second_sign_time")
            ->selectRaw("TIMESTAMPDIFF(MINUTE,operations.operation_date,operations.deposit_date) as total_time")
            ->selectRaw("if(operations.operation_status_id = 2 && (select od.created_at from operation_documents od where od.operation_id = operations.id and od.type = 'Comprobante' order by id limit 1) is null,
                TIMESTAMPDIFF(MINUTE,operations.operation_date,now()),
                if(operations.operation_status_id = 2 && (select od.created_at from operation_documents od where od.operation_id = operations.id and od.type = 'Comprobante' order by id limit 1) is not null,
                TIMESTAMPDIFF(MINUTE,(select od.created_at from operation_documents od where od.operation_id = operations.id and od.type = 'Comprobante' order by id limit 1 ),now()),
                if(operations.operation_status_id=3 && (op2.sign_date is null),
                TIMESTAMPDIFF(MINUTE,operations.funds_confirmation_date,now()),
                if(operations.operation_status_id = 3 && (op2.sign_date is not null),
                TIMESTAMPDIFF(MINUTE,op2.sign_date,now()),
                if(operations.operation_status_id = 4 && op2.operation_status_id = 5,
                TIMESTAMPDIFF(MINUTE,op2.deposit_date,now()),
                if(operations.operation_status_id = 4 && op2.operation_status_id = 7 && (operations.sign_date is null),
                TIMESTAMPDIFF(MINUTE,op2.funds_confirmation_date,now()),
                if(operations.operation_status_id = 4 && op2.operation_status_id = 7 && (operations.sign_date is not null),
                TIMESTAMPDIFF(MINUTE,op2.funds_confirmation_date,now()),

                if((operations.operation_status_id in (6,7,8)) && (op2.operation_status_id in (6,7,8)),
                TIMESTAMPDIFF(MINUTE,operations.operation_date,operations.deposit_date),


                0)))))))) as currenttime");

        if($request->executives == true){

            $executive_id = auth()->id();

            $report = $report->where('clients.executive_id', $executive_id)->get();
        }
        else{
            $report = $report->get();
        }

        return response()->json([
            'success' => true,
            'data' => $report,
        ]);
    }

    // Reporte tiempos de atención
    public function tiemposAtencion(Request $request) {
        $val = Validator::make($request->all(), [
            'month' => 'required|numeric',
            'year' => 'required|numeric'
        ]);
        if($val->fails()) return response()->json($val->messages());

        $month = $request->month;
        $year = $request->year;

        $reporte = DB::table('Operacion as op')
            ->join('OperacionEmparejar', 'OperacionEmparejar.OperacionEmparejado', '=', 'op.OperacionId')
            ->join('Operacion as op2', 'op2.OperacionId', '=', 'OperacionEmparejar.OperacionEmparejador')
            ->join('Cliente as cl', 'op.ClienteId', '=', 'cl.ClienteId')
            ->join('Cliente as cl2', 'op2.ClienteId', '=', 'cl2.ClienteId')
            ->whereIn('op2.ClienteId', [4540,366,2815,3166,4280])
            ->whereIn('op.EstadoId', ['FAC','FSF','PFA'])
            ->where('op.FechaOperacion', '>=', '2022-09-12')
            ->whereRaw("MONTH(op.FechaOperacion) = $month and YEAR(op.FechaOperacion) = $year")
            ->select('op.OperacionId','op.operacioncodigo','op.monto','op.DivisaId','op.FechaOperacion as fecha_creacion','op2.FechaOperacion as fecha_calce','op.FecConfirmacion as fecha_deposito_cliente','op.FechaPrimeraFirma','op2.FecConfirmacion as fecha_deposito_a_pl','op2.FecDeposito as envio_fondos_pl','op.FechaSegundaFirma','op.FecDeposito as fecha_contraparte','cl2.NonmbresRazonSocial as nombre_pl')
            ->selectRaw("Case cl.TipoClienteId when 1 then CONCAT(cl.NonmbresRazonSocial,' ',cl.ApellidoNombreComercial,' ',cl.ApellidoMaterno) when 4 then cl.NonmbresRazonSocial END as nombre_cliente")
            ->selectRaw("iif(op.TipoOperacionId = 1, 'Compra', iif(op.TipoOperacionId = 2,'Venta', 'Interbancaria')) as tipo_op, iif(op.DivisaId=1, 'S/', '$') as divisa")
            ->selectRaw("DATEDIFF(minute, op.FechaOperacion, op.FecConfirmacion) as tiempo_deposito_cliente")
            ->selectRaw("DATEDIFF(minute, op.FecConfirmacion, op.FechaPrimeraFirma) as tiempo_envio_primera_firma")
            ->selectRaw("DATEDIFF(minute, op.FechaPrimeraFirma, op2.FecConfirmacion) as tiempo_corfid_primera_firma")
            ->selectRaw("DATEDIFF(minute, op2.FecConfirmacion, op2.FecDeposito) as tiempo_pl")
            ->selectRaw("DATEDIFF(minute, op2.FecDeposito, op.FechaSegundaFirma) as tiempo_envio_segunda_firma")
            ->selectRaw("DATEDIFF(minute, op.FechaSegundaFirma, op.FecDeposito) as tiempo_corfid_segunda_firma")
            ->selectRaw("DATEDIFF(minute, op.FecConfirmacion, op.FecDeposito) as tiempo_total")
            ->get();

        return response()->json([
            'success' => true,
            'data' => $reporte
        ]);
    }
}
