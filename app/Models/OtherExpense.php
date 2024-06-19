<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtherExpense extends Model
{
    use HasFactory;

    protected $connection = 'mysql2';

    protected $guarded = [];

    public function currency() {
        return $this->belongsTo(Currency::class);
    }

    public function business_bank_account() {
        return $this->belongsTo(BusinessBankAccount::class);
    }
}
