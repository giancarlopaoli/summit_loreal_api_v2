<?php

namespace App\Http\Controllers\Admin;

use App\Events\NewExchangeRate;
use App\Events\DatatecExchangeRate;
use App\Http\Controllers\Controller;
use App\Models\ExchangeRate;
use App\Models\Range;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Clients\InmediateOperationController;

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

    public function datatec_exchange_rate(Request $request) {
        
        logger('LOG: Tipo de Cambio: DatatecController@datatec_exchange_rate', ["data" => $request->all()]);

        /*$validator = Validator::make($request->all(), [
            'Compra' => 'required|numeric',
            'Venta' => 'required|numeric'
        ]);*/

        $request['compra'] = $request->Compra;
        $request['venta'] = $request->Venta;

        $registro = DatatecController::new_exchange_rate($request);

        return response()->json(
            $registro
        );        
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

    public function exchange_rate_list(Request $request) {
        $min_amount = Range::minimun_amount();
        $exchange_rate = ExchangeRate::latest()->get();

        return response()->json([
            'success' => true,
            'data' => [
                'exchange_rate' => $exchange_rate
            ]
        ]);
    }

    public function calculadora(Request $request) {
        $val = Validator::make($request->all(), [
            'monto' => 'required|numeric',
            'tipo' => 'required|in:envias,recibes',
            'moneda' => 'required|in:usd,pen'
        ]);
        if($val->fails()) return response()->json($val->messages());

        $request['amount'] = $request->monto;
        $request['client_id'] = 363;

        if($request->moneda == 'usd'){
            $request['currency_id'] = 2;
        }
        elseif($request->moneda == 'pen'){
            $request['currency_id'] = 1;
        }

        if(($request->moneda == 'usd' && $request->tipo == 'envias') || ($request->moneda == 'pen' && $request->tipo == 'recibes')){
            $request['type'] = 'venta';
        }
        else{
            $request['type'] = 'compra';
        }

        $consult = new InmediateOperationController();
        $result = $consult->quote_operation($request)->getData();

        //return response()->json($result);

        if($result->success){

            $porc_ahorro = 0.0155;
            $ahorro = $result->data->amount * $porc_ahorro;

            if($request->moneda == 'usd'){
                $amount = $result->data->final_mount;
            }
            else{
                $amount = $result->data->amount;
            }

            return response()->json([
                'tc_final'      => round($result->data->final_exchange_rate, 4),
                'monto_cambio' => round($amount, 2),
                'comision' => round($result->data->comission_amount, 2),
                'igv' => $result->data->igv,
                'ahorro' => round($ahorro, 2)
            ]);
        }
        else{
            return response()->json($result);
        }
        
    }

    public function tipocambio() {
        $min_amount = 1000;
        $exchange_rate = ExchangeRate::latest()->first()->for_user(null, $min_amount);

        return response()->json([
            'tc_venta'  => $exchange_rate->venta,
            'tc_compra' => $exchange_rate->compra
        ]);
    }
}
