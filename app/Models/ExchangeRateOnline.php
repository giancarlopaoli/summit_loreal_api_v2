<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExchangeRateOnline extends Model
{

    protected $table = 'view_exchange_rate'; // Specify the database view name
    protected $guarded = []; // Or define fillable properties if needed
}
