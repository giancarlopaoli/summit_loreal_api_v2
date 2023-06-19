<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientTracking extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function client() {
        return $this->belongsTo(Client::class);
    }

    public function status() {
        return $this->belongsTo(TrackingStatus::class, "tracking_status_id");
    }

    public function creator() {
        return $this->belongsTo(User::class, "created_by");
    }

    public function form() {
        return $this->belongsTo(TrackingForm::class, "tracking_form_id");
    }
}
