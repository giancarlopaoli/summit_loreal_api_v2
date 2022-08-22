<?php

namespace App\Http\Controllers\Clients;

use App\Enums\CouponType;
use App\Http\Controllers\Controller;
use App\Models\AssociationComission;
use App\Models\ClientComission;
use App\Models\Configuration;
use App\Models\Coupon;
use App\Models\ExchangeRate;
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

        round($exchange_rate, 4);

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

        $igv = $total_comission - $comission_amount;

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

        $coupon = Coupon::validate($request->coupon_code);

        return response()->json([
            'success' => true,
            'data' => $coupon == null ? false : $coupon
        ]);
    }
}
