<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalePayment extends Model
{
    use HasFactory;

    protected $connection = 'mysql2';

    public function sale() {
        return $this->belongsTo(Sale::class);
    }

    public function currency() {
        return $this->belongsTo(Currency::class);
    }
}
