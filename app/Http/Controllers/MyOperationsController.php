<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Operation;
use App\Models\OperationStatus;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

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

    public function operation_detail(Request $request, Operation $operation) {

        $operation->load('currency',
            'status'
        );

        return response()->json([
            'success' => true,
            'data' => [
                'operation' => $operation
            ]
        ]);

    }

}
