<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorSpread extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function vendor_range() {
        return $this->belongsTo(VendorRange::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}
