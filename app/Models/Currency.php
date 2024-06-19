<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasFactory;

    protected $connection = 'mysql';

    public function bank_accounts() {
        return $this->hasMany(BankAccount::class);
    }

    public function escrow_accounts() {
        return $this->hasMany(EscrowAccount::class);
    }

    public function operations() {
        return $this->hasMany(Operation::class);
    }

    public function quotations() {
        return $this->hasMany(Quotation::class);
    }

    public function ibops_ranges() {
        return $this->hasMany(IbopsRange::class);
    }

    public function supplier_bank_accounts() {
        return $this->hasMany(BankAccount::class);
    }

    public function purchase_invoices() {
        return $this->hasMany(PurchaseInvoice::class);
    }

    public function business_bank_accounts() {
        return $this->hasMany(BusinessBankAccount::class);
    }

    public function purchase_payments() {
        return $this->hasMany(PurchasePayment::class);
    }

    public function other_income() {
        return $this->hasMany(OtherIncome::class);
    }

    public function other_expenses() {
        return $this->hasMany(OtherExpense::class);
    }

    public function sales() {
        return $this->hasMany(Sale::class);
    }

    public function sale_payments() {
        return $this->hasMany(SalePayment::class);
    }
}
