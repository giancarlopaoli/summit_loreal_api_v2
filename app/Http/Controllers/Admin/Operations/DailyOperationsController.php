<?php

namespace App\Http\Controllers\Admin\Operations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Client;
use App\Models\BankAccount;
use App\Models\EscrowAccount;
use App\Models\Operation;
use App\Models\OperationStatus;
use App\Models\OperationDocument;
use App\Models\Configuration;
use App\Models\Currency;
use App\Enums\BankAccountStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Enums;
use Illuminate\Support\Facades\Storage;

class DailyOperationsController extends Controller
{
    public function daily_operations(Request $request) {
        $val = Validator::make($request->all(), [
            'date' => 'date',
            'status' => 'required|in:Todas,Pendientes,Finalizadas'
        ]);
        if($val->fails()) return response()->json($val->messages());

        ########### Configuration ##################

        $date = isset($request->date) ? $request->date : Carbon::now()->format('Y-m-d');
        $month = Carbon::now()->month;
        $year = Carbon::now()->year;

        $pendientes = OperationStatus::wherein('name', ['Disponible','Pendiente envio fondos','Pendiente fondos contraparte','Contravalor recaudado','Fondos enviados'])->get()->pluck('id');
        $finalizadas = OperationStatus::wherein('name', ['Facturado','Finalizado sin factura', 'Pendiente facturar'])->get()->pluck('id');
        $todas = OperationStatus::get()->pluck('id');


        if($request->status == 'Pendientes'){
            $status = $pendientes;
            $status_str = "(op1.operation_status_id in (" . substr($pendientes, 1, Str::length($pendientes)-2) . ") or op2.operation_status_id in (" . substr($pendientes, 1, Str::length($pendientes)-2) . ") )";
        }
        elseif($request->status == 'Finalizadas'){
            $status = $finalizadas;
            $status_str = "(op1.operation_status_id in (" . substr($finalizadas, 1, Str::length($finalizadas)-2) . ") and op2.operation_status_id in (" . substr($finalizadas, 1, Str::length($finalizadas)-2) . ") )";
        }
        else{
            $status = $todas;
            $status_str = "(1)";
        }

        #############################################

        $indicators = Operation::selectRaw("coalesce(sum(amount),0) as total_amount, count(id) as num_operations")
            ->selectRaw("(select sum(op1.amount) from operations op1 where month(op1.operation_date) = $month and year(op1.operation_date) = $year and op1.operation_status_id in (" . substr($finalizadas, 1, Str::length($finalizadas)-2) . ")) as monthly_amount")
            ->selectRaw("(select count(op1.amount) from operations op1 where month(op1.operation_date) = $month and year(op1.operation_date) = $year and op1.operation_status_id in (" . substr($finalizadas, 1, Str::length($finalizadas)-2) . ")) as monthly_operations")
            ->whereRaw("date(operation_date) = '$date'")
            ->whereIn('operation_status_id', $finalizadas)
            ->get();

        $graphs = Operation::
            selectRaw("day(operation_date) as dia, sum(amount) as amount, count(amount) as num_operations")
            ->selectRaw("(select sum(amount) from operations as op2 where month(op2.operation_date) = month(CURRENT_TIMESTAMP) and year(op2.operation_date) = year(CURRENT_TIMESTAMP) and day(op2.operation_date) <= day(operations.operation_date) and operation_status_id in (" . substr($finalizadas, 1, Str::length($finalizadas)-2) . ") and type in ('Compra','Venta')) as accumulated_amount")

            ->selectRaw("(select sum(amount) from operations as op2 where month(op2.operation_date) = month(CURRENT_TIMESTAMP) and year(op2.operation_date) = year(CURRENT_TIMESTAMP) and day(op2.operation_date) <= day(operations.operation_date) and operation_status_id in (" . substr($finalizadas, 1, Str::length($finalizadas)-2) . ") and type in ('Compra','Venta')) as accumulated_num_operations")

            ->whereIn('operation_status_id', $finalizadas)
            ->whereIn('type', ['Compra', 'Venta'])
            ->whereRaw('month(operation_date) = month(CURRENT_TIMESTAMP) and year(operation_date) = year(CURRENT_TIMESTAMP) ')
            ->groupByRaw("day(operation_date)")
            ->orderByRaw('day(operation_date)')
            ->get();

        $pending_operations = Operation::select('id','code','class','type','client_id','user_id','amount','currency_id','exchange_rate','comission_spread','comission_amount','igv','spread','operation_status_id','post','operation_date')
            ->selectRaw("if(type = 'Interbancaria', round(amount + round(amount * spread/10000, 2 ), 2), round(amount * exchange_rate, 2)) as conversion_amount")
            ->selectRaw("if(type = 'Compra', round(exchange_rate + comission_spread/10000, 4), if(type = 'Venta', round(exchange_rate - comission_spread/10000, 4), round(exchange_rate * (1 + spread/10000),4))) as final_exchange_rate")
            ->selectRaw("if(type = 'Compra', round(round(amount * exchange_rate, 2) + comission_amount + igv, 2), if(type = 'Venta', round(round(amount * exchange_rate, 2) - comission_amount - igv, 2), round(amount + round(amount * spread/10000, 2 ) + comission_amount + igv, 2)) ) as counter_value")
            ->selectRaw("if(type = 'Interbancaria', round(amount * spread/10000, 2 ) , null ) as financial_expenses")
            ->whereIn('operation_status_id', OperationStatus::wherein('name', ['Disponible','Cancelado'])->get()->pluck('id'))
            ->whereRaw("date(operation_date) = '$date'")
            ->with('client:id,name,last_name,mothers_name,customer_type,type')
            ->with('currency:id,name:sign')
            ->with('status:id,name')
            ->with('bank_accounts:id,bank_id,currency_id,account_number,cci_number')
            ->with('bank_accounts.currency:id,name,sign')
            ->with('bank_accounts.bank:id,name,shortname,image')
            ->with('escrow_accounts:id,bank_id,currency_id,account_number,cci_number')
            ->with('escrow_accounts.currency:id,name,sign')
            ->with('escrow_accounts.bank:id,name,shortname,image')
            ->with('documents:id,operation_id,type')
            ->get();

        $matched_operations = DB::table('operation_matches')
            ->select('operation_id', 'matched_id')
            ->join('operations as op1', 'op1.id', "=", "operation_matches.operation_id")
            ->join('operations as op2', 'op2.id', "=", "operation_matches.matched_id")
            ->whereRaw("date(operation_matches.created_at) = '$date'")
            ->whereRaw("$status_str")
            ->get();


        $matched_operations->each(function ($item, $key) {

            $item->created_operation = Operation::where('id',$item->operation_id)
                ->select('operations.id','code','class','type','client_id','user_id','amount','currency_id','exchange_rate','comission_spread','comission_amount','igv','spread','operation_status_id','post','operation_date','funds_confirmation_date', 'sign_date', 'mail_instructions', 'invoice_url')
                ->selectRaw("if(type = 'Interbancaria', round(amount + round(amount * spread/10000, 2 ), 2), round(amount * exchange_rate, 2)) as conversion_amount")
                ->selectRaw("if(type = 'Compra', round(exchange_rate + comission_spread/10000, 4), if(type = 'Venta', round(exchange_rate - comission_spread/10000, 4), round(exchange_rate * (1 + spread/10000),4))) as final_exchange_rate")
                ->selectRaw("if(type = 'Compra', round(round(amount * exchange_rate, 2) + comission_amount + igv, 2), if(type = 'Venta', round(round(amount * exchange_rate, 2) - comission_amount - igv, 2), round(amount + round(amount * spread/10000, 2 ) + comission_amount + igv, 2)) ) as counter_value")
                ->selectRaw("if(type = 'Interbancaria', round(amount * spread/10000, 2 ) , null ) as financial_expenses")
                ->with('status:id,name')
                ->with('client:id,name,last_name,mothers_name,customer_type,type')
                ->with('currency:id,name:sign')
                ->with('bank_accounts:id,bank_id,currency_id,account_number,cci_number')
                ->with('bank_accounts.bank:id,name,shortname,image')
                ->with('bank_accounts.currency:id,name,sign')
                ->with('escrow_accounts:id,bank_id,currency_id,account_number,cci_number')
                ->with('escrow_accounts.currency:id,name,sign')
                ->with('escrow_accounts.bank:id,name,shortname,image')
                ->with('documents:id,operation_id,type')
                ->first();

            $item->matched_operation = Operation::where('id',$item->matched_id)
                ->select('operations.id','code','class','type','client_id','user_id','amount','currency_id','exchange_rate','comission_spread','comission_amount','igv','spread','operation_status_id','post','operation_date','funds_confirmation_date', 'sign_date', 'mail_instructions', 'invoice_url')
                ->selectRaw("if(type = 'Interbancaria', round(amount + round(amount * spread/10000, 2 ), 2), round(amount * exchange_rate, 2)) as conversion_amount")
                ->selectRaw("if(type = 'Compra', round(exchange_rate + comission_spread/10000, 4), if(type = 'Venta', round(exchange_rate - comission_spread/10000, 4), round(exchange_rate * (1 + spread/10000),4))) as final_exchange_rate")
                ->selectRaw("if(type = 'Compra', round(round(amount * exchange_rate, 2) + comission_amount + igv, 2), if(type = 'Venta', round(round(amount * exchange_rate, 2) - comission_amount - igv, 2), round(amount + round(amount * spread/10000, 2 ) + comission_amount + igv, 2)) ) as counter_value")
                ->selectRaw("if(type = 'Interbancaria', round(amount * spread/10000, 2 ) , null ) as financial_expenses")
                ->with('status:id,name')
                ->with('client:id,name,last_name,mothers_name,customer_type,type')
                ->with('currency:id,name:sign')
                ->with('bank_accounts:id,bank_id,currency_id,account_number,cci_number')
                ->with('bank_accounts.currency:id,name,sign')
                ->with('bank_accounts.bank:id,name,shortname,image')
                ->with('escrow_accounts:id,bank_id,currency_id,account_number,cci_number')
                ->with('escrow_accounts.currency:id,name,sign')
                ->with('escrow_accounts.bank:id,name,shortname,image')
                ->with('documents:id,operation_id,type')
                ->first();
        });


        return response()->json([
            'success' => true,
            'data' => [
                //'status' =>  $status,
                'indicators' => $indicators,
                'graphs' => $graphs,
                'pending_operations' => $pending_operations,
                'matched_operations' => $matched_operations,
            ]
        ]);

    }


