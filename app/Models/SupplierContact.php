<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplierContact extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'mysql2';

    protected $guarded = [];

    public function contact_type() {
        return $this->belongsTo(SupplierContactType::class, "supplier_contact_type_id");
    }

    public function supplier() {
        return $this->belongsTo(Supplier::class);
    }
}
