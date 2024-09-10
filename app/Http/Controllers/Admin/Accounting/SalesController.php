<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Models\Client;
use App\Models\Operation;
use App\Models\OperationDocument;
use App\Models\OperationHistory;
use App\Models\Sale;
use Carbon\Carbon;
use App\Enums;
use Illuminate\Support\Facades\Mail;
use App\Mail\SelfDetraction;
use App\Models\Configuration;

class SalesController extends Controller
{
    //New Sale
    public function new_sale(Request $request) {
        $val = Validator::make($request->all(), [
            'type' => 'required|in:afecta,inafecta',
            'client_id' => 'required|exists:clients,id',
            'currency_id' => 'required|exists:currencies,id',
            'invoice_issue_date' => 'required|date',
            'invoice_due_date' => 'required|date',
            'exchange_rate' => 'nullable|numeric',
            'detail' => 'required|array'
        ]);
        if($val->fails()) return response()->json($val->messages());

        $configurations = new Configuration();

        $amount = 0;
        $igv = 0;
        $ipm = 0;
        $detraction_amount = 0;

        foreach ($request->detail as $line) {
            $amount += $line['quantity'] * $line['unit_amount'];
            $igv += $line['quantity'] * $line['igv'];
            $ipm += $line['quantity'] * $line['ipm'];
        }

        if($request->currency_id == 2){
            $val = Validator::make($request->all(), [
                'exchange_rate' => 'required|numeric'
            ]);
            if($val->fails()) return response()->json($val->messages());

            if(($amount + $igv)*$request->exchange_rate > 700){
                $detraction_amount = round(round(($amount + $igv)*0.12, 2) *$request->exchange_rate,0) ;
            }
        }
        else{
            if($amount + $igv > 700){
                $detraction_amount = round(($amount + $igv)*0.12, 0);
            }
        }
        

        // Creando Factura
        ############################################################
        $client = Client::find($request->client_id);

        $client_name = $client->client_full_name;
        $customer_type = ($client->customer_type == 'PJ') ? 1 : 2;
        $invoice_serie = (($client->customer_type == 'PJ') ? 'F' : 'B') .'001';
        $client_document_type = ($client->document_type->name == 'RUC') ? 6 : ($client->document_type->name == 'DNI' ? 1 : ($client->document_type->name == 'Carné de extranjería' ? 4 : null));
        $client_document_number = $client->document_number;
        $localidad = isset($client->district) ? $client->district->name . " - " . $client->district->province->name ." - " . $client->district->province->department->name : "";
        $client_address = $client->address . ", " . $localidad;
        $client_type = $client->customer_type;

        $detraction = ($detraction_amount > 0 && $client_type == 'PJ') ? "true" : "false";
        $detraction_type = ($detraction_amount > 0 && $client_type == 'PJ') ? 35 : "";
        $detraction_total = ($detraction_amount > 0 && $client_type == 'PJ') ? round($detraction_amount,0) : "";
        $detraction_percentage = ($detraction_amount > 0) ? Configuration::where('shortname', 'DETRACTION')->first()->value : "";
        $detraction_payment = ($detraction_amount > 0 && $client_type == 'PJ') ? 1 : "";

        $currency_detraction = 'S/';

        $observation = ($detraction_amount > 0 && $client_type == 'PJ') ? "Monto detracción: " . $currency_detraction . round($detraction_amount,2) : "";

        $invoice_code = Carbon::now()->format('ymdHisv') . rand(0,9);

        try{

            $data = array(
                "operacion"                         => "generar_comprobante",
                "tipo_de_comprobante"               => $customer_type,
                "serie"                             => $invoice_serie,
                "numero"                            => "",
                "sunat_transaction"                 => "1",
                "cliente_tipo_de_documento"         => $client_document_type,
                "cliente_numero_de_documento"       => $client_document_number,
                "cliente_denominacion"              => ucfirst($client_name),
                "cliente_direccion"                 => $client_address,
                "cliente_email"                     => "",
                "cliente_email_1"                   => "",
                "cliente_email_2"                   => "",
                "fecha_de_emision"                  => Carbon::parse($request->invoice_issue_date)->format('d-m-Y'),
                "fecha_de_vencimiento"              => Carbon::parse($request->invoice_due_date)->format('d-m-Y'),
                "moneda"                            => $request->currency_id,
                "tipo_de_cambio"                    => (!is_null($request->exchange_rate)) ? $request->exchange_rate : "",
                "porcentaje_de_igv"                 => $configurations->get_value('IGV'),
                "descuento_global"                  => "",
                "descuento_global"                  => "",
                "total_descuento"                   => "",
                "total_anticipo"                    => "",
                "total_gravada"                     => ($request->type == 'afecta') ? round($amount, 2) : "",
                "total_inafecta"                    => ($request->type == 'inafecta') ? round($amount, 2) : "",
                "total_exonerada"                   => "",
                "total_igv"                         => ($request->type == 'afecta') ? round($igv, 2) : "0",
                "total_gratuita"                    => "",
                "total_otros_cargos"                => "",
                "total"                             => round($amount + $igv, 2),
                "percepcion_tipo"                   => "",
                "percepcion_base_imponible"         => "",
                "total_percepcion"                  => "",
                "total_incluido_percepcion"         => "",
                "detraccion"                        => $detraction,
                "detraccion_tipo"                   => $detraction_type,
                "detraccion_total"                  => $detraction_total,
                "detraccion_porcentaje"             => $detraction_percentage,
                "medio_de_pago_detraccion"          => $detraction_payment,
                "observaciones"                     => $observation,
                "documento_que_se_modifica_tipo"    => "",
                "documento_que_se_modifica_serie"   => "",
                "documento_que_se_modifica_numero"  => "",
                "tipo_de_nota_de_credito"           => "",
                "tipo_de_nota_de_debito"            => "",
                "enviar_automaticamente_a_la_sunat" => "true",
                "enviar_automaticamente_al_cliente" => "true",
                "codigo_unico"                      => $invoice_code,
                "condiciones_de_pago"               => "CONTADO",
                "medio_de_pago"                     => "",
                "placa_vehiculo"                    => "",
                "orden_compra_servicio"             => "",
                "tabla_personalizada_codigo"        => "",
                "formato_de_pdf"                    => "A4",
                "tipo_de_igv"                       => ($request->type == 'afecta') ? 1 : 9,
                "items" => array()
            );

            foreach ($request->detail as $line) {
                $line = array(
                    "unidad_de_medida"          => $line['unit'],
                    "codigo"                    => $line['code'],
                    "descripcion"               => $line['description'],
                    "cantidad"                  => $line['quantity'],
                    "valor_unitario"            => $line['unit_amount'],
                    "precio_unitario"           => $line['unit_amount'] + $line['igv'],
                    "descuento"                 => "",
                    "subtotal"                  => $line['unit_amount'] * $line['quantity'],
                    "tipo_de_igv"               => ($request->type == 'afecta') ? 1 : 9,
                    "igv"                       => $line['igv'] * $line['quantity'],
                    "total"                     => round(($line['unit_amount'] * $line['quantity']) + $line['igv'] * $line['quantity'], 2),
                    "anticipo_regularizacion"   => "false",
                    "anticipo_serie"            => "",
                    "anticipo_documento_numero" => ""
                );

                array_push($data['items'], $line);
            }
            

            // Executing Nubefact API
            $consulta = Http::withToken(env('NUBEFACT_TOKEN'))->post(env('NUBEFACT_URL'), $data);

            $rpta_json = json_decode($consulta);

            if(is_object($rpta_json)){
                if(isset($rpta_json->errors)){
                    logger('ERROR: archivo adjunto: DailyOperationsController@invoice', ["error" => $rpta_json]);

                    return response()->json([
                        'success' => false,
                        'errors' => [
                            $rpta_json->errors
                        ]
                    ]);
                }
                else{

                    //try{
                        $new_sale = Sale::create([
                            'code' => $invoice_code,
                            'client_id' => $request->client_id,
                            'amount' => $amount,
                            'igv' => $igv,
                            'currency_id' => $request->currency_id,
                            'exchange_rate' => isset($request->exchange_rate) ? $request->exchange_rate : null,
                            'detraction_amount' => $detraction_amount,
                            'invoice_serie' => $rpta_json->serie,
                            'invoice_number' => $rpta_json->numero,
                            'invoice_url' => $rpta_json->enlace,
                            'invoice_issue_date' => $request->invoice_issue_date,
                            'invoice_due_date' => $request->invoice_due_date,
                            'status' => 'Aceptada',
                            'created_by' => auth()->id()
                        ]);

                        foreach ($request->detail as $line) {
                            $new_sale->lines()->create([
                                'description' => $line['description'],
                                'quantity' => $line['quantity'],
                                'unit_amount' => $line['unit_amount'],
                                'igv' => $line['igv'],
                                'discount' => 0
                            ]);
                        }
                    //}

                    return response()->json([
                        'success' => true,
                        'data' => [
                            'Venta creada exitosamente'
                        ]
                    ]);
                }
            }

        } catch (\Exception $e) {
            // Registrando el el log los datos ingresados
            logger('ERROR: Creación de Factura: DailyOperationsController@invoice', ["error" => $e]);
        }


        return response()->json([
            'success' => true,
            'data' => [
                'Venta creada exitosamente'
            ]
        ]);
    }

