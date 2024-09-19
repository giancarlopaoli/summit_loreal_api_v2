<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\BusinessBankAccount;
use App\Models\Client;
use App\Models\Executive;
use App\Models\SupplierBankAccount;
use App\Models\RefundBankAccount;
use App\Models\Service;
use App\Models\Supplier;

class GeneralController extends Controller
{
    //Suppliers list
    public function list_suppliers(Request $request) {

        $supplier = Supplier::select('id','name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'supplier' => $supplier
            ]
        ]);
    }

    //Services list
    public function list_services(Request $request) {

        $services = Service::select('id','budget_id','name')
            ->with('budget:id,area_id,code,period','budget.area:id,name,code')
            ->where('status','Activo')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'services' => $services
            ]
        ]);
    }
    
    //Download file
    public function download_file(Request $request) {
        $val = Validator::make($request->all(), [
            'url_file' => 'required|string'
        ]);
        if($val->fails()) return response()->json($val->messages());

        if (Storage::disk('s3')->exists($request->url_file)) {
            return Storage::disk('s3')->download($request->url_file);
        }
        else{
            return response()->json([
                'success' => false,
                'errors' => [
                    'Archivo no encontrado'
                ]
            ]);
        }

        return Storage::disk('s3')->download($request->url_file);
    }

    //Refund bank accounts
    public function refund_accounts(Request $request) {

        $refund_accounts = RefundBankAccount::select('id','user_id','bank_id','account_number','cci_number','currency_id','account_type_id','status')
            ->with('user:id,name,last_name')
            ->with('bank:id,name,shortname')
            ->with('account_type:id,name,shortname')
            ->with('currency:id,name,sign')
            ->where('status','Activo')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'refund_accounts' => $refund_accounts
            ]
        ]);
    }

    //Business bank accounts
    public function business_accounts(Request $request) {

        $business_accounts = BusinessBankAccount::select('id','bank_id','alias','account_number','cci_number','currency_id','account_type_id','status')
            ->with('bank:id,name,shortname')
            ->with('account_type:id,name,shortname')
            ->with('currency:id,name,sign')
            ->where('status','Activo')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'business_accounts' => $business_accounts
            ]
        ]);
    }

    //Supplier bank accounts
    public function supplier_accounts(Request $request, Supplier $supplier) {

        return response()->json([
            'success' => true,
            'data' => [
                'supplier_accounts' => $supplier->bank_accounts
                                        ->where('status','Activo')
                                        ->load('bank:id,name,shortname')
                                        ->load('account_type:id,name,shortname')
                                        ->load('currency:id,name,sign')
                                        ->load('supplier:id,name')
                                        ->where('status','Activo')
            ]
        ]);
    }

    //Suppliers list
    public function list_clients(Request $request) {

        $clients = Client::select('id','document_type_id','document_number','customer_type')
            ->selectRaw("if(customer_type = 'PN',concat(name,' ',last_name,' ',mothers_name),name) as client_name")
            ->where('client_status_id',3)
            ->with('document_type:id,name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'clients' => $clients
            ]
        ]);
    }
}
