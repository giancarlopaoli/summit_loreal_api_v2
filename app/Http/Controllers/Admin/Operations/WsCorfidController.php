<?php

namespace App\Http\Controllers\Admin\Operations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Operation;
use App\Models\OperationDocument;

class WsCorfidController extends Controller
{
    // Registro cliente PJ WS Corfid
    public function register_operation(Request $request, Operation $operation) {
        
        // Error si la operación ya fue enviada con mensaje satisfactorio
        if($operation->corfid_id == 1) return response()->json(['success' => false, 'data' => 'La operación ya fue enviada a corfid']);


        if($operation->type == 'Compra' || $operation->type == 'Venta'){
            if($operation->client->type == 'Cliente' && $operation->matches[0]->client->type == 'Cliente'){
                $type = 3;
            }
            elseif($operation->client->type == 'Cliente' && $operation->matches[0]->client->type == 'PL'){
                $type = 4;
                $registro = WsCorfidController::operacion_type_4($request, $operation);
            }
        }
        elseif ($operation->type == 'Interbancaria') {
                $type = 6;
            // code...
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
    public function operacion_type_4(Request $request, Operation $operation) {

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
            "moret01" => ($operation->type == 'Compra') ? $operation->Monto : round(round($operation->amount*$operation->exchange_rate,2) - round($operation->comission_amount - $operation->igv, 2),2),

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

            if(isset($request->json)){
                return response()->json([
                    'success' => true,
                    'key' => $key,
                    'value' => $value,
                ]);
            }

            $deposit = array(
                "tmdep01" => ($operation->type == 'Compra') ? 1 : 2,
                "modep01" => $value->pivot->amount,
                "idcbdep01" => $operation->cuenta_fideicomiso->Corfid,
                "nrooperacion" => (!is_null($operation->NumeroTransferencia)) ? $operation->NumeroTransferencia : "na",
                "voucher" => "https://billex.pe",
                "mocoed01" => 0,
                "mocofd01" => ($operation->TipoOperacionId == 1) ? round($operation->Comision + $operation->IGVmonto, 2): 0
            );
            array_push($deposit_list,$deposit);
        }

            
        
        // Retribución
        /*$listadoRetribucion = array();

        $retribucion = array(
            "tmret01" => ($operation->TipoOperacionId == 1) ? 2 : 1,
            "moret01" => ($operation->TipoOperacionId == 2) ? round(round($operation->Monto*$operation->TipoCambio,2) - round($operation->Comision + $operation->IGVmonto, 2),2) : $operation->Monto,
            "idbret01" => $operation->cuenta_cliente->banco->Corfid,
            "ncret01" => $operation->cuenta_cliente->NroCuenta,
            "cciret01" => $operation->cuenta_cliente->CCI,
            "mocoer01" => 0,
            "mocofr01" => ($operation->TipoOperacionId == 2) ? round($operation->Comision + $operation->IGVmonto, 2): 0,
            "idcbret01" => $operation2->cuenta_fideicomiso->Corfid,
        );
        
        array_push($listadoRetribucion,$retribucion);*/

        $params["deposit_list"] = $deposit_list;
        //$params["listadoRetribucion"] = $listadoRetribucion;

        if(isset($request->json)){
            return response()->json([
                'success' => true,
                'params' => $params,
            ]);
        }

        /*$corfid = Http::withHeaders(['Authorization' => 'Basic '.env('TOKEN_WSCORFID')])->post(env('URL_WSCORFID').'/fintechWSOperacion/WSCFDOPE-01', $params);

        $rpta_json = json_decode($corfid);*/

        return response()->json([
            'success' => true,
            'data' => [
                'op tipo 4'
            ]
        ]);
    }


    // Registro Operaciones en WS Corfid
    public function wscorfidop(Request $request) {
        $val = Validator::make($request->all(), [
            'operation_id' => 'required|numeric'
        ]);
        if($val->fails()) return response()->json($val->messages());

        $operation = Operacion::
            //select('*')
            select('ClienteId','OperacionId','OperacionCodigo','Monto','TipoCambio','DivisaId','ClaseOperacionId','TipoOperacionId','FechaOperacion','ComisionPorcentaje','Comision','IGVmonto','CuentaClienteId','CuentafideicomisoId','NumeroTransferencia','EstadoCorfidId')
            ->where('OperacionId', $request->operation_id)
            ->with('cliente:ClienteId,NonmbresRazonSocial,TipodocumentoId,NroDocumento','cuenta_cliente:CuentasBancariasId,BancoId,NroCuenta,CCI','cuenta_cliente.banco:BancoId,Corfid','cuenta_fideicomiso:CuentaFideicomisoId,BancoId,NroCuenta,CCI,Corfid','cuenta_fideicomiso.banco:BancoId,Corfid')
            ->first();

        // Error si no se encuentra la operación
        if(is_null($operation)) return response()->json(['success' => false, 'data' => 'Operación no encontrada']);

        // Error si la operación ya fue enviada con mensaje satisfactorio
        if($operation->EstadoCorfidId == 1) return response()->json(['success' => false, 'data' => 'La operación ya fue enviada a corfid']);

        if($operation->cliente->count() == 0) {
            $clienteup = DB::table('Operacion')->where('OperacionId', $operation->OperacionId)->update([
                'EstadoCorfidId' => 3,
                'MensajeCorfi' => 'Información de cliente no encontrada'
            ]);
            return response()->json(['success' => false, 'data' => 'Información de cliente no encontrada']);
        }

        // Error si no se encuentra en la tabla de operaciones emparejadas
        $operationEmparejada = DB::table('OperacionEmparejar')->where('OperacionEmparejado',$request->operation_id)->first();

        if(is_null($operationEmparejada)) {
            $clienteup = DB::table('Operacion')->where('OperacionId', $operation->OperacionId)->update([
                'EstadoCorfidId' => 3,
                'MensajeCorfi' => 'No se encontró la operación emparejada'
            ]);
            return response()->json(['success' => false, 'data' => 'No se encontró la operación emparejada']);
        }

        // Obteniendo información de la operación contraparte
        $operation2 = Operacion::
            select('ClienteId','OperacionId','OperacionCodigo','Monto','CuentafideicomisoId')
            ->where('OperacionId', $operationEmparejada->OperacionEmparejador)
            ->with('cliente:ClienteId,NonmbresRazonSocial,TipodocumentoId,NroDocumento,TipoEmpresaId','cuenta_fideicomiso:CuentaFideicomisoId,BancoId,NroCuenta,CCI,Corfid')
            ->first();

        // Error si no se encuentra operación de contraparte
        if(is_null($operation2)) {
            $clienteup = DB::table('Operacion')->where('OperacionId', $operation->OperacionId)->update([
                'EstadoCorfidId' => 3,
                'MensajeCorfi' => 'Operación contraparte no encontrada'
            ]);
            return response()->json(['success' => false, 'data' => 'Operación contraparte no encontrada']);
        }

        // Error si el cliente contraparte es un proveedor de liquidez
        if($operation2->cliente->TipoEmpresaId == 2) {
            $clienteup = DB::table('Operacion')->where('OperacionId', $operation->OperacionId)->update([
                'EstadoCorfidId' => 3,
                'MensajeCorfi' => 'Operación contraparte es con proveedor de liquidez y debería ser cliente regular'
            ]);
            return response()->json(['success' => false, 'data' => 'Operación contraparte es con proveedor de liquidez y debería ser cliente regular']);
        }

        // Obteniendo archivo de voucher de transferencia desde S3
        $documento = DB::table('DocumentoOperacion')
            ->select('Nombre')
            ->where('OperacionId', $request->operation_id)
            ->first();

        // Error si no se encuentra documento
        if(is_null($documento)) {
            $clienteup = DB::table('Operacion')->where('OperacionId', $operation->OperacionId)->update([
                'EstadoCorfidId' => 3,
                'MensajeCorfi' => 'Voucher de operación no encontrado'
            ]);
            return response()->json(['success' => false, 'data' => 'Voucher de operación no encontrado']);
        }

        $url = env('APIBILLEX_URL').'/Json/ComprobantesPago?Nombre='.$documento->Nombre;

        $params = array(
            "coper01" => "3",
            "nref01" => $operation->OperacionCodigo, //substr($operation->OperacionCodigo,0,Str::length($operation->OperacionCodigo)),
            "urlvo01" => $url,
            "postidofo01" => "10", // origen de fondos
            "otrof01" => "otro origen de fondo", // origen de fondos

            "tope01" => ($operation->TipoOperacionId == 1) ? 'C' : 'V',
            "tmone01" => ($operation->TipoOperacionId == 1) ? 1 : 2,
            "mont01" => $operation->Monto,
            "tcamb01" => $operation->TipoCambio,

            //"tdocc01" => ($operation->cliente->TipodocumentoId == 1) ? 6 : ($operation->cliente->TipodocumentoId == 2 ? 1 : ($operation->cliente->TipodocumentoId == 3 ? 2 : ($operation->cliente->TipodocumentoId == 4 ? 9 : ($operation->cliente->TipodocumentoId == 9 ? 5 : ($operation->cliente->TipodocumentoId == 10 ? 8 : ($operation->cliente->TipodocumentoId == 11 ? 2 : $operation->cliente->TipodocumentoId == 12 ? 10 : 4)))))),
            "ndocc01" => $operation->cliente->NroDocumento,
            
            "tmdep01" => ($operation->TipoOperacionId == 1) ? 1 : 2,
            "modep01" => ($operation->TipoOperacionId == 1) ? round(round($operation->Monto*$operation->TipoCambio,2) + round($operation->Comision + $operation->IGVmonto, 2),2) : $operation->Monto,
            
            "tmret01" => ($operation->TipoOperacionId == 1) ? 2 : 1,
            "moret01" => ($operation->TipoOperacionId == 1) ? $operation->Monto : (round(round($operation->Monto*$operation->TipoCambio,2) - round($operation->Comision + $operation->IGVmonto, 2),2)),

            "nrefr01" => "",
            "tdefi01" => "",
            "ndefi01" => "",
            "nrcefi01" => "",
            "cciefi01" => "",

            "tmcoe01" => "0",
            "mocoe01" => "0",
            "tmcof01" => 1,
            "mocof01" => round($operation->Comision + $operation->IGVmonto, 2)
        );

        // Deposito
        $listadoDeposito = array();

        $deposito = array(
            "tmdep01" => ($operation->TipoOperacionId == 1) ? 1 : 2,
            "modep01" => ($operation->TipoOperacionId == 1) ? round(round($operation->Monto*$operation->TipoCambio,2) + round($operation->Comision + $operation->IGVmonto, 2),2) : $operation->Monto,
            "idcbdep01" => $operation->cuenta_fideicomiso->Corfid,
            "nrooperacion" => (!is_null($operation->NumeroTransferencia)) ? $operation->NumeroTransferencia : "na",
            "voucher" => "https://billex.pe",
            "mocoed01" => 0,
            "mocofd01" => ($operation->TipoOperacionId == 1) ? round($operation->Comision + $operation->IGVmonto, 2): 0
        );
        array_push($listadoDeposito,$deposito);
        
        // Retribución
        $listadoRetribucion = array();

        $retribucion = array(
            "tmret01" => ($operation->TipoOperacionId == 1) ? 2 : 1,
            "moret01" => ($operation->TipoOperacionId == 2) ? round(round($operation->Monto*$operation->TipoCambio,2) - round($operation->Comision + $operation->IGVmonto, 2),2) : $operation->Monto,
            "idbret01" => $operation->cuenta_cliente->banco->Corfid,
            "ncret01" => $operation->cuenta_cliente->NroCuenta,
            "cciret01" => $operation->cuenta_cliente->CCI,
            "mocoer01" => 0,
            "mocofr01" => ($operation->TipoOperacionId == 2) ? round($operation->Comision + $operation->IGVmonto, 2): 0,
            "idcbret01" => $operation2->cuenta_fideicomiso->Corfid,
        );
        
        array_push($listadoRetribucion,$retribucion);

        $params["listadoDeposito"] = $listadoDeposito;
        $params["listadoRetribucion"] = $listadoRetribucion;

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
                $opupdated = DB::table('Operacion')->where('OperacionId', $operation->OperacionId)->update([
                    'EstadoCorfidId' => $rpta_json->{'WSr-resultado'},
                    'MensajeCorfi' => $rpta_json->{'WSr-Mensaje'},
                    //'EstadoId' => 'FSF'
                ]);

                // si la respuesta fue satisfactoria
                if($rpta_json->{'WSr-resultado'} ==  1){
                    OperationsController::wscorfidopEmparejar($request->operation_id);
                }
            }
        }

        /*return response()->json([
            'success' => true,
            'data' => $rpta_json
        ]);*/

        return response()->json($rpta_json);
    }
}
