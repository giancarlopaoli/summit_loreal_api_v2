<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Range extends Model
{
    use HasFactory;

    public function updater() {
        return $this->belongsTo(User::class, "modified_by");
    }

    public static function minimun_amount() {
        return self::min('min_range');
    }
}
