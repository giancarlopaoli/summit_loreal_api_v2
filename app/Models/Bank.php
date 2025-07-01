<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $connection = 'mysql';

    public function bank_accounts() {
        return $this->hasMany(BankAccount::class);
    }

    public function escrow_accounts() {
        return $this->hasMany(EscrowAccount::class);
    }

    public function supplier_bank_accounts() {
        return $this->hasMany(SupplierBankAccount::class);
    }

    public function business_bank_accounts() {
        return $this->hasMany(BusinessBankAccount::class);
    }
}
