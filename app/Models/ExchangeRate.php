<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    use HasFactory;

    public function get_rate_with_spread($spread) {
        $last_tc = ExchangeRate::latest()->first();

        return [
            'compra' => $last_tc->compra + $spread,
            'venta' => $last_tc->venta - $spread
        ];
    }
}
