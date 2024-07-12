<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Budget;
use App\Models\Service;
use App\Models\Supplier;
use Carbon\Carbon;

class ServicesController extends Controller
{
    //Budgets list
    public function list_budgets(Request $request) {

        $year = Carbon::now()->year;

        $budgets = Budget::select('id','code','description','initial_budget','area_id')
            ->where('period', $year)
            ->with('area:id,name,code')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'budgets' => $budgets
            ]
        ]);
    }

    //Suppliers list
    public function list_suppliers(Request $request) {

        $year = Carbon::now()->year;

        $suppliers = Supplier::select('id','name','logo_url')
            ->where('status', 'Activo')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'suppliers' => $suppliers
            ]
        ]);
    }

    //Add Service
    public function new_service(Request $request) {
        $val = Validator::make($request->all(), [
            'budget_id' => 'required|exists:mysql2.budgets,id',
            'supplier_id' => 'required|exists:mysql2.suppliers,id',
            'name' => 'required|string|max:100',
            'description' => 'required|string|max:200',
            'amount' => 'required|numeric',
            'currency_id' => 'required|exists:currencies,id',
            'exchange_rate' => 'nullable|numeric',
            'frequency' => 'required|in:Compra única,Mensual,Anual,Otro'
        ]);
        if($val->fails()) return response()->json($val->messages());

        $insert = Service::create([
            'budget_id' => $request->budget_id,
            'supplier_id' => $request->supplier_id,
            'name' => $request->name,
            'description' => $request->description,
            'amount' => $request->amount,
            'currency_id' => $request->currency_id,
            'exchange_rate' => isset($request->exchange_rate) ? $request->exchange_rate : null,
            'frequency' => $request->frequency
        ]); 

        return response()->json([
            'success' => true,
            'data' => [
                'Servicio creado exitosamente'
            ]
        ]);
    }

    //Services list
    public function list_services(Request $request) {

        $year = Carbon::now()->year;

        $services = Service::select('id','budget_id','supplier_id','name','description','amount','currency_id','exchange_rate','frequency')
            ->with('budget:id,area_id,code,description,period,initial_budget','budget.area:id,name,code')
            ->with('supplier:id,name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'services' => $services
            ]
        ]);
    }

    //Service detail
    public function detail_service(Request $request, Service $service) {

        return response()->json([
            'success' => true,
            'data' => [
                'service' => $service
                            ->load('budget:id,area_id,code,description,period,initial_budget','budget.area:id,name,code')
                            ->load('supplier:id,name,document_type_id,document_number,email,phone,address,logo_url,district_id,country_id','supplier.document_type:id,name','supplier.district:id,name,ubigeo,province_id','supplier.district.province:id,name,department_id','supplier.district.province.department:id,name','supplier.country:id,name')
                            ->load('purchase_invoices')
            ]
        ]);
    }
}
