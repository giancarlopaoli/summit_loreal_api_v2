<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ClientComission;
use App\Http\Controllers\Clients\InmediateOperationController;

class ExchangeRate extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function get_rate_with_spread($spread) {
        $last_tc = ExchangeRate::latest()->first();

        return [
            'compra' => $last_tc->compra + $spread,
            'venta' => $last_tc->venta - $spread
        ];
    }

    //public function for_user(User $user=null, float $amount) : ExchangeRate {
    public function for_user(User $user=null, float $amount) {
        
        $client_id = 363;
        if(!is_null($user)){
            $client = $user->assigned_client()->first();

            $client_id = is_null($client) ? null : $client->id;
        }
            
        $market_close_time = Configuration::where('shortname', 'MARKETCLOSE')->first()->value;
        $market_closed = Carbon::now() >= Carbon::create($market_close_time);

        $buy_spreads =[];
        $sell_spreads =[];

        $ranges = Range::where('min_range', '<=', $amount)
            ->where('max_range', '>', $amount)
            ->where('active', true)
            ->first();

        $general_spread = $market_closed ? $ranges->spread_close : $ranges->spread_open;
        $general_spread_comission_sell = $market_closed ? $ranges->comission_close_sell : $ranges->comission_open_sell;
        $general_spread_comission_buy = $market_closed ? $ranges->comission_close_buy : $ranges->comission_open_buy;

        $buy_spreads[] = $general_spread;
        $sell_spreads[] = $general_spread;
        $buy_spread_comission = $general_spread_comission_sell;
        $sell_spread_comission = $general_spread_comission_buy;

        $vendor_ranges = VendorRange::where('min_range', '<=', $amount)
            ->where('max_range', '>', $amount)
            ->where('active', true)
            ->get();

        $vendor_spreads = VendorSpread::whereIn('vendor_range_id', $vendor_ranges->pluck('id'))
            ->where('active', true)
            ->get();

        foreach ($vendor_spreads as $vendor_spread) {
            $buy_spreads[] = $vendor_spread->buying_spread;
            $sell_spreads[] = $vendor_spread->selling_spread;
        }

        $buy_spread = min($buy_spreads) / 10000.0;
        $sell_spread = min($sell_spreads) / 10000.0;


        if(!is_null($user)){
            $special_exchange_rate = SpecialExchangeRate::where('client_id', $client_id)
                ->where('active', true)
                ->latest()
                ->first();

            $client_comission = ClientComission::where('client_id', $client_id)
                ->where('active', true)
                ->latest()
                ->first();

            if(!is_null($client_comission)){
                if(!$market_closed){
                    if(!is_null($client_comission->comission_open)){
                        $buy_spread_comission = $client_comission->comission_open;
                        $sell_spread_comission = $client_comission->comission_open;
                    }
                }
                else{
                    if(!is_null($client_comission->comission_close)){
                        $buy_spread_comission = $client_comission->comission_close;
                        $sell_spread_comission = $client_comission->comission_close;
                    }
                }
            }

        }
        else {
            $special_exchange_rate = null;
        }

        /*$buy_spread_comission = min($buy_spread_comissions) / 10000.0;
        $sell_spread_comission = min($sell_spread_comissions) / 10000.0;*/

        // Cotizando con plataforma cerrada
        $consult = new InmediateOperationController();
        $hours = $consult->operation_hours($client_id)->getData();

        if(!$hours->available){
            $comission_spread_conf = Configuration::where("shortname", "COMSPREADCLOSEPLAT")->first()->value;
            $pl_spread_conf = Configuration::where("shortname", "PLPREADCLOSEPLAT")->first()->value;

            $buy_spread_comission = $comission_spread_conf;
            $sell_spread_comission = $comission_spread_conf;

            $buy_spread = $pl_spread_conf / 10000.0;
            $sell_spread = $pl_spread_conf / 10000.0;
        }

        $buy_spread_comission = $buy_spread_comission / 10000.0;
        $sell_spread_comission = $sell_spread_comission / 10000.0;

        $exchange_rate = ExchangeRate::latest()->first();
        $user_exchange_rate = new ExchangeRate();
        $user_exchange_rate->created_at = $exchange_rate->created_at;
        $user_exchange_rate->updated_at = $exchange_rate->updated_at;

        if($special_exchange_rate == null) {
            $user_exchange_rate->compra = round($exchange_rate->compra + $buy_spread + $buy_spread_comission, 4);
            $user_exchange_rate->venta = round($exchange_rate->venta - $sell_spread - $sell_spread_comission, 4);

        } else {
            $user_exchange_rate->compra = round($special_exchange_rate->buying, 4);
            $user_exchange_rate->venta = round($special_exchange_rate->selling, 4);
        }

        return $user_exchange_rate;
    }
}
