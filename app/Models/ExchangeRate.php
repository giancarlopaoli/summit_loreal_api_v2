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

    public function for_user(User $user, float $amount) : ExchangeRate {
        $client = $user->assigned_client()->first();

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

        $vendor_spreads = VendorSpread::whereIn('vendor_range_id', $vendor_ranges->only('id')->toArray())
            ->where('active', true)
            ->get();

        foreach ($vendor_spreads as $vendor_spread) {
            $buy_spreads[] = $vendor_spread->buying_spread;
            $sell_spreads[] = $vendor_spread->selling_spread;
        }

        $buy_spread = min($buy_spreads) / 10000.0;
        $sell_spread = min($sell_spreads) / 10000.0;

        $special_exchange_rate = SpecialExchangeRate::where('client_id', $client->id)
            ->where('active', true)
            ->latest()
            ->first();

        $exchange_rate = ExchangeRate::select('id','compra','venta')->latest()->first();

        if($special_exchange_rate == null) {
            $exchange_rate->compra = round($exchange_rate->compra + $buy_spread, 4);
            $exchange_rate->venta = round($exchange_rate->venta - $sell_spread, 4);

        } else {
            $exchange_rate->compra = round($special_exchange_rate->buying, 4);
            $exchange_rate->venta = round($special_exchange_rate->selling, 4);
        }

        return $exchange_rate;
    }
}