    public function list_sales(Request $request) {

        $year = Carbon::now()->year;

        $sales = Sale::select('id','code','client_id','amount','igv','currency_id','exchange_rate','invoice_serie','invoice_number','invoice_url','invoice_issue_date','invoice_due_date','status')
            ->with('lines')
            ->with('currency:id,name,sign')
            ->with('client:id,name,last_name,mothers_name,document_type_id,document_number');

        if(!is_null($request->status)){
            $sales = $sales->where('status',$request->status);
        }

        if(!is_null($request->client_id)){
            $sales = $sales->where('client_id', $request->client_id);
        }

        if(!is_null($request->month)){
            $sales = $sales->whereRaw("month(invoice_issue_date) = " . $request->month);
        }

        if(!is_null($request->year)){
            $sales = $sales->whereRaw("year(invoice_issue_date) = " . $request->year);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'sales' => $sales->get()
            ]
        ]);
    }

    ########################################################################


    //Selfdetractions pending
    public function pending_selfdetractions(Request $request) {

        $pending_selfdetractions = Operation::select('id','code','type','client_id','igv','comission_amount','invoice_serie','invoice_number','invoice_url','operation_date','invoice_date','detraction_amount','detraction_paid')
            ->where('detraction_paid',0)
            ->where('operation_status_id', 6)
            ->where('detraction_amount','>',0)
            ->with('client:id,name,document_type_id,document_number,invoice_to','client.document_type:id,name','client.client_invoice_to:id,name,document_type_id,document_number','client.client_invoice_to.document_type:id,name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'pending_selfdetractions' => $pending_selfdetractions
            ]
        ]);
    }

    //Selfdetractions register massive payment
    public function selfdetraction_register(Request $request) {
        $val = Validator::make($request->all(), [
            'operations' => 'required|array'
        ]);
        if($val->fails()) return response()->json($val->messages());

        //AutoDetracciones registradas
        $selfdetractions = Operation::where('detraction_paid', 2)->get();

        if($selfdetractions->count() > 0){
            return response()->json([
                'success' => false,
                'errors' => [
                    'Existen autodetracciones en proceso de pago. Confirme el pago o cancele el proceso actual para enviar un nuevo proceso.'
                ]
            ]);
        }

        Operation::whereIn('id', $request->operations)->where('detraction_paid',0)->update(["detraction_paid" => 2]);

        return response()->json([
            'success' => true,
            'data' => [
                'Pago de autodetracciones masivo registrado exitosamente.'
            ]
        ]);
    }

    //Selfdetractions in payment process
    public function selfdetractions_in_progress(Request $request) {

        $massive_selfdetractions = Operation::select('id','code','type','client_id','igv','comission_amount','invoice_serie','invoice_number','invoice_url','operation_date','invoice_date','detraction_amount','detraction_paid')
            ->where('detraction_paid',2)
            ->where('operation_status_id', 6)
            ->where('detraction_amount','>',0)
            ->with('client:id,name,document_type_id,document_number,invoice_to','client.document_type:id,name','client.client_invoice_to:id,name,document_type_id,document_number','client.client_invoice_to.document_type:id,name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'massive_selfdetractions' => $massive_selfdetractions,
            ]
        ]);
    }

    //SelfDetractions confirm payment
    public function selfdetraction_payment(Request $request, Operation $operation) {
        $val = Validator::make($request->all(), [
            'file' => 'required|file',
        ]);
        if($val->fails()) return response()->json($val->messages());

        if($operation->detraction_paid != 2){
            return response()->json([
                'success' => false,
                'errors' => [
                    'La operación no se encuentra en proceso de pago de detracción'
                ]
            ]);
        }

        if($operation->documents->where('type', 'Detraccion')->count() && !isset($request->force_voucher)){
            return response()->json([
                'success' => false,
                'errors' => [
                    'La operación ya cuenta con Comprobante de pago de detracción'
                ]
            ]);
        }

        if($request->hasFile('file')){
            $file = $request->file('file');
            $path = env('AWS_ENV').'/operations/';
            
            $original_name = $file->getClientOriginalName();
            $longitud = Str::length($file->getClientOriginalName());

            $filename = Carbon::parse($operation->operation_date)->format('Y m d')." - " . $operation->invoice_serie . " ". $operation->invoice_number ." - " . (is_null($operation->client->invoice_to) ? $operation->client->name : $operation->client->client_invoice_to->name) .".pdf";

            try {
                $s3 = Storage::disk('s3')->putFileAs($path, $file, $filename);
                
                $delete = OperationDocument::where('operation_id', $request->operation->id)
                        ->where('type', Enums\DocumentType::Detraccion)
                        ->delete();

                $insert = OperationDocument::create([
                    'operation_id' => $request->operation->id,
                    'type' => Enums\DocumentType::Detraccion,
                    'document_name' => $filename
                ]);

            } catch (\Exception $e) {
                // Registrando el el log los datos ingresados
                logger('ERROR: archivo adjunto: selfdetraction_payment@SalesController', ["error" => $e]);

                return response()->json([
                    'success' => false,
                    'errors' => [
                        'Error en el archivo adjunto'
                    ]
                ]);
            }

            $operation->detraction_paid = 1;
            $operation->save();

            OperationHistory::create(["operation_id" => $request->operation->id,"user_id" => auth()->id(),"action" => "Comprobante detracción cargado", "detail" => 'filename: ' . $filename]);

            /// Envío de correo a cliente
            try {
                $rpta_mail = Mail::send(new SelfDetraction($operation));
            } catch (\Exception $e) {
                logger('ERROR: SelfDetraction Email: selfdetraction_payment@SalesController', ["error" => $e]);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'Archivo agregado'
                ]
            ]);

        }
        else{
            return response()->json([
                'success' => false,
                'errors' => [
                    'Error en el archivo adjunto'
                ]
            ]);
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'Pago autodetracción registrado exitosamente.'
            ]
        ]);
    }

    //SelfDetractions cancel massive payment
    public function selfdetraction_cancel(Request $request) {

        Operation::where('detraction_paid', 2)->update(["detraction_paid" => 0]);

        return response()->json([
            'success' => true,
            'data' => [
                'Proceso de pago de autodetracciones masiva cancelado exitosamente'
            ]
        ]);
    }

    public function download_selfdetraction(Request $request, Operation $operation) {

        $document = OperationDocument::where('type','Detraccion')->where('operation_id', $request->operation->id)->first();

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
}
