<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    protected $connection = 'mysql'; 

    public function clients() {
        return $this->hasMany(Client::class);
    }

    public function suppliers() {
        return $this->hasMany(Supplier::class);
    }
}
