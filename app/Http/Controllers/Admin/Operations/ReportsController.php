<?php

namespace App\Http\Controllers\Admin\Operations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Operation;
use App\Models\OperationStatus;

class ReportsController extends Controller
{
    //
    //Report
    public function selling_buying_report(Request $request) {
        $val = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date'
        ]);
        if($val->fails()) return response()->json($val->messages());

        $start_date = $request->start_date;
        $end_date = $request->end_date;
        
        $operations = Operation::select('id','code','class','type','user_id','amount','exchange_rate','currency_id','operation_status_id','operation_date')
            ->where('operation_status_id', OperationStatus::where('name', 'Finalizado sin factura')->first()->id)
            ->whereRaw("date(operation_date) >= date('$start_date') and date(operation_date) <= date('$end_date')")
            ->with('currency:id,name,sign')
            ->with('status:id,name')
            ->with('user:id,name,last_name')
/*            ->with('bank_accounts.bank:id,name,shortname,image','bank_accounts.currency:id,name,sign')
            ->with('escrow_accounts.bank:id,name,shortname,image','escrow_accounts.currency:id,name,sign')*/
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'operations' => $operations
            ]
        ]);
    }

    public function corfid(Request $request) {
        $val = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date'
        ]);
        if($val->fails()) return response()->json($val->messages());

        $start_date = $request->start_date;
        $end_date = $request->end_date;
        
        $buying_operations = Operation::select('operations.id','operations.type','operations.exchange_rate','operations.currency_id','operations.operation_status_id', 'operations.comission_spread as client_comission_spread', 'op2.comission_spread as counterpart_comission_spread')
            ->selectRaw("date_format(operations.operation_date, '%Y-%m-%d') as operation_date")
            ->selectRaw(" round(round(operations.amount*operations.exchange_rate,2) + operations.comission_amount + operations.igv,2) as transfer_amount_pen")
            ->selectRaw(" operations.amount as  receipt_amount_usd")
            ->selectRaw(" op2.amount as  transfer_amount_usd")
            ->selectRaw(" round(round(op2.amount*op2.exchange_rate,2) + op2.comission_amount + op2.igv,2) as  receipt_amount_pen")
            ->selectRaw(" if(clients.customer_type ='PN', concat(clients.name,' ',clients.last_name, ' ', clients.mothers_name), clients.name ) as client_name")
            ->selectRaw(" if(cl2.customer_type ='PN', concat(cl2.name,' ',cl2.last_name, ' ', cl2.mothers_name), cl2.name ) as counterpart_name")
            ->selectRaw("round(operations.comission_amount + operations.igv,2) as client_comission")
            ->selectRaw("round(op2.comission_amount + op2.igv,2) as counterpart_comission")
            ->join('clients', 'clients.id', '=', 'operations.client_id')
            ->join('operation_matches', 'operation_matches.operation_id', '=', 'operations.id')
            ->join('operations as op2', 'operation_matches.matched_id', '=', 'op2.id')
            ->join('clients as cl2', 'cl2.id', '=', 'op2.client_id')
            ->where('operations.type', 'Compra')
            ->where('clients.type', 'Cliente')
            ->whereIn('operations.operation_status_id', [6,7,8])
            ->whereRaw("date(operations.operation_date) >= date('$start_date') and date(operations.operation_date) <= date('$end_date')")
            ->get();


        $selling_operations = Operation::select('operations.id','operations.type','operations.exchange_rate','operations.currency_id','operations.operation_status_id', 'operations.comission_spread as client_comission_spread', 'op2.comission_spread as counterpart_comission_spread')
            ->selectRaw("date_format(operations.operation_date, '%Y-%m-%d') as operation_date")
            ->selectRaw(" round(round(operations.amount*operations.exchange_rate,2) - operations.comission_amount - operations.igv,2) as receipt_amount_pen")
            ->selectRaw(" operations.amount as  transfer_amount_usd")
            ->selectRaw(" op2.amount as  receipt_amount_usd")
            ->selectRaw(" round(round(op2.amount*op2.exchange_rate,2) + op2.comission_amount + op2.igv,2) as  transfer_amount_pen")
            ->selectRaw(" if(clients.customer_type ='PN', concat(clients.name,' ',clients.last_name, ' ', clients.mothers_name), clients.name ) as client_name")
            ->selectRaw(" if(cl2.customer_type ='PN', concat(cl2.name,' ',cl2.last_name, ' ', cl2.mothers_name), cl2.name ) as counterpart_name")
            ->selectRaw("round(operations.comission_amount + operations.igv,2) as client_comission")
            ->selectRaw("round(op2.comission_amount + op2.igv,2) as counterpart_comission")
            ->join('clients', 'clients.id', '=', 'operations.client_id')
            ->join('operation_matches', 'operation_matches.operation_id', '=', 'operations.id')
            ->join('operations as op2', 'operation_matches.matched_id', '=', 'op2.id')
            ->join('clients as cl2', 'cl2.id', '=', 'op2.client_id')
            ->where('operations.type', 'Venta')
            ->where('clients.type', 'Cliente')
            ->whereIn('operations.operation_status_id', [6,7,8])
            ->whereRaw("date(operations.operation_date) >= date('$start_date') and date(operations.operation_date) <= date('$end_date')")
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'buying' => $buying_operations,
                'selling' => $selling_operations
            ]
        ]);
    }
}