    public function vendor_list(Request $request) {

        return response()->json([
            'success' => true,
            'data' => [
                'vendors' => Client::select('id','name','last_name','type')->where('type', 'PL')->get()
            ]
        ]);
    }

    public function match_operation(Request $request, Operation $operation) {
        $val = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id'
        ]);
        if($val->fails()) return response()->json($val->messages());

        ####### Validating operation is not previusly matched ##########
        $operation_match = DB::table('operation_matches')
            ->where("operation_id", $operation->id)
            ->get();

        if($operation_match->count() > 0) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'La operación ya se encuentra emparejada'
                ]
            ], 404);
        }

        ######### Creating vendor operation #############

        $op_code = Carbon::now()->format('YmdHisv') . rand(0,9);
        $status_id = OperationStatus::where('name', 'Pendiente envio fondos')->first()->id;

        // Calculando detracción
        $detraction_percentage = Configuration::where('shortname', 'DETRACTION')->first()->value;
        $detraction_amount = 0;

        $matched_operation = Operation::create([
            'code' => $op_code,
            'class' => Enums\OperationClass::Inmediata,
            'type' => ($operation->type == "Compra") ? 'Venta' : ($operation->type == "Venta" ? 'Compra' : 'Interbancaria'),
            'client_id' => $request->client_id,
            'user_id' => auth()->id(),
            'amount' => $operation->amount,
            'currency_id' => $operation->currency_id,
            'exchange_rate' => $operation->exchange_rate,
            'comission_spread' => 0,
            'comission_amount' => 0,
            'detraction_amount' => $detraction_amount,
            'detraction_percentage' => $detraction_percentage,
            'igv' => 0,
            'spread' => ($operation->type == "Interbancaria") ? $operation->spread : 0,
            'operation_status_id' => $status_id,
            'operation_date' => Carbon::now(),
            'post' => false
        ]);

        if($matched_operation){
            foreach ($operation->bank_accounts as $bank_account_data) {
                
                $escrow_account = EscrowAccount::where('bank_id',$bank_account_data->bank_id)
                    ->where('currency_id', $bank_account_data->currency_id)
                    ->first();

                if(!is_null($escrow_account)){
                    $matched_operation->escrow_accounts()->attach($escrow_account->id, [
                        'amount' => $bank_account_data->pivot->amount + $bank_account_data->pivot->comission_amount,
                        'comission_amount' => 0,
                        'created_at' => Carbon::now()
                    ]);
                }
                else{
                    return response()->json([
                        'success' => false,
                        'errors' => [
                            'Error en cuenta bancaria'
                        ]
                    ], 404);
                }
            }

            foreach ($operation->escrow_accounts as $escrow_account_data) {
                
                $bank_account = BankAccount::where('bank_id',$escrow_account_data->bank_id)
                    ->where('client_id', $request->client_id)
                    ->where('currency_id', $escrow_account_data->currency_id)
                    ->first();

                if(!is_null($bank_account)){
                    $matched_operation->bank_accounts()->attach($bank_account->id, [
                        'amount' => $escrow_account_data->pivot->amount - $escrow_account_data->pivot->comission_amount,
                        'comission_amount' => 0,
                        'created_at' => Carbon::now()
                    ]);
                }
                else{
                    return response()->json([
                        'success' => false,
                        'errors' => [
                            'Error en cuenta bancaria'
                        ]
                    ], 404);
                }

            }

            $operations_matches = $operation->matches()->attach($matched_operation->id, ['created_at' => Carbon::now()]);

            $operation->operation_status_id = $status_id;
            $operation->save();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'Operación emparejada exitosamente'
            ]
        ]);
    }

    public function cancel(Request $request, Operation $operation) {

        $operation->operation_status_id = OperationStatus::where('name', 'Cancelado')->first()->id;
        $operation->canceled_at = Carbon::now();
        $operation->save();

        return response()->json([
            'success' => true,
            'data' => [
                'operation' => $operation
            ]
        ]);
    }

    public function confirm_funds(Request $request, Operation $operation) {

        if( $operation->operation_status_id != OperationStatus::where('name', 'Pendiente envio fondos')->first()->id){
            return response()->json([
                'success' => false,
                'errors' => [
                    'La operación no se encuentra en estado Pendiente envio fondos'
                ]
            ], 404);
        }

        if($operation->matches->count() > 0) { // Si es operación creadora
            if($operation->matches[0]->operation_status_id == OperationStatus::where('name', 'Pendiente envio fondos')->first()->id){
                $operation->operation_status_id = OperationStatus::where('name', 'Pendiente fondos contraparte')->first()->id;
                $operation->funds_confirmation_date = Carbon::now();
                $operation->save();
            }
            elseif($operation->matches[0]->operation_status_id == OperationStatus::where('name', 'Pendiente fondos contraparte')->first()->id){
                $operation->operation_status_id = OperationStatus::where('name', 'Contravalor recaudado')->first()->id;
                $operation->funds_confirmation_date = Carbon::now();
                $operation->save();

                $operation->matches[0]->operation_status_id = OperationStatus::where('name', 'Contravalor recaudado')->first()->id;
                $operation->matches[0]->save();
            }
            else{
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'Error en el estado de la operación emparejadora'
                    ]
                ], 404);
            }
        }
        elseif ($operation->matched_operation->count() > 0) { // Si es operación emparejadora
            if($operation->matched_operation[0]->operation_status_id == OperationStatus::where('name', 'Pendiente envio fondos')->first()->id){
                $operation->operation_status_id = OperationStatus::where('name', 'Pendiente fondos contraparte')->first()->id;
                $operation->funds_confirmation_date = Carbon::now();
                $operation->save();
            }
            elseif($operation->matched_operation[0]->operation_status_id == OperationStatus::where('name', 'Pendiente fondos contraparte')->first()->id){
                
                if($operation->client->type == 'PL'){
                    $operation->operation_status_id = OperationStatus::where('name', 'Fondos enviados')->first()->id;
                    $operation->deposit_date = Carbon::now();
                }
                else{
                    $operation->operation_status_id = OperationStatus::where('name', 'Contravalor recaudado')->first()->id;
                    $operation->funds_confirmation_date = Carbon::now();
                }
                
                
                $operation->save();

                $operation->matched_operation[0]->operation_status_id = OperationStatus::where('name', 'Contravalor recaudado')->first()->id;
                $operation->matched_operation[0]->save();
            }
            else{
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'Error en el estado de la operación emparejadora'
                    ]
                ], 404);
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'operation' => $operation
            ]
        ]);
    }

    public function upload_voucher(Request $request) {
        $val = Validator::make($request->all(), [
            'operation_id' => 'required|exists:operations,id',
            'file' => 'required|file'
        ]);
        if($val->fails()) return response()->json($val->messages());


        logger('Archivo adjunto: DailyOperationsController@upload_voucher', ["operation_id" => $request->operation_id]);

        if($request->hasFile('file')){
            $file = $request->file('file');
            $path = env('AWS_ENV').'/operations/';

            try {
                $extension = strrpos($file->getClientOriginalName(), ".")? (Str::substr($file->getClientOriginalName(), strrpos($file->getClientOriginalName(), ".") , Str::length($file->getClientOriginalName()) -strrpos($file->getClientOriginalName(), ".") +1)): "";
                
                $now = Carbon::now();
                $filename = md5($now->toDateTimeString().$file->getClientOriginalName()).$extension;
            } catch (\Exception $e) {
                $filename = $file->getClientOriginalName();
            }


            try {
                $s3 = Storage::disk('s3')->putFileAs($path, $file, $filename);

                // eliminando cualquier comprobante anterior
                $delete = OperationDocument::where('operation_id', $request->operation_id)
                    ->where('type', Enums\DocumentType::Comprobante,)
                    ->delete();
                $insert = OperationDocument::create([
                    'operation_id' => $request->operation_id,
                    'type' => Enums\DocumentType::Comprobante,
                    'document_name' => $filename
                ]);

            } catch (\Exception $e) {
                // Registrando el el log los datos ingresados
                logger('ERROR: archivo adjunto: DailyOperationsController@upload_voucher', ["error" => $e]);
            }


            return response()->json([
                'success' => true,
                'data' => [
                    'Archivo agregado'
                ]
            ]);

        } else{
            return response()->json([
                'success' => false,
                'errors' => 'Error en el archivo adjunto',
            ]);
        }

    }

    public function to_pending_funds(Request $request, Operation $operation) {

        $operation->operation_status_id = OperationStatus::where('name', 'Pendiente envio fondos')->first()->id;
        $operation->save();

        return response()->json([
            'success' => true,
            'data' => [
                'operation' => $operation
            ]
        ]);
    }


    public function invoice(Request $request, Operation $operation) {

        $configurations = new Configuration();

        if($operation->operation_status_id == OperationStatus::where('name', 'Facturado')->first()->id){
            return response()->json([
                'success' => false,
                'errors' => [
                    'La operación ya se encuentra facturada'
                ]
            ]);
        }

        if($operation->client->customer_type == 'PJ'){
            $client_name = $operation->client->name;
        }
        else{
            $client_name = $operation->client->name . ' ' . $operation->client->last_name . ' ' . $operation->client->mothers_name;
        }

        try{

            $data = array(
                "operacion"                         => "generar_comprobante",
                "tipo_de_comprobante"               => ($operation->client->customer_type == 'PJ') ? 1 : 2,
                "serie"                             => (($operation->client->customer_type == 'PJ') ? 'F' : 'B') .'001',
                "numero"                            => "",
                "sunat_transaction"                 => "1",
                "cliente_tipo_de_documento"         => ($operation->client->document_type->name == 'RUC') ? 6 : ($operation->client->document_type->name == 'DNI' ? 1 : ($operation->client->document_type->name == 'Carné de extranjería' ? 4 : null)),
                "cliente_numero_de_documento"       => $operation->client->document_number,
                "cliente_denominacion"              => ucfirst($client_name),
                "cliente_direccion"                 => $operation->client->address,
                "cliente_email"                     => env('MAIL_OPS'),
                "cliente_email_1"                   => null,
                "cliente_email_2"                   => null,
                "fecha_de_emision"                  => Carbon::now()->format('d-m-Y'),
                "fecha_de_vencimiento"              => Carbon::now()->format('d-m-Y'),
                "moneda"                            => ($operation->type == 'Interbancaria') ? $operation->currency_id : 1,
                "tipo_de_cambio"                    => ($operation->type == 'Interbancaria' && $operation->currency_id == 2) ? $operation->exchange_rate : "",
                "porcentaje_de_igv"                 => $configurations->get_value('IGV'),
                "descuento_global"                  => "",
                "descuento_global"                  => "",
                "total_descuento"                   => "",
                "total_anticipo"                    => "",
                "total_gravada"                     => round($operation->comission_amount, 2),
                "total_inafecta"                    => "",
                "total_exonerada"                   => "",
                "total_igv"                         => round($operation->igv, 2),
                "total_gratuita"                    => "",
                "total_otros_cargos"                => "",
                "total"                             => round($operation->comission_amount + $operation->igv, 2),
                "percepcion_tipo"                   => "",
                "percepcion_base_imponible"         => "",
                "total_percepcion"                  => "",
                "total_incluido_percepcion"         => "",
                "detraccion"                        => "false",
                "observaciones"                     => "",
                "documento_que_se_modifica_tipo"    => "",
                "documento_que_se_modifica_serie"   => "",
                "documento_que_se_modifica_numero"  => "",
                "tipo_de_nota_de_credito"           => "",
                "tipo_de_nota_de_debito"            => "",
                "enviar_automaticamente_a_la_sunat" => "true",
                "enviar_automaticamente_al_cliente" => "true",
                "codigo_unico"                      => $operation->code,
                "condiciones_de_pago"               => "CONTADO",
                "medio_de_pago"                     => "",
                "placa_vehiculo"                    => "",
                "orden_compra_servicio"             => "",
                "tabla_personalizada_codigo"        => "",
                "formato_de_pdf"                    => "A4",
                "items" => array(
                                
                    array(
                        "unidad_de_medida"          => "ZZ",
                        "codigo"                    => "001",
                        "descripcion"               => "SERVICIOS PLATAFORMA BILLEX (" . date("d-m-Y", strtotime($operation->operation_date)) . " - " . strtoupper($operation->type) . " DE " . strtoupper($operation->currency->name) . " " . $operation->currency->sign . $operation->amount . " - TC " . $operation->exchange_rate . " - " . $operation->code . ")",
                        "cantidad"                  => "1",
                        "valor_unitario"            => round($operation->comission_amount, 2),
                        "precio_unitario"           => round($operation->comission_amount + $operation->igv, 2),
                        "descuento"                 => "",
                        "subtotal"                  => round($operation->comission_amount, 2),
                        "tipo_de_igv"               => "1",
                        "igv"                       => round($operation->igv, 2),
                        "total"                     => round($operation->comission_amount + $operation->igv, 2),
                        "anticipo_regularizacion"   => "false",
                        "anticipo_serie"            => "",
                        "anticipo_documento_numero" => ""
                    )   
                )
            );

            // Executing Nubefact API
            $consulta = Http::withToken(env('NUBEFACT_TOKEN'))->post(env('NUBEFACT_URL'), $data);

            $rpta_json = json_decode($consulta);

            if(is_object($rpta_json)){
                if(isset($rpta_json->errors)){
                    logger('ERROR: archivo adjunto: DailyOperationsController@invoice', ["error" => $rpta_json]);

                    $operation->operation_status_id = OperationStatus::where('name', 'Pendiente facturar')->first()->id;
                    $operation->save();

                    return response()->json([
                        'success' => false,
                        'errors' => [
                            $rpta_json->errors
                        ]
                    ]);
                }
                else{
                    $operation->invoice_serie = $rpta_json->serie;
                    $operation->invoice_number = $rpta_json->numero;
                    $operation->invoice_url = $rpta_json->enlace;
                    $operation->operation_status_id = OperationStatus::where('name', 'Facturado')->first()->id;
                    $operation->save();

                    return response()->json([
                        'success' => true,
                        'data' => [
                            'Factura creada exitosamente'
                        ]
                    ]);
                }
            }

        } catch (\Exception $e) {
            // Registrando el el log los datos ingresados
            logger('ERROR: archivo adjunto: DailyOperationsController@invoice', ["error" => $e]);
        }

        return response()->json([
            'success' => false,
            'errors' => [
                'Ocurrió un error al facturar'
            ]
        ]);
    }
    
    public function download_file(Request $request) {
        $val = Validator::make($request->all(), [
            'operation_id' => 'required|exists:operations,id',
            'document_id' => 'required|exists:operation_documents,id'
        ]);
        if($val->fails()) return response()->json($val->messages());

        $document = OperationDocument::find($request->document_id)->where('operation_id', $request->operation_id)->first();

        if(is_null($document)){
            return response()->json([
                'success' => false,
                'data' => [
                    $document->document_name
                ]
            ]);
        }

        if (Storage::disk('s3')->exists(env('AWS_ENV').'/operations/' . $document->document_name)) {
            return Storage::disk('s3')->download(env('AWS_ENV').'/operations/' . $document->document_name);
        }
        else{
            return response()->json([
                'success' => false,
                'errors' => [
                    'Archivo no encontrado'
                ]
            ]);
        }

        return Storage::disk('s3')->download(env('AWS_ENV').'/operations/' . $document->name);
    }
    
    public function countervalue_list(Request $request) {

        $val = Validator::make($request->all(), [
            'date' => 'date'
        ]);
        if($val->fails()) return response()->json($val->messages());

        ########### Configuration ##################

        $date = isset($request->date) ? $request->date : Carbon::now()->format('Y-m-d');
        $month = Carbon::now()->month;
        $year = Carbon::now()->year;

        $status = substr(OperationStatus::wherein('name', ['Pendiente fondos contraparte', 'Contravalor recaudado'])->get()->pluck('id'), 1, Str::length(OperationStatus::wherein('name', ['Pendiente fondos contraparte', 'Contravalor recaudado'])->get()->pluck('id'))-2);

        $matched_operations = DB::table('operation_matches')
            ->select('operation_id', 'matched_id')
            ->join('operations as op1', 'op1.id', "=", "operation_matches.operation_id")
            ->join('operations as op2', 'op2.id', "=", "operation_matches.matched_id")
            ->whereRaw("date(operation_matches.created_at) = '$date'")
            ->whereRaw("(op1.operation_status_id in ($status) or op2.operation_status_id in ($status))")
            ->get();

        $matched_operations->each(function ($item, $key) {

            $item->created_operation = Operation::where('id',$item->operation_id)
                ->select('operations.id','code','class','type','client_id','user_id','amount','currency_id','exchange_rate','comission_spread','comission_amount','igv','spread','operation_status_id','post','operation_date','funds_confirmation_date', 'sign_date', 'mail_instructions', 'invoice_url')
                ->selectRaw("if(type = 'Interbancaria', round(amount + round(amount * spread/10000, 2 ), 2), round(amount * exchange_rate, 2)) as conversion_amount")
                ->selectRaw("if(type = 'Compra', round(exchange_rate + comission_spread/10000, 4), if(type = 'Venta', round(exchange_rate - comission_spread/10000, 4), round(exchange_rate * (1 + spread/10000),4))) as final_exchange_rate")
                ->selectRaw("if(type = 'Compra', round(round(amount * exchange_rate, 2) + comission_amount + igv, 2), if(type = 'Venta', round(round(amount * exchange_rate, 2) - comission_amount - igv, 2), round(amount + round(amount * spread/10000, 2 ) + comission_amount + igv, 2)) ) as counter_value")
                ->selectRaw("if(type = 'Interbancaria', round(amount * spread/10000, 2 ) , null ) as financial_expenses")
                ->with('status:id,name')
                ->with('client:id,name,last_name,mothers_name,customer_type,type')
                ->with('currency:id,name:sign')
                ->with('bank_accounts:id,bank_id,currency_id,account_number,cci_number')
                ->with('bank_accounts.bank:id,name,shortname,image')
                ->with('bank_accounts.currency:id,name,sign')
                ->with('escrow_accounts:id,bank_id,currency_id,account_number,cci_number')
                ->with('escrow_accounts.currency:id,name,sign')
                ->with('escrow_accounts.bank:id,name,shortname,image')
                ->with('documents:id,operation_id,type')
                ->first();

            $item->matched_operation = Operation::where('id',$item->matched_id)
                ->select('operations.id','code','class','type','client_id','user_id','amount','currency_id','exchange_rate','comission_spread','comission_amount','igv','spread','operation_status_id','post','operation_date','funds_confirmation_date', 'sign_date', 'mail_instructions', 'invoice_url')
                ->selectRaw("if(type = 'Interbancaria', round(amount + round(amount * spread/10000, 2 ), 2), round(amount * exchange_rate, 2)) as conversion_amount")
                ->selectRaw("if(type = 'Compra', round(exchange_rate + comission_spread/10000, 4), if(type = 'Venta', round(exchange_rate - comission_spread/10000, 4), round(exchange_rate * (1 + spread/10000),4))) as final_exchange_rate")
                ->selectRaw("if(type = 'Compra', round(round(amount * exchange_rate, 2) + comission_amount + igv, 2), if(type = 'Venta', round(round(amount * exchange_rate, 2) - comission_amount - igv, 2), round(amount + round(amount * spread/10000, 2 ) + comission_amount + igv, 2)) ) as counter_value")
                ->selectRaw("if(type = 'Interbancaria', round(amount * spread/10000, 2 ) , null ) as financial_expenses")
                ->with('status:id,name')
                ->with('client:id,name,last_name,mothers_name,customer_type,type')
                ->with('currency:id,name:sign')
                ->with('bank_accounts:id,bank_id,currency_id,account_number,cci_number')
                ->with('bank_accounts.currency:id,name,sign')
                ->with('bank_accounts.bank:id,name,shortname,image')
                ->with('escrow_accounts:id,bank_id,currency_id,account_number,cci_number')
                ->with('escrow_accounts.currency:id,name,sign')
                ->with('escrow_accounts.bank:id,name,shortname,image')
                ->with('documents:id,operation_id,type')
                ->first();
        });

        return response()->json([
            'success' => true,
            'data' => [
                'operation' => $matched_operations
            ]
        ]);
    }

    public function operation_sign(Request $request, Operation $operation) {
        $val = Validator::make($request->all(), [
            'sign' => 'required|in:1,2'
        ]);
        if($val->fails()) return response()->json($val->messages());

        /*$operation->operation_status_id = OperationStatus::where('name', 'Pendiente envio fondos')->first()->id;
        $operation->save();*/


        if($request->sign ==1 && $operation->operation_status_id != OperationStatus::wherein('name', ['Pendiente envio fondos'])->first()->id){
            return response()->json([
                'success' => false,
                'errors' => [
                    'La operación debe encontrarse en estado Pendiente envio fondos.'
                ]
            ]); 
        }
        else{
            
            // Enviar Correo()

            $operation->sign_date = Carbon::now();
            $operation->save();
        }

        if($request->sign == 2 && $operation->operation_status_id != OperationStatus::wherein('name', ['Contravalor recaudado'])->first()->id){
            return response()->json([
                'success' => false,
                'errors' => [
                    'La operación debe encontrarse en estado Contravalor recaudado.'
                ]
            ]);
        }
        else{
            
            // Enviar Correo()

            $operation->sign_date = Carbon::now();
            $operation->save();
        }


        return response()->json([
            'success' => true,
            'data' => [
                'Correo de firma enviado',
            ]
        ]);
    }

    public function close_operation(Request $request, Operation $operation) {

        if($operation->operation_status_id != OperationStatus::wherein('name', ['Fondos enviados'])->first()->id){
            return response()->json([
                'success' => false,
                'errors' => [
                    'La operación debe encontrarse en estado Fondos enviados.'
                ]
            ]); 
        }
        else{
            
            // Enviar Correo()

            $operation->operation_status_id = OperationStatus::where('name', 'Finalizado sin factura')->first()->id;
            $operation->funds_confirmation_date = Carbon::now();
            $operation->save();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'operation' => $operation
            ]
        ]);
    }

    public function vendor_instruction(Request $request, Operation $operation) {

        /**/

        if($operation->client->type == 'Cliente'){
            return response()->json([
                'success' => false,
                'errors' => [
                    'El cliente de la operación no es un Proveedor de Liquidez'
                ]
            ]);
        }


        // Enviar Correo()

        if(is_null($operation->mail_instructions)){
            $operation->mail_instructions = Carbon::now();
            $operation->save();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'Correo de instrucciones enviado exitosamente',
            ]
        ]);
    }

    // Actualización de parámetros de operación
    public function update(Request $request, Operation $operation) {
        $val = Validator::make($request->all(), [
            'field' => 'required|in:amount,comission_spread,exchange_rate',
            'value' => 'required|numeric'
        ]);
        if($val->fails()) return response()->json($val->messages());

        if(OperationStatus::wherein('name', ['Facturado', 'Finalizado sin factura','Pendiente facturar'])->pluck('id')->contains($operation->operation_status_id)){
            return response()->json([
                'success' => false,
                'errors' => [
                    'La operación no puede estar finalizada para ser editada',
                ]
            ]);
        }
        else{
            if($request->field == 'amount'){
                $total_comission = round($request->value * ($operation->comission_spread/10000), 2);

                $igv_percetage = Configuration::where('shortname', 'IGV')->first()->value / 100;
                $comission_amount = round($total_comission / (1+$igv_percetage), 2);

                $igv = round($total_comission - $comission_amount,2);

                $operation->amount = $request->value;
                $operation->comission_amount = $comission_amount;
                $operation->igv = $igv;
                $operation->save();
            }
            elseif($request->field == 'comission_spread'){
                $total_comission = round($operation->amount * $request->value/10000, 2);

                $igv_percetage = Configuration::where('shortname', 'IGV')->first()->value / 100;
                $comission_amount = round($total_comission / (1+$igv_percetage), 2);

                $igv = round($total_comission - $comission_amount,2);

                $operation->comission_spread = $request->value;
                $operation->comission_amount = $comission_amount;
                $operation->igv = $igv;
                $operation->save();
            }
            else{
                $operation->exchange_rate = $request->value;
                $operation->save();
            }   
        }

        return response()->json([
            'success' => true,
            'data' => [
                'operation' => $operation
            ]
        ]);
    }

    public function update_escrow_accounts(Request $request, Operation $operation) {
        $val = Validator::make($request->all(), [
            'escrow_accounts' => 'required|array'
        ]);
        if($val->fails()) return response()->json($val->messages());


        $soles_id = Currency::where('name', 'Soles')->first()->id;
        $dolares_id = Currency::where('name', 'Dolares')->first()->id;
        $total_amount_escrow = 0;
        $total_comission = round($operation->comission_amount + $operation->igv);

        //Validating Escrow Accounts
        $escrow_accounts = [];
        foreach ($request->escrow_accounts as $escrow_account_data) {
            $escrow_account = EscrowAccount::where('id', $escrow_account_data['id'])
                ->where('active', true)
                ->first();

            if(is_null($escrow_account)) {
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'La cuenta fideicomiso ' . $escrow_account_data['id'] . ' no es valida'
                    ]
                ]);
            }

            if($operation->type == 'Compra') {
                if($escrow_account->currency_id != $soles_id) {
                    return response()->json([
                        'success' => false,
                        'errors' => [
                            'La cuenta fideicomiso ' . $escrow_account->id . ' no tiene la divisa valida'
                        ]
                    ]);
                }

                if($escrow_account_data['amount'] >= $total_comission){
                    $escrow_account->comission_amount = $total_comission;
                    $total_comission = 0;
                }
                else{
                    $escrow_account->comission_amount = $escrow_account_data['amount'];
                    $total_comission = $total_comission -  $escrow_account_data['amount'];
                }

            } else {
                if($escrow_account->currency_id != $dolares_id) {
                    return response()->json([
                        'success' => false,
                        'errors' => [
                            'La cuenta fideicomiso ' . $escrow_account->id . ' no tiene la divisa valida'
                        ]
                    ]);
                }

                $escrow_account->comission_amount = 0;
            }

            $escrow_account->amount = $escrow_account_data['amount'];
            $total_amount_escrow += $escrow_account_data['amount'];
            $escrow_accounts[] = $escrow_account;
        }

        //Validating amounts in accounts
        if($operation->type == 'Compra') {
            $envia = round($operation->amount * $operation->exchange_rate + $operation->comission_amount + $operation->igv,2);
            $recibe = $operation->amount;
        } else {
            $envia = $operation->amount;
            $recibe = round($operation->amount * $operation->exchange_rate - $operation->comission_amount - $operation->igv,2);
        }

        if( $envia != $total_amount_escrow){
            return response()->json([
                'success' => false,
                'errors' => [
                    'La suma de montos enviados en las cuentas de fideicomiso es incorrecto = ' . $total_amount_escrow . '. Debería ser ' . $envia 
                ]
            ]);
        }   

        // Detaching old escrow accounts from operation
        $operation->escrow_accounts()->detach();

        // attaching new escrow accounts
        foreach ($escrow_accounts as $escrow_account_data) {
            $operation->escrow_accounts()->attach($escrow_account_data['id'], [
                'amount' => $escrow_account_data['amount'],
                'comission_amount' => $escrow_account_data['comission_amount']
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'operation' => $operation
            ]
        ]);
    }

    public function update_client_accounts(Request $request, Operation $operation) {
        $val = Validator::make($request->all(), [
            'bank_accounts' => 'required|array'
        ]);
        if($val->fails()) return response()->json($val->messages());


        //Validating Bank Accounts
        $soles_id = Currency::where('name', 'Soles')->first()->id;
        $dolares_id = Currency::where('name', 'Dolares')->first()->id;

        $bank_accounts = [];
        $total_amount_bank = 0;
        $total_comission = round($operation->comission_amount + $operation->igv);

        foreach ($request->bank_accounts as $bank_account_data) {
            $bank_account = BankAccount::where('id', $bank_account_data['id'])
                ->where('client_id', $operation->client_id)
                ->where('bank_account_status_id', BankAccountStatus::Activo)
                ->first();

            // Validating that the bank account is valid.
            if(is_null($bank_account)) {
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'Error en la cuenta bancaria id = ' . $bank_account_data['id']
                    ]
                ]);
            }

            if($operation->type == 'Compra') {
                if($bank_account->currency_id != $dolares_id) {
                    return response()->json([
                        'success' => false,
                        'errors' => [
                            'La cuenta bancaria ' . $bank_account->id . ' no tiene la divisa valida'
                        ]
                    ]);
                }

                $bank_account->comission_amount = 0 ;
            } else {
                if($bank_account->currency_id != $soles_id) {
                    return response()->json([
                        'success' => false,
                        'errors' => [
                            'La cuenta bancaria ' . $bank_account->id . ' no tiene la divisa valida'
                        ]
                    ]);
                }

                if($bank_account_data['amount'] >= $total_comission){
                    $bank_account->comission_amount = $total_comission;
                    $total_comission = 0;
                }
                else{
                    $bank_account->comission_amount = $bank_account_data['amount'];
                    $total_comission = $total_comission -  $bank_account_data['amount'];
                }
            }

            $bank_account->amount = $bank_account_data['amount'];
            $total_amount_bank += $bank_account_data['amount'];
            $bank_accounts[] = $bank_account;
        }

        //Validating amounts in accounts
        if($operation->type == 'Compra') {
            $envia = round($operation->amount * $operation->exchange_rate + $operation->comission_amount + $operation->igv,2);
            $recibe = $operation->amount;
        } else {
            $envia = $operation->amount;
            $recibe = round($operation->amount * $operation->exchange_rate - $operation->comission_amount - $operation->igv,2);
        }

        if( $recibe != $total_amount_bank){
            return response()->json([
                'success' => false,
                'errors' => [
                    'La suma de montos enviados en las cuentas bancarias del cliente es incorrecto = ' . $total_amount_bank . '. Debería ser ' . $recibe 
                ]
            ]);
        }

        // Detaching old client accounts from operation
        $operation->bank_accounts()->detach();

        // attaching new escrow accounts
        foreach ($bank_accounts as $bank_account_data) {
            $operation->bank_accounts()->attach($bank_account_data['id'], [
                'amount' => $bank_account_data['amount'],
                'comission_amount' => $bank_account_data['comission_amount']
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'operation' => $operation
            ]
        ]);
    }
}
