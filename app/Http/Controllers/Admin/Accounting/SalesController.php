<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Operation;
use App\Models\OperationDocument;
use App\Models\OperationHistory;
use App\Models\Sale;
use Carbon\Carbon;
use App\Enums;

class SalesController extends Controller
{
    

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
