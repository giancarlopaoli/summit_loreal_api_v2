<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperationOnline extends Model
{
    use HasFactory;

    protected $table = 'view_operations_online'; // Specify the database view name
    protected $guarded = []; // Or define fillable properties if needed

    public function client() {
        return $this->belongsTo(Client::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function bank_accounts() {
        return $this->belongsToMany(BankAccount::class, 'bank_account_operation','operation_id','bank_account_id')->withPivot("id","amount", "comission_amount","transfer_number","voucher_id","signed_at","escrow_account_operation_id","deposit_at");
    }

    public function vendor_bank_accounts() {
        return $this->belongsToMany(BankAccount::class,'vendor_bank_account_operation','operation_id','bank_account_id')->withPivot("id","amount", "comission_amount","transfer_number","voucher_id");
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
        return $this->belongsToMany(EscrowAccount::class, 'escrow_account_operation','operation_id','escrow_account_id')->withPivot("id","amount", "comission_amount","transfer_number","voucher_id","deposit_at");
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
        return $this->hasMany(OperationDocument::class, 'operation_id');
    }

    public function history() {
        return $this->hasMany(OperationHistory::class);
    }

    public function operations_analyst() {
        return $this->belongsTo(OperationsAnalyst::class, 'operations_analyst_id');
    }
}
