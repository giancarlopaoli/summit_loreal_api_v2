<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssociationComission extends Model
{
    use HasFactory;

    public function association() {
        return $this->belongsTo(Association::class);
    }
}
