<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agenda extends Model
{
    use HasFactory;

    public function category() {
        return $this->belongsTo(AgendaCategory::class);
    }

    public function speakers() {
        return $this->hasMany(AgendaSpeaker::class);
    }
}
