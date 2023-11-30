<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExecutiveGoal extends Model
{
    use HasFactory;

    public function executive() {
        return $this->belongsTo(Executive::class);
    }
}
