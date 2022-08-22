<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use phpDocumentor\Reflection\Types\This;

class Coupon extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function campaign() {
        return $this->belongsTo(Campaign::class);
    }

    public function creator() {
        return $this->belongsTo(User::class, "created_by");
    }

    public function operations() {
        return $this->hasMany(Operation::class);
    }

    public function validate(String $coupon_code) {
        return This::where('code', $coupon_code)->where('active', true)->latest()->first();
    }

}
