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

    public static function validate(String $coupon_code) {
        return self::where('code', $coupon_code)->where('active', true)->where('start_date', '<=', now())
            ->where('end_date', '>=', now())->latest()->first();
    }

}
