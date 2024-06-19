<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;
    
    protected $connection = 'mysql2';

    protected $guarded = [];

    public function document_type() {
        return $this->belongsTo(DocumentType::class);
    }

    public function contacts() {
        return $this->hasMany(SupplierContact::class);
    }

    public function bank_accounts() {
        return $this->hasMany(SupplierBankAccount::class);
    }

    public function services() {
        return $this->hasMany(SupplierBankAccount::class);
    }
}
