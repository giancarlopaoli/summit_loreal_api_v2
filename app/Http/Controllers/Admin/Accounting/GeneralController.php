<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'services' => $services
            ]
        ]);
    }
}
