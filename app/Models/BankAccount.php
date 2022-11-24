<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankAccount extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

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

    public function status() {
        return $this->belongsTo(BankAccountStatus::class, 'bank_account_status_id');
    }

    public function account_type() {
        return $this->belongsTo(AccountType::class);
    }

    public function bank_account_receipts() {
        return $this->hasMany(BankAccountReceipt::class);
    }
}
