<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IbopsClientComission extends Model
{
    use HasFactory;

    public function client() {
        return $this->belongsTo(Client::class);
    }

    public function updater() {
        return $this->belongsTo(User::class, "updated_by");
    }
}
