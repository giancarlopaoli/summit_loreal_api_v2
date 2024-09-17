<?php

namespace App\Http\Controllers\Admin\Supervisors;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\Operation;
use Carbon\Carbon;

class OperationsController extends Controller
{
    // Operations list
    public function list(Request $request) {

        $operations = Operation::select('operations.id','code','operation_date','client_id','user_id','operations.type','amount','operation_status_id','invoice_serie','invoice_number','invoice_url','unaffected_invoice_serie','unaffected_invoice_number','unaffected_invoice_url')
            ->join('clients as cl','cl.id','=','operations.client_id')
            ->selectRaw("if(customer_type = 'PN',concat(name,' ',last_name,' ',mothers_name),name) as client_name")
            ->where('cl.type','Cliente')
            ->whereIn('operations.operation_status_id',[6,7])
            ->limit(20)
            ->orderBy('operations.id');

        if(isset($request->start_date)) $operations = $operations->where('operation_date','>=', $request->start_date);
        if(isset($request->end_date)) $operations = $operations->where('operation_date','<=', $request->end_date.' 23:59:59');
        

        if($request->client_name != "") $operations = $operations->whereRaw("(CONCAT(name,' ',last_name,' ',mothers_name) like "."'%"."$request->client_name"."%'" . "or name like "."'%"."$request->client_name"."%')");

        if($request->code != "")  $operations = $operations->where('code', 'like', "%".$request->code."%");

        $operations = $operations->get();

        return response()->json([
            'success' => true,
            'data' => [
                'operations' => $operations
            ]
        ]);
    }

    // Operations list
    public function clients(Request $request) {
        $clients = Client::select('id')
            ->selectRaw("if(customer_type = 'PN',concat(name,' ',last_name,' ',mothers_name),name) as client_name")
            ->where('client_status_id',3)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'clients' => $clients
            ]
        ]);
    }

    // Operations list
    public function edit(Request $request, Operation $operation) {
        $val = Validator::make($request->all(), [
            'type' => 'required|in:1,2',
            'serie' => 'required|string:10',
            'number' => 'required|numeric:10',
            'url' => 'required|string:200',
        ]);
        if($val->fails()) return response()->json($val->messages());

        if ($request->type == 1) {
            if (!is_null($operation->invoice_serie) && !is_null($operation->invoice_number) && !is_null($operation->invoice_url)) {
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'La operación ya tiene su factura registrada'
                    ]
                ]);
            }

            $operation->invoice_serie = $request->serie;
            $operation->invoice_number = $request->number;
            $operation->invoice_url = $request->url;
            $operation->save();
        }

        if ($request->type == 2) {
            if (!is_null($operation->unaffected_invoice_serie) && !is_null($operation->unaffected_invoice_number) && !is_null($operation->unaffected_invoice_url)) {
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'La operación ya tiene su factura registrada'
                    ]
                ]);
            }

            $operation->unaffected_invoice_serie = $request->serie;
            $operation->unaffected_invoice_number = $request->number;
            $operation->unaffected_invoice_url = $request->url;
            $operation->save();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'Información de factura actualizada exitosamente'
            ]
        ]);
    }
}
