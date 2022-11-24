<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankAccountReceipt extends Model
{
    use HasFactory;

    protected $guarded = [];
    
    public function bank_account() {
        return $this->belongsTo(BankAccount::class);
    }
}
