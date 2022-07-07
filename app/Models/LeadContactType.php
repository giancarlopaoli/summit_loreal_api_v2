<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadContactType extends Model
{
    use HasFactory;

    public function contacts_data() {
        return $this->hasMany(ContactData::class);
    }
}
