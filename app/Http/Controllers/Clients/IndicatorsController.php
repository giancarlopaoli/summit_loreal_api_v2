<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Configuration;
use App\Models\Operation;
use App\Models\OperationStatus;

class IndicatorsController extends Controller
{
    //
    public function indicators(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id'
        ]);
        if ($validator->fails()) return response()->json($validator->messages());

        $pips_save = Configuration::where("shortname", "PIPSAVE")->first()->value;

        $indicators = Operation::selectRaw("count(*) as num_operations, sum(amount) as total_operated")
            ->selectRaw('(select date(operation_date) from operations where client_id = '.$request->client_id.' order by id limit 1) as first_operation')
            ->selectRaw("(select sum(round(amount * $pips_save /10000,2)) from operations where client_id = ".$request->client_id.' and operation_status_id in (6,7,8)) as total_saved')
            ->where('client_id', $request->client_id)
            ->whereIn('operation_status_id', OperationStatus::wherein('name', ['Facturado','Finalizado sin factura', 'Pendiente facturar'])->get()->pluck('id'))
            ->first();

        $graphs = Operation::selectRaw('month(operation_date) as month, year(operation_date) as year')
            ->selectRaw("(select coalesce(sum(op.amount),0) from operations op where op.client_id = operations.client_id and month(op.operation_date) = month(operations.operation_date) and year(op.operation_date) = year(operations.operation_date) and op.operation_status_id in (6,7,8) and op.client_id = ".$request->client_id.") as amount")
            ->selectRaw("(select coalesce(sum(round(op.amount * $pips_save /10000,2)),0) from operations op where op.client_id = operations.client_id and month(op.operation_date) = month(operations.operation_date) and year(op.operation_date) = year(operations.operation_date) and op.operation_status_id in (6,7,8) and op.client_id = ".$request->client_id.") as saved")
            ->whereIn('operation_status_id', OperationStatus::wherein('name', ['Facturado','Finalizado sin factura', 'Pendiente facturar'])->get()->pluck('id'))
            ->whereRaw('((year(operation_date)-2000)*12 + MONTH(operation_date)) >= ((year(CURRENT_TIMESTAMP)-2000)*12 + MONTH(CURRENT_TIMESTAMP)-12)')
            ->groupByRaw('month(operation_date), year(operation_date)')
            ->orderByRaw('year(operation_date), month(operation_date)')
            ->get();


        return response()->json([
            'success' => true,
            'data' => [
                'total_operated_amount' => $indicators->total_operated,
                'total_saved' => $indicators->total_saved,
                'first_operation_at' => $indicators->first_operation,
                'total_number_operations' => $indicators->num_operations,
                'graphs' => [
                    'month' => $graphs->pluck(['month']),
                    'year' => $graphs->pluck(['year']),
                    'amount' => $graphs->pluck(['amount']),
                    'saved' => $graphs->pluck(['saved']),
                ]
            ]
        ]);
    }
}
