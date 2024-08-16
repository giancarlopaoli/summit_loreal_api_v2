<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Configuration extends Model
{
    use HasFactory;

    protected $connection = 'mysql';

    protected $fillable = [
        'value',
        'updated_by'
    ];

    public function updater() {
        return $this->belongsTo(User::class, "updated_by");
    }

    public function get_value ($config) {
        //return $this->where('shortname', $name)->first()->id;
        return $this->select('value', 'description')->where('shortname', $config)->first()->value;
        //return true;
    }
}
