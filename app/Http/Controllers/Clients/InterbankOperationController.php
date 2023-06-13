<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\BankAccount;
use App\Models\Client;
use App\Models\Configuration;
use App\Models\Currency;
use App\Models\EscrowAccount;
use App\Models\ExchangeRate;
use App\Models\IbopsClientComission;
use App\Models\IbopsRange;
use App\Models\Operation;
use App\Models\OperationStatus;
use App\Models\OperationHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Enums;
use Illuminate\Support\Facades\Mail;
use App\Mail\OperationInstructions;
use App\Mail\NewInterbankOperation;

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
            'currency_id' => 'required|exists:currencies,id',
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

            // Retrieving client comissions
            $client_comision = IbopsClientComission::where('client_id', $request->client_id)
                ->where('active', true)
                ->get();
            
            if($client_comision->count() > 0){
                $client_comision = $client_comision->first();

                if(!is_null($client_comision->comission_spread)) $val_comision = $client_comision->comission_spread;
                if(!is_null($client_comision->spread)) $val_spread = $client_comision->spread;
                if(!is_null($client_comision->exchange_rate)) $exchange_rate = $client_comision->exchange_rate;
            }


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
                    'counter_value' => round(round($request->amount,2) + $financial_expenses,2),
                    'comission' => $comission,
                    'igv' => $igv,
                    'exchange_rate' => $exchange_rate,
                    'receives' => $depositar,
                    'selling_exchange_rate' => $tcventa,
                    'currency' => Currency::find($request->currency_id)->only(['id','name','sign'])
                ]
            ]);

        } catch (\Exception $e) {
            logger('Error en quote_operation@InterbancariasController', ["error" => $e]);
        }

        return response()->json([
            'success' => false,
            'data' => [
                "Se encontró un error al cotizar la operación"
            ]
        ]);
    }

    //Creación de operación
    public function create_operation(Request $request) {
        $val = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'escrow_account_id' => 'required|exists:escrow_accounts,id',
            'amount' => 'required|numeric',
            'comission' => 'required|numeric',
            'igv' => 'required|numeric',
            'currency_id' => 'required|numeric',
            'exchange_rate' => 'required|numeric',
            'financial_expenses' => 'required|numeric',
        ]);
        if($val->fails()) return response()->json($val->messages());

        //try {

            $client = Client::find($request->client_id);

            if($client == null) {
                return response()->json([
                    'success' => false,
                    'errors' => 'Cliente no encontrado'
                ], 404);
            }

            // Validating client accounts
            $bank_accounts = $client->bank_accounts()
                ->where('id', $request->bank_account_id)
                ->where('currency_id', $request->currency_id)
                ->whereRelation('status', 'name', 'Activo')
                ->get();

            if($bank_accounts->count() == 0) return response()->json(['success' => false,'data' => ['Error en la cuenta de destino seleccionada.']]);

            $bank_account_operation = array(
                "bank_account_id" => $request->bank_account_id,
                "amount" => $request->amount,
                "comission_amount" => 0
            );
            $bank_account_list = array();
            array_push($bank_account_list,$bank_account_operation);

            // Validating escrow account
            $escrow_accounts = EscrowAccount::where('id', $request->escrow_account_id)
                ->where('currency_id', $request->currency_id)
                ->where('active', true)
                ->where('bank_id', '<>', $bank_accounts[0]->bank_id)
                ->get();

            if($escrow_accounts->count() == 0) return response()->json(['success' => false,'data' => ['Error en la cuenta de fideicomiso seleccionada']]);

            $igv_porcentaje = round((float) Configuration::where('shortname', 'IGV')->first()->value / 100, 2);

            $total_comission = $request->comission + $request->igv;

            $comission_spread = round(10000*$total_comission / $request->amount,2);

            $spread = round(round($request->financial_expenses / $request->amount,6)*10000,2);

            // Calculando detracción
            $detraction_percentage = Configuration::where('shortname', 'DETRACTION')->first()->value;
            $detraction_amount = 0;

            if($total_comission >= 700 && $request->currency_id == 1) {
                $detraction_amount = round( ($total_comission) * ($detraction_percentage / 100), 0);
            }
            elseif($request->currency_id == 2 && ($total_comission*round((1 + $spread/10000) * $request->exchange_rate, 4) >= 700)) {
                $detraction_amount = round( ($total_comission*round((1 + $spread/10000) * $request->exchange_rate, 4)) * ($detraction_percentage / 100), 0);
            }

            $now = Carbon::now();
            $code = $now->format('YmdHisv') . rand(0, 9);

            $escrow_account_operation = array(
                "escrow_account_id" => $request->escrow_account_id,
                "amount" => $request->amount + round($request->amount * $spread/10000, 2) + $request->comission + $request->igv,
                "comission_amount" => $request->comission + $request->igv
            );
            $escrow_account_list = array();
            array_push($escrow_account_list,$escrow_account_operation);


            $operation = Operation::create([
                'code' => $code,
                'class' => Enums\OperationClass::Interbancaria,
                'type' => Enums\OperationType::Interbancaria,
                'client_id' => $request->client_id,
                'user_id' => auth()->id(),
                'amount' => $request->amount,
                'currency_id' => $request->currency_id,
                'exchange_rate' => $request->exchange_rate,
                'comission_spread' => (float) $comission_spread,
                'comission_amount' => $request->comission,
                'igv' => $request->igv,
                'spread' => $spread,
                'detraction_amount' => $detraction_amount,
                'detraction_percentage' => $detraction_percentage,
                'operation_status_id' => OperationStatus::where('name', 'Disponible')->first()->id,
                'operation_date' => $now->toDateTimeString(),
                'post' => false
            ]);

            $operation->bank_accounts()->attach($bank_account_list);
            $operation->escrow_accounts()->attach($escrow_account_list);

            // Matching with vendor
            if(isset($request->vendor_id)){

                $vendor_operation = InterbankOperationController::match_operation_vendor($operation->id, $request->vendor_id)->getData();

                return response()->json([
                    'success' => true,
                    'data' => [ 
                        $vendor_operation
                    ],
                ]);
            }
            else{
                // Enviar Correo()
                $rpta_mail = Mail::send(new NewInterbankOperation($operation));
            }

            return response()->json([
                'success' => true,
                'data' => [ 
                    'operation' => $operation
                ],
            ]);

            /*$rpta_mail = Mail::send(new NewOperation($op->OperacionId));
            $rpta_mail = Mail::send(new NotifyOpItbc($op->OperacionId));*/

        /*} catch (\Exception $e) {
            return response()->json(['success' => false,'data' => ['Error al crear operación']]);
            logger('Creación de Operación Interbancaria: create_operation@InterbankOperationController', ["error" => $e]);
        }*/

        OperationHistory::create(["operation_id" => $operation->id,"user_id" => auth()->id(),"action" => "Operación creada"]);

        // Enviar Correo()

        return response()->json([
            'success' => true,
            'data' => [
                "Operación creada exitosamente"
            ]
        ]);
    }

    public function match_operation_vendor($operation_id, $vendor_id) {
        $operation = Operation::find($operation_id)->load('bank_accounts','escrow_accounts');

        ####### Validating operation is not previusly matched ##########
        $operation_match = DB::table('operation_matches')
            ->where("operation_id", $operation->id)
            ->get();

        if($operation_match->count() > 0) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'La operación ya se encuentra emparejada'
                ]
            ]);
        }

        ######### Creating vendor operation #############

        $op_code = Carbon::now()->format('YmdHisv') . rand(0,9);
        $status_id = OperationStatus::where('name', 'Pendiente envio fondos')->first()->id;

        // Calculando detracción
        $detraction_percentage = Configuration::where('shortname', 'DETRACTION')->first()->value;
        $detraction_amount = 0;

        $matched_operation = Operation::create([
            'code' => $op_code,
            'class' => Enums\OperationClass::Interbancaria,
            'type' => 'Interbancaria',
            'client_id' => $vendor_id,
            'user_id' => auth()->id(),
            'amount' => $operation->amount,
            'currency_id' => $operation->currency_id,
            'exchange_rate' => $operation->exchange_rate,
            'comission_spread' => $operation->spread,
            'comission_amount' => 0,
            'detraction_amount' => $detraction_amount,
            'detraction_percentage' => $detraction_percentage,
            'igv' => 0,
            'spread' => ($operation->type == "Interbancaria") ? $operation->spread : 0,
            'operation_status_id' => $status_id,
            'operation_date' => Carbon::now(),
            'post' => false
        ]);

        if($matched_operation){
            try {

                // Getting Vendor bank account
                $vendor_bank_account = BankAccount::where('client_id', $vendor_id)
                    ->where('bank_account_status_id', Enums\BankAccountStatus::Activo)
                    ->where('bank_id', $operation->escrow_accounts[0]->bank_id)
                    ->where('currency_id', $operation->escrow_accounts[0]->currency_id )
                    ->first();

                if(is_null($vendor_bank_account)) return response()->json(['error' => true,'data' => ['Error en la cuenta de destino del proveedor de liquidez.']]);

                $financial_expenses =  round(round($matched_operation->spread / 10000, 6) * $matched_operation->amount,2);

                $matched_operation->bank_accounts()->attach($vendor_bank_account->id, [
                    'amount' => $matched_operation->amount + $financial_expenses,
                    'comission_amount' => 0,
                    'created_at' => Carbon::now()
                ]);
                

                // Getting Vendor Escrow Accont
                $vendor_escrow_account = EscrowAccount::where('active', true)
                    ->where('bank_id', $operation->bank_accounts[0]->bank_id)
                    ->where('currency_id', $operation->bank_accounts[0]->currency_id)
                    ->first();

                if(is_null($vendor_escrow_account)) return response()->json(['error' => true,'data' => ['Error en la cuenta de fideicomiso del proveedor de liquidez.']]);

                $matched_operation->escrow_accounts()->attach($vendor_escrow_account->id, [
                    'amount' => $matched_operation->amount,
                    'comission_amount' => 0,
                    'created_at' => Carbon::now()
                ]);
                
            } catch (\Exception $e) {
                logger('ERROR: archivo adjunto: match_operation_vendor@InmediateOperationController', ["error" => $e]);

                // Envio de correo de notificación de error
            }

            $operations_matches = $operation->matches()->attach($matched_operation->id, ['created_at' => Carbon::now()]);

            $operation->operation_status_id = $status_id;
            $operation->save();
        }

        // Enviar correo instrucciones ()
        $rpta_mail = Mail::send(new OperationInstructions($operation->id));
        $rpta_mail = Mail::send(new OperationInstructions($matched_operation->id));

        OperationHistory::create(["operation_id" => $operation->id,"user_id" => auth()->id(),"action" => "Operación emparejada"]);

        return response()->json([
            "vendor_id" => $vendor_id,
            "operacion" => $operation
        ]);
    }

}
