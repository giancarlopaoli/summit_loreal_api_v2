<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadTracking extends Model
{
    use HasFactory;

    protected $guarded = [];
    
    public function lead() {
        return $this->belongsTo(Lead::class);
    }

    public function tracking_phase() {
        return $this->belongsTo(TrackingPhase::class);
    }

    public function lead_contact() {
        return $this->belongsTo(LeadContact::class);
    }

    public function tracking_status() {
        return $this->belongsTo(TrackingStatus::class);
    }

    public function tracking_form() {
        return $this->belongsTo(TrackingForm::class);
    }

    public function creator() {
        return $this->belongsTo(User::class, "created_by");
    }

}
