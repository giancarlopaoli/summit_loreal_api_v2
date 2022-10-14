<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    public function contacts() {
        return $this->hasMany(LeadContact::class);
    }

    public function trackings() {
        return $this->hasMany(LeadTracking::class);
    }

    public function region() {
        return $this->belongsTo(Region::class);
    }

    public function sector() {
        return $this->belongsTo(Sector::class);
    }

    public function document_type() {
        return $this->belongsTo(DocumentType::class);
    }

    public function lead_contact_type() {
        return $this->belongsTo(LeadContactType::class);
    }

    public function lead_status() {
        return $this->belongsTo(LeadStatus::class);
    }

    public function creator() {
        return $this->belongsTo(User::class, "created_by");
    }

    public function executive() {
        return $this->belongsTo(Executive::class, "executive_id");
    }

    public function status() {
        return $this->belongsTo(LeadStatus::class);
    }
}
