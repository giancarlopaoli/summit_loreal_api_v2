<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessBankAccount extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $connection = 'mysql2';

    public function bank() {
        return $this->belongsTo(Bank::class);
    }

    public function account_type() {
        return $this->belongsTo(AccountType::class);
    }

    public function currency() {
        return $this->belongsTo(Currency::class);
    }

    public function purchase_payments() {
        return $this->hasMany(PurchasePayment::class);
    }

    public function other_income() {
        return $this->hasMany(OtherIncome::class);
    }

    public function other_expenses() {
        return $this->hasMany(OtherExpense::class);
    }
}
