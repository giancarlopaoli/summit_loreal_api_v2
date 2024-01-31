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
        
        $buying_operations = Operation::select('id','operations.type','exchange_rate','currency_id','operation_status_id','operation_date')
            ->selectRaw(" round(round(amount*exchange_rate,2) + comission_amount + igv,2) transfer_amount_pen")
            ->selectRaw(" amount as  receipt_amount_usd")
            ->selectRaw(" amount as  transfer_amount_usd")
            ->selectRaw(" round(amount*exchange_rate,2) as  receipt_amount_pen")
            //->selectRaw(" if(clients.customer_type ='PN', concat(name,' ',last_name, ' ', mothers_name), name ) as client_name")
            //->join('clients', 'clients.id', '=', 'operations.client_id')
            ->where('operations.type', 'Compra')
            //->where('clients.type', 'Cliente')
            ->whereIn('operation_status_id', [6,7,8])
            ->whereRaw("date(operation_date) >= date('$start_date') and date(operation_date) <= date('$end_date')")
            ->with('currency:id,name,sign')
            ->with('status:id,name')
            ->with('matches:id,client_id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'buying' => $buying_operations
            ]
        ]);
    }
}
