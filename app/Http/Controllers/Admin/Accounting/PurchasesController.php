<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\AccountingDocument;
use App\Models\BusinessBankAccount;
use App\Models\PurchaseInvoice;
use App\Models\PurchasePayment;
use App\Models\Service;
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
                //'file' => 'required|file',
                'detail' => 'required|array',
                'detraction_type' => 'nullable|exists:mysql2.detraction_types,code',
                'detraction_percentage' => 'nullable|numeric',
                'detraction_amount' => 'nullable|numeric',
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

            $service = Service::find($request->service_id)->load('supplier')->supplier->apply_detraction;

            // Cálculo de detracción -> ya no va, se envían los datos desde front
            if($service == 'Si'){
                /*if($request->currency_id == 2){
                    $val = Validator::make($request->all(), [
                        'exchange_rate' => 'required|numeric'
                    ]);
                    if($val->fails()) return response()->json($val->messages());

                    if(($amount + $igv)*$request->exchange_rate > 700 && $request->type = 'Servicio'){
                        $detraction_amount = round(round(($amount + $igv)*0.12, 2) *$request->exchange_rate,0) ;
                    }

                }
                else{
                    if($amount + $igv > 700 && $request->type = 'Servicio'){
                        $detraction_amount = round(($amount + $igv)*0.12, 0);
                    }
                }*/

                if(isset($request->detraction_type) || isset($request->detraction_percentage) || isset($request->detraction_amount)){

                    $val = Validator::make($request->all(), [
                        'detraction_type' => 'required|exists:mysql2.detraction_types,code',
                        'detraction_percentage' => 'required|numeric',
                        'detraction_amount' => 'required|numeric',
                    ]);
                    if($val->fails()) return response()->json($val->messages());
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
                'detraction_type' => isset($request->detraction_type) ? $request->detraction_type : null,
                'detraction_percentage' => isset($request->detraction_percentage) ? $request->detraction_percentage : null,
                'detraction_amount' => isset($request->detraction_amount) ? $request->detraction_amount : null,
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
                    logger('ERROR: subiendo factura proveedor: new_supplier@PurchasesController', ["error" => $e]);

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

    //Purchase detail
    public function purchase_detail(Request $request, PurchaseInvoice $purchase_invoice) {

        $paid = PurchasePayment::selectRaw("sum(if(status ='Pagado',amount,0)) as pagado")
            ->where('purchase_invoice_id', $purchase_invoice->id)
            ->first()->pagado*1.0;

        $detraction_paid = PurchaseInvoice::selectRaw("if(detraction_payment_date is null,0,if(currency_id = 1,detraction_amount,round((total_amount+total_igv)*0.12,2))) as pagado")
            ->where('id', $purchase_invoice->id)
            ->first()->pagado*1.0;

        $paid += $detraction_paid;

        $pending = ($purchase_invoice->total_amount + $purchase_invoice->total_igv + $purchase_invoice->total_ipm) - $paid;

        $purchase_invoice->paid = $paid;
        $purchase_invoice->pending = $pending;

        return response()->json([
            'success' => true,
            'data' => [
                'purchase_invoice' => $purchase_invoice
                            ->load('service:id,budget_id,supplier_id,name,description,amount,currency_id,frequency','service.budget:id,area_id,code,description,period','service.budget.area:id,name,code','service.supplier:id,name,document_number,logo_url','service.currency:id,name,sign')
                            ->load('currency:id,name,sign')
                            ->load('lines:id,purchase_invoice_id,description,quantity,unit_amount,igv,ipm')
                            ->load('payments:id,purchase_invoice_id,payment_date,payment_method,amount,currency_id,transfer_number,status,business_bank_account_id,supplier_bank_account_id,refund_bank_account_id','payments.documents','payments.business_bank_account','payments.supplier_bank_account','payments.refund_bank_account','payments.refund_bank_account.user:id,name,last_name','payments.currency:id,name,sign')
                            ->load('documents:id,purchase_invoice_id,name,type,created_at')
            ]
        ]);
    }

    //Reopen closed Purchase
    public function reopen_purchase(Request $request, PurchaseInvoice $purchase_invoice) {

        if($purchase_invoice->status != 'Pagado'){
            return response()->json([
                'success' => false,
                'errors' => [
                    'La compra aún se encuentra abierta'
                ]
            ]);
        }

        $purchase_invoice->status = 'Pendiente pago';
        $purchase_invoice->save();

        return response()->json([
            'success' => true,
            'data' => [
                'Compra abierta exitosamente'
            ]
        ]);
    }

    //Request edit Purchase
    public function request_edit(Request $request, PurchaseInvoice $purchase_invoice) {

        $purchase_invoice->status = 'Borrador';
        $purchase_invoice->save();

        return response()->json([
            'success' => true,
            'data' => [
                'purchase_invoice' => $purchase_invoice
                            ->load('service:id,budget_id,supplier_id,name,description,amount,currency_id,frequency','service.budget:id,area_id,code,description,period','service.budget.area:id,name,code','service.supplier:id,name,document_number,logo_url','service.currency:id,name,sign')
                            ->load('currency:id,name,sign')
                            ->load('lines:id,purchase_invoice_id,description,quantity,unit_amount,igv,ipm')
                            ->load('payments')
            ]
        ]);
    }

    //Validate Purchase edition
    public function validate_purchase(Request $request, PurchaseInvoice $purchase_invoice) {
        $val = Validator::make($request->all(), [
            'service_id' => 'required|exists:mysql2.services,id',
            'type' => 'required|in:Producto,Servicio',
            'currency_id' => 'required|exists:currencies,id',
            'serie' => 'required|string',
            'number' => 'required|string',
            'issue_date' => 'required|date',
            'due_date' => 'required|date',
            'service_month' => 'nullable|numeric',
            'service_year' => 'nullable|numeric',
            'detail' => 'required|array',
            'detraction_type' => 'nullable|exists:mysql2.detraction_types,code',
            'detraction_percentage' => 'nullable|numeric',
            'detraction_amount' => 'nullable|numeric',
        ]);
        if($val->fails()) return response()->json($val->messages());

        if($purchase_invoice->documents->count() == 0){
            $val = Validator::make($request->all(), [
                'file' => 'required|file',
            ]);
            if($val->fails()) return response()->json($val->messages());
        }
        
        $amount = 0;
        $igv = 0;
        $ipm = 0;
        $detraction_amount = 0;

        foreach ($request->detail as $line) {
            $amount += $line['quantity'] * $line['unit_amount'];
            $igv += $line['quantity'] * $line['igv'];
            $ipm += $line['quantity'] * $line['ipm'];
        }

        if(isset($request->detraction_type) || isset($request->detraction_percentage) || isset($request->detraction_amount)){

            $val = Validator::make($request->all(), [
                'detraction_type' => 'required|exists:mysql2.detraction_types,code',
                'detraction_percentage' => 'required|numeric',
                'detraction_amount' => 'required|numeric',
            ]);
            if($val->fails()) return response()->json($val->messages());
        }

        $purchase_invoice->update([
            'service_id' => $request->service_id,
            'type' => $request->type,
            'total_amount' => $amount,
            'total_igv' => $igv,
            'currency_id' => $request->currency_id,
            'exchange_rate' => !is_null($request->exchange_rate) ? $request->exchange_rate : null,
            'serie' => $request->serie,
            'number' => $request->number,
            'detraction_type' => isset($request->detraction_type) ? $request->detraction_type : null,
            'detraction_percentage' => isset($request->detraction_percentage) ? $request->detraction_percentage : null,
            'detraction_amount' => isset($request->detraction_amount) ? $request->detraction_amount : null,
            'issue_date' => $request->issue_date,
            'due_date' => $request->due_date,
            'service_month' => isset($request->service_month) ? $request->service_month : null,
            'service_year' => isset($request->service_year) ? $request->service_year : null,
            'status' => 'Pendiente pago'
        ]);

        $purchase_invoice->lines()->delete();

        foreach ($request->detail as $line) {
            $purchase_invoice->lines()->create([
                'description' => $line['description'],
                'quantity' => $line['quantity'],
                'unit_amount' => $line['unit_amount'],
                'igv' => $line['igv'],
                'ipm' => $line['ipm']
            ]);
        }

        if($request->hasFile('file')){
            // Eliminando factura anterior
            $purchase_invoice->documents()->delete();

            $file = $request->file('file');
            $path = env('AWS_ENV').'/accounting/purchases/';
            
            $original_name = $file->getClientOriginalName();
            $longitud = Str::length($file->getClientOriginalName());

            $filename = "invoice_" . $purchase_invoice->id . "_" . substr($original_name, $longitud - 6, $longitud);

            try {
                $s3 = Storage::disk('s3')->putFileAs($path, $file, $filename);

                AccountingDocument::create([
                    'name' => $path . $filename,
                    'purchase_invoice_id' => $purchase_invoice->id,
                    'type' => 'Invoice'
                ]);

            } catch (\Exception $e) {
                // Registrando el el log los datos ingresados
                logger('ERROR: subiendo factura proveedor: validate_purchase@PurchasesController', ["error" => $e]);

                return response()->json([
                    'success' => false,
                    'errors' => [
                        'Error en el archivo adjunto'
                    ]
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'Factura validada exitosamente.'
            ]
        ]);
    }

    //Request edit Purchase
    public function delete(Request $request, PurchaseInvoice $purchase_invoice) {

        if($purchase_invoice->payments->count() > 0){
            return response()->json([
                'success' => false,
                'errors' => [
                    'La Compra que desea eliminar tiene pagos registrados, debe eliminar los pagos primero.'
                ]
            ]);
        }

        if($purchase_invoice->lines->count() > 0){
            $purchase_invoice->lines()->delete();
        }

        if($purchase_invoice->documents->count() > 0){
            $purchase_invoice->documents()->delete();
        }

        $purchase_invoice->delete();

        return response()->json([
            'success' => true,
            'data' => [
                'Compra eliminada exitosamente'
            ]
        ]);
    }

    //Register purchase payment
    public function register_payment(Request $request, PurchaseInvoice $purchase_invoice) {
        $val = Validator::make($request->all(), [
            'payment_method' => 'required|in:Efectivo,Cheque,Transferencia bancaria,Reembolso',
            'type' => 'required|in:Pendiente,Pagado',
            'amount' => 'nullable|numeric',
            'currency_id' => 'required|exists:currencies,id',
            'comments' => 'nullable|string',
            'business_bank_account_id' => 'required|exists:mysql2.business_bank_accounts,id',
        ]);
        if($val->fails()) return response()->json($val->messages());

        if($request->payment_method == 'Efectivo' || $request->payment_method == 'Cheque'){
            if($request->type == 'Pendiente'){
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'No se puede registrar un pago pendiente para el método de pago seleccionado'
                    ]
                ]);
            }
        }

        if($purchase_invoice->status == 'Pagado'){
            return response()->json([
                'success' => false,
                'errors' => [
                    'La factura ya se encuentra en estado Pagado'
                ]
            ]);
        }

        if($request->payment_method == 'Transferencia bancaria'){
            $val = Validator::make($request->all(), [
                'supplier_bank_account_id' => 'required|exists:mysql2.supplier_bank_accounts,id',
            ]);
            if($val->fails()) return response()->json($val->messages());
        }

        if($request->payment_method == 'Reembolso'){
            $val = Validator::make($request->all(), [
                'refund_bank_account_id' => 'required|exists:mysql2.refund_bank_accounts,id',
            ]);
            if($val->fails()) return response()->json($val->messages());
        }

        $paid = PurchasePayment::selectRaw("sum(if(status ='Pagado',amount,0)) + (select if(detraction_payment_date is null,0,detraction_amount) from purchase_invoices where id = " . $purchase_invoice->id . ") as pagado")
            ->where('purchase_invoice_id', $purchase_invoice->id)
            ->first()->pagado*1.0;

        $pending = ($purchase_invoice->total_amount + $purchase_invoice->total_igv + $purchase_invoice->total_ipm) - $paid;

        if($request->type == 'Pagado'){
            if($request->amount > $pending){
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'El monto de pago ingresado es mayor que el pendiente en la factura'
                    ]
                ]);
            }
        }

        $paid_total = PurchasePayment::selectRaw("sum(amount) + (select detraction_amount from purchase_invoices where id = " . $purchase_invoice->id . ") as pagado")
            ->where('purchase_invoice_id', $purchase_invoice->id)
            ->first()->pagado*1.0;

        $pending = ($purchase_invoice->total_amount + $purchase_invoice->total_igv + $purchase_invoice->total_ipm) - $paid_total;

        if($request->amount > $pending){
            return response()->json([
                'success' => false,
                'errors' => [
                    'El monto de pago ingresado es mayor que el pendiente en la factura'
                ]
            ]);
        }


        if($request->type == 'Pendiente'){
            $purchase_invoice->payments()->create([
                'payment_method' => $request->payment_method,
                'amount' => $request->amount,
                'currency_id' => $request->currency_id,
                'comments' => $request->comments,
                'status' => $request->type,
                'business_bank_account_id' => $request->business_bank_account_id,
                'supplier_bank_account_id' => ($request->payment_method == 'Transferencia bancaria') ? $request->supplier_bank_account_id : null,
                'refund_bank_account_id' => ($request->payment_method == 'Reembolso') ? $request->refund_bank_account_id : null
            ]);
        }
        elseif ($request->type == 'Pagado') {
            $val = Validator::make($request->all(), [
                'payment_date' => 'required|date',
                'transfer_number' => 'nullable|string',
                'file' => 'nullable|file',
            ]);
            if($val->fails()) return response()->json($val->messages());

            $purchase_payment = $purchase_invoice->payments()->create([
                'payment_date' => $request->payment_date,
                'payment_method' => $request->payment_method,
                'amount' => $request->amount,
                'currency_id' => $request->currency_id,
                'transfer_number' => $request->transfer_number,
                'comments' => $request->comments,
                'status' => $request->type,
                'business_bank_account_id' => $request->business_bank_account_id,
                'supplier_bank_account_id' => ($request->payment_method == 'Transferencia bancaria') ? $request->supplier_bank_account_id : null,
                'refund_bank_account_id' => ($request->payment_method == 'Reembolso') ? $request->refund_bank_account_id : null
            ]);

            if($request->hasFile('file')){
                $file = $request->file('file');
                $path = env('AWS_ENV').'/accounting/purchases/';
                
                $original_name = $file->getClientOriginalName();
                $longitud = Str::length($file->getClientOriginalName());

                $filename = "purchase_payment_" . $purchase_payment->id . "_" . substr($original_name, $longitud - 6, $longitud);

                try {
                    $s3 = Storage::disk('s3')->putFileAs($path, $file, $filename);

                    AccountingDocument::create([
                        'name' => $path . $filename,
                        'purchase_payment_id' => $purchase_payment->id,
                        'type' => 'Invoice'
                    ]);

                } catch (\Exception $e) {
                    // Registrando el el log los datos ingresados
                    logger('ERROR: subiendo comprobante de pago: register_payment@PurchasesController', ["error" => $e]);

                    return response()->json([
                        'success' => false,
                        'errors' => [
                            'Error en el archivo adjunto'
                        ]
                    ]);
                }
            }
        }

        $registro = PurchasesController::invoice_paid($purchase_invoice);

        return response()->json([
            'success' => true,
            'data' => [
                'Pago registrado exitosamente'
            ]
        ]);
    }

    //Validating Paid purchase
    public function invoice_paid(PurchaseInvoice $purchase_invoice) {

        $paid = PurchasePayment::selectRaw("sum(if(status ='Pagado',amount,0)) + (select if(detraction_payment_date is null,0,detraction_amount) from purchase_invoices where id = " . $purchase_invoice->id . ") as pagado")
            ->where('purchase_invoice_id', $purchase_invoice->id)
            ->first()->pagado*1.0;

        $pending = ($purchase_invoice->total_amount + $purchase_invoice->total_igv + $purchase_invoice->total_ipm) - $paid;

        if($pending == 0){
            $purchase_invoice->status='Pagado';
            $purchase_invoice->save();
        }
    }

    //Delete purchase payment
    public function delete_payment(Request $request, PurchasePayment $purchase_payment) {
        
        if($purchase_payment->status != 'Pendiente'){
            return response()->json([
                'success' => false,
                'errors' => [
                    'Solo se pueden eliminar pagos en estado Pendiente'
                ]
            ]);
        }

        $purchase_payment->delete();

        return response()->json([
            'success' => true,
            'data' => [
                'Pago eliminado exitosamente'
            ]
        ]);
    }

