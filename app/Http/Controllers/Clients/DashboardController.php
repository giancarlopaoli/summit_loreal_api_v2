<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ExchangeRate;
use App\Models\Range;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function get_indicators(Request $request) {
        $client = Client::find($request->client_id);

        if($client == null) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'El id de client enviado no existe'
                ]
            ]);
        }

        $latest_operations = $client
            ->operations()
            ->select('id','code','class','type','amount','currency_id','operation_date','operation_status_id','exchange_rate')
            ->latest()
            ->take(5)
            ->get();

        $latest_operations->load(['status:id,name', 'currency:id,name,sign']);

        $total_amount = $client->operations()->whereIn("operation_status_id", [4, 5])->selectRaw('SUM(amount) as total')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'operations' => $latest_operations,
                'total_operated_amount' => (float) $total_amount[0]->total
            ]
        ]);
    }

    public function graphs(Request $request) {
        $val = Validator::make($request->all(), [
            'type' => 'required|in:1,2,3,4'
        ]);

        if ($val->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $val->errors()->toJson()
            ]);
        }

        // Del día
        if($request->type == '1'){
            $data = ExchangeRate::select('created_at')
                ->selectRaw('(compra + venta)/2 as tipodecambio')
                ->whereRaw('DATE(created_at) = DATE(NOW())')
                ->orderByDesc('created_at')
                ->get();

            if(count($data) == 0){
                $data = ExchangeRate::select('created_at')
                    ->selectRaw('(compra + venta)/2 as tipodecambio')
                    ->orderByDesc('created_at')
                    ->take(1)
                    ->get();
            }
        }
        
        // De la última semana
        elseif($request->type == '2'){
            $data = ExchangeRate::selectRaw('max(created_at) as created_at,(select (exr.compra + exr.venta)/2 from exchange_rates exr where exr.created_at = max(exchange_rates.created_at) limit 1 ) as tipodecambio')
                ->whereRaw('DATE(created_at) >= DATE(NOW() - INTERVAL 7 DAY)')
                ->groupByRaw('DATE(created_at)')
                ->orderByRaw('max(created_at) desc')
                ->get();
        }
        // Del último mes
        elseif($request->type == '3'){
            $data = ExchangeRate::selectRaw('max(created_at) as created_at,(select (exr.compra + exr.venta)/2 from exchange_rates exr where exr.created_at = max(exchange_rates.created_at) limit 1 ) as tipodecambio')
                ->whereRaw('DATE(created_at) >= DATE(NOW() - INTERVAL 30 DAY)')
                ->groupByRaw('DATE(created_at)')
                ->orderByRaw('max(created_at) desc')
                ->get();
        }
        // Del último anio
        elseif($request->type == '4'){
            $data = ExchangeRate::selectRaw('max(created_at) as created_at,(select (exr.compra + exr.venta)/2 from exchange_rates exr where exr.created_at = max(exchange_rates.created_at) limit 1 ) as tipodecambio')
                ->whereRaw('DATE(created_at) >= DATE(NOW() - INTERVAL 365 DAY)')
                ->groupByRaw('DATE(created_at)')
                ->orderByRaw('max(created_at) desc')
                ->get();
        }
        else{
            return response()->json([
                'success' => false,
                'errors' => [
                    'Input incorrecto'
                ]
            ], 400);
        }

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    
    

    public function exchange_rate(Request $request) {
        $min_amount = Range::minimun_amount();
        $exchange_rate = ExchangeRate::latest()->first()->for_user(Auth::user(), $min_amount);

        return response()->json([
            'success' => true,
            'data' => [
                'exchange_rate' => $exchange_rate
            ]
        ]);
    }
}
