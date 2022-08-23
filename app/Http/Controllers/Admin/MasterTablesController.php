<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccountType;
use App\Models\Bank;
use App\Models\EscrowAccount;
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
            'data' => AccountType::where('active', true)->get()
        ]);
    }

    public function escrow_accounts(Request $request) {
        $validator = Validator::make($request->all(), [
            'currency_id' => 'exists:currencies,id'
        ]);
        if ($validator->fails()) return response()->json($validator->messages());

        $escrow_accounts = EscrowAccount::when($request->currency_id, function ($query, $currency_id) {
            $query->where('currency_id', $currency_id);
        })->get();

        return response()->json([
            'success' => true,
            'data' => $escrow_accounts
        ]);
    }
}
