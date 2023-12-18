<?php

namespace App\Http\Controllers\Admin\Operations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Operation;
use App\Models\OperationDocument;
use Illuminate\Support\Facades\Http;

class WsCorfidController extends Controller
{
    // Registro cliente PJ WS Corfid
    public function register_operation(Request $request, Operation $operation) {
        
        // Error si la operación ya fue enviada con mensaje satisfactorio
        if($operation->corfid_id == 1) return response()->json(['success' => false, 'data' => 'La operación ya fue enviada a corfid']);

        if(($operation->type == 'Compra' || $operation->type == 'Venta') && $operation->matches->count() > 0){
            if($operation->client->type == 'Cliente' && $operation->matches[0]->client->type == 'Cliente'){
                $type = 3;
                $registro = WsCorfidController::operation_type_3($request, $operation);
            }
            elseif($operation->client->type == 'Cliente' && $operation->matches[0]->client->type == 'PL'){
                $type = 4;
                $registro = WsCorfidController::operation_type_4_6($request, $operation);
            }
        }
        elseif ($operation->type == 'Interbancaria' && $operation->matches->count() > 0) {
            $type = 6;
            $registro = WsCorfidController::operation_type_4_6($request, $operation);
        }
        else{
            return response()->json([
                'success' => false,
                'errors' => [
                    'Tipo de operación invalido'
                ]
            ]);
        }

        return response()->json(
            $registro->getData()
        );
    }

