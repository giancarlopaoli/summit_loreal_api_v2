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
}
