<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollContract extends Model
{
    use HasFactory;

    protected $connection = 'mysql2';

    protected $guarded = [];

    public function payroll() {
        return $this->belongsTo(Payroll::class);
    }

    public function creater() {
        return $this->belongsTo(User::class, "created_by");
    }

    public function updater() {
        return $this->belongsTo(User::class, "updated_by");
    }
}
