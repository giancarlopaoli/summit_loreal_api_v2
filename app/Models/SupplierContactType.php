<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierContactType extends Model
{
    use HasFactory;
 
    protected $connection = 'mysql2';

    public function contacts() {
        return $this->hasMany(SupplierContact::class);
    }
}
