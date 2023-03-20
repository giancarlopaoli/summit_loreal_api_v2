<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperationsAnalyst extends Model
{
    use HasFactory;

    public function user() {
        return $this->hasOne(User::class, "id");
    }

    public function operations() {
        return $this->hasMany(Operation::class);
    }
}
