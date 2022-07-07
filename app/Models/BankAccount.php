<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    use HasFactory;

    public function type() {
        return $this->belongsTo(AccountType::class);
    }

    public function client() {
        return $this->belongsTo(Client::class);
    }

    public function bank() {
        return $this->belongsTo(Bank::class);
    }

    public function updater() {
        return $this->belongsTo(User::class);
    }

    public function operations() {
        return $this->belongsToMany(Operation::class)->withPivot("amount", "comission_amount");
    }

    public function currency() {
        return $this->belongsTo(Currency::class);
    }
}
