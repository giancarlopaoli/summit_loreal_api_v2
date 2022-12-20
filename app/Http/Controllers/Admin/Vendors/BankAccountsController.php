<?php

namespace App\Http\Controllers\Admin\Vendors;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\BankAccount;
use App\Models\BankAccountStatus;

class BankAccountsController extends Controller
{
    //Bank Account List
    public function bank_accounts(Request $request) {
        $val = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id,type,PL',
        ]);
        if($val->fails()) return response()->json($val->messages());

        $bank_accounts = BankAccount::select('id','client_id','bank_id','account_number','cci_number','bank_account_status_id','account_type_id','currency_id')
            ->with('bank:id,name,shortname,image')
            ->with('status:id,name')
            ->with('currency:id,name,sign')
            ->where('client_id', $request->client_id)->get();

        return response()->json([
            'success' => true,
            'data' => [
                'bank_accounts' => $bank_accounts
            ]
        ]);
    }
    
    //Update status Bank Account
    public function update_bank_account(Request $request, BankAccount $bank_account) {
        $val = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id,type,PL',
        ]);
        if($val->fails()) return response()->json($val->messages());

        if($bank_account->bank_account_status_id == BankAccountStatus::where('name', 'Activo')->first()->id){

            $bank_account->bank_account_status_id = BankAccountStatus::where('name', 'Inactivo')->first()->id;
        }
        elseif($bank_account->bank_account_status_id == BankAccountStatus::where('name', 'Inactivo')->first()->id){
            $bank_account->bank_account_status_id = BankAccountStatus::where('name', 'Activo')->first()->id;
        }
        $bank_account->save();

        return response()->json([
            'success' => true,
            'data' => [
                'Cuenta bancaria actualizada'
            ]
        ]);
    }
}
