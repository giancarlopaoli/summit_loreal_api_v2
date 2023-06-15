<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrackingPhase extends Model
{
    use HasFactory;

    public function leads() {
        return $this->hasMany(LeadTracking::class);
    }

    public function clients() {
        return $this->hasMany(Client::class);
    }
}
