<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecomendationCategory extends Model
{
    use HasFactory;

    public function recomendations() {
        return $this->hasMany(Recomendation::class);
    }
}
