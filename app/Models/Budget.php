<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    use HasFactory;

    protected $connection = 'mysql2';

    protected $guarded = [];

    public function area() {
        return $this->belongsTo(Area::class);
    }

    public function updater() {
        return $this->belongsTo(User::class, "updated_by");
    }

    public function services() {
        return $this->hasMany(SupplierBankAccount::class);
    }
}
