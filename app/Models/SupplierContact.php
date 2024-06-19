<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierContact extends Model
{
    use HasFactory;

    protected $connection = 'mysql2';

    protected $guarded = [];

    public function contact_type() {
        return $this->belongsTo(SupplierContactType::class);
    }

    public function supplier() {
        return $this->belongsTo(Supplier::class);
    }
}
