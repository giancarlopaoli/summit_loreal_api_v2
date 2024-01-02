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

        if($request->monto < 1000 && $request->moneda == 'usd'){
            return response()->json([
                'error' => 'El monto mínimo de operación es $1000.00.',
            ]);
        }

        if($request->monto > 9999999999.99){
            return response()->json([
                'error' => 'El valor instroducido no es un monto válido.',
            ]);
        }  

        if(($request->tipo == 'envias' && $request->moneda == 'usd') || ($request->tipo == 'recibes' && $request->moneda == 'pen')){
            $tipoOp = 2;
        }
        else{
            $tipoOp = 1;
        }

        $tc = $tipoOp == 1 ?
            TipoCambio::latest('TipoCambioId')->first()->Compra :
            TipoCambio::latest('TipoCambioId')->first()->Venta;


        if( $request->moneda == 'usd'){
            $montoop = $request->monto;
        }
        else{
            $montoop = ($request->tipo == 'envias') ? $request->monto/TipoCambio::latest('TipoCambioId')->first()->Compra : $request->monto*TipoCambio::latest('TipoCambioId')->first()->Venta;
            
            $comisionTmp = Comision::where('ComisionId', 1)->first()->comision;

            if(time() < strtotime(Config::where('acronimo', 'HORCIEOPE')->first()->valor)){//Antes de las 1:30pm
                $spreadsTmp[] = Comision::where('ComisionId', 1)->first()->spread;
            }else{
                $spreadsTmp[] = Comision::where('ComisionId', 1)->first()->spreadAux;
            }
            
            $spreadTmp = DB::table('spread')->where('ComisionId', 1)->where('Estado', 'ACT');
            $spreadProvTmp = ($request->tipo == 'envias') ? $spreadTmp->min('Compra') : $spreadTmp->min('Venta');
            if(!is_null($spreadProvTmp)) $spreadsTmp[] = $spreadProvTmp;
            $spreadFTmp = min($spreadsTmp);


            $tc_final_tmp = $tipoOp == 1 ?
            round(($tc + $spreadFTmp) * (1 + $comisionTmp / 100), 4) :
            round(($tc - $spreadFTmp) * (1 - $comisionTmp / 100), 4);

            $montoop = $request->monto/$tc_final_tmp;
            //$montopen = 1000*$tc_final_tmp;

            $montopen = ceil(1000*$tc_final_tmp);
        }

        if($montoop < 1000){
            return response()->json([
                'error' => 'El monto mínimo de operación es $1,000 o S/'.number_format($montopen,0),
            ]);
        }


        if(time() < strtotime(Config::where('acronimo', 'HORCIEOPE')->first()->valor)){//Antes de las 1:30pm
            $rango = Comision::where('RangoMin', '<=', $montoop)
                ->where('RangoMax', '>=', $montoop)
                ->where('Estado', 'ACT')
                ->select('ComisionId', 'comision', 'RangoMin', 'RangoMax', 'spread')
                ->first();
        }else{
            $rango = Comision::where('RangoMin', '<=', $montoop)
                ->where('RangoMax', '>=', $montoop)
                ->where('Estado', 'ACT')
                ->select('ComisionId', 'comision', 'RangoMin', 'RangoMax', 'spreadAux as spread')
                ->first();
        }


        if(is_null($rango)){
            return response()->json([
                'error' => 'El monto ingresado es incorrecto.',
            ]);
        }

        

        $proveedores = Cliente::where('TipoEmpresaId', 2)->with('spreads')->get();

        $spreads = [];
        //$spreads[] = $rango->spread;

        foreach ($proveedores as $proveedor) {
            $sp = $proveedor->spreads->where('Estado', 'ACT')->where('ComisionId', $rango->ComisionId)->first();
            if($sp == null){
                $spreads[] = $rango->spread;
            }else{
                $spreads[] = $tipoOp == 1 ? $sp->Venta : $sp->Compra;
            }
        }


        /*return response()->json([
                'error' => $spreads,
                'spreadFinal' => $spreadFTmp,
                'comisionTmp' => $spreadsTmp
            ]);*/


        $spread = min($spreads);
        $comision = $rango->comision;
        

        $tc_final = $tipoOp == 1 ?
            round(($tc + $spread) * (1 + $comision / 100), 4) :
            round(($tc - $spread) * (1 - $comision / 100), 4);


        /*$tc_final_compra = round(($tcc + $spreadVenta) * (1 + $comision / 100), 4);
        $tc_final_venta = round(($tcv - $spreadCompra) * (1 - $comision / 100), 4);*/


        $porc_ahorro = 0.0155;

         
        if($request->tipo == 'envias' && $request->moneda == 'usd'){
            $monto_comision = round(round($request->monto * ($tc - $spread),2) * $comision/100, 2);
            $igv = round($monto_comision *(1-1/1.18),2);

            $monto_cambio = round($request->monto * ($tc - $spread), 2) - $monto_comision;
            $tc_final = round($monto_cambio/$request->monto,4);

            $ahorro = $monto_cambio * $porc_ahorro;
        }
        elseif ($request->tipo == 'recibes' && $request->moneda == 'usd') {
            $monto_comision = round(round($request->monto * ($tc + $spread),2) * $comision/100, 2);
            $igv = round($monto_comision *(1-1/1.18),2);

            $monto_cambio = round($request->monto * ($tc + $spread), 2) + $monto_comision;
            $tc_final = round($monto_cambio/$request->monto,4);

            $ahorro = $monto_cambio * $porc_ahorro;
            
        }
        elseif ($request->tipo == 'recibes' && $request->moneda == 'pen') {
            
            $monto_tmp = ($request->monto)/($tc - $spread);
            //$monto_cambio = round($monto_tmp/(1-$comision/100),2);

            $monto_cambio = ($request->monto)/($tc_final);

            //$monto_comision = round(round($monto_cambio * ($tc - $spread), 2) * $comision/100, 2);
            $monto_comision = round($monto_cambio * $tc_final * $comision/100, 2);

            $tc_final = round($request->monto/$monto_cambio,4);

            $igv = round($monto_comision * (1-1/1.18),2);

            $ahorro = $request->monto * $porc_ahorro;
        }
        else{
            $monto_tmp = ($request->monto)/($tc + $spread);
            $monto_cambio = round($monto_tmp/(1 + $comision/100),2);
            $tc_final = round($request->monto/$monto_cambio,4);

            $monto_comision = round(round($monto_cambio * ($tc + $spread),2) * $comision/100, 2);
            $igv = round($monto_comision * (1-1/1.18),2);

            $ahorro = $request->monto * $porc_ahorro;
        }

        if($request->moneda == 'pen' && $monto_cambio < 1000 ){
            return response()->json([
                'error' => 'El monto mínimo de operación es $1000.00.',
            ]);
        }

        return response()->json([
            'tc_final'      => round($tc_final, 4),
            'monto_cambio' => round($monto_cambio, 2),
            'comision' => round($monto_comision, 2),
            'igv' => $igv,
            'ahorro' => round($ahorro, 2)
        ]);
    }

    public function tipocambio() {
        $min_amount = 1000;
        $exchange_rate = ExchangeRate::latest()->first()->for_user(null, $min_amount);

        return response()->json([
            'success' => true,
            'data' => [
                'exchange_rate' => $exchange_rate
            ]
        ]);
    }
}
