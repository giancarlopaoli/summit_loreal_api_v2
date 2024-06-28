<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactData extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $connection = 'mysql'; 

    public function lead_contact() {
        return $this->belongsTo(LeadContact::class);
    }

    public function type() {
        return $this->belongsTo(LeadContactType::class);
    }

    public function creator() {
        return $this->belongsTo(User::class, "created_by");
    }
}
