<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Executive extends Model
{
    use HasFactory;

    protected $connection = 'mysql';

    public function leads() {
        return $this->hasMany(Lead::class);
    }

    public function comissions() {
        return $this->hasMany(ExecutivesComission::class);
    }

    public function history() {
        return $this->hasMany(ExecutivesHistory::class);
    }

    public function goals() {
        return $this->hasMany(ExecutivesGoal::class);
    }

    public function clients() {
        return $this->hasMany(Client::class);
    }

    public function user() {
        return $this->belongsTo(User::class, "id");
    }

    public function client_trackins() {
        return $this->hasMany(ClientTracking::class, "created_by");
    }
}
