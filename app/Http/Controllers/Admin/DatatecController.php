<?php

namespace App\Http\Controllers\Admin;

use App\Events\NewExchangeRate;
use App\Events\DatatecExchangeRate;
use App\Http\Controllers\Controller;
use App\Models\ExchangeRate;
use App\Models\Range;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DatatecController extends Controller
{
    public function new_exchange_rate(Request $request) {
        $validator = Validator::make($request->all(), [
            'compra' => 'required|numeric',
            'venta' => 'required|numeric'
        ]);

        if($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()->toJson()
            ]);
        }

        $exchange_rate = ExchangeRate::create([
            'compra' => $request->compra,
            'venta' => $request->venta
        ]);

        // Haciendo broadcast a los proveedores de liquidez
        DatatecExchangeRate::dispatch();


        $auth_users = User::get_authenticated_users();
        foreach ($auth_users as $user) {
            NewExchangeRate::dispatch($user);
        }

        return $exchange_rate;
    }

    public function exchange_rate(Request $request) {
        $min_amount = Range::minimun_amount();
        $exchange_rate = ExchangeRate::latest()->first();

        return response()->json([
            'success' => true,
            'data' => [
                'exchange_rate' => $exchange_rate
            ]
        ]);
    }
}
