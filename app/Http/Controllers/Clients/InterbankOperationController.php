<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\IbopsRange;
use App\Models\EscrowAccount;
use App\Models\Client;

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

    //Scrow accounts list
    public function get_escrow_accounts(Request $request) {
        $val = Validator::make($request->all(), [
            'currency_id' => 'required|numeric',
        ]);
        if($val->fails()) return response()->json($val->messages());

        $escrow_accounts = EscrowAccount::where('currency_id', $request->currency_id)
            ->select('id','bank_id','account_number','cci_number','currency_id')
            ->where('active', true)
            ->with('bank:id,name,shortname,image', 'currency:id,name,sign')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $escrow_accounts
        ]);
    }

    //Lista de cuentas del cliente
    public function get_client_bank_accounts(Request $request) {
        $val = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id',
            'currency_id' => 'required|exists:currencies,id',
        ]);
        if($val->fails()) return response()->json($val->messages());

        $client = Client::find($request->client_id);

        if($client == null) {
            return response()->json([
                'success' => false,
                'errors' => 'Cliente no encontrado'
            ], 404);
        }

        $bank_accounts = $client->bank_accounts()
            ->select('id','client_id','alias','account_number','cci_number','main','bank_account_status_id','currency_id','bank_id')
            ->where('currency_id', $request->currency_id)
            ->whereRelation('status', 'name', 'Activo')
            ->with([
            'bank:id,name,shortname,image',
            'currency:id,name,sign'
        ])->get();

        return response()->json([
            'success' => true,
            'data' => $bank_accounts
        ]);
    }

    // Operation quote
    public function quote_operation(Request $request) {
        $val = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id',
            'currency_id' => 'required|numeric',
            'amount' => 'required|numeric',
        ]);
        if($val->fails()) return response()->json($val->messages());

        try {
            $comisiones = DB::connection('mysql')
                ->table('ibops_comission')
                ->select('comission','spread')
                ->where('minval','<=',$request->monto)
                ->where('maxval','>=',$request->monto)
                ->where('currency_id',$request->divisa_id)
                ->get();

            if($comisiones->count() == 0){
                return response()->json([
                    'error' => true,
                    'msg' => 'Monto fuera de rango de operación'
                ]);
            }

            $comisiones = $comisiones->first();

            $val_comision = $comisiones->comission;
            $val_spread = $comisiones->spread;
            
            $tc = DB::table('TipoCambio')
                ->where('Estado', 'ACT')
                ->first()->Venta;

            // Buscando comisiones del cliente
            $comisiones_cliente = DB::connection('mysql')
                ->table('ibops_client_comission')
                ->where('client_id', $request->authuser->customer_id)
                ->where('status', 'Activo')
                ->get();

            if($comisiones_cliente->count() > 0){
                $comisiones_cliente = $comisiones_cliente->first();

                if(!is_null($comisiones_cliente->comission)) $val_comision = $comisiones_cliente->comission;
                if(!is_null($comisiones_cliente->spread)) $val_spread = $comisiones_cliente->spread;
                if(!is_null($comisiones_cliente->exchange_rate)) $tc = $comisiones_cliente->exchange_rate;
            }

            // Calculando gastos financieros en base a un spread resultante a 6 decimales
            $spread = round (($tc + ($val_spread / 10000))/$tc, 6) - 1;
            $gastos_financieros = round($request->monto * $spread, 2 );

            $tcventa = $tc + ($val_spread / 10000);
            //$gastos_financieros = round ($request->monto * ($tc + ($val_spread / 10000))/$tc - $request->monto, 2);


            // Cálculo comisión
            $comision_total = round($request->monto * ($val_comision / 10000), 2);
            $comision = round($comision_total/1.18, 2);
            $igv = round($comision_total - $comision,2);

            $depositar = round($request->monto + $gastos_financieros + $comision_total,2);

            $terminos = DB::table('Cliente')->where('ClienteId', $request->authuser->customer_id)->first()->AprobacionTerminos;

            return response()->json([
                'success' => true,
                'data' => [
                    'transferir' => round($request->monto,2),
                    'gastos_financieros' => $gastos_financieros,
                    'comision' => $comision,
                    'igv' => $igv,
                    'tipocambio' => $tc,
                    //'itf' => round($request->monto* (2/10000),2),
                    'depositar' => $depositar,
                    'tcventa' => $tcventa,
                    'terminos' => $terminos
                ]
            ]);

        } catch (\Exception $e) {
            logger('Error en detalleOperacion@InterbancariasController', ["error" => $e]);
        }

        return response()->json([
            'error' => true,
        ]);
    }

}
