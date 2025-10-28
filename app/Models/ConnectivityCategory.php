<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConnectivityCategory extends Model
{
    use HasFactory;

    public function connectivities() {
        return $this->hasMany(Connectivity::class);
    }
}
