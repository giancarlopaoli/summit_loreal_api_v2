<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollPayment extends Model
{
    use HasFactory;

    protected $connection = 'mysql2';

    protected $guarded = [];

    public function payroll() {
        return $this->belongsTo(Payroll::class);
    }

    public function bank_account() {
        return $this->belongsTo(BankAccount::class);
    }
}
