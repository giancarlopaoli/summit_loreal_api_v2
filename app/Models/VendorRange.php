<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorRange extends Model
{
    use HasFactory;

    protected $guarded = [];

    
    public function spreads() {
        return $this->hasMany(VendorSpread::class);
    }

    public function active_spreads() {
        return $this->hasMany(VendorSpread::class)->where('active', 1);
    }

    public function vendor() {
        return $this->belongsTo(Client::class, "vendor_id");
    }

    public function updater() {
        return $this->belongsTo(User::class, "updated_by");
    }
}
