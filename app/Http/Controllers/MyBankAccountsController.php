<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MyBankAccountsController extends Controller
{
    public function list_accounts(Request $request, $client_id) {
        $client = Client::find($client_id);

        if($client == null) {
            return response()->json([
                'success' => false,
                'errors' => 'Cliente no encontrado'
            ], 404);
        }

        $accounts = $client->bank_accounts()
            ->where('active', true)
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

    public function new_account(Request $request, $client_id) {

        $client = Client::find($client_id);

        if($client == null) {
            return response()->json([
                'success' => false,
                'errors' => 'Cliente no encontrado'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'bank_id' => 'required|exists:banks,id',
            'account_type' => 'required|exists:account_types,id',
            'currency_id' => 'required|exists:currencies,id',
            'account_number' => 'required|min:10',
            'cci' => 'required|min:10'
        ]);

        if($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ]);
        }

        $client->bank_accounts()->create([
            'alias' => $request->alias ?? '',
            'bank_id' => $request->bank_id,
            'account_number' => $request->account_number,
            'cci_number' => $request->cci_number,
            'active' => true,
            'comments' => '',
            'account_type_id' => $request->account_type_id,
            'currency_id' => $request->currency_id,
            'updated_by' => auth()->id()
        ]);
    }
}
