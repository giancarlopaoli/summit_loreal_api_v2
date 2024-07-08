<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
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
}
