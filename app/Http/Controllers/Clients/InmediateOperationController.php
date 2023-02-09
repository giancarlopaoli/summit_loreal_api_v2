<?php

namespace App\Http\Controllers\Clients;

use App\Enums\CouponType;
use App\Enums\OperationClass;
use App\Enums\BankAccountStatus;
use App\Http\Controllers\Controller;
use App\Models\AssociationComission;
use App\Models\BankAccount;
use App\Models\ClientComission;
use App\Models\Configuration;
use App\Models\Coupon;
use App\Models\Currency;
use App\Models\EscrowAccount;
use App\Models\ExchangeRate;
use App\Models\Operation;
use App\Models\OperationStatus;
use App\Models\Quotation;
use App\Models\Range;
use App\Models\Client;
use App\Models\SpecialExchangeRate;
use App\Models\VendorRange;
use App\Models\VendorSpread;
use App\Models\OperationHistory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Events\AvailableOperations;

class InmediateOperationController extends Controller
{
    public function get_minimum_amount(Request $request) {
        $conf = Configuration::where("shortname", "MNTMIN")->first();

        if($conf != null) {
            return response()->json([
                'success' => true,
                'data' => [
                    'value' => $conf->value
                ]
            ]);
        } else {
            return response()->json([
                'success' => false,
                'data' => [
                    'errors' => "Valor de monto minimo no configurado"
                ]
            ]);
        }

    }

