<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierBankAccount extends Model
{
    use HasFactory;

    protected $connection = 'mysql2';

    protected $guarded = [];

    public function supplier() {
        return $this->belongsTo(Supplier::class);
    }

    public function bank() {
        return $this->belongsTo(Bank::class);
    }

    public function currency() {
        return $this->belongsTo(Currency::class);
    }

    public function account_type() {
        return $this->belongsTo(AccountType::class);
    }

    public function purchase_payments() {
        return $this->hasMany(PurchasePayment::class);
    }
}
