<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\OperationStatus;
use Illuminate\Http\Request;
use App\Enums;
use Illuminate\Support\Facades\Validator;

class MyOperationsController extends Controller
{
    public function list_my_operations(Request $request) {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|integer',
            'status' => 'required|in:inprogress,finished,expired,canceled'
        ]);

        if ($validator->fails()) return $validator->errors()->toJson();

        if($request->status == 'inprogress'){
            $status = OperationStatus::wherein('name', ['Disponible','Pendiente envio fondos','Pendiente fondos contraparte','Contravalor recaudado','Fondos enviados'])->get()->pluck('id');
        }
        elseif ($request->status == 'finished') {
            $status = OperationStatus::wherein('name', ['Facturado','Finalizado sin factura', 'Pendiente facturar'])->get()->pluck('id');
        }
        elseif ($request->status == 'expired') {
            $status = OperationStatus::wherein('name', ['Expirado'])->get()->pluck('id');
        }
        elseif ($request->status == 'canceled') {
            $status = OperationStatus::wherein('name', ['Cancelado'])->get()->pluck('id');
        }
        else {
            $status = OperationStatus::get()->pluck('id');
        }

        $client = Client::find($request->client_id);

        if($client == null) {
            return response()->json([
                'success' => false,
                'errors' => 'El cliente no existe'
            ], 404);
         }

        $ops = $client->operations()->whereIn('operation_status_id', $status)
            ->orderByDesc('operation_date')
            ->get();

        $ops->load(
            'client:id,name,last_name,mothers_name,customer_type,type',
            'currency:id,name,sign',
            'status:id,name',
            'bank_accounts:id,bank_id,currency_id,account_number,cci_number',
            'bank_accounts.currency:id,name,sign',
            'bank_accounts.bank:id,name,shortname,image',
            'escrow_accounts:id,bank_id,account_number,cci_number,currency_id',
            'escrow_accounts.currency:id,name,sign',
            'escrow_accounts.bank:id,name,shortname,image',
            'vendor_bank_accounts:id,bank_id,currency_id,account_number,cci_number',
            'vendor_bank_accounts.currency:id,name,sign',
            'vendor_bank_accounts.bank:id,name,shortname,image',
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
            ->select('id','client_id','code','class','type','user_id','use_escrow_account','amount','currency_id','exchange_rate','comission_amount','igv','operation_status_id','transfer_number','invoice_url','coupon_id','coupon_code','coupon_type','coupon_value','operation_date','funds_confirmation_date','deposit_date','spread','comission_spread','canceled_at')
            ->selectRaw("if(type = 3, round(amount + round(amount * spread/10000, 2), 2), round(amount * exchange_rate, 2)) as conversion_amount")
            ->where('code', $operation_id)
            ->where('client_id', $request->client_id)
            ->first();

        if($operation == null) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'La operacion no es parte del cliente'
                ]
            ], 404);
        }

        // custom fields for Buying operations
        if($operation->type == Enums\OperationType::Compra){
            $operation->final_exchange_rate = round($operation->exchange_rate + $operation->comission_spread/10000, 4);

            $operation->counter_value = round(round($operation->amount * $operation->exchange_rate, 2) + $operation->comission_amount + $operation->igv, 2);
        }

        // custom fields for Selling operations
        if($operation->type == Enums\OperationType::Venta){
            $operation->final_exchange_rate = round($operation->exchange_rate - $operation->comission_spread/10000, 4);

            $operation->counter_value = round(round($operation->amount * $operation->exchange_rate, 2) - $operation->comission_amount - $operation->igv, 2);
        }

        // custom fields for interbank operations
        if($operation->type == Enums\OperationType::Interbancaria){
            $operation->selling_exchange_rate = round($operation->exchange_rate + $operation->spread/10000,4);

            $comission_pl = round($operation->amount * $operation->spread/10000, 2);
            
            $operation->sends = round($operation->amount + $comission_pl + $operation->comission_amount + $operation->igv, 2);

            $operation->counter_value = round($operation->amount + $comission_pl, 2);
        }

        $operation->load(
            'client:id,name,last_name,mothers_name,customer_type,type',
            'currency:id,name,sign',
            'status:id,name',
            'bank_accounts:id,client_id,bank_id,currency_id,account_number,cci_number',
            'bank_accounts.currency:id,name,sign',
            'bank_accounts.bank:id,name,shortname,image',
            'escrow_accounts:id,bank_id,account_number,cci_number,currency_id',
            'escrow_accounts.currency:id,name,sign',
            'escrow_accounts.bank:id,name,shortname,image',
            'vendor_bank_accounts:id,client_id,bank_id,currency_id,account_number,cci_number',
            'vendor_bank_accounts.currency:id,name,sign',
            'vendor_bank_accounts.bank:id,name,shortname,image',
            'documents:id,operation_id,type',
            
        );

        return response()->json([
            'success' => true,
            'data' => [
                'operation' => $operation
            ]
        ]);

    }

}
