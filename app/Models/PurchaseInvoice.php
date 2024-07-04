<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseInvoice extends Model
{
    use HasFactory;

    protected $connection = 'mysql2';

    protected $guarded = [];

    public function service() {
        return $this->belongsTo(Service::class);
    }

    public function currency() {
        return $this->belongsTo(Currency::class);
    }

    public function lines() {
        return $this->hasMany(PurchaseInvoiceLine::class);
    }

    public function payments() {
        return $this->hasMany(PurchasePayment::class);
    }

    public function documents() {
        return $this->hasMany(AccountingDocument::class);
    }
}
