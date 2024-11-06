<?php

namespace App\Http\Controllers\Admin\Operations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Client;
use App\Models\Operation;
use App\Models\EscrowAccount;
use App\Models\OperationDocument;
use App\Models\Representative;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class WsCorfidController extends Controller
{
    // Registro de operaciones
    public function register_operation(Request $request, Operation $operation) {
        
        if($request->json != "true"){
            // Error si la operación ya fue enviada con mensaje satisfactorio
            if($operation->corfid_id == 1) return response()->json(['success' => false, 'data' => 'La operación ya fue enviada a corfid']);

            // Si el error es que ya existe una operación con ese código, se registra 
            if($operation->corfid_id == 2 && $operation->corfid_message = 'nref01 (Numero de Referencia) ya Existe como operacion'){

                $operation->corfid_id = 1;
                $operation->save();

                return response()->json([
                    'success' => true,
                    'errors' => [
                        'La operación ya se había enviado, se actualizó estado.'
                    ]
                ]);
            }
        }
        
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

            /*if($operation->matches[0]->escrow_accounts->count() == 1){
                $banco_pago_al_cliente = $operation->matches[0]->escrow_accounts[0]->corfid_id;
            }
            else{
                foreach ($operation->matches[0]->escrow_accounts as $key1 => $value1) {
                    
                    if(($value->pivot->amount + $value->pivot->comission_amount) == ($value1->pivot->amount + $value1->pivot->comission_amount)){
                        $banco_pago_al_cliente = $value1->corfid_id;
                        break;
                    }
                }
            }*/

            $escrow_account_operation = DB::table('escrow_account_operation')
                ->where('id', $value->pivot->escrow_account_operation_id);

            if($escrow_account_operation->count() == 1){
                $banco_pago_al_cliente = EscrowAccount::where('id', $escrow_account_operation->first()->escrow_account_id)->first()->corfid_id;
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

        if($request->json == "true"){

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

                return response()->json([
                    'success' => true,
                    'data' => [
                        $rpta_json->{'WSr-Mensaje'}
                    ]
                ]);
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

            /*if($operation->escrow_accounts->count() == 1){
                $banco_pago_al_cliente = $operation->escrow_accounts[0]->corfid_id;
            }
            else{
                foreach ($operation->escrow_accounts as $key1 => $value1) {
                    
                    if(($value->pivot->amount + $value->pivot->comission_amount) == ($value1->pivot->amount + $value1->pivot->comission_amount)){
                        $banco_pago_al_cliente = $value1->corfid_id;
                        break;
                    }
                }
            }*/

            $escrow_account_operation = DB::table('escrow_account_operation')
                ->where('id', $value->pivot->escrow_account_operation_id);

            if($escrow_account_operation->count() == 1){
                $banco_pago_al_cliente = EscrowAccount::where('id', $escrow_account_operation->first()->escrow_account_id)->first()->corfid_id;
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

        $corfid = Http::withHeaders(['Authorization' => 'Basic '.env('TOKEN_WSCORFID')])->post(env('URL_WSCORFID').'/fintechWSOperacion/WSCFDOPE-01', $params);

        $rpta_json = json_decode($corfid);

        if(is_object($rpta_json)){
            if(isset($rpta_json->{'WSr-resultado'})){

                $operation->corfid_id = $rpta_json->{'WSr-resultado'};
                $operation->corfid_message = $rpta_json->{'WSr-Mensaje'};
                $operation->save();

                return response()->json([
                    'success' => true,
                    'data' => [
                        $rpta_json->{'WSr-Mensaje'}
                    ]
                ]);
            }
        }
    
        return response()->json($rpta_json);
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

        $gastos_financieros = ($operation->type == 'Interbancaria') ? round(round($operation->amount/$operation->exchange_rate * ($operation->exchange_rate+ $operation->spread/10000),2) - $operation->amount,2) : 0;

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
            "modep01" => ($operation->type == 'Compra') ? round(round($operation->amount*$operation->exchange_rate,2) + round($operation->comission_amount + $operation->igv, 2),2) : (($operation->type == 'Venta') ? round($operation->amount,2) : (round($operation->amount/$operation->exchange_rate*($operation->exchange_rate + $operation->spread/10000),2) + round($operation->comission_amount,2) + $operation->igv)),
            
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

            $escrow_account_operation = DB::table('escrow_account_operation')
                ->where('id', $value->pivot->escrow_account_operation_id);

            if($escrow_account_operation->count() == 1){
                $banco_pago_al_cliente = EscrowAccount::where('id', $escrow_account_operation->first()->escrow_account_id)->first()->corfid_id;
            }

            /*if($operation->matches[0]->escrow_accounts->count() == 1){
                $banco_pago_al_cliente = $operation->matches[0]->escrow_accounts[0]->corfid_id;
            }
            else{
                foreach ($operation->matches[0]->escrow_accounts as $key1 => $value1) {
                    
                    if(($value->pivot->amount + $value->pivot->comission_amount) == ($value1->pivot->amount + $value1->pivot->comission_amount)){
                        $banco_pago_al_cliente = $value1->corfid_id;
                        break;
                    }
                }
            }*/

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


        if($request->json == "true"){
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

                return response()->json([
                    'success' => true,
                    'data' => [
                        $rpta_json->{'WSr-Mensaje'}
                    ]
                ]);
            }
        }
    
        return response()->json($rpta_json);
    }



    ################# Registro de clientes ##########
    
    // Registro clientes WS Corfid
    public function register_client(Request $request, Client $client) {
        
        // Error si el cliente ya fue registrado
        if($client->corfid_id == 1) return response()->json(['success' => false, 'data' => 'El cliente ya se encuentra registrado en corfid']);

        if($client->customer_type == 'PJ'){
            $registro = WsCorfidController::register_client_company($request, $client);
        }
        else{
            $registro = WsCorfidController::register_client_person($request, $client);
        }

        return response()->json(
            $registro->getData()
        );
    }

    public function register_client_company(Request $request, Client $client) {

        $fondos = explode(';', $client->funds_source);

        $fondoscod = "";
        $codigo = "";

        foreach ($fondos as $key => $value) {
            if($value == "Fondos propios" || $value == "Fondos propios / Recursos propios") $codigo = 2;
            if($value == "Venta de activos") $codigo = 4;
            if($value == "Intereses y rendimiento") $codigo = 7;
            if($value == "Financiamiento") $codigo = 8;
            if($value == "Resultados acumulados") $codigo = 9;
            if($value == "Dividendos y participaciones") $codigo = 6;
            if($value == "Otros" || $value == "Otros (especifique)") $codigo = 12;

            if($codigo != "") $fondoscod .= $codigo.",";
            $codigo = "";
        }

        if($fondoscod != "") $fondoscod = substr($fondoscod,0,Str::length($fondoscod)-1);
        else {
            $client->corfid_id = 3;
            $client->corfid_message = 'Origen de fondos inválido';
            $client->save();

            return response()->json(['success' => false, 'data' => ['Origen de fondos inválido']]);
        }

        $url = ($client->documents->count()>0)  ? env('APP_URL').'/api/res/download-document-register/'.$client->id.'?document_id='.$client->documents[0]->id : null;


        $params = array(
            "tpers01" => ($client->type == 'Cliente') ? "2" : "3",
            "tdocu01" => ($client->document_type_id == 1) ? '6' : null,
            "ndocu01" => $client->document_number,
            "rsoci01" => $client->name,
            "idact01" => (!is_null($client->economic_activity_id)) ? $client->economic_activity->code : null,
            "fcons01" => $client->birthdate,
            "direc01" => $client->address,
            "telef01" => $client->phone,
            "paisd01" => (!is_null($client->country_id)) ? $client->country->prefix : null,
            "ubige01" => (!is_null($client->district_id)) ? $client->district->ubigeo : null,
            "idofo01" => $fondoscod,
            "urlfr01" => $url
        );


        $representatives = $client->representatives;
        $business_associates = $client->business_associates;

        $listadoAccionista = array();
        $listadoRepresentantes = array();

        // Rep Legal
        foreach ($representatives as $key => $value) {
            $repr = array(
                "tdocu01" => ($value->document_type_id == 1) ? 6 : ($value->document_type_id == 2 ? 1 : ($value->document_type_id == 3 ? 2 : ($value->document_type_id == 4 ? 9 : ($value->document_type_id == 9 ? 5 : ($value->document_type_id == 10 ? 8 : ($value->document_type_id == 11 ? 2 : 4)))))),
                "ndocu01" => $value->document_number,
                "nombr01" => $value->names,
                "apate01" => $value->last_name,
                "amate01" => $value->mothers_name
            );

            array_push($listadoRepresentantes, $repr);
        }

        // Socios
        foreach ($business_associates as $key => $value) {

            $socio = array(
                "tpers01" => (in_array($value->document_type_id, array(2,3,9))) ? 1 : 2,
                "tdocu01" => ($value->document_type_id == 1) ? 6 : ($value->document_type_id == 2 ? 1 : ($value->document_type_id == 3 ? 3 : ($value->document_type_id == 4 ? 9 : ($value->document_type_id == 9 ? 5 : ($value->document_type_id == 10 ? 8 : ($value->document_type_id == 11 ? 2 : 4)))))),
                "ndocu01" => $value->document_number,
                
                "espep01" => $value->pep
            );

            if($socio['tpers01'] == 1){
                $socio['nombr01'] = $value->names;
                $socio['apate01'] = $value->last_name;
                $socio['amate01'] = $value->mothers_name;
            }
            elseif($socio['tpers01'] == 2){
                $socio['rsoci01'] = $value->names;
            }

            if($socio['espep01'] == 1){
                $socio['insti01'] = $value->pep_company;
                $socio['cargo01'] = $value->pep_position;
            }

            array_push($listadoAccionista,$socio);
        }

        $params["listadoAccionista"] = $listadoAccionista;
        $params["listadoRepresentantes"] = $listadoRepresentantes;

        if($request->json == "true"){
            return response()->json([
                'success' => true,
                'params' => $params,
            ]);
        }

        ############### Envio servicio a Corfid ########################
        $corfid = Http::withHeaders(['Authorization' => 'Basic '.env('TOKEN_WSCORFID')])->post(env('URL_WSCORFID').'/fintechWS/WSCFDADM-02', $params);

        $rpta_json = json_decode($corfid);

        if(is_object($rpta_json)){
            if(isset($rpta_json->{'strCodMensaje'})){

                $client->corfid_id = $rpta_json->{'strCodMensaje'};
                $client->corfid_message = $rpta_json->strMensaje;
                $client->save();
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                $rpta_json->strMensaje
            ]
        ]);

    }

    public function register_client_person(Request $request, Client $client) {

        $fondos = explode(';', $client->funds_source);

        $fondoscod = "";
        $codigo = "";

        foreach ($fondos as $key => $value) {
            if($value == "Fondos propios" || $value == "Fondos propios / Recursos propios") $codigo = 2;
            if($value == "Venta de activos") $codigo = 4;
            if($value == "Intereses y rendimiento") $codigo = 7;
            if($value == "Financiamiento") $codigo = 8;
            if($value == "Resultados acumulados") $codigo = 9;
            if($value == "Dividendos y participaciones") $codigo = 6;
            if($value == "Otros" || $value == "Otros (especifique)") $codigo = 12;

            if($codigo != "") $fondoscod .= $codigo.",";
            $codigo = "";
        }

        if($fondoscod != "") $fondoscod = substr($fondoscod,0,Str::length($fondoscod)-1);
        else {

            $client->corfid_id = 3;
            $client->corfid_message = 'Origen de fondos inválido';
            $client->save();

            return response()->json(['success' => false, 'data' => ['Origen de fondos inválido']]);
        }

        $url = ($client->documents->count()>0) ? env('APP_URL').'/api/res/download-document-register/'.$client->id.'?document_id='.$client->documents[0]->id : null;

        $params = array(
            "tpers01" => "1",
            "nombr01" => $client->name,
            "apate01" => $client->last_name,
            "amate01" => $client->mothers_name,
            "tdocu01" => ($client->document_type_id == 2) ? '1' : (($client->document_type_id == 3) ? '2' : null),
            "ndocu01" => $client->document_number,
            "fnaci01" => $client->birthdate,
            "nacio01" => (!is_null($client->country_id)) ? $client->country->prefix : null,
            "direc01" => $client->address,
            "idocu01" => (!is_null($client->profession_id)) ? $client->profession->id : null,
            "espep01" => $client->pep,
            "urldj01" => $url,
            "idofo01" => $fondoscod,
            "paisd01" => "PE",
            "ubige01" => (!is_null($client->district_id)) ? $client->district->ubigeo : null,
        );

        if($params['espep01'] == 1){
            $params['insti01'] = $value->pep_company;
            $params['cargo01'] = $value->pep_position;
        }

        if($request->json == "true"){
            return response()->json([
                'success' => true,
                'params' => $params,
            ]);
        }

        ############### Envio servicio a Corfid ########################
        $corfid = Http::withHeaders(['Authorization' => 'Basic '.env('TOKEN_WSCORFID')])->post(env('URL_WSCORFID').'/fintechWS/WSCFDADM-01', $params);

        $rpta_json = json_decode($corfid);

        if(is_object($rpta_json)){
            if(isset($rpta_json->{'strCodMensaje'})){

                $client->corfid_id = $rpta_json->{'strCodMensaje'};
                $client->corfid_message = $rpta_json->strMensaje;
                $client->save();
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                $rpta_json->strMensaje
            ]
        ]);
    }

    // API confirmación de Operación por parte de Corfid
    public function confirm_operation_corfid(Request $request) {
        $val = Validator::make($request->all(), [
            'nref01' => 'required|string',
            'estado' => 'required|in:APROBADO,ANULADO,EXTORNADO',
            'mensaje' => 'nullable|string'
        ]);
        logger('Confirmación de operación Corfid: confirm_operation_corfid@WsCorfidController', ["success" => $request->all()]);
        
        if($val->fails()) return response()->json($val->messages());


        try {
            $operation = Operation::where('code', $request->nref01)->get();

            if($operation->count() == 0){
                return response()->json([
                    'success' => false,
                    'errortype' => 1,
                    'fecha' => Carbon::now()->toDateTimeString(),
                    'msg' => "No se encontró la operación con código ".$request->nref01
                ]);
            }

            $operation = $operation->first();

            if($request->estado == "APROBADO"){
                $operation->corfid_id = 1;
                $operation->corfid_message = 'Operación aprobada';
                $operation->save();
            }
            elseif($request->estado == "ANULADO") {
                $operation->corfid_id = 2;
                $operation->corfid_message = (!is_null($request->mensaje)) ? $request->mensaje : 'Operación anulada';
                $operation->save();
            }
            elseif($request->estado == "EXTORNADO") {
                $operation->corfid_id = 3;
                $operation->corfid_message = (!is_null($request->mensaje)) ? $request->mensaje : 'Operación extornada';
                $operation->save();
            }

        } catch (\Exception $e) {
            logger('Confirmación de operación Corfid: confirm_operation_corfid@WsCorfidController', ["error" => $e]);

            return response()->json([
                'success' => false,
                'errortype' => 99,
                'fecha' => Carbon::now()->toDateTimeString(),
                'msg' => "Se encontró un error al tratar de confirmar la operación "
            ]);
        }

        return response()->json([
            'success' => true,
            'fecha' => Carbon::now()->toDateTimeString(),
            'msg' => "Operación " .$request->estado . " exitosamente"
        ]);
    }

    ##### Reporte masivo corfid
    public function reporte_ws_corfid(Request $request, Operation $operation) {

        $operations = Operation::with('bank_accounts')
            ->where('operation_date', '>=','2024-07-01')
            ->where('operation_date', '<=','2024-08-01')
            ->whereIn('operation_status_id', [6,7])
            ->whereNotIn('client_id', Client::select('id')->where('type','PL')->get())
            ->get();

        $ops = array();

        // Retribución
        
        foreach ($operations as $operation) {

            $retribution_list = array();
            foreach ($operation->bank_accounts as $key => $value) {

                $banco_pago_al_cliente = null;
                try{
                    /*if($operation->matches[0]->escrow_accounts->count() == 1){
                        $banco_pago_al_cliente = $operation->matches[0]->escrow_accounts[0]->corfid_id;
                    }
                    else{
                        foreach ($operation->matches[0]->escrow_accounts as $key1 => $value1) {
                            
                            if(($value->pivot->amount + $value->pivot->comission_amount) == ($value1->pivot->amount + $value1->pivot->comission_amount)){
                                $banco_pago_al_cliente = $value1->corfid_id;
                                break;
                            }
                        }
                    }*/

                    $escrow_account_operation = DB::table('escrow_account_operation')
                        ->where('id', $value->pivot->escrow_account_operation_id);

                    if($escrow_account_operation->count() == 1){
                        $banco_pago_al_cliente = EscrowAccount::where('id', $escrow_account_operation->first()->escrow_account_id)->first()->corfid_id;
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
                } catch (\Exception $e) {

                }
                    
            }

            $op = array(
                "nref01" => $operation->code,
                "tope01" => ($operation->type == 'Compra') ? 'C' : ($operation->type == 'Venta' ? 'V' : 'T'),
                "tmone01" => ($operation->type == 'Compra') ? 1 : ($operation->type == 'Venta' ? 2 : $operation->currency_id),
                "mont01" => $operation->amount,
                "tcamb01" => $operation->exchange_rate,
            );

            $op['retribution_list'] = array();

            array_push($op['retribution_list'], $retribution_list);

            array_push($ops, $op);
        }

        return response()->json([
            'operations' => $ops
        ]);

    }
}
