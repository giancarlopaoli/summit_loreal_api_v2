<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentType extends Model
{
    use HasFactory;

    protected $connection = 'mysql';

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

    public function users() {
        return $this->hasMany(User::class);
    }

    public function payrolls() {
        return $this->hasMany(Payroll::class);
    }

    public function suppliers() {
        return $this->hasMany(Supplier::class);
    }
}
