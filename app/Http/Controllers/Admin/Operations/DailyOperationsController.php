<?php

namespace App\Http\Controllers\Admin\Operations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Operation;
use App\Models\OperationStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DailyOperationsController extends Controller
{
    public function daily_operations(Request $request) {
        $val = Validator::make($request->all(), [
            'date' => 'date',
            'status' => 'required|in:Todas,Pendientes,Finalizadas'
        ]);
        if($val->fails()) return response()->json($val->messages());

        ########### Configuration ##################

        $date = isset($request->date) ? $request->date : Carbon::now()->format('Y-m-d');
        $month = Carbon::now()->month;
        $year = Carbon::now()->year;

        $pendientes = OperationStatus::wherein('name', ['Disponible','Pendiente envio fondos','Pendiente fondos contraparte','Contravalor recaudado','Fondos enviados'])->get()->pluck('id');
        $finalizadas = OperationStatus::wherein('name', ['Facturado','Finalizado sin factura', 'Pendiente facturar'])->get()->pluck('id');
        $todas = OperationStatus::get()->pluck('id');

        $status = ($request->status == 'Pendientes') ? $pendientes : (($request->status == 'Finalizadas') ? $finalizadas : $todas);

        #############################################

        $indicators = Operation::selectRaw("date(operation_date) as date, sum(amount) as total_amount, count(id) as num_operations")
            ->selectRaw("(select sum(op1.amount) from operations op1 where month(op1.operation_date) = $month and year(op1.operation_date) = $year and op1.id = operations.id) as monthly_amount")
            ->selectRaw("(select count(op1.amount) from operations op1 where month(op1.operation_date) = $month and year(op1.operation_date) = $year and op1.id = operations.id) as monthly_operations")
            ->whereRaw("date(operation_date) = '$date'")
            ->whereIn('operation_status_id', $finalizadas)
            ->get();

        $graphs = Operation::
            selectRaw("day(operation_date) as dia, sum(amount) as amount, count(amount) as num_operations")
            ->selectRaw("(select sum(amount) from operations as op2 where month(op2.operation_date) = month(CURRENT_TIMESTAMP) and year(op2.operation_date) = year(CURRENT_TIMESTAMP) and day(op2.operation_date) <= day(operations.operation_date) and operation_status_id in (" . substr($finalizadas, 1, Str::length($finalizadas)-2) . ") and type in ('Compra','Venta')) as accumulated_amount")

            ->selectRaw("(select sum(amount) from operations as op2 where month(op2.operation_date) = month(CURRENT_TIMESTAMP) and year(op2.operation_date) = year(CURRENT_TIMESTAMP) and day(op2.operation_date) <= day(operations.operation_date) and operation_status_id in (" . substr($finalizadas, 1, Str::length($finalizadas)-2) . ") and type in ('Compra','Venta')) as accumulated_num_operations")

            ->whereIn('operation_status_id', $finalizadas)
            ->whereIn('type', ['Compra', 'Venta'])
            ->whereRaw('month(operation_date) = month(CURRENT_TIMESTAMP) and year(operation_date) = year(CURRENT_TIMESTAMP) ')
            ->groupByRaw("day(operation_date)")
            ->orderByRaw('day(operation_date)')
            ->get();

        $pending_operations = Operation::select('id','code','class','type','client_id','user_id','amount','currency_id','exchange_rate','comission_spread','comission_amount','igv','spread','operation_status_id','post','operation_date')
            ->whereIn('operation_status_id', OperationStatus::wherein('name', ['Disponible','Cancelado'])->get()->pluck('id'))
            ->whereRaw("date(operation_date) = '$date'")
            ->with('client:id,name,last_name,mothers_name,customer_type')
            ->with('currency:id,name:sign')
            ->with('status:id,name')
            ->get();

        $matched_operations = Operation::select('operations.id','code','class','type','client_id','user_id','amount','currency_id','exchange_rate','comission_spread','comission_amount','igv','spread','operation_status_id','post','operation_date')
            ->join('operation_matches', 'operation_matches.operation_id' , "=", "operations.id")
            //->whereIn('operation_status_id', $status)
            ->whereRaw("date(operation_date) = '$date'")
            ->with('client:id,name,last_name,mothers_name,customer_type')
            ->with('currency:id,name:sign')
            ->with('bank_accounts:id,bank_id,currency_id,account_number,cci_number')
            ->with('bank_accounts.currency:id,name,sign')
            ->with('bank_accounts.bank:id,name,shortname,image')
            ->with('escrow_accounts:id,bank_id,currency_id,account_number,cci_number')
            ->with('escrow_accounts.currency:id,name,sign')
            ->with('escrow_accounts.bank:id,name,shortname,image')
            ->with('matches.client')
            ->with('matches.client:id,name,last_name,mothers_name,customer_type')
            ->with('matches.currency:id,name:sign')
            //->with('matches.bank_accounts:id,bank_id,currency_id,account_number,cci_number')
            ->with('matches.bank_accounts.currency:id,name,sign')
            ->with('matches.bank_accounts.bank:id,name,shortname,image')
            //->with('matches.escrow_accounts:id,bank_id,currency_id,account_number,cci_number')
            ->with('matches.escrow_accounts.currency:id,name,sign')
            ->with('matches.escrow_accounts.bank:id,name,shortname,image')
            ->get();


        return response()->json([
            'success' => true,
            'data' => [
                'indicators' => $indicators,
                'graphs' => $graphs,
                'pending_operations' => $pending_operations,
                'matched_operations' => $matched_operations,
            ]
        ]);

    }
}
