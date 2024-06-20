<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Supplier;
use App\Models\SupplierContact;

class SuppliersController extends Controller
{
    // New Supplier
    public function new_supplier(Request $request) {
        $val = Validator::make($request->all(), [
            'name' => 'required|string',
            'document_type_id' => 'required|in:1,2,3,11',
            'document_number' => 'required|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'detraction_account' => 'nullable|string',
            "address" => 'nullable|string',
            "district_id" => 'nullable|exists:districts,id',
            "country_id" => 'nullable|exists:countries,id'
        ]);
        if($val->fails()) return response()->json($val->messages());

        $exists = Supplier::where('document_type_id', $request->document_type_id)
            ->where('document_number', $request->document_number)
            ->get();

        if($exists->count() > 0){
            return response()->json([
                'success' => false,
                'data' => [
                    'Proveedor ya se encuentra registrado'
                ]
            ]);
        }

        $insert = Supplier::create([
            'name' => $request->name,
            'document_type_id' => $request->document_type_id,
            'document_number' => $request->document_number,
            'email' => $request->email,
            'phone' => $request->phone,
            'detraction_account' => $request->detraction_account,
            'address' => $request->address,
            'district_id' => $request->district_id,
            'country_id' => $request->country_id
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'Proveedor creado exitosamente'
            ]
        ]);
    }

    //Suppliers list
    public function list_suppliers(Request $request) {

        $supplier = Supplier::select('id','name','document_type_id','document_number','email','phone','detraction_account','logo_url')
            ->with('document_type:id,name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'supplier' => $supplier
            ]
        ]);
    }

    // Add supplier contact
    public function new_contact(Request $request) {

        $supplier = Supplier::select('id','name','document_type_id','document_number','email','phone','detraction_account','logo_url')
            ->with('document_type:id,name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'supplier' => $supplier
            ]
        ]);
    }
}
