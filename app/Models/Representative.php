<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Representative extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function client() {
        return $this->belongsTo(Client::class);
    }

    public function document_type() {
        return $this->belongsTo(DocumentType::class);
    }
}