    // Registro Operaciones en WS Corfid
    ##### Operacion tipo 3 (Cliente con cliente)
    public function operation_type_3(Request $request, Operation $operation) {

        // Obteniendo archivo de voucher de transferencia
        $document = OperationDocument::select('id')
            ->where('operation_id', $operation->id)
            ->where('type', 'Comprobante')
            ->first();

        // Error si no se encuentra documento
        if(is_null($document)) {
            $client_update = Operation::where('id', $operation->id)->update([
                'corfid_id' => 3,
                'corfid_message' => 'Voucher de operación no encontrado'
            ]);
            return response()->json(['success' => false, 'errors' => ['Voucher de operación no encontrado']]);
        }

        $url = env('APP_URL').'/api/res/download-document-operation?operation_id='.$operation->id.'&document_id='.$document->id;

        $params = array(
            "coper01" => "3",
            "nref01" => $operation->code, //substr($operation->OperacionCodigo,0,Str::length($operation->OperacionCodigo)),
            "urlvo01" => $url,
            "postidofo01" => "10", // origen de fondos
            "otrof01" => "otro origen de fondo", // origen de fondos

            "tope01" => ($operation->type == 'Compra') ? 'C' : 'V',
            "tmone01" => ($operation->type == 'Compra') ? 1 : 2,
            "mont01" => $operation->amount,
            "tcamb01" => $operation->exchange_rate,

            "tdocc01" => $operation->client->document_type_id == 1 ? 6 : ($operation->client->document_type_id == 2 ? 1 : 
                ($operation->client->document_type_id == 3 ? 2 : ($operation->client->document_type_id == 4 ? 9 : 
                ($operation->client->document_type_id == 9 ? 5 : ($operation->client->document_type_id == 10 ? 8 : 
                ($operation->client->document_type_id == 11 ? 2 : ($operation->client->document_type_id == 12 ? 10 : 4))))))) ,
            "ndocc01" => $operation->client->document_number,
            
            "tmdep01" => ($operation->type == 'Compra') ? 1 : 2,
            "modep01" => ($operation->type == 'Compra') ? round(round($operation->amount*$operation->exchange_rate,2) + round($operation->comission_amount + $operation->igv, 2),2) : $operation->amount,
            
            "tmret01" => ($operation->type == 'Compra') ? 2 : 1,
            "moret01" => ($operation->type == 'Compra') ? $operation->amount : round(round($operation->amount*$operation->exchange_rate,2) - round($operation->comission_amount - $operation->igv, 2),2),

            "nrefr01" => "",
            "tdefi01" => "",
            "ndefi01" => "",
            "nrcefi01" => "",
            "cciefi01" => "",

            "tmcoe01" => "0",
            "mocoe01" => "0",
            "tmcof01" => 1,
            "mocof01" => round($operation->comission_amount + $operation->igv, 2)
        );

        // Deposito
        $deposit_list = array();

        foreach ($operation->escrow_accounts as $key => $value) {

            $deposit = array(
                "tmdep01" => ($operation->type == 'Compra') ? 1 : 2,
                "modep01" => $value->pivot->amount,
                "idcbdep01" => $value->corfid_id,
                "nrooperacion" => (!is_null($operation->transfer_number)) ? $operation->transfer_number : "na",
                "voucher" => $url,
                "mocoed01" => 0,
                "mocofd01" => $value->pivot->comission_amount,
            );
            array_push($deposit_list, $deposit);
        }

        
        // Retribución
        $retribution_list = array();

        foreach ($operation->bank_accounts as $key => $value) {

            $banco_pago_al_cliente = null;

            if($operation->matches[0]->escrow_accounts->count() == 1){
                $banco_pago_al_cliente = $operation->matches[0]->escrow_accounts[0]->corfid_id;
            }
            else{
                foreach ($operation->matches[0]->escrow_accounts as $key1 => $value1) {
                    
                    if(($value->pivot->amount + $value->pivot->comission_amount) == ($value1->pivot->amount + $value1->pivot->comission_amount)){
                        $banco_pago_al_cliente = $value1->corfid_id;
                        break;
                    }
                }
            }

            $retribution = array(
                "tmret01" => ($operation->type == 'Compra') ? 2 : 1,
                "moret01" => $value->pivot->amount,
                "idbret01" => $value->bank->corfid_id,
                "ncret01" => $value->account_number,
                "cciret01" => $value->cci_number,
                "mocoer01" => 0,
                "mocofr01" => $value->pivot->comission_amount,
                "idcbret01" => $banco_pago_al_cliente,
            );
            
            array_push($retribution_list, $retribution);
        }

        $params["listadoDeposito"] = $deposit_list;
        $params["listadoRetribucion"] = $retribution_list;

        if(isset($request->json)){

            $match = WsCorfidController::match_operation_type_3($operation, $request->json);

            return response()->json([
                'success' => true,
                'params' => $params,
                'match' => $match->getData()
            ]);
        }

        $corfid = Http::withHeaders(['Authorization' => 'Basic '.env('TOKEN_WSCORFID')])->post(env('URL_WSCORFID').'/fintechWSOperacion/WSCFDOPE-01', $params);

        $rpta_json = json_decode($corfid);

        if(is_object($rpta_json)){
            if(isset($rpta_json->{'WSr-resultado'})){

                $operation->corfid_id = $rpta_json->{'WSr-resultado'};
                $operation->corfid_message = $rpta_json->{'WSr-Mensaje'};
                $operation->save();

                // si la respuesta fue satisfactoria
                if($rpta_json->{'WSr-resultado'} ==  1){
                    WsCorfidController::match_operation_type_3($operation);
                }
            }
        }

        return response()->json($rpta_json);
    }

