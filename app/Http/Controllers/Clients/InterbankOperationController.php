<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\IbopsRange;
use App\Models\EscrowAccount;
use App\Models\Client;
use App\Models\ExchangeRate;
use App\Models\IbopsClientComission;

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
            // Retrieving comission amount 
            $range = IbopsRange::select('comission_spread', 'spread')
                ->where('min_range','<=',$request->amount)
                ->where('max_range','>=',$request->amount)
                ->where('currency_id',$request->currency_id)
                ->get();

            if($range->count() == 0){
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'Monto fuera de rango de operación'
                    ]
                ]);
            }

            $range = $range->first();

            $val_comision = $range->comission_spread;
            $val_spread = $range->spread;

            $exchange_rate = ExchangeRate::latest()->first()->venta;


            // Buscando comisiones del cliente
            $client_comision = IbopsClientComission::where('client_id', $request->client_id)
                ->where('active', true)
                ->get();
            
            if($client_comision->count() > 0){
                $client_comision = $client_comision->first();

                if(!is_null($client_comision->comission)) $val_comision = $client_comision->comission;
                if(!is_null($client_comision->spread)) $val_spread = $client_comision->spread;
                if(!is_null($client_comision->exchange_rate)) $exchange_rate = $client_comision->exchange_rate;
            }

            /*return response()->json([
                'error' => true,
                'msg' => $exchange_rate
            ]);*/

            // Calculating Vendor comission in base of 6 decimals spread
            $spread = round (($exchange_rate + ($val_spread / 10000))/$exchange_rate, 6) - 1;
            $financial_expenses = round($request->amount * $spread, 2 );

            $tcventa = round($exchange_rate + ($val_spread / 10000), 4);

            // Calculating commission amount
            $total_comission = round($request->amount * ($val_comision / 10000), 2);
            $comission = round($total_comission/1.18, 2);
            $igv = round($total_comission - $comission,2);

            $depositar = round($request->amount + $financial_expenses + $total_comission,2);

            return response()->json([
                'success' => true,
                'data' => [
                    'transfers' => round($request->amount,2),
                    'financial_expenses' => $financial_expenses,
                    'comission' => $comission,
                    'igv' => $igv,
                    'exchange_rate' => $exchange_rate,
                    'receives' => $depositar,
                    'selling_exchange_rate' => $tcventa
                ]
            ]);

        } catch (\Exception $e) {
            logger('Error en detalleOperacion@InterbancariasController', ["error" => $e]);
        }

        return response()->json([
                'success' => false,
                'data' => [
                    "Se encontró un error al cotizar la operación"
                ]
            ]);
    }

}
