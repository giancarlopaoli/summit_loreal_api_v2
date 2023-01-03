<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrackingForm extends Model
{
    use HasFactory;

    public function lead_tracking() {
        return $this->hasMany(LeadTracking::class);
    }

    public function client_tracking() {
        return $this->hasMany(ClientTracking::class);
    }
}
