<?php

namespace App\Http\Controllers\Admin\Operations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Client;
use App\Models\ClientStatus;
use App\Models\BankAccount;

class ClientsController extends Controller
{
    //Clients list
    public function list(Request $request) {
        $val = Validator::make($request->all(), [
            'type' => 'required|in:pending,approved,canceled,corfid,all'
        ]);
        if($val->fails()) return response()->json($val->messages());


        $client = Client::select('id','name','last_name','mothers_name','document_type_id','phone','email','address','birthdate','customer_type','type','client_status_id','billex_approved_at','corfid_approved_at','registered_at','updated_at as last_update')
            ->with('document_type:id,name','status:id,name')
            ->with('bank_accounts:id,client_id,bank_id,account_number,cci_number,bank_account_status_id,currency_id','bank_accounts.bank:id,shortname,image','bank_accounts.currency:id,name,sign','bank_accounts.status:id,name');
        
        if($request->type == 'pending'){
            $client = $client->whereIn('client_status_id', ClientStatus::whereIn('name', ['Registrado','Aprobado Billex','Rechazo parcial'])->get()->pluck('id'));
        }
        elseif($request->type == 'approved'){
            $client = $client->whereIn('client_status_id', ClientStatus::whereIn('name', ['Activo','Pendiente Aprobacion'])->get()->pluck('id'));
        }
        elseif($request->type == 'canceled'){
            $client = $client->whereIn('client_status_id', ClientStatus::whereIn('name', ['Rechazado'])->get()->pluck('id'));
        }
        elseif($request->type == 'corfid'){
            $client = $client->whereIn('client_status_id', ClientStatus::whereIn('name', ['Aprobado Billex'])->get()->pluck('id'));
        }

        return response()->json([
            'success' => true,
            'data' => [
                'clients' => $client->get()
            ]
        ]);
    }

    //Bank Account list
    public function bank_account_list(Request $request, Client $client) {

        return response()->json([
            'success' => true,
            'data' => [
                'bank_accounts' => $client->bank_accounts->load('bank:id,name,shortname,main','status:id,name','currency:id,name,sign')
            ]
        ]);
    }

    //Edit Bank Account
    public function edit_bank_account(Request $request, BankAccount $bank_account) {
        $val = Validator::make($request->all(), [
            'account_number' => 'required|string|min:5',
            'cci_number' => 'required|string|min:20|max:20',
            'currency_id' => 'required|exists:currencies,id',
            'account_type_id' => 'required|exists:account_types,id'
        ]);
        if($val->fails()) return response()->json($val->messages());

        $bank_account->update($request->only(["account_number","cci_number","currency_id", "account_type_id"]));

        return response()->json([
            'success' => true,
            'data' => [
                'bank_account' => $bank_account
            ]
        ]);
    }
}
