<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Association extends Model
{
    use HasFactory;

    public function clients() {
        return $this->hasMany(Client::class);
    }

    public function association_comissions() {
        return $this->hasMany(AssociationComission::class);
    }
}
