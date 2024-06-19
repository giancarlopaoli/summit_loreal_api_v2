<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $connection = 'mysql2';

    protected $guarded = [];

    public function client() {
        return $this->belongsTo(Client::class);
    }

    public function currency() {
        return $this->belongsTo(Currency::class);
    }

    public function lines() {
        return $this->hasMany(SaleLine::class);
    }

    public function payments() {
        return $this->hasMany(SalePayment::class);
    }
}
