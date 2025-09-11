<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TriviaQuestion extends Model
{
    use HasFactory;

    public function trivia_options() {
        return $this->hasMany(TriviaOption::class);
    }

    public function results() {
        return $this->hasMany(TriviaResult::class);
    }

    public function speaker() {
        return $this->belongsTo(Speaker::class);
    }
}
