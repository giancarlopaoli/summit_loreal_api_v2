<?php

namespace App\Models;

use App\Enums\ClientUserStatus;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'last_name',
        'email',
        'phone',
        'password',
        'status',
        'document_type_id',
        'document_number'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function clients() {
        return $this->belongsToMany(Client::class)->using(ClientUser::class);
    }

    public function assigned_client() {
        return $this->belongsToMany(Client::class)->using(ClientUser::class)->wherePivot('status', ClientUserStatus::Asignado)->latest();
    }

    public function quotations() {
        return $this->hasMany(Quotation::class);
    }

    public function ibops_ranges() {
        return $this->hasMany(IbopsRange::class);
    }

    public function vendor_spreads() {
        return $this->hasMany(VendorSpread::class);
    }

    public function operations() {
        return $this->hasMany(Operation::class);
    }

    public function executive() {
        return $this->hasOne(Executive::class);
    }

    public function logs() {
        return $this->hasMany(AccessLog::class);
    }

    public function scopeActivityOlderThan($query, $interval)
    {
        return $query->where('last_active', '>=', Carbon::now()->subMinutes($interval)->toDateTimeString());
    }

    public static function get_authenticated_users() {
        return self::activityOlderThan(5)->get();
    }
}
