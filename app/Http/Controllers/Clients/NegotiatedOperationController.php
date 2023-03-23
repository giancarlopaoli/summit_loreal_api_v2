<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\BankAccount;
use App\Models\Client;
use App\Models\Currency;
use App\Models\Configuration;
use App\Models\EscrowAccount;
use App\Models\ExchangeRate;
use App\Models\Operation;
use App\Models\OperationStatus;
use App\Models\OperationHistory;
use App\Models\Quotation;
use App\Models\Range;
use Carbon\Carbon;
use App\Enums;

class NegotiatedOperationController extends Controller
{
    //
    public function quote_operation(Request $request) {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id',
            'amount' => 'required|numeric',
            'type' => 'required|in:compra,venta',
            'currency_id' => 'required|exists:currencies,id',
            'exchange_rate' => 'nullable|numeric'
        ]);

        if($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()->toJson()
            ]);
        }

        $client = Client::find($request->client_id);
        $client_id = $client->id;

        // Validating minimum amount
        $min_amount = NegotiatedOperationController::minimun_amount($request->client_id)->getData()[0];
        
        $market_close_time = Configuration::where('shortname', 'MARKETCLOSE')->first()->value;
        $market_closed = Carbon::now() >= Carbon::create($market_close_time);

        // If currency == soles
        if($request->currency_id == 1){
            $type = $request->type == 'compra' ? 'venta' : 'compra';
            $exchange_rate = ExchangeRate::latest()->first();
            $amount = $request->amount;
            //retreiving operation range

            $exchange_rate = (isset($request->exchange_rate)) ? $request->exchange_rate : round(($exchange_rate->compra + $exchange_rate->venta)/2, 4);

            $range = NegotiatedOperationController::calculate_range_pen($amount,$type,$exchange_rate,$market_closed)->getData()->range;

            $comission_spread = $range->comission_spread;

            $final_exchange_rate = $type == 'compra' ? round($exchange_rate + $comission_spread/10000,4) : round($exchange_rate - $comission_spread/10000,4);

            $amount = round($request->amount / $final_exchange_rate,2);

            // Validating General Min Amount
            if($amount < $min_amount){
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'El monto mínimo de operación es $' . number_format($min_amount,2) . " (S/ " . number_format($min_amount*$final_exchange_rate,2) . ")"
                    ]
                ]);
            }

            // Validating if client is validated
            $max_amount = $client->customer_type == 'PN' ? Configuration::where('shortname', 'MAXOPPN')->first()->value : Configuration::where('shortname', 'MAXOPPJ')->first()->value;

            if($amount > $max_amount && $client->validated == false){
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'Ha excedido el monto máximo de operación. Para poder continuar comuníquese con su ejecutivo.' . $amount,
                    ]
                ]);
            }

            $conversion_amount = round($amount * $exchange_rate,2);

            $total_comission = ($type == 'compra') ? round($request->amount - $conversion_amount, 2) : round($conversion_amount - $request->amount, 2);

            $igv_percetage = Configuration::where('shortname', 'IGV')->first()->value / 100;
            $comission_amount = round($total_comission / (1+$igv_percetage), 2);

            $igv = round($total_comission - $comission_amount,2);

            $final_amount = $type == 'compra' ? $conversion_amount + $total_comission : $conversion_amount - $total_comission;
            $final_amount = round($final_amount, 2);
            
            $data = [
                'amount' => $amount,
                'type' => $type,
                'spread' => 0,
                'exchange_rate' => $exchange_rate,
                'conversion_amount' => $conversion_amount,
                'comission_spread' => $comission_spread,
                'comission_amount' => $comission_amount,
                'igv' => $igv,
                'final_mount' => $final_amount,
                'final_exchange_rate' => $final_exchange_rate,
                'save' => round($amount * (120/10000) , 2)
            ];

            Quotation::create([
                "user_id" => auth()->id(),
                "client_id" => $client->id,
                "type" => $type,
                "amount" => $amount,
                "exchange_rate" => $exchange_rate,
                "comission_spread" => $comission_spread,
                "comission_amount" => $comission_amount,
                "igv" => $igv,
                "spread" => 0,
                "special_exchange_rate_id" => null
            ]);

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        }
        else{
            $type = $request->type;
        }

        $amount = (float) $request->amount;

        // Validating General min Amount
        if($amount < $min_amount){
            return response()->json([
                'success' => false,
                'errors' => [
                    'El monto mínimo de operación es $' . number_format($min_amount,2)
                ]
            ]);
        }

        // Validating if client is validated
        $max_amount = $client->customer_type == 'PN' ? Configuration::where('shortname', 'MAXOPPN')->first()->value : Configuration::where('shortname', 'MAXOPPJ')->first()->value;

        if($request->amount > $max_amount && $client->validated == false){
            return response()->json([
                'success' => false,
                'errors' => [
                    'Ha excedido el monto máximo de operación. Para poder continuar comuníquese con su ejecutivo.',
                ]
            ]);
        }

        ############### Calculating Exchange Rate ##################
        $exchange_rate = ExchangeRate::latest()->first();

        $exchange_rate = (isset($request->exchange_rate)) ? $request->exchange_rate : round(($exchange_rate->compra + $exchange_rate->venta)/2, 4);

        $conversion_amount = round($amount * $exchange_rate, 2);

        ################### Calculating Spread Comission
        $general_comission = Range::where('min_range', '<=', $amount)
                ->where('max_range', '>=', $amount)
                ->where('active', true)
                ->first();

        $comission_spread = $market_closed ? $general_comission->comission_close : $general_comission->comission_open;
        
        $total_comission = round($amount * $comission_spread/10000, 2);
        ############# End calculating comission

        $igv_percetage = Configuration::where('shortname', 'IGV')->first()->value / 100;
        $comission_amount = round($total_comission / (1+$igv_percetage), 2);

        $igv = round($total_comission - $comission_amount,2);

        $final_amount = $type == 'compra' ? $conversion_amount + $total_comission : $conversion_amount - $total_comission;
        $final_amount = round($final_amount, 2);

        $final_exchange_rate = round($final_amount/$amount, 4);

        $data = [
            'amount' => $amount,
            'type' => $type,
            'spread' => 0,
            'exchange_rate' => $exchange_rate,
            'conversion_amount' => $conversion_amount,
            'comission_spread' => $comission_spread,
            'comission_amount' => $comission_amount,
            'igv' => $igv,
            'final_mount' => $final_amount,
            'final_exchange_rate' => $final_exchange_rate,
            'save' => round($amount * (120/10000) , 2)
        ];

        Quotation::create([
            "user_id" => auth()->id(),
            "client_id" => $client->id,
            "type" => $type,
            "amount" => $amount,
            "exchange_rate" => $exchange_rate,
            "comission_spread" => $comission_spread,
            "comission_amount" => $comission_amount,
            "igv" => $igv,
            "spread" => 0,
            "special_exchange_rate_id" => null
        ]);

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function minimun_amount($client_id) {
        $client = Client::find($client_id);

        $min_amount = Range::where('active', true)->min('min_range');

        return response()->json([
            $min_amount
        ]);
    }

    public function calculate_range_pen($amount,$type,$exchange_rate,$market_closed) {
        if($type == 'compra'){

            if($market_closed){
                $range = Range::select("id","min_range","max_range","comission_close as comission_spread","spread_close as spread")->selectRaw("round(($amount/($exchange_rate+(comission_close)/10000)),2) as amount")->whereRaw("($amount/($exchange_rate+(comission_close)/10000) >= min_range and $amount/($exchange_rate+(comission_close)/10000) <= max_range)")->orderByDesc('id')->first();

                if(is_null($range)){
                    $range = Range::selectRaw("id,min_range,max_range,comission_close as comission_spread,spread_close as spread, abs($amount - max_range*($exchange_rate+(comission_close)/10000)) as maximo, max_range as amount")->orderby("maximo")->first(); 
                }
            }
            else{
                $range = Range::select("id","min_range","max_range","comission_open as comission_spread","spread_open as spread")->selectRaw("round(($amount/($exchange_rate+(comission_open)/10000)),2) as amount")->whereRaw("($amount/($exchange_rate+(comission_open)/10000) >= min_range and $amount/($exchange_rate+(comission_open)/10000) <= max_range)")->orderByDesc('id')->first();

                if(is_null($range)){
                    $range = Range::selectRaw("id,min_range,max_range,comission_open as comission_spread,spread_open as spread, abs($amount - max_range*($exchange_rate+(comission_open)/10000)) as maximo, max_range as amount")->orderby("maximo")->first(); 
                }
            }
        }
        else{

            if($market_closed){
                $range = Range::select("id","min_range","max_range","comission_close as comission_spread","spread_close as spread")->selectRaw("round(($amount/($exchange_rate-(comission_close)/10000)),2) as amount")->whereRaw("($amount/($exchange_rate-(comission_close)/10000) >= min_range and $amount/($exchange_rate-(comission_close)/10000) <= max_range)")->orderByDesc('id')->first();

                if(is_null($range)){
                    $range = Range::selectRaw("id,min_range,max_range,comission_close as comission_spread,spread_close as spread, abs($amount - max_range*($exchange_rate-(comission_close)/10000)) as maximo, max_range as amount")->orderby("maximo")->first(); 
                }
            }
            else{
                $range = Range::select("id","min_range","max_range","comission_open as comission_spread","spread_open as spread")->selectRaw("round(($amount/($exchange_rate-(comission_open)/10000)),2) as amount")->whereRaw("($amount/($exchange_rate-(comission_open)/10000) >= min_range and $amount/($exchange_rate-(comission_open)/10000) <= max_range)")->orderByDesc('id')->first();

                if(is_null($range)){
                    $range = Range::selectRaw("id,min_range,max_range,comission_open as comission_spread,spread_open as spread, abs($amount - max_range*($exchange_rate-(comission_open)/10000)) as maximo, max_range as amount")->orderby("maximo")->first(); 
                }
            }
        }

        return response()->json([
            'range' => $range
        ]);
    }

    public function create_operation(Request $request) {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id',
            'amount' => 'required',
            'type' => 'required|in:compra,venta',
            'exchange_rate' => 'required|numeric',
            'comission_spread' => 'required|numeric',
            'comission_amount' => 'required|numeric',
            'igv' => 'required|numeric',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'escrow_account_id' => 'required|exists:escrow_accounts,id'
        ]);

        if($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()->toJson()
            ]);
        }
        $client = Client::find($request->client_id);

        // Validating minimum amount
        $min_amount = NegotiatedOperationController::minimun_amount($request->client_id)->getData()[0];

        if($request->amount < $min_amount){
            return response()->json([
                'success' => false,
                'errors' => [
                    'El monto mínimo de operación es $' . number_format($min_amount,2)
                ]
            ]);
        }

        // Validating if client is validated
        $max_amount = $client->customer_type == 'PN' ? Configuration::where('shortname', 'MAXOPPN')->first()->value : Configuration::where('shortname', 'MAXOPPJ')->first()->value;

        if($request->amount > $max_amount && $client->validated == false){
            return response()->json([
                'success' => false,
                'errors' => [
                    'Ha excedido el monto máximo de operación. Para poder continuar comuníquese con su ejecutivo.',
                ]
            ]);
        }

        //Validating Bank Accounts
        $soles_id = Currency::where('name', 'Soles')->first()->id;
        $dolares_id = Currency::where('name', 'Dolares')->first()->id;

        // Calculando detracción
        $total_comission = round($request->comission_amount + $request->igv,2);
        $final_amount = ($request->type == 'compra') ? round(round($request->amount*$request->exchange_rate,2) + $total_comission,2) : round(round($request->amount*$request->exchange_rate,2)  - $total_comission,2);
        $detraction_percentage = Configuration::where('shortname', 'DETRACTION')->first()->value;
        $detraction_amount = 0;

        if($total_comission >= 700) {
            $detraction_amount = round( ($total_comission) * ($detraction_percentage / 100), 0);
        }

        $ba_currency_id = ($request->type == 'compra') ? $dolares_id : $soles_id;

        $bank_account = BankAccount::where('id', $request->bank_account_id)
            ->where('client_id',$request->client_id)
            ->where('currency_id',$ba_currency_id)
            ->where('bank_account_status_id', Enums\BankAccountStatus::Activo)
            ->first();

        // Validating that the bank account is valid.
        if(is_null($bank_account)) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'Error en la cuenta bancaria id = ' . $request->bank_account_id
                ]
            ]);
        }

        $ea_currency_id = ($request->type == 'venta') ? $dolares_id : $soles_id;

        //Validating Escrow Accounts
        $escrow_accounts = [];
        $escrow_account = EscrowAccount::where('id', $request->escrow_account_id)
            ->where('active', true)
            ->where('currency_id',$ea_currency_id)
            ->first();

        if(is_null($escrow_account)) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'La cuenta fideicomiso ' . $request->escrow_account_id . ' no es valida'
                ]
            ]);
        }

        $op_code = Carbon::now()->format('YmdHisv') . rand(0,9);
        $status_id = OperationStatus::where('name', 'Disponible')->first()->id;

        $operation = Operation::create([
            'code' => $op_code,
            'class' => Enums\OperationClass::Programada,
            'type' => $request->type,
            'client_id' => $request->client_id,
            'user_id' => auth()->id(),
            'amount' => $request->amount,
            'currency_id' => $dolares_id,
            'exchange_rate' => $request->exchange_rate,
            'comission_spread' => $request->comission_spread,
            'comission_amount' => $request->comission_amount,
            'igv' => $request->igv,
            'spread' => 0,
            'detraction_amount' => $detraction_amount,
            'detraction_percentage' => $detraction_percentage,
            'operation_status_id' => $status_id,
            'operation_date' => Carbon::now(),
            'post' => 0
        ]);

        $operation->bank_accounts()->attach($request->bank_account_id, [
            'amount' => ($request->type == 'compra') ? $request->amount : $final_amount,
            'comission_amount' => ($request->type == 'compra') ? 0 : $total_comission,
        ]);

        $operation->escrow_accounts()->attach($request->escrow_account_id, [
            'amount' => ($request->type == 'venta') ? $request->amount : $final_amount,
            'comission_amount' => ($request->type == 'venta') ? 0 : $total_comission,
        ]);
        
        OperationHistory::create(["operation_id" => $operation->id,"user_id" => auth()->id(),"action" => "Operación creada"]);

        // Enviar Correo()

        return response()->json([
            'success' => true,
            'data' => $operation
        ]);

    }

    public function operations_list(Request $request) {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id',
            'type' => 'nullable|in:compra,venta'
        ]);

        if($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()->toJson()
            ]);
        }
        $client = Client::find($request->client_id);

        $operations = Operation::select('id','code','class','type','amount','currency_id','exchange_rate','comission_amount','igv','operation_date','comission_spread')
            ->selectRaw("(round(amount * exchange_rate, 2)) as conversion_amount")
            ->where('class', Enums\OperationClass::Programada)
            ->where('operation_status_id', OperationStatus::where('name', 'Disponible')->first()->id)
            ->where('client_id','<>', $request->client_id)
            ->with('bank_accounts:id,bank_id','bank_accounts.bank:id,name,shortname')
            ->with('escrow_accounts:id,bank_id','escrow_accounts.bank:id,name,shortname');

        if(isset($request->type)) $operations = $operations->where('type', $request->type);

        return response()->json([
            'success' => true,
            'data' => $operations->get()
        ]);
    }

    public function operation_detail(Request $request, Operation $operation) {

        if($operation->class != Enums\OperationClass::Programada) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'Error en la operación seleccionada'
                ]
            ]);
        }

        $operation->load(
            'currency:id,name,sign',
            'status:id,name',
            'bank_accounts:id,bank_id,currency_id,account_number,cci_number',
            'bank_accounts.currency:id,name,sign',
            'bank_accounts.bank:id,name,shortname,image',
            'escrow_accounts:id,bank_id,account_number,cci_number,currency_id',
            'escrow_accounts.currency:id,name,sign',
            'escrow_accounts.bank:id,name,shortname,image'
        );

        return response()->json([
            'success' => true,
            'data' => [
                'operation' => $operation->only(['id','code','class','type','amount','currency_id','exchange_rate','comission_amount','igv','operation_date','comission_spread','currency','bank_accounts','escrow_accounts'])
            ]
        ]);
    }

    public function accept_operation(Request $request, Operation $operation) {

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

}
