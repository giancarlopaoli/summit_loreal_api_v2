<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasFactory;

    public function bank_accounts() {
        return $this->hasMany(BankAccount::class);
    }

    public function escrow_accounts() {
        return $this->hasMany(EscrowAccount::class);
    }

    public function operations() {
        return $this->hasMany(Operation::class);
    }

    public function quotations() {
        return $this->hasMany(Quotation::class);
    }

    public function ibops_ranges() {
        return $this->hasMany(IbopsRange::class);
    }
}
