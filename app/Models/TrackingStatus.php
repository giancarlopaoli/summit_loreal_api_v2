<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrackingStatus extends Model
{
    use HasFactory;

    public function lead_trackings() {
        return $this->hasMany(LeadTracking::class);
    }

    public function client_trackings() {
        return $this->hasMany(ClientTracking::class);
    }
}
