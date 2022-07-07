<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EscrowAccount extends Model
{
    use HasFactory;

    public function bank() {
        return $this->belongsTo(Bank::class);
    }

    public function document_type() {
        return $this->belongsTo(DocumentType::class);
    }

    public function currency() {
        return $this->belongsTo(Currency::class);
    }

    public function escrow_account_operations() {
        return $this->hasMany(EscrowAccountOperation::class);
    }

    public function operations() {
        return $this->belongsToMany(EscrowAccount::class)->withPivot("amount", "comission_amount");
    }
}
