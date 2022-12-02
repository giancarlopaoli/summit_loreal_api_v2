<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientComission extends Model
{
    use HasFactory;

    protected $fillable = [
        'comission_open',
        'comission_close',
        'active',
        'comments',
        'updated_by'
    ];

    public function client() {
        return $this->belongsTo(Client::class);
    }

    public function updater() {
        return $this->belongsTo(User::class, "updated_by");
    }
}
