<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorRange extends Model
{
    use HasFactory;

    public function spreads() {
        return $this->hasMany(VendorSpread::class);
    }

    public function vendor() {
        return $this->belongsTo(Client::class, "vendor_id");
    }

    public function updater() {
        return $this->belongsTo(User::class, "updated_by");
    }
}