    public function match_operation_type_3(Operation $operation, $json=null) {
        
        if($operation->matches->count() == 0) return response()->json(['success' => false, 'data' => 'Operación no encontrada']);

        // Error si la operación ya fue enviada con mensaje satisfactorio
        if($operation->matches[0]->corfid_id == 1) return response()->json(['success' => false, 'data' => 'La operación ya fue enviada a corfid']);

        // Obteniendo archivo de voucher de transferencia
        $document = OperationDocument::select('id')
            ->where('operation_id', $operation->id)
            ->where('type', 'Comprobante')
            ->first();

        // Error si no se encuentra documento
        if(is_null($document)) {
            $client_update = Operation::where('id', $operation->id)->update([
                'corfid_id' => 3,
                'corfid_message' => 'Voucher de operación no encontrado'
            ]);
            return response()->json(['success' => false, 'errors' => ['Voucher de operación no encontrado']]);
        }

         $url = env('APP_URL').'/api/res/download-document-operation?operation_id='.$operation->id.'&document_id='.$document->id;

        $params = array(
            "coper01" => "3",
            "nref01" => $operation->matches[0]->code,
            "urlvo01" => $url,
            "postidofo01" => "10", // origen de fondos
            "otrof01" => "otro origen de fondo", // origen de fondos

            "tope01" => ($operation->matches[0]->type == 'Compra') ? 'C' : 'V',
            "tmone01" => ($operation->matches[0]->type == 'Compra') ? 1 : 2,
            "mont01" => $operation->matches[0]->amount,
            "tcamb01" => $operation->matches[0]->exchange_rate,

            "tdocc01" => $operation->matches[0]->client->document_type_id == 1 ? 6 : ($operation->matches[0]->client->document_type_id == 2 ? 1 : 
                ($operation->matches[0]->client->document_type_id == 3 ? 2 : ($operation->matches[0]->client->document_type_id == 4 ? 9 : 
                ($operation->matches[0]->client->document_type_id == 9 ? 5 : ($operation->matches[0]->client->document_type_id == 10 ? 8 : 
                ($operation->matches[0]->client->document_type_id == 11 ? 2 : ($operation->matches[0]->client->document_type_id == 12 ? 10 : 4))))))) ,
            "ndocc01" => $operation->matches[0]->client->document_number,
            
            "tmdep01" => ($operation->matches[0]->type == 'Compra') ? 1 : 2,
            "modep01" => ($operation->matches[0]->type == 'Compra') ? round(round($operation->matches[0]->amount*$operation->matches[0]->exchange_rate,2) + round($operation->matches[0]->comission_amount + $operation->matches[0]->igv, 2),2) : $operation->matches[0]->amount,
            
            "tmret01" => ($operation->matches[0]->type == 'Compra') ? 2 : 1,
            "moret01" => ($operation->matches[0]->type == 'Compra') ? $operation->matches[0]->amount : round(round($operation->matches[0]->amount*$operation->matches[0]->exchange_rate,2) - round($operation->matches[0]->comission_amount - $operation->matches[0]->igv, 2),2),
            
            "nrefr01" => $operation->code,
            "tdefi01" => "",
            "ndefi01" => "",
            "nrcefi01" => "",
            "cciefi01" => "",

            "tmcoe01" => "0",
            "mocoe01" => "0",
            "tmcof01" => 1,
            "mocof01" => round($operation->matches[0]->comission_amount + $operation->matches[0]->igv, 2)
        );

        // Deposito
        $deposit_list = array();

        foreach ($operation->matches[0]->escrow_accounts as $key => $value) {

            $deposit = array(
                "tmdep01" => ($operation->matches[0]->type == 'Compra') ? 1 : 2,
                "modep01" => $value->pivot->amount,
                "idcbdep01" => $value->corfid_id,
                "nrooperacion" => (!is_null($operation->matches[0]->transfer_number)) ? $operation->matches[0]->transfer_number : "na",
                "voucher" => $url,
                "mocoed01" => 0,
                "mocofd01" => $value->pivot->comission_amount,
            );
            array_push($deposit_list, $deposit);
        }

        
        // Retribución
        $retribution_list = array();

        foreach ($operation->matches[0]->bank_accounts as $key => $value) {

            $banco_pago_al_cliente = null;

            if($operation->escrow_accounts->count() == 1){
                $banco_pago_al_cliente = $operation->escrow_accounts[0]->corfid_id;
            }
            else{
                foreach ($operation->escrow_accounts as $key1 => $value1) {
                    
                    if(($value->pivot->amount + $value->pivot->comission_amount) == ($value1->pivot->amount + $value1->pivot->comission_amount)){
                        $banco_pago_al_cliente = $value1->corfid_id;
                        break;
                    }
                }
            }

            $retribution = array(
                "tmret01" => ($operation->matches[0]->type == 'Compra') ? 2 : 1,
                "moret01" => $value->pivot->amount,
                "idbret01" => $value->bank->corfid_id,
                "ncret01" => $value->account_number,
                "cciret01" => $value->cci_number,
                "mocoer01" => 0,
                "mocofr01" => $value->pivot->comission_amount,
                "idcbret01" => $banco_pago_al_cliente,
            );
            
            array_push($retribution_list, $retribution);
        }

        $params["listadoDeposito"] = $deposit_list;
        $params["listadoRetribucion"] = $retribution_list;

        return response()->json([
            'success' => true,
            'data' => $params
        ]);

        /*
        $corfid = Http::withHeaders(['Authorization' => 'Basic '.env('TOKEN_WSCORFID')])->post(env('URL_WSCORFID').'/fintechWSOperacion/WSCFDOPE-01', $params);

        $rpta_json = json_decode($corfid);

        if(is_object($rpta_json)){
            if(isset($rpta_json->{'WSr-resultado'})){
                $opupdated = DB::table('Operacion')->where('OperacionId', $operacion->OperacionId)->update([
                    'EstadoCorfidId' => $rpta_json->{'WSr-resultado'},
                    'MensajeCorfi' => $rpta_json->{'WSr-Mensaje'},
                    //'EstadoId' => 'FSF'
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $rpta_json
        ]);*/
    }


