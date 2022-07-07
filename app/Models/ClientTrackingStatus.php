<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientTrackingStatus extends Model
{
    use HasFactory;

    public function client() {
        return $this->belongsTo(Client::class);
    }

    public function creator() {
        return $this->belongsTo(User::class, "created_by");
    }
}