########################################################################



    //Detractions pending
    public function pending_detractions(Request $request) {

        $pending_detractions = PurchaseInvoice::select('id','service_id','total_amount','detraction_percentage','detraction_amount','detraction_type','detraction_payment_date','serie','number','issue_date','serie','number','currency_id')
            ->selectRaw("concat(year(issue_date),if(month(issue_date)<10,concat(0,month(issue_date)),month(issue_date))) as period")
            ->where('status', 'Pendiente pago')
            ->where('detraction_amount', '>', 0)
            ->whereRaw('detraction_payment_date is null')
            ->with('service:id,supplier_id','service.supplier:id,name,document_type_id,document_number,detraction_account')
            ->with('currency:id,name,sign')
            ->with('detraction_type:code,name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'pending_detractions' => $pending_detractions
            ]
        ]);
    }

    //Detractions register massive payment
    public function detraction_register(Request $request) {
        $val = Validator::make($request->all(), [
            'purchases' => 'required|array'
        ]);
        if($val->fails()) return response()->json($val->messages());

        //Detracciones registradas
        $detractions = PurchaseInvoice::where('detraction_url', "pagoMasivo")->get();

        if($detractions->count() > 0){
            return response()->json([
                'success' => false,
                'errors' => [
                    'Existen detracciones en proceso de pago. Confirme el pago o cancele el proceso actual para enviar un nuevo proceso.'
                ]
            ]);
        }

        PurchaseInvoice::whereIn('id', $request->purchases)->whereRaw('detraction_url is null')->update(["detraction_url" => "pagoMasivo"]);

        return response()->json([
            'success' => true,
            'data' => [
                'Pago de detracciones masivo registrado exitosamente.'
            ]
        ]);
    }

    //Detractions pending
    public function detractions_in_progress(Request $request) {

        $massive_detractions = PurchaseInvoice::select('id','service_id','total_amount','detraction_percentage','detraction_amount','detraction_type','detraction_payment_date','serie','number','issue_date','serie','number')
            ->selectRaw("concat(year(issue_date),if(month(issue_date)<10,concat(0,month(issue_date)),month(issue_date))) as period")
            ->where('status', 'Pendiente pago')
            ->where('detraction_url', 'pagoMasivo')
            ->with('service:id,supplier_id','service.supplier:id,name,document_type_id,document_number,detraction_account')
            ->with('detraction_type:code,name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'massive_detractions' => $massive_detractions,
            ]
        ]);
    }

    //Detractions confirmen payment
    public function detraction_payment(Request $request) {
        $val = Validator::make($request->all(), [
            'date' => 'required|date',
            'file' => 'required|file',
        ]);
        if($val->fails()) return response()->json($val->messages());

        //Detracciones registradas
        $detractions = PurchaseInvoice::where('detraction_url', "pagoMasivo");

        if($detractions->get()->count() == 0){
            return response()->json([
                'success' => false,
                'errors' => [
                    'No se encontraron detracciones en proceso de pago masivo.'
                ]
            ]);
        }

        if($request->hasFile('file')){
            $file = $request->file('file');
            $path = env('AWS_ENV').'/accounting/purchases/';
            
            $original_name = $file->getClientOriginalName();
            $longitud = Str::length($file->getClientOriginalName());

            $filename = "invoice_" . $detractions->get()[0]->id . "_det_" . substr($original_name, $longitud - 6, $longitud);

            $ids = $detractions->pluck('id');

            try {
                $s3 = Storage::disk('s3')->putFileAs($path, $file, $filename);

                $detractions->update([
                    'detraction_payment_date' => $request->date,
                    'detraction_url' => $path . $filename
                ]);

            } catch (\Exception $e) {
                // Registrando el el log los datos ingresados
                logger('ERROR: subiendo constancia pago detracciones: detraction_payment@PurchasesController', ["error" => $e]);

                return response()->json([
                    'success' => false,
                    'errors' => [
                        'Error en el archivo adjunto'
                    ]
                ]);
            }
        }

        foreach ($ids as $id) {
            $registro = PurchasesController::invoice_paid(PurchaseInvoice::find($id));
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'Pago registrado exitosamente.'
            ]
        ]);
    }

    //Detractions cancel massive payment
    public function detraction_cancel(Request $request) {

        PurchaseInvoice::where('detraction_url', "pagoMasivo")->update(["detraction_url" => null]);

        return response()->json([
            'success' => true,
            'data' => [
                'Proceso de pago de detracciones masiva cancelado exitosamente'
            ]
        ]);
    }

    //Detractions register individual payment
    public function register_individual_detraction(Request $request, PurchaseInvoice $purchase_invoice) {
        $val = Validator::make($request->all(), [
            'date' => 'required|date',
            'file' => 'required|file',
        ]);
        if($val->fails()) return response()->json($val->messages());

        if($purchase_invoice->detraction_amount == 0 || is_null($purchase_invoice->detraction_amount)){
            return response()->json([
                'success' => false,
                'errors' => [
                    'No se encuentró monto de pago de detracción'
                ]
            ]);
        }

        if($purchase_invoice->status == 'Pagado'){
            return response()->json([
                'success' => false,
                'errors' => [
                    'La factura ya se encuentra pagada'
                ]
            ]);
        }

        if($purchase_invoice->detraction_url == 'pagoMasivo'){
            return response()->json([
                'success' => false,
                'errors' => [
                    'La detracción se encuentra en proceso de pago masivo'
                ]
            ]);
        }

        if(!is_null($purchase_invoice->detraction_amount) && !is_null($purchase_invoice->detraction_url)){
            return response()->json([
                'success' => false,
                'errors' => [
                    'La detracción ya se encuentra pagada'
                ]
            ]);
        }

        if($request->hasFile('file')){
            $file = $request->file('file');
            $path = env('AWS_ENV').'/accounting/purchases/';
            
            $original_name = $file->getClientOriginalName();
            $longitud = Str::length($file->getClientOriginalName());

            $filename = "invoice_" . $purchase_invoice->id . "_det_" . substr($original_name, $longitud - 6, $longitud);

            try {
                $s3 = Storage::disk('s3')->putFileAs($path, $file, $filename);

                $purchase_invoice->update([
                    'detraction_payment_date' => $request->date,
                    'detraction_url' => $path . $filename
                ]);

            } catch (\Exception $e) {
                // Registrando el el log los datos ingresados
                logger('ERROR: subiendo constancia pago detracciones: register_individual_detraction@PurchasesController', ["error" => $e]);

                return response()->json([
                    'success' => false,
                    'errors' => [
                        'Error en el archivo adjunto'
                    ]
                ]);
            }
        }

        $registro = PurchasesController::invoice_paid($purchase_invoice);
        
        return response()->json([
            'success' => true,
            'data' => [
                'Pago registrado exitosamente.'
            ]
        ]);
    }

