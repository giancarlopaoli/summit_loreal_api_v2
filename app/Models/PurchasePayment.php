<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchasePayment extends Model
{
    use HasFactory;

    protected $connection = 'mysql2';

    protected $guarded = [];

    public function purchase_invoice() {
        return $this->belongsTo(PurchaseInvoice::class);
    }

    public function currency() {
        return $this->belongsTo(Currency::class);
    }

    public function business_bank_account() {
        return $this->belongsTo(BusinessBankAccount::class);
    }

    public function supplier_bank_account() {
        return $this->belongsTo(SupplierBankAccount::class);
    }
}
