<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;

class MyOperationsController extends Controller
{
    public function list_my_operations(Request $request) {

         $client = Client::find($request->client_id);

         if($client == null) {
             return response()->json([
                 'success' => false,
                 'errors' => 'El client no existe'
             ], 404);
         }

         $ops = $client->operations()->where('operation_status_id', $request->status)->get();

         $ops->load('currency',
             'status'
         );

         return response()->json([
             'success' => true,
             'data' => [
                 'operations' => $ops
             ]
         ]);
    }

    public function operation_detail(Request $request, $operation_id) {

        $client = Client::find($request->client_id);
        if($client == null) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'Cliente no encontrado'
                ]
            ], 404);
        }

        $operation = $client->operations()
            ->select('id','code','class','type','user_id','amount','currency_id','exchange_rate','comission_amount','igv','operation_status_id','transfer_number','invoice_url','coupon_id','coupon_code','coupon_type','coupon_value','operation_date','funds_confirmation_date','deposit_date')
            ->where('id', $operation_id)
            ->first();

        if($operation == null) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'La operacion no es parte del cliente'
                ]
            ], 404);
        }

        $operation->load(
            'currency:id,name,sign',
            'status:id,name'
        );

        return response()->json([
            'success' => true,
            'data' => [
                'operation' => $operation
            ]
        ]);

    }

}
