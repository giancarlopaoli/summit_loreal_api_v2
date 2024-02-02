<?php

namespace App\Http\Controllers\Admin\Vendors;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Client;
use App\Models\Operation;
use App\Models\OperationStatus;

class ReportsController extends Controller
{
    // operations Report
    public function operations(Request $request) {
        $val = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id,type,PL',
            'start_date' => 'required|date',
            'end_date' => 'required|date'
        ]);
        if($val->fails()) return response()->json($val->messages());

        $start_date = $request->start_date;
        $end_date = $request->end_date;

        $operations = Operation::select('id','code','class','type','user_id','amount','exchange_rate','currency_id','operation_status_id','operation_date')
            ->selectRaw('round(amount*exchange_rate, 2) as counter_value')
            ->where('client_id', $request->client_id)
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

    public function vendor_spreads(Request $request) {

        $spreads = Client::select('id','name')
            ->whereIn('id', [366,3166,4280])
            ->where('client_status_id', 3)
            ->with('vendor_ranges:id,vendor_id,min_range,max_range','vendor_ranges.active_spreads')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'spreads' => $spreads
            ]
        ]);
    }

}
