<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadContact extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function data() {
        return $this->hasMany(ContactData::class);
    }

    public function trackins() {
        return $this->hasMany(LeadTracking::class);
    }

    public function lead() {
        return $this->belongsTo(Lead::class);
    }

    public function creator() {
        return $this->belongsTo(User::class, "created_by");
    }
}
