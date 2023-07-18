<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperationsAnalystLog extends Model
{
    use HasFactory;

    public function operations_analyst() {
        return $this->belongsTo(OperationsAnalyst::class);
    }

    public function creator() {
        return $this->belongsTo(User::class, "created_by");
    }
}
