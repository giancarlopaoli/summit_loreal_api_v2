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
        return $this->belongsToMany(Operation::class)->withPivot("amount", "comission_amount","transfer_number","voucher_id","signed_at");
    }

    public function vendor_operations() {
        return $this->belongsToMany(Operation::class, "vendor_bank_account_operation", "bank_account_id", "operation_id")->withPivot("amount", "comission_amount","transfer_number","voucher_id");
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

    public function receipts() {
        return $this->hasMany(BankAccountReceipt::class);
    }

    public function payroll_payments() {
        return $this->hasMany(PayrollPayment::class);
    }
}
