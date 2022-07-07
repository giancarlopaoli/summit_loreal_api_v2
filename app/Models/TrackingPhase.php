<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrackingPhase extends Model
{
    use HasFactory;

    public function trackins() {
        return $this->hasMany(LeadTracking::class);
    }
}
