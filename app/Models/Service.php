<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $connection = 'mysql2';

    protected $guarded = [];

    public function budget() {
        return $this->belongsTo(Budget::class);
    }

    public function supplier() {
        return $this->belongsTo(Supplier::class);
    }

    public function updater() {
        return $this->belongsTo(User::class, "updated_by");
    }

    public function purchase_invoices() {
        return $this->hasMany(PurchaseInvoice::class);
    }

    public function currency() {
        return $this->belongsTo(Currency::class);
    }
}