    public function quote_operation(Request $request) {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id',
            'amount' => 'required|numeric',
            'type' => 'required|in:compra,venta',
            'currency_id' => 'required|exists:currencies,id'
        ]);

        if($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()->toJson()
            ]);
        }

        $coupon = null;
        if($request->coupon_code != null) {
            $coupon = Coupon::validate($request->coupon_code);

            if($coupon == null) {
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'El cupon enviado no es valido'
                    ]
                ], 400);
            }
        }

        $client = Client::find($request->client_id);
        $client_id = $client->id;
        $market_close_time = Configuration::where('shortname', 'MARKETCLOSE')->first()->value;
        $market_closed = Carbon::now() >= Carbon::create($market_close_time);

        // If currency == soles
        if($request->currency_id == 1){
            $type = $request->type == 'compra' ? 'venta' : 'compra';
            $exchange_rate = ExchangeRate::latest()->first();
            $amount = $request->amount;
            //retreiving operation range

            $range = InmediateOperationController::calculate_range_pen($amount,$type,$exchange_rate,$market_closed)->getData()->range;
            $amount = $range->amount;

            ################## Calculating final spread  ###########################################
                $exchange_rate_field = $type == 'compra' ? 'buying' : 'selling';
                $special_exchange_rate = SpecialExchangeRate::where('client_id', $client_id)
                ->where('active', true)
                ->where($exchange_rate_field, '!=', null)
                ->latest()
                ->first();

            if(!is_null($special_exchange_rate)) {
                $exchange_rate = $type == 'compra' ? $special_exchange_rate->buying : $special_exchange_rate->selling;
            } else {

                // retreiving Vendor spread
                $spreads =[];

                $spreads[] = $range->spread;

                $vendor_ranges = VendorRange::where('min_range', '<=', $amount)
                    ->where('max_range', '>=', $amount)
                    ->where('active', true)
                    ->get();

                $vendor_spreads = VendorSpread::whereIn('vendor_range_id', $vendor_ranges->pluck("id"))
                    ->where('active', true)
                    ->get();

                foreach ($vendor_spreads as $vendor_spread) {
                    if($type == 'compra') {
                        $spreads[] = $vendor_spread->buying_spread;
                    } else {
                        $spreads[] = $vendor_spread->selling_spread;
                    }
                }

                $spread = min($spreads);

                $exchange_rate = $type == 'compra' ? round($exchange_rate->compra + $spread/10000, 4) : round($exchange_rate->venta - $spread/10000, 4);
            }


            ################## Calculating Comission  ###########################################

            $client_comision = ClientComission::where('client_id', $client_id)
                ->where('active', true)
                ->latest()
                ->first();

            $association = Client::find($client_id)->association;
            $association_comision = null;

            if($association != null) {
                $association_comision = AssociationComission::where('association_id', $association->id)
                    ->where('active', true)
                    ->latest()
                    ->first();
            }

            if($client_comision != null) {
                $comission_spread = $market_closed ? $client_comision->comission_close : $client_comision->comission_open;
            } else if($association_comision != null) {
                $comission_spread = $market_closed ? $association_comision->comission_close : $association_comision->comission_open;
            } else {

                $comission_spread = $range->comission_spread;

                if($coupon != null) {
                    if($coupon->type == CouponType::Comision) {
                        if($type == "compra") {
                            $comission_spread += $coupon->value;
                        } else {
                            $comission_spread -= $coupon->value;
                            $comission_spread = $comission_spread < 0 ? 0 : $comission_spread;
                        }
                    } else if($coupon->type == CouponType::Porcentaje) {
                        $comission_spread = $comission_spread * ($coupon->value / 100.0);
                    } else {
                        return response()->json([
                            'success' => false,
                            'errors' => [
                                'El cupon enviado no es valido'
                            ]
                        ], 400);
                    }
                }
            }


            $final_exchange_rate = $type == 'compra' ? round($exchange_rate + $comission_spread/10000,4) : round($exchange_rate - $comission_spread/10000,4);

            $amount = round($request->amount / $final_exchange_rate,2);
            $conversion_amount = round($amount * $final_exchange_rate,2);

            $total_comission = round($request->amount - $conversion_amount, 2);

            $igv_percetage = Configuration::where('shortname', 'IGV')->first()->value / 100;
            $comission_amount = round($total_comission / (1+$igv_percetage), 2);

            $igv = round($total_comission - $comission_amount,2);

            $final_amount = $type == 'compra' ? $conversion_amount + $total_comission : $conversion_amount - $total_comission;
            $final_amount = round($final_amount, 2);
            
            $data = [
                'amount' => $amount,
                'type' => $type,
                'spread' => $spread,
                'exchange_rate' => $exchange_rate,
                'conversion_amount' => $conversion_amount,
                'comission_spread' => $comission_spread,
                'comission_amount' => $comission_amount,
                'igv' => $igv,
                'final_mount' => $final_amount,
                'final_exchange_rate' => $final_exchange_rate,
                'coupon_code' => $coupon?->code,
                'coupon_value' => $coupon?->value,
                'special_exchange_rate_id' => !is_null($special_exchange_rate) ? $special_exchange_rate->id : null
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
                "spread" => $spread,
                "special_exchange_rate_id" => !is_null($special_exchange_rate) ? $special_exchange_rate->id : null
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


        ############### Calculating Exchange Rate ##################
        $exchange_rate = InmediateOperationController::calculate_exchange_rate($amount,$request->client_id,$type)->getData()->exchange_rate;
        $spread = InmediateOperationController::calculate_exchange_rate($amount,$request->client_id,$type)->getData()->spread;
        $special_exchange_rate = InmediateOperationController::calculate_exchange_rate($amount,$request->client_id,$type)->getData()->special_exchange_rate;
        ############### End Calculating Exchange Rate ##################

        $conversion_amount = round($amount * $exchange_rate, 2);


        ################### Calculating Spread Comission
        $comission_spread = InmediateOperationController::calculate_comission_spread($amount,$request->client_id,$type,$coupon)->getData()->comission_spread;
        
        $total_comission = round($amount * $comission_spread, 2);
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
            'spread' => $spread,
            'exchange_rate' => $exchange_rate,
            'conversion_amount' => $conversion_amount,
            'comission_spread' => $comission_spread,
            'comission_amount' => $comission_amount,
            'igv' => $igv,
            'final_mount' => $final_amount,
            'final_exchange_rate' => $final_exchange_rate,
            'coupon_code' => $coupon?->code,
            'coupon_value' => $coupon?->value,
            'special_exchange_rate_id' => !is_null($special_exchange_rate) ? $special_exchange_rate->id : null
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
            "spread" => $spread,
            "special_exchange_rate_id" => !is_null($special_exchange_rate) ? $special_exchange_rate->id : null
        ]);

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function calculate_exchange_rate($amount,$client_id,$type) {

        $spread = 0;

        $market_close_time = Configuration::where('shortname', 'MARKETCLOSE')->first()->value;
        $market_closed = Carbon::now() >= Carbon::create($market_close_time);

        $exchange_rate_field = $type == 'compra' ? 'buying' : 'selling';

        $special_exchange_rate = SpecialExchangeRate::where('client_id', $client_id)
            ->where('active', true)
            ->where($exchange_rate_field, '!=', null)
            ->latest()
            ->first();

        if(!is_null($special_exchange_rate)) {
            $exchange_rate = $type == 'compra' ? $special_exchange_rate->buying : $special_exchange_rate->selling;
        } else {

            // retreiving Vendor spread
            $spreads =[];

            $general_spread = Range::where('min_range', '<=', $amount)
                ->where('max_range', '>=', $amount)
                ->where('active', true)
                ->first();

            $general_spread = $market_closed ? $general_spread->spread_close : $general_spread->spread_open;
            $spreads[] = $general_spread;

            $vendor_ranges = VendorRange::where('min_range', '<=', $amount)
                ->where('max_range', '>', $amount)
                ->where('active', true)
                ->get();

            $vendor_spreads = VendorSpread::whereIn('vendor_range_id', $vendor_ranges->pluck("id"))
                ->where('active', true)
                ->get();

            foreach ($vendor_spreads as $vendor_spread) {
                if($type == 'compra') {
                    $spreads[] = $vendor_spread->buying_spread;
                } else {
                    $spreads[] = $vendor_spread->selling_spread;
                }
            }

            $spread = min($spreads);

            $exchange_rate = ExchangeRate::latest()->first();
            $exchange_rate = $type == 'compra' ? $exchange_rate->compra + $spread/ 10000.0 : $exchange_rate->venta - $spread/ 10000.0;
        }

        return response()->json([
            'exchange_rate' => round($exchange_rate, 4),
            'spread' => $spread,
            'special_exchange_rate' => $special_exchange_rate
        ]);
    }

    public function calculate_comission_spread($amount,$client_id,$type,$coupon) {

        $market_close_time = Configuration::where('shortname', 'MARKETCLOSE')->first()->value;
        $market_closed = Carbon::now() >= Carbon::create($market_close_time);

        $client_comision = ClientComission::where('client_id', $client_id)
            ->where('active', true)
            ->latest()
            ->first();

        $association = Client::find($client_id)->association;
        $association_comision = null;

        if($association != null) {
            $association_comision = AssociationComission::where('association_id', $association->id)
                ->where('active', true)
                ->latest()
                ->first();
        }

        /*if($client_comision != null && $association_comision != null)  {
            if($market_closed) {
                $comission_spread = min((float) $client_comision->comission_close, (float) $association_comision->comission_close);
            } else {
                $comission_spread = min((float) $client_comision->comission_open, (float) $association_comision->comission_open);
            }
        } else */

        if($client_comision != null) {
            $comission_spread = $market_closed ? $client_comision->comission_close : $client_comision->comission_open;
        } else if($association_comision != null) {
            $comission_spread = $market_closed ? $association_comision->comission_close : $association_comision->comission_open;
        } else {
            $general_comission = Range::where('min_range', '<=', $amount)
                ->where('max_range', '>=', $amount)
                ->where('active', true)
                ->first();

            $comission_spread = $market_closed ? $general_comission->comission_close : $general_comission->comission_open;

            if($coupon != null) {
                if($coupon->type == CouponType::Comision) {
                    if($type == "compra") {
                        $comission_spread += $coupon->value;
                    } else {
                        $comission_spread -= $coupon->value;
                        $comission_spread = $comission_spread < 0 ? 0 : $comission_spread;
                    }
                } else if($coupon->type == CouponType::Porcentaje) {
                    $comission_spread = $comission_spread * ($coupon->value / 100.0);
                } else {
                    return response()->json([
                        'success' => false,
                        'errors' => [
                            'El cupon enviado no es valido'
                        ]
                    ], 400);
                }
            }
        }

        return response()->json([
            'comission_spread' => $comission_spread,
        ]);
    }

    public function calculate_range_pen($amount,$type,$exchange_rate,$market_closed) {
        if($type == 'compra'){
            $exchange_rate = $exchange_rate->compra;

            if($market_closed){
                $range = Range::select("id","min_range","max_range","comission_close as comission_spread","spread_close as spread")->selectRaw("($amount/($exchange_rate+(comission_close+spread_close)/10000)) as amount")->whereRaw("($amount/($exchange_rate+(comission_close+spread_close)/10000) >= min_range and $amount/($exchange_rate+(comission_close+spread_close)/10000) <= max_range)")->orderByDesc('id')->first();

                if(is_null($range)){
                    $range = Range::selectRaw("id,min_range,max_range,comission_close as comission_spread,spread_close as spread, abs($amount - max_range*($exchange_rate+(comission_close+spread_close)/10000)) as maximo, max_range as amount")->orderby("maximo")->first(); 
                }
            }
            else{
                $range = Range::select("id","min_range","max_range","comission_open as comission_spread","spread_open as spread")->selectRaw("($amount/($exchange_rate+(comission_open+spread_open)/10000)) as amount")->whereRaw("($amount/($exchange_rate+(comission_open+spread_open)/10000) >= min_range and $amount/($exchange_rate+(comission_open+spread_open)/10000) <= max_range)")->orderByDesc('id')->first();

                if(is_null($range)){
                    $range = Range::selectRaw("id,min_range,max_range,comission_open as comission_spread,spread_open as spread, abs($amount - max_range*($exchange_rate+(comission_open+spread_open)/10000)) as maximo, max_range as amount")->orderby("maximo")->first(); 
                }
            }
        }
        else{
            $exchange_rate = $exchange_rate->venta;

            if($market_closed){
                $range = Range::select("id","min_range","max_range","comission_close as comission_spread","spread_close as spread")->selectRaw("($amount/($exchange_rate-(comission_close+spread_close)/10000)) as amount")->whereRaw("($amount/($exchange_rate-(comission_close+spread_close)/10000) >= min_range and $amount/($exchange_rate-(comission_close+spread_close)/10000) <= max_range)")->orderByDesc('id')->first();

                if(is_null($range)){
                    $range = Range::selectRaw("id,min_range,max_range,comission_close as comission_spread,spread_close as spread, abs($amount - max_range*($exchange_rate-(comission_close+spread_close)/10000)) as maximo, max_range as amount")->orderby("maximo")->first(); 
                }
            }
            else{
                $range = Range::select("id","min_range","max_range","comission_open as comission_spread","spread_open as spread")->selectRaw("($amount/($exchange_rate-(comission_open+spread_open)/10000)) as amount")->whereRaw("($amount/($exchange_rate-(comission_open+spread_open)/10000) >= min_range and $amount/($exchange_rate-(comission_open+spread_open)/10000) <= max_range)")->orderByDesc('id')->first();

                if(is_null($range)){
                    $range = Range::selectRaw("id,min_range,max_range,comission_open as comission_spread,spread_open as spread, abs($amount - max_range*($exchange_rate-(comission_open+spread_open)/10000)) as maximo, max_range as amount")->orderby("maximo")->first(); 
                }
            }
        }

        return response()->json([
            'range' => $range
        ]);
    }

    public function validate_coupon(Request $request) {
        $validator = Validator::make($request->all(), [
            'coupon_code' => 'required|string'
        ]);

        if($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()->toJson()
            ]);
        }

        $coupon = Coupon::validate($request->coupon_code);

        return response()->json([
            'success' => true,
            'data' => $coupon == null ? false : $coupon->only(['id','code','type'])
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
            'spread' => 'required|numeric',
            'bank_accounts' => 'required|array',
            'escrow_accounts' => 'required|array'
        ]);

        if($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()->toJson()
            ]);
        }
        $client = Client::find($request->client_id);

        $coupon = null;
        if($request->has('coupon_id') && !is_null($request->coupon_id) && $request->coupon_id != "") {
            $coupon = Coupon::find($request->coupon_id);

            if(is_null($coupon)){
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'El cupón ingresado es inválido'
                    ]
                ]);
            }
        }

        //Validating Bank Accounts
        $soles_id = Currency::where('name', 'Soles')->first()->id;
        $dolares_id = Currency::where('name', 'Dolares')->first()->id;

        $bank_accounts = [];
        $total_amount_bank = 0;
        $total_amount_scrow = 0;
        $total_comission = round($request->comission_amount + $request->igv);

        // Calculando detracción
        $detraction_percentage = Configuration::where('shortname', 'DETRACTION')->first()->value;
        $detraction_amount = 0;

        if($total_comission >= 700) {
            $detraction_amount = round( ($total_comission) * ($detraction_percentage / 100), 0);
        }

        foreach ($request->bank_accounts as $bank_account_data) {
            $bank_account = BankAccount::where('id', $bank_account_data['id'])
                ->where('client_id',$request->client_id)
                ->where('bank_account_status_id', BankAccountStatus::Activo)
                ->first();

            // Validating that the bank account is valid.
            if(is_null($bank_account)) {
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'Error en la cuenta bancaria id = ' . $bank_account_data['id']
                    ]
                ]);
            }

            if($request->type == 'compra') {
                if($bank_account->currency_id != $dolares_id) {
                    return response()->json([
                        'success' => false,
                        'errors' => [
                            'La cuenta bancaria ' . $bank_account->id . ' no tiene la divisa valida'
                        ]
                    ]);
                }

                $bank_account->comission_amount = 0 ;
            } else {
                if($bank_account->currency_id != $soles_id) {
                    return response()->json([
                        'success' => false,
                        'errors' => [
                            'La cuenta bancaria ' . $bank_account->id . ' no tiene la divisa valida'
                        ]
                    ]);
                }

                if($bank_account_data['amount'] >= $total_comission){
                    $bank_account->comission_amount = $total_comission;
                    $total_comission = 0;
                }
                else{
                    $bank_account->comission_amount = $bank_account_data['amount'];
                    $total_comission = $total_comission -  $bank_account_data['amount'];
                }
            }

            $bank_account->amount = $bank_account_data['amount'];
            $total_amount_bank += $bank_account_data['amount'];
            $bank_accounts[] = $bank_account;
        }


        //Validating Escrow Accounts
        $escrow_accounts = [];
        foreach ($request->escrow_accounts as $escrow_account_data) {
            $escrow_account = EscrowAccount::where('id', $escrow_account_data['id'])
                ->where('active', true)
                ->first();

            if(is_null($escrow_account)) {
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'La cuenta fideicomiso ' . $escrow_account_data['id'] . ' no es valida'
                    ]
                ]);
            }

            if($request->type == 'compra') {
                if($escrow_account->currency_id != $soles_id) {
                    return response()->json([
                        'success' => false,
                        'errors' => [
                            'La cuenta fideicomiso ' . $escrow_account->id . ' no tiene la divisa valida'
                        ]
                    ]);
                }

                if($escrow_account_data['amount'] >= $total_comission){
                    $escrow_account->comission_amount = $total_comission;
                    $total_comission = 0;
                }
                else{
                    $escrow_account->comission_amount = $escrow_account_data['amount'];
                    $total_comission = $total_comission -  $escrow_account_data['amount'];
                }

            } else {
                if($escrow_account->currency_id != $dolares_id) {
                    return response()->json([
                        'success' => false,
                        'errors' => [
                            'La cuenta fideicomiso ' . $escrow_account->id . ' no tiene la divisa valida'
                        ]
                    ]);
                }

                $escrow_account->comission_amount = 0;
            }

            $escrow_account->amount = $escrow_account_data['amount'];
            $total_amount_scrow += $escrow_account_data['amount'];
            $escrow_accounts[] = $escrow_account;
        }
        
        //Validating amounts in accounts
        if($request->type == 'compra') {
            $envia = round($request->amount * $request->exchange_rate + $request->comission_amount + $request->igv,2);
            $recibe = $request->amount;
        } else {
            $envia = $request->amount;
            $recibe = round($request->amount * $request->exchange_rate - $request->comission_amount - $request->igv,2);
        }

        if( $recibe != $total_amount_bank){
            return response()->json([
                'success' => false,
                'errors' => [
                    'La suma de montos enviados en las cuentas bancarias del cliente es incorrecto = ' . $total_amount_bank . '. Debería ser ' . $recibe 
                ]
            ]);
        }

        if( $envia != $total_amount_scrow){
            return response()->json([
                'success' => false,
                'errors' => [
                    'La suma de montos enviados en las cuentas de fideicomiso es incorrecto = ' . $total_amount_scrow . '. Debería ser ' . $envia 
                ]
            ]);
        }

        /*return response()->json([
            'success' => true,
            'data' => [
                'bank_accounts' => $bank_accounts,
                'escrow_accounts' => $escrow_accounts
            ]
        ]);*/


        $op_code = Carbon::now()->format('YmdHisv') . rand(0,9);
        $status_id = OperationStatus::where('name', 'Disponible')->first()->id;

        $operation = Operation::create([
            'code' => $op_code,
            'class' => OperationClass::Inmediata,
            'type' => $request->type,
            'client_id' => $request->client_id,
            'user_id' => auth()->id(),
            'amount' => $request->amount,
            'currency_id' => $dolares_id,
            'exchange_rate' => $request->exchange_rate,
            'comission_spread' => $request->comission_spread,
            'comission_amount' => $request->comission_amount,
            'igv' => $request->igv,
            'spread' => $request->spread,
            'detraction_amount' => $detraction_amount,
            'detraction_percentage' => $detraction_percentage,
            'operation_status_id' => $status_id,
            'coupon_id' => $request->coupon_id,
            'coupon_code' => $coupon?->code,
            'coupon_type' => $coupon?->type,
            'coupon_value' => $coupon?->value,
            'operation_date' => Carbon::now(),
            'post' => true
        ]);

        foreach ($bank_accounts as $bank_account_data) {
            $operation->bank_accounts()->attach($bank_account_data['id'], [
                'amount' => $bank_account_data['amount'],
                'comission_amount' => $bank_account_data['comission_amount']
            ]);
        }

        foreach ($escrow_accounts as $escrow_account_data) {
            $operation->escrow_accounts()->attach($escrow_account_data['id'], [
                'amount' => $escrow_account_data['amount'],
                'comission_amount' => $escrow_account_data['comission_amount']
            ]);
        }

        OperationHistory::create(["operation_id" => $operation->id,"user_id" => auth()->id(),"action" => "Operación creada"]);

        AvailableOperations::dispatch();

        // Enviar Correo()

        return response()->json([
            'success' => true,
            'data' => $operation
        ]);
    }
}
