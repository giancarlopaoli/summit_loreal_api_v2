<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;

    protected $connection = 'mysql2';

    protected $guarded = [];

    public function document_type() {
        return $this->belongsTo(DocumentType::class);
    }

    public function afp() {
        return $this->belongsTo(Afp::class);
    }

    public function updater() {
        return $this->belongsTo(User::class, "updated_by");
    }

    public function payroll_contracts() {
        return $this->hasMany(PayrollContract::class);
    }

    public function vacations() {
        return $this->hasMany(Vacation::class);
    }

    public function payments() {
        return $this->hasMany(PayrollPayment::class);
    }
}
