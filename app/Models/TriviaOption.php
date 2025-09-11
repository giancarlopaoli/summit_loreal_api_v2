<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TriviaOption extends Model
{
    use HasFactory;

    public function question() {
        return $this->belongsTo(TriviaQuestion::class);
    }

    public function results() {
        return $this->hasMany(TriviaResult::class);
    }
}
