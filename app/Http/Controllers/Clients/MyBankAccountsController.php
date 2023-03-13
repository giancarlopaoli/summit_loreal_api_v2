<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Models\BankAccountStatus;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MyBankAccountsController extends Controller
{
    public function list_accounts(Request $request) {
        $client = Client::find($request->client_id);

        if($client == null) {
            return response()->json([
                'success' => false,
                'errors' => 'Cliente no encontrado'
            ], 404);
        }

        $accounts = $client->bank_accounts()
            ->whereRelation('status', 'name', 'Activo')
            ->with([
            'bank',
            'account_type',
            'currency'
        ])->get();

        if($accounts->isEmpty()) {
            return response()->json([
                'success' => false,
                'errors' => 'No cuenta con cuentas bancarias'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'accounts' => $accounts
            ]
        ]);
    }

    public function new_account(Request $request) {

        $client = Client::find($request->client_id);

        if($client == null) {
            return response()->json([
                'success' => false,
                'errors' => 'Cliente no encontrado'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'bank_id' => 'required|exists:banks,id',
            'account_type_id' => 'required|exists:account_types,id',
            'currency_id' => 'required|exists:currencies,id',
            'account_number' => 'required|min:10',
            'cci_number' => 'required|min:10'
        ]);

        if($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ]);
        }

        $account = $client->bank_accounts()->create([
            'alias' => $request->alias ?? '',
            'bank_id' => $request->bank_id,
            'account_number' => $request->account_number,
            'cci_number' => $request->cci_number,
            'bank_account_status_id' => BankAccountStatus::where('name', 'Activo')->first()->id,
            'comments' => '',
            'account_type_id' => $request->account_type_id,
            'currency_id' => $request->currency_id,
            'updated_by' => auth()->id()
        ]);

        return response()->json([
            'success' => true,
            'data' => $account
        ]);
    }

    public function delete_account(Request $request, $account_id) {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id',
        ]);

        if($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ]);
        }

        $client = Client::find($request->client_id);
        $account = $client->bank_accounts()->where('id', $account_id)->first();

        if($account == null) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'Cuenta bancaria no encontrada para este cliente'
                ]
            ], 404);
        }

        $account->updated_by = auth()->id();
        $account->bank_account_status_id = BankAccountStatus::where('name', 'Inactivo')->first()->id;
        $account->save();

        $account->delete();

        return response()->json([
            'success' => true
        ]);
    }

    public function set_main_account(Request $request) {
        $client = Client::find($request->client_id);

        if($client == null) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'Cliente no encontrado'
                ]
            ], 404);
        }

        $account = $client->bank_accounts()->where('id', $request->account_id)->first();

        if($account == null) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'La cuenta no es parte del cliente'
                ]
            ], 404);

        }

        $client->bank_accounts()->update(['main' => false]);

        $account->main = true;
        $account->save();

        return response()->json([
            'success' => true
        ]);
    }

    public function edit_bank_account(Request $request, $account_id) {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id',
        ]);

        if($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ]);
        }

        $client = Client::find($request->client_id);
        $account = $client->bank_accounts()->where('id', $account_id)->first();

        if($account == null) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'Cuenta bancaria no encontrada para este cliente'
                ]
            ], 404);
        }

        $account->updated_by = auth()->id();
        $account->alias = $request->alias;
        $account->save();


        return response()->json([
            'success' => true
        ]);
    }
}
