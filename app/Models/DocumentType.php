<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentType extends Model
{
    use HasFactory;

    public function leads() {
        return $this->hasMany(Lead::class);
    }

    public function representatives() {
        return $this->hasMany(Representative::class);
    }

    public function clients() {
        return $this->hasMany(Client::class);
    }

    public function escrow_accounts() {
        return $this->hasMany(EscrowAccount::class);
    }
}