########################################################################
    
    //Pending Payments
    public function pending_payments(Request $request) {

        $pending_payments = PurchasePayment::where('status','Pendiente')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'pending_payments' => $pending_payments->load('purchase_invoice:id,service_id,total_amount,total_igv,type,currency_id,issue_date,service_month,service_year,serie,number','purchase_invoice.service:id,budget_id,supplier_id','purchase_invoice.service.supplier:id,name,document_type_id,document_number','purchase_invoice.service.supplier.document_type:id,name')
                    ->load('business_bank_account:id,bank_id,alias,account_number,cci_number,account_type_id,currency_id','business_bank_account.bank:id,name,shortname','business_bank_account.currency:id,name,sign','business_bank_account.account_type:id,name,shortname')
                    ->load('supplier_bank_account:id,bank_id,account_number,cci_number,account_type_id,currency_id','supplier_bank_account.bank:id,name,shortname','supplier_bank_account.currency:id,name,sign','supplier_bank_account.account_type:id,name,shortname')
                    ->load('refund_bank_account:id,user_id,bank_id,account_number,cci_number,account_type_id,currency_id','refund_bank_account.bank:id,name,shortname','refund_bank_account.currency:id,name,sign','refund_bank_account.account_type:id,name,shortname','refund_bank_account.user:id,name,last_name,document_type_id,document_number','refund_bank_account.user.document_type:id,name')
            ]
        ]);
    }


    //Massive payment register
    public function payments_register(Request $request) {
        $val = Validator::make($request->all(), [
            'payments' => 'required|array'
        ]);
        if($val->fails()) return response()->json($val->messages());

        //Pagos Registrados
        $payments = PurchasePayment::where('status', "Masivo")->get();

        if($payments->count() > 0){
            return response()->json([
                'success' => false,
                'errors' => [
                    'Existen pagos en proceso. Confirme el pago o cancele el proceso actual para enviar un nuevo proceso.'
                ]
            ]);
        }

        PurchasePayment::whereIn('id', $request->payments)->update(["status" => "Masivo"]);

        return response()->json([
            'success' => true,
            'data' => [
                'Pago masivo registrado exitosamente.'
            ]
        ]);
    }

    //Payments in progress
    public function payments_in_progress(Request $request) {

        $pending_payments = PurchasePayment::where('status','Masivo')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'pending_payments' => BusinessBankAccount::select('id','bank_id','alias','account_number','cci_number','account_type_id','currency_id')
                    ->with('bank:id,name,shortname')
                    ->with('account_type:id,name,shortname')
                    ->with('currency:id,name,sign')
                    ->with(['purchase_payments' => function ($query) {
                        $query->where('status', 'Masivo');
                    }, 
                    'purchase_payments.purchase_invoice:id,service_id,total_amount,total_igv,type,currency_id,serie,number,issue_date','purchase_payments.purchase_invoice.service:id,budget_id,supplier_id','purchase_payments.purchase_invoice.service.supplier:id,name,document_type_id,document_number','purchase_payments.purchase_invoice.service.supplier.document_type:id,name',
                    'purchase_payments.currency:id,name,sign',
                    'purchase_payments.business_bank_account:id,bank_id,alias,account_number,cci_number,account_type_id,currency_id','purchase_payments.business_bank_account.bank:id,name,shortname','purchase_payments.business_bank_account.currency:id,name,sign','purchase_payments.business_bank_account.account_type:id,name,shortname',
                    'purchase_payments.supplier_bank_account:id,bank_id,account_number,cci_number,account_type_id,currency_id','purchase_payments.supplier_bank_account.bank:id,name,shortname','purchase_payments.supplier_bank_account.currency:id,name,sign','purchase_payments.supplier_bank_account.account_type:id,name,shortname',
                    'purchase_payments.refund_bank_account:id,user_id,bank_id,account_number,cci_number,account_type_id,currency_id','purchase_payments.refund_bank_account.bank:id,name,shortname','purchase_payments.refund_bank_account.currency:id,name,sign','purchase_payments.refund_bank_account.account_type:id,name,shortname','purchase_payments.refund_bank_account.user:id,name,last_name,document_type_id,document_number','purchase_payments.refund_bank_account.user.document_type:id,name'

                    ])
                    ->where('status','Activo')
                    ->get()
            ]
        ]);
    }

    //Payments confirmation
    public function confirm_payment(Request $request) {
        $val = Validator::make($request->all(), [
            'date' => 'required|date',
            'file' => 'required|file',
        ]);
        if($val->fails()) return response()->json($val->messages());

        //Pagos registrados
        $payments = PurchasePayment::where('status','Masivo');

        if($payments->get()->count() == 0){
            return response()->json([
                'success' => false,
                'errors' => [
                    'No se encontraron Pagos en proceso de pago masivo.'
                ]
            ]);
        }

        if($request->hasFile('file')){
            $file = $request->file('file');
            $path = env('AWS_ENV').'/accounting/purchases/';
            
            $original_name = $file->getClientOriginalName();
            $longitud = Str::length($file->getClientOriginalName());

            $filename = "purchase_payment_" . $payments->get()[0]->id . "_" . substr($original_name, $longitud - 6, $longitud);

            try {
                $s3 = Storage::disk('s3')->putFileAs($path, $file, $filename);

                foreach ($payments->get() as $key => $value) {
                    AccountingDocument::create([
                        'name' => $path . $filename,
                        'purchase_payment_id' => $value['id'],
                        'type' => 'Payment'
                    ]);
                }

                $ids = $payments->pluck('purchase_invoice_id');

                $payments->update([
                    'payment_date' => $request->date,
                    'status' => 'Pagado'
                ]);

            } catch (\Exception $e) {
                // Registrando el el log los datos ingresados
                logger('ERROR: subiendo constancia pago masivo: confirm_payment@PurchasesController', ["error" => $e]);

                return response()->json([
                    'success' => false,
                    'errors' => [
                        'Error en el archivo adjunto'
                    ]
                ]);
            }
        }

        foreach ($ids as $id) {
            $registro = PurchasesController::invoice_paid(PurchaseInvoice::find($id));
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'Pago confirmado exitosamente.'
            ]
        ]);
    }

    //Cancel massive payment
    public function payments_cancel(Request $request) {

        PurchasePayment::where('status', "Masivo")->update(["status" => 'Pendiente']);

        return response()->json([
            'success' => true,
            'data' => [
                'Proceso de pago masivo cancelado exitosamente'
            ]
        ]);
    }


}