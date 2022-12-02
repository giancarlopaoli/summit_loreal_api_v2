<?php

namespace App\Http\Controllers\Admin\Operations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
/*use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Enums;*/
use App\Models\ExchangeRate;
use Carbon\Carbon;

class ExchangeRateController extends Controller
{

    //Exchange Rate list
    public function list(Request $request) {
        $val = Validator::make($request->all(), [
            'date' => 'nullable|date'
        ]);
        if($val->fails()) return response()->json($val->messages());

        $date = isset($request->date) ? $request->date : Carbon::now()->format('Y-m-d');

        $exchange_rate = ExchangeRate::select('id', 'compra', 'venta', 'created_at')
            ->whereRaw("DATE(created_at) = DATE('$date')")
            ->orderByDesc('created_at')
            ->get();

        if(count($exchange_rate) == 0){
            $exchange_rate = ExchangeRate::select('id', 'compra', 'venta', 'created_at')
                ->orderByDesc('created_at')
                ->take(1)
                ->get();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'exchange_rate' => $exchange_rate
            ]
        ]);
    }

    //Exchange Rate list
    public function delete(Request $request, ExchangeRate $exchange_rate) {

        $exchange_rate->delete();

        return response()->json([
            'success' => true,
            'data' => [
                'Tipo de cambio eliminado exitosamente'
            ]
        ]);
    }
}
