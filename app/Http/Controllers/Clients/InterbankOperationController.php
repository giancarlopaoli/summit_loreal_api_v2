<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\IbopsRange;

class InterbankOperationController extends Controller
{
    // Operation minimum amount
    public function get_minimum_amount(Request $request) {
        $val = Validator::make($request->all(), [
            'currency_id' => 'required|numeric',
        ]);
        if($val->fails()) return response()->json($val->messages());

        $comisiones = IbopsRange::selectRaw('min(min_range) as minimum_amount')
            ->where('currency_id',$request->currency_id)
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'minimum_amount' => $comisiones->minimum_amount
            ]
        ]);
    }
}
