<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\AccountingDocument;
use App\Models\PurchaseInvoice;
use App\Models\Supplier;
use Carbon\Carbon;

class PurchasesController extends Controller
{
    //Add Purchase
    public function new_purchase(Request $request) {
        $val = Validator::make($request->all(), [
            'invoice_type' => 'required|in:Normal,Adelanto'
        ]);
        if($val->fails()) return response()->json($val->messages());

        if($request->invoice_type == 'Normal'){
            $val = Validator::make($request->all(), [
                'service_id' => 'required|exists:mysql2.services,id',
                'type' => 'required|in:Producto,Servicio',
                'currency_id' => 'required|exists:currencies,id',
                'exchange_rate' => 'nullable|numeric',
                'serie' => 'required|string',
                'number' => 'required|string',
                'issue_date' => 'required|date',
                'due_date' => 'required|date',
                'service_month' => 'nullable|numeric',
                'service_year' => 'nullable|numeric',
                'file' => 'required|file',
                'detail' => 'required|array'
            ]);
            if($val->fails()) return response()->json($val->messages());

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

            $new_purchase = PurchaseInvoice::create([
                'service_id' => $request->service_id,
                'type' => $request->type,
                'total_amount' => $amount,
                'total_igv' => $igv,
                'currency_id' => $request->currency_id,
                'exchange_rate' => isset($request->exchange_rate) ? $request->exchange_rate : null,
                'serie' => $request->serie,
                'number' => $request->number,
                'detraction_amount' => $detraction_amount,
                'issue_date' => $request->issue_date,
                'due_date' => $request->due_date,
                'service_month' => isset($request->service_month) ? $request->service_month : null,
                'service_year' => isset($request->service_year) ? $request->service_year : null,
                'status' => 'Pendiente pago'
            ]);

            foreach ($request->detail as $line) {
                $new_purchase->lines()->create([
                    'description' => $line['description'],
                    'quantity' => $line['quantity'],
                    'unit_amount' => $line['unit_amount'],
                    'igv' => $line['igv'],
                    'ipm' => $line['ipm']
                ]);
            }

            if($request->hasFile('file')){
                $file = $request->file('file');
                $path = env('AWS_ENV').'/accounting/purchases/';
                
                $original_name = $file->getClientOriginalName();
                $longitud = Str::length($file->getClientOriginalName());

                $filename = "invoice_" . $new_purchase->id . "_" . substr($original_name, $longitud - 6, $longitud);

                try {
                    $s3 = Storage::disk('s3')->putFileAs($path, $file, $filename);

                    AccountingDocument::create([
                        'name' => $path . $filename,
                        'purchase_invoice_id' => $new_purchase->id,
                        'type' => 'Invoice'
                    ]);

                } catch (\Exception $e) {
                    // Registrando el el log los datos ingresados
                    logger('ERROR: subiendo logo proveedor: new_supplier@SuppliersController', ["error" => $e]);

                    return response()->json([
                        'success' => false,
                        'errors' => [
                            'Error en el archivo adjunto'
                        ]
                    ]);
                }
            }
        }
        else{
            $val = Validator::make($request->all(), [
                'service_id' => 'required|exists:mysql2.services,id',
                'type' => 'required|in:Producto,Servicio',
                'currency_id' => 'required|exists:currencies,id',
                'exchange_rate' => 'nullable|numeric',
                'service_month' => 'nullable|numeric',
                'service_year' => 'nullable|numeric',
            ]);
            if($val->fails()) return response()->json($val->messages());

            if($request->currency_id == 2){
                $val = Validator::make($request->all(), [
                    'exchange_rate' => 'required|numeric'
                ]);
                if($val->fails()) return response()->json($val->messages());
            }

            $new_purchase = PurchaseInvoice::create([
                'service_id' => $request->service_id,
                'type' => $request->type,
                'currency_id' => $request->currency_id,
                'exchange_rate' => isset($request->exchange_rate) ? $request->exchange_rate : null,
                'service_month' => isset($request->service_month) ? $request->service_month : null,
                'service_year' => isset($request->service_year) ? $request->service_year : null,
                'status' => 'Borrador'
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'Compra creada exitosamente'
            ]
        ]);
    }

    //Purchases list
    public function list_purchases(Request $request) {

        $year = Carbon::now()->year;

        $purchases = PurchaseInvoice::select('id','service_id','total_amount','total_igv','type','invoice_type', 'serie','number','issue_date','due_date','status')
            ->with('service:id,budget_id,name,supplier_id','service.supplier:id,name,logo_url');

        if(!is_null($request->status)){
            $purchases = $purchases->where('status',$request->status);
        }

        if(!is_null($request->supplier_id)){
            $purchases = $purchases->whereRelation('service', 'supplier_id', $request->supplier_id);
        }

        if(!is_null($request->month)){
            $purchases = $purchases->whereRaw("month(issue_date) = " . $request->month);
        }

        if(!is_null($request->year)){
            $purchases = $purchases->whereRaw("year(issue_date) = " . $request->year);
        }

        if(!is_null($request->service_id)){
            $purchases = $purchases->where('service_id',$request->service_id);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'purchases' => $purchases->get()
            ]
        ]);
    }
    
}
