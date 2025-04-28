<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountableExchangeRate extends Model
{
    use HasFactory;

    protected $connection = 'mysql2';

    protected $guarded = [];

    protected $table = 'accountable_exchange_rate';

    protected $primaryKey = 'date';
}
