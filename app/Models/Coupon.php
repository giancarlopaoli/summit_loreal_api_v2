<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    public function campaign() {
        return $this->belongsTo(Campaign::class);
    }

    public function creator() {
        return $this->belongsTo(User::class, "created_by");
    }

    public function operations() {
        return $this->hasMany(Operation::class);
    }
}
