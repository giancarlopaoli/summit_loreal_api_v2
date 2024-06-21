<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    use HasFactory;

    protected $connection = 'mysql';

    public function province() {
        return $this->belongsTo(Province::class);
    }

    public function clients() {
        return $this->hasMany(Client::class);
    }

    public function suppliers() {
        return $this->hasMany(Supplier::class);
    }
}
