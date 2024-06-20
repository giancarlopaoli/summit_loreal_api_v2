<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountType extends Model
{
    use HasFactory;

    protected $connection = 'mysql';

    public function bank_accounts() {
        return $this->hasMany(BankAccount::class);
    }

    public function supplier_bank_accounts() {
        return $this->hasMany(BankAccount::class);
    }

    public function business_bank_accounts() {
        return $this->hasMany(BusinessBankAccount::class);
    }
}
