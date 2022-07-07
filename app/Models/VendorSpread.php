<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorSpread extends Model
{
    use HasFactory;

    public function range() {
        return $this->belongsTo(VendorRange::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}
