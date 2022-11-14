<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Operation extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function client() {
        return $this->belongsTo(Client::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function bank_accounts() {
        return $this->belongsToMany(BankAccount::class)->withPivot("amount", "comission_amount");
    }

    public function coupon() {
        return $this->belongsTo(Coupon::class);
    }

    public function currency() {
        return $this->belongsTo(Currency::class);
    }

    public function notifications() {
        return $this->hasMany(Notification::class);
    }

    public function escrow_accounts() {
        return $this->belongsToMany(EscrowAccount::class)->withPivot("amount", "comission_amount");
    }

    public function matches() {
        return $this->belongsToMany(Operation::class, "operation_matches", "operation_id", "matched_id");
    }

    public function matched_operation() {
        return $this->belongsToMany(Operation::class, "operation_matches", "matched_id", "operation_id");
    }

    public function status() {
        return $this->belongsTo(OperationStatus::class, 'operation_status_id');
    }

    public function documents() {
        return $this->hasMany(OperationDocument::class);
    }

    public function history() {
        return $this->hasMany(OperationHistory::class);
    }
}
