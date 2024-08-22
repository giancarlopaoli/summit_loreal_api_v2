<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountingDocument extends Model
{
    use HasFactory;

    protected $connection = 'mysql2';

    protected $guarded = [];

    public function purchase_invoice() {
        return $this->belongsTo(PurchaseInvoice::class);
    }

    public function purchase_payment() {
        return $this->belongsTo(PurchasePayment::class);
    }
}
