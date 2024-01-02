<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function for_user(User $user=null, float $amount) : ExchangeRate {
        
        if(!is_null($user)){
            $client = $user->assigned_client()->first();

            $client_id = is_null($client) ? null : $client->id;
        }
            
        $market_close_time = Configuration::where('shortname', 'MARKETCLOSE')->first()->value;
        $market_closed = Carbon::now() >= Carbon::create($market_close_time);

        $buy_spreads =[];
        $sell_spreads =[];

        $general_spread = Range::where('min_range', '<=', $amount)
            ->where('max_range', '>', $amount)
            ->where('active', true)
            ->first();

        $general_spread = $market_closed ? $general_spread->spread_close : $general_spread->spread_open;
        $buy_spreads[] = $general_spread;
        $sell_spreads[] = $general_spread;

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
        }
        else {
            $special_exchange_rate = null;
        }


        $exchange_rate = ExchangeRate::latest()->first();
        $user_exchange_rate = new ExchangeRate();
        #$user_exchange_rate->id = $exchange_rate->id;
        $user_exchange_rate->created_at = $exchange_rate->created_at;
        $user_exchange_rate->updated_at = $exchange_rate->updated_at;

        if($special_exchange_rate == null) {
            $user_exchange_rate->compra = round($exchange_rate->compra + $buy_spread, 4);
            $user_exchange_rate->venta = round($exchange_rate->venta - $sell_spread, 4);

        } else {
            $user_exchange_rate->compra = round($special_exchange_rate->buying, 4);
            $user_exchange_rate->venta = round($special_exchange_rate->selling, 4);
        }

        return $user_exchange_rate;
    }
}
