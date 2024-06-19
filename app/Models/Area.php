<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $connection = 'mysql2';

    public function budgets() {
        return $this->hasMany(Budget::class);
    }
}
