<?php

namespace App\Http\Controllers\Clients;

use App\Enums\CouponType;
use App\Enums\OperationClass;
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
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
            'amount' => 'required',
            'type' => 'required|in:compra,venta'
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
                return repsonse()->json([
                    'success' => false,
                    'errors' => [
                        'El cupon enviado no es valido'
                    ]
                ], 400);
            }
        }

        $client = Client::find($request->client_id);

        $amount = (float) $request->amount;

        $market_close_time = Configuration::where('shortname', 'MARKETCLOSE')->first()->value;
        $market_closed = Carbon::now() >= Carbon::create($market_close_time);

        $spreads =[];

        $general_spread = Range::where('min_range', '<=', $amount)
            ->where('max_range', '>', $amount)
            ->where('active', true)
            ->first();

        $general_spread = $market_closed ? $general_spread->spread_close : $general_spread->spread_open;
        $spreads[] = $general_spread;

        $vendor_ranges = VendorRange::where('min_range', '<=', $amount)
            ->where('max_range', '>', $amount)
            ->where('active', true)
            ->get();

        $vendor_spreads = VendorSpread::whereIn('vendor_range_id', $vendor_ranges->only('id')->toArray())
            ->where('active', true)
            ->get();

        foreach ($vendor_spreads as $vendor_spread) {
            if($request->type == 'compra') {
                $spreads[] = $vendor_spread->buying_spread;
            } else {
                $spreads[] = $vendor_spread->selling_spread;
            }
        }

        $spread = min($spreads);
        $spread = $spread / 10000.0;

        $special_exchange_rate = SpecialExchangeRate::where('client_id', $request->client_id)
            ->where('active', true)
            ->latest()
            ->first();

        if($special_exchange_rate == null) {
            $exchange_rate = ExchangeRate::latest()->first();
            $exchange_rate = $request->type == 'compra' ? $exchange_rate->compra + $spread : $exchange_rate->venta - $spread;

        } else {
            $exchange_rate = $request->type == 'compra' ? $special_exchange_rate->buying : $special_exchange_rate->selling;
        }

        $exchange_rate = round($exchange_rate, 4);

        $conversion_amount = round($amount * $exchange_rate, 2);

        $client_comision = ClientComission::where('client_id', $request->client_id)
            ->where('active', true)
            ->latest()
            ->first();

        $association = $client->association;
        $association_comision = null;
        if($association != null) {
            $association_comision = AssociationComission::where('association_id', $association->id)
                ->where('active', true)
                ->latest()
                ->first();
        }
        if($client_comision != null && $association_comision != null)  {
            if($market_closed) {
                $comission_spread = min((float) $client_comision->comission_close, (float) $association_comision->comission_close);
            } else {
                $comission_spread = min((float) $client_comision->comission_open, (float) $association_comision->comission_open);
            }
        } else if($client_comision != null) {
            $comission_spread = $market_closed ? $client_comision->comission_close : $client_comision->comission_open;
        } else if($association_comision != null) {
            $comission_spread = $market_closed ? $association_comision->comission_close : $association_comision->comission_open;
        } else {
            $general_comission = Range::where('min_range', '<=', $amount)
                ->where('max_range', '>', $amount)
                ->where('active', true)
                ->first();
            $comission_spread = $market_closed ? $general_comission->comission_open : $general_comission->comission_close;
            if($coupon != null) {
                if($coupon->type == CouponType::Comision) {
                    if($request->type == "compra") {
                        $comission_spread += $coupon->value;
                    } else {
                        $comission_spread -= $coupon->value;
                        $comission_spread = $comission_spread < 0 ? 0 : $comission_spread;
                    }
                } else if($coupon->type == CouponType::Porcentaje) {
                    $comission_spread = $comission_spread * ($coupon->value / 100.0);
                } else {
                    return repsonse()->json([
                        'success' => false,
                        'errors' => [
                            'El cupon enviado no es valido'
                        ]
                    ], 400);
                }
            }
        }
        $comission_spread = (float) $comission_spread / 10000.0;

        $total_comission = round($amount * $comission_spread, 2);

        $igv_percetage = Configuration::where('shortname', 'IGV')->first()->value / 100;
        $comission_amount = round($total_comission / (1+$igv_percetage), 2);

        $igv = round($total_comission - $comission_amount,2);

        $final_amount = $request->type == 'compra' ? $conversion_amount + $total_comission : $conversion_amount - $total_comission;
        $final_amount = round($final_amount, 2);

        $final_exchange_rate = round($final_amount/$amount, 4);

        $data = [
            'amount' => $amount,
            'type' => $request->type,
            'spread' => $spread * 10000,
            'exchange_rate' => $exchange_rate,
            'conversion_amount' => $conversion_amount,
            'comission_spread' => $comission_spread * 10000,
            'comission_amount' => $comission_amount,
            'igv' => $igv,
            'final_mount' => $final_amount,
            'final_exchange_rate' => $final_exchange_rate,
            'coupon_code' => $coupon?->code,
            'coupon_value' => $coupon?->value,
        ];

        Quotation::create([
            "user_id" => auth()->id(),
            "client_id" => $client->id,
            "type" => $request->type,
            "amount" => $amount,
            "exchange_rate" => $exchange_rate,
            "comission_spread" => $comission_spread,
            "comission_amount" => $comission_amount,
            "igv" => $igv,
            "spread" => $spread
        ]);

        return response()->json([
            'success' => true,
            'data' => $data
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

        $coupon = Coupon::validate($request->coupon_code)->only(['id','code']);

        return response()->json([
            'success' => true,
            'data' => $coupon == null ? false : $coupon
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
            'escrow_accounts' => 'required|array',
            'coupon_id' => 'exists:coupons,id'
        ]);

        if($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()->toJson()
            ]);
        }
        $client = Client::find($request->client_id);

        $coupon = null;
        if($request->has('coupon_id')) {
            $coupon = Coupon::find($request->coupon_id);
        }

        $bank_accounts = [];
        foreach ($request->bank_accounts as $bank_account_data) {
            $bank_account = BankAccount::find($bank_account_data['id']);
            if($bank_account == null || $bank_account?->client_id != $request->client_id) {
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'La cuenta bancaria ' . $bank_account_data['id'] . ' no pertenece al cliente'
                    ]
                ]);
            }
            $bank_accounts[] = $bank_account;
        }

        $escrow_accounts = [];
        foreach ($request->escrow_accounts as $escrow_account_data) {
            $escrow_account = EscrowAccount::find($escrow_account_data['id']);
            if($escrow_account == null || $escrow_account?->active != true) {
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'La cuenta fideicomiso ' . $escrow_account_data['id'] . ' no es valida'
                    ]
                ]);
            }
            $escrow_accounts[] = $escrow_account;
        }

        $soles_id = Currency::where('name', 'Soles')->first()->id;
        $dolares_id = Currency::where('name', 'Dolares')->first()->id;

        if($request->type == 'compra') {
            foreach ($bank_accounts as $bank_account) {
                if($bank_account->currency_id != $dolares_id) {
                    return response()->json([
                        'success' => false,
                        'errors' => [
                            'La cuenta bancaria ' . $bank_account->id . ' no tiene la divisa valida'
                        ]
                    ]);
                }
            }

            foreach ($escrow_accounts as $escrow_account) {
                if($escrow_account->currency_id != $soles_id) {
                    return response()->json([
                        'success' => false,
                        'errors' => [
                            'La cuenta fideicomiso ' . $escrow_account->id . ' no tiene la divisa valida'
                        ]
                    ]);
                }
            }
        } else {
            foreach ($bank_accounts as $bank_account) {
                if($bank_account->currency_id != $soles_id) {
                    return response()->json([
                        'success' => false,
                        'errors' => [
                            'La cuenta bancaria ' . $bank_account->id . ' no tiene la divisa valida'
                        ]
                    ]);
                }
            }

            foreach ($escrow_accounts as $escrow_account) {
                if($escrow_account->currency_id != $dolares_id) {
                    return response()->json([
                        'success' => false,
                        'errors' => [
                            'La cuenta fideicomiso ' . $escrow_account->id . ' no tiene la divisa valida'
                        ]
                    ]);
                }
            }
        }

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
            'operation_status_id' => $status_id,
            'coupon_id' => $request->coupon_id,
            'coupon_code' => $coupon?->code,
            'coupon_type' => $coupon?->type,
            'coupon_value' => $coupon?->value,
            'operation_date' => Carbon::now(),
            'post' => true
        ]);

        foreach ($request->bank_accounts as $bank_account_data) {
            $operation->bank_accounts()->attach($bank_account_data['id'], [
                'amount' => $bank_account_data['amount'],
                'comission_amount' => $bank_account_data['comission_amount']
            ]);
        }

        foreach ($request->escrow_accounts as $escrow_account_data) {
            $operation->escrow_accounts()->attach($escrow_account_data['id'], [
                'amount' => $escrow_account_data['amount'],
                'comission_amount' => $escrow_account_data['comission_amount']
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $operation
        ]);
    }
}
