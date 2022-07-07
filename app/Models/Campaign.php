<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    use HasFactory;

    public function creator() {
        return $this->belongsTo(User::class, "created_by");
    }

    public function coupons() {
        return $this->hasMany(Coupon::class);
    }
}
