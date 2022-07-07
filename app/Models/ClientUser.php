<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ClientUser extends Pivot
{
    public function creator() {
        return $this->belongsTo(User::class, "updated_by");
    }
}
