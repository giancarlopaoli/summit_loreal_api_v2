<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TriviaResult extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function question() {
        return $this->belongsTo(TriviaQuestion::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function option() {
        return $this->belongsTo(TriviaOption::class);
    }
}
