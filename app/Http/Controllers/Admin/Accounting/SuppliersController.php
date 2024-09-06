<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Supplier;
use App\Models\SupplierBankAccount;
use App\Models\SupplierContact;
use App\Models\SupplierContactType;

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
            'apply_detraction' => 'Si',
            'detraction_account' => $request->detraction_account,
            'address' => $request->address,
            'district_id' => $request->district_id,
            'country_id' => $request->country_id,
            'status' => 'Activo'
        ]);


        if($request->hasFile('logo')){
            $file = $request->file('logo');
            $path = env('AWS_ENV').'/accounting/supplier/';

            
            $original_name = $file->getClientOriginalName();
            $longitud = Str::length($file->getClientOriginalName());

            $filename = "logo_" . $insert->id . "_" . substr($original_name, $longitud - 6, $longitud);

            try {
                $s3 = Storage::disk('s3')->putFileAs($path, $file, $filename);

                $insert->logo_url = $path . $filename;
                $insert->save();

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

        return response()->json([
            'success' => true,
            'data' => [
                'Proveedor creado exitosamente'
            ]
        ]);
    }

    //Suppliers list
    public function list_suppliers(Request $request) {

        $supplier = Supplier::select('id','name','document_type_id','document_number','email','phone','status')
            ->with('document_type:id,name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'supplier' => $supplier
            ]
        ]);
    }

    //Supplier detail
    public function detail_supplier(Request $request, Supplier $supplier) {

        return response()->json([
            'success' => true,
            'data' => [
                'supplier' => $supplier->load('document_type:id,name')
                    ->load('contacts','contacts.contact_type:id,name')
                    ->load('bank_accounts:id,supplier_id,bank_id,account_number,cci_number,currency_id,account_type_id,main','district:id,name,province_id,ubigeo','district.province:id,name,department_id','district.province.department:id,name','bank_accounts.bank:id,name,shortname','bank_accounts.currency:id,name','bank_accounts.account_type:id,name,shortname')
                    ->load('country:id,name,prefix')
                    ->only('id','name','document_type_id','document_number','email','phone','address','district_id','status','apply_detraction','detraction_account','logo_url','document_type','contacts','bank_accounts','district','country_id','country')
            ]
        ]);
    }

    // New Supplier
    public function edit_supplier(Request $request, Supplier $supplier) {

        $val = Validator::make($request->all(), [
            'name' => 'required|string',
            'document_type_id' => 'required|in:1,2,3,11',
            'document_number' => 'required|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'apply_detraction' => 'nullable|in:Si,No',
            'detraction_account' => 'nullable|string',
            "address" => 'nullable|string',
            "district_id" => 'nullable|exists:districts,id',
            "country_id" => 'nullable|exists:countries,id'
        ]);
        if($val->fails()) return response()->json($val->messages());


        $supplier->update($request->only('name','document_type_id','document_number','email','phone','apply_detraction','detraction_account','address','district_id','country_id','status'));


        if($request->hasFile('logo')){
            $file = $request->file('logo');
            $path = env('AWS_ENV').'/accounting/supplier/';

            
            $original_name = $file->getClientOriginalName();
            $longitud = Str::length($file->getClientOriginalName());

            $filename = "logo_" . $supplier->id . "_" . substr($original_name, $longitud - 6, $longitud);

            try {
                $s3 = Storage::disk('s3')->putFileAs($path, $file, $filename);

                $supplier->logo_url = $path . $filename;
                $supplier->save();

            } catch (\Exception $e) {
                // Registrando el el log los datos ingresados
                logger('ERROR: subiendo logo proveedor: edit_supplier@SuppliersController', ["error" => $e]);

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
                'Proveedor modificado exitosamente'
            ]
        ]);
    }

    //Suppliers Contact types
    public function contact_types(Request $request) {

        $contact_types = SupplierContactType::select('id','name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'contact_types' => $contact_types
            ]
        ]);
    }

    // Add supplier contact
    public function new_contact(Request $request, Supplier $supplier) {
        $val = Validator::make($request->all(), [
            'name' => 'required|string',
            'job_area' => 'required|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            "supplier_contact_type_id" => 'required|exists:mysql2.supplier_contact_types,id'
        ]);
        if($val->fails()) return response()->json($val->messages());

        $insert = $supplier->contacts()->create([
            'name' => $request->name,
            'job_area' => $request->job_area,
            'email' => $request->email,
            'phone' => $request->phone,
            'supplier_contact_type_id' => $request->supplier_contact_type_id
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'Contacto creado exitosamente'
            ]
        ]);
    }

    // Add supplier contact
    public function list_contacts(Request $request, Supplier $supplier) {

        return response()->json([
            'success' => true,
            'data' => [
                'supplier_contacts' => $supplier->contacts()
                    ->select('id','supplier_id','name','email','phone','supplier_contact_type_id')
                    ->with(['contact_type:id,name'])
                    ->get()
            ]
        ]);
    }

    // Delete supplier contact
    public function delete_contact(Request $request, SupplierContact $supplier_contact) {

        $supplier_contact->delete();

        return response()->json([
            'success' => true,
            'data' => [
                'Contacto eliminado exitosamente'
            ]
        ]);
    }

    // Add Bank Account
    public function new_bank_account(Request $request, Supplier $supplier) {
        $val = Validator::make($request->all(), [
            'account_number' => 'required|string',
            'cci_number' => 'required|string|min:20|max:20',
            'bank_id' => 'required|exists:banks,id',
            'currency_id' => 'required|exists:currencies,id',
            'account_type_id' => 'required|exists:account_types,id'
        ]);
        if($val->fails()) return response()->json($val->messages());

        $insert = $supplier->bank_accounts()->create([
            'bank_id' => $request->bank_id,
            'account_number' => $request->account_number,
            'cci_number' => $request->cci_number,
            'account_type_id' => $request->account_type_id,
            'currency_id' => $request->currency_id,
            'status' => 'Activo'
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'Cuenta bancaria creada exitosamente'
            ]
        ]);
    }

    // Edit default bank account
    public function set_default_account(Request $request, SupplierBankAccount $supplier_bank_account) {

        SupplierBankAccount::where("supplier_id", $supplier_bank_account->supplier_id)->where("main", 1)->update(["main" => 0]);

        $supplier_bank_account->main = 1;
        $supplier_bank_account->save();

        return response()->json([
            'success' => true,
            'data' => [
                'Cuenta bancaria predeterminada modificada exitosamente'
            ]
        ]);
    }

    
}
