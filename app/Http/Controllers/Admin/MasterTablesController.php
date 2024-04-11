<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccountType;
use App\Models\Bank;
use App\Models\Currency;
use App\Models\DocumentType;
use App\Models\EscrowAccount;
use App\Models\Executive;
use App\Models\Role;
use App\Models\LeadContactType;
use App\Models\Region;
use App\Models\Sector;
use App\Models\EconomicActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MasterTablesController extends Controller
{
    public function banks() {
        return response()->json([
            'success' => true,
            'data' => Bank::where('active', true)->get()
        ]);
    }

    public function account_types() {
        return response()->json([
            'success' => true,
            'data' => AccountType::select('id','name','shortname','size','active')->where('active', true)->get()
        ]);
    }

    public function currencies() {
        return response()->json([
            'success' => true,
            'data' => Currency::select('id','name','iso_code','sign','image','active')->where('active', true)->get()
        ]);
    }

    public function escrow_accounts(Request $request) {
        $validator = Validator::make($request->all(), [
            'currency_id' => 'exists:currencies,id'
        ]);
        if ($validator->fails()) return response()->json($validator->messages());

        $escrow_accounts = EscrowAccount::when($request->currency_id, function ($query, $currency_id) {
                $query->where('currency_id', $currency_id);
            })
            ->select('id','bank_id','account_number','cci_number','currency_id')
            ->with('bank:id,name,shortname,image', 'currency:id,name,sign')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $escrow_accounts
        ]);
    }

    public function person_document_types() {
        return response()->json([
            'success' => true,
            'data' => DocumentType::select('id','name','size')->where('active', true)->wherein('name', ['DNI', 'Carné de extranjería','Pasaporte'])->get()
        ]);
    }

    public function associate_document_types() {
        return response()->json([
            'success' => true,
            'data' => DocumentType::select('id','name','size')->where('active', true)->wherein('name', ['RUC','DNI', 'Carné de extranjería','Pasaporte','No Domiciliado'])->get()
        ]);
    }

    public function clients_document_types() {
        return response()->json([
            'success' => true,
            'data' => DocumentType::select('id','name','size')->where('active', true)->wherein('name', ['RUC','DNI', 'Carné de extranjería'])->get()
        ]);
    }

    public function roles() {
        return response()->json([
            'success' => true,
            'data' => Role::select('id','name')->get()
        ]);
    }
    
    public function lead_contact_type() {
        return response()->json([
            'success' => true,
            'data' => LeadContactType::select('id','name')->get()
        ]);
    }

    public function regions() {
        return response()->json([
            'success' => true,
            'data' => Region::select('id','name')->get()
        ]);
    }

    public function sectors() {
        return response()->json([
            'success' => true,
            'data' => Sector::select('id','name')->get()
        ]);
    }

    public function economic_activities() {
        return response()->json([
            'success' => true,
            'data' => EconomicActivity::select('id','name')->get()
        ]);
    }

    public function executives_full_time() {
        return response()->json([
            'success' => true,
            'data' => Executive::select('id','type')
                ->where('type', 'Tiempo Completo')
                ->with('user:id,name,last_name')
                ->get()
        ]);
    }
}
