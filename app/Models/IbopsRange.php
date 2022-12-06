<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IbopsRange extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function updater() {
        return $this->belongsTo(User::class, "modified_by");
    }

    public function currency() {
        return $this->belongsTo(Currency::class);
    }
}
