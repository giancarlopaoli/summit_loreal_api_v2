<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetractionType extends Model
{
    use HasFactory;

    protected $guarded = [];
    
    protected $connection = 'mysql2';

    public function purchase_invoices() {
        return $this->hasMany(PurchaseInvoice::class,'code','detraction_type');
    }
}