    ##### Operacion tipo 4 (Cliente con PL)
    public function operation_type_4_6(Request $request, Operation $operation) {
        
        // Obteniendo archivo de voucher de transferencia
        $document = OperationDocument::select('id')
            ->where('operation_id', $operation->id)
            ->where('type', 'Comprobante')
            ->first();

        // Error si no se encuentra documento
        if(is_null($document)) {
            $client_update = Operation::where('id', $operation->id)->update([
                'corfid_id' => 3,
                'corfid_message' => 'Voucher de operación no encontrado'
            ]);
            return response()->json(['success' => false, 'errors' => ['Voucher de operación no encontrado']]);
        }

        $url = env('APP_URL').'/api/res/download-document-operation?operation_id='.$operation->id.'&document_id='.$document->id;

        $gastos_financieros = ($operation->type == 'Interbancaria') ? (round($operation->amount * $operation->spread,2)) : 0;

        $params = array(
            "coper01" => ($operation->type == 'Interbancaria') ? "6" : "4",
            "nref01" => $operation->code,
            "urlvo01" => $url,
            "postidofo01" => "10", // origen de fondos
            "otrof01" => "otro origen de fondo", // origen de fondos

            // Operación
            "tope01" => ($operation->type == 'Compra') ? 'C' : ($operation->type == 'Venta' ? 'V' : 'T'),
            "tmone01" => ($operation->type == 'Compra') ? 1 : ($operation->type == 'Venta' ? 2 : $operation->currency_id),
            "mont01" => $operation->amount,
            "tcamb01" => $operation->exchange_rate,

            // Cliente
            "tdocc01" => $operation->client->document_type_id == 1 ? 6 : ($operation->client->document_type_id == 2 ? 1 : 
                ($operation->client->document_type_id == 3 ? 2 : ($operation->client->document_type_id == 4 ? 9 : 
                ($operation->client->document_type_id == 9 ? 5 : ($operation->client->document_type_id == 10 ? 8 : 
                ($operation->client->document_type_id == 11 ? 2 : ($operation->client->document_type_id == 12 ? 10 : 4))))))) ,
            "ndocc01" => $operation->client->document_number,
            
            //Depósito
            "tmdep01" => ($operation->type == 'Compra') ? 1 : (($operation->type == 'Venta') ? 2 : $operation->currency_id),
            "modep01" => ($operation->type == 'Compra') ? round(round($operation->amount*$operation->exchange_rate,2) + round($operation->comission_amount + $operation->igv, 2),2) : round(($operation->type == 'Venta') ? $operation->amount : ($operation->amount + round($operation->amount*$operation->spread,2) + round($operation->comission_amount,2)),2 ),
            
            // Retribución
            "tmret01" => ($operation->type == 'Compra') ? 2 : (($operation->type == 'Venta') ? 1 : $operation->currency_id),
            "moret01" => ($operation->type == 'Compra' || $operation->type == 'Interbancaria') ? $operation->amount : (round(round($operation->amount*$operation->exchange_rate,2) - round($operation->comission_amount + $operation->igv, 2),2)),

            "nrefr01" => "",
            "tdefi01" => "6",
            "ndefi01" => $operation->matches[0]->client->document_number, //"20379902996",
            "nrcefi01" => $operation->matches[0]->bank_accounts[0]->account_number,
            "cciefi01" => $operation->matches[0]->bank_accounts[0]->cci_number,

            "tmcoe01" => ($operation->type == 'Interbancaria') ? $operation->currency_id : 0,
            "mocoe01" => $gastos_financieros,
            "tmcof01" => ($operation->type == 'Interbancaria') ? $operation->currency_id : 1,
            "mocof01" => round($operation->comission_amount + $operation->igv, 2)
        );

        // Deposito
        $deposit_list = array();

        foreach ($operation->escrow_accounts as $key => $value) {

            $deposit = array(
                "tmdep01" => ($operation->type == 'Compra') ? 1 : ($operation->type == 'Venta' ? 2 : $operation->currency_id),
                "modep01" => $value->pivot->amount,
                "idcbdep01" => $value->corfid_id,
                "nrooperacion" => (!is_null($operation->transfer_number)) ? $operation->transfer_number : "na",
                "voucher" => $url,
                "mocoed01" => ($operation->type == 'Interbancaria') ? $gastos_financieros : 0,
                "mocofd01" => $value->pivot->comission_amount,
            );
            array_push($deposit_list, $deposit);
        }

        
        // Retribución
        $retribution_list = array();

        foreach ($operation->bank_accounts as $key => $value) {

            $banco_pago_al_cliente = null;

            if($operation->matches[0]->escrow_accounts->count() == 1){
                $banco_pago_al_cliente = $operation->matches[0]->escrow_accounts[0]->corfid_id;
            }
            else{
                foreach ($operation->matches[0]->escrow_accounts as $key1 => $value1) {
                    
                    if(($value->pivot->amount + $value->pivot->comission_amount) == ($value1->pivot->amount + $value1->pivot->comission_amount)){
                        $banco_pago_al_cliente = $value1->corfid_id;
                        break;
                    }
                }
            }

            $retribution = array(
                "tmret01" => ($operation->type == 'Compra') ? 2 : ($operation->type == 'Venta' ? 1 : $operation->currency_id),
                "moret01" => $value->pivot->amount,
                "idbret01" => $value->bank->corfid_id,
                "ncret01" => $value->account_number,
                "cciret01" => $value->cci_number,
                "mocoer01" => $operation->currency_id,
                "mocofr01" => $value->pivot->comission_amount,
                "idcbret01" => $banco_pago_al_cliente,
            );
            
            array_push($retribution_list, $retribution);
        }

        $params["listadoDeposito"] = $deposit_list;
        $params["listadoRetribucion"] = $retribution_list;


        if(isset($request->json)){
            return response()->json([
                'success' => true,
                'params' => $params,
            ]);
        }

        $corfid = Http::withHeaders(['Authorization' => 'Basic '.env('TOKEN_WSCORFID')])->post(env('URL_WSCORFID').'/fintechWSOperacion/WSCFDOPE-01', $params);

        $rpta_json = json_decode($corfid);

        if(is_object($rpta_json)){
            if(isset($rpta_json->{'WSr-resultado'})){

                $operation->corfid_id = $rpta_json->{'WSr-resultado'};
                $operation->corfid_message = $rpta_json->{'WSr-Mensaje'};
                $operation->save();

                // si la respuesta fue satisfactoria
                /*if($rpta_json->{'WSr-resultado'} ==  1){
                    OperationsController::wscorfidopEmparejar($request->operation_id);
                }*/
            }
        }
    
        return response()->json($rpta_json);
    }

}
