<?php

namespace App\Models;

use App\Enums\ClientUserStatus;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

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
        'document_number',
        'role_id'
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
        return $this->belongsToMany(Client::class)->using(ClientUser::class)->select(['id', 'name', 'last_name', 'mothers_name','document_type_id','document_number','phone','email','customer_type','type'])->withPivot("status");
    }

    public function active_clients() {
        return $this->belongsToMany(Client::class)->using(ClientUser::class)->select(['id', 'name', 'last_name', 'mothers_name','document_type_id','document_number','phone','email','customer_type','type'])->withPivot("status")->wherePivotIn('status', [ClientUserStatus::Asignado, ClientUserStatus::Activo]);
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

    public function ibops_client_comissions() {
        return $this->hasMany(IbopsClientComission::class);
    }

    public function vendor_spreads() {
        return $this->hasMany(VendorSpread::class);
    }

    public function operations() {
        return $this->hasMany(Operation::class);
    }

    public function operation_histories() {
        return $this->hasMany(OperationHistory::class);
    }

    public function executive() {
        return $this->hasOne(Executive::class);
    }

    public function operations_analyst() {
        return $this->hasOne(OperationAnalyst::class);
    }

    public function logs() {
        return $this->hasMany(AccessLog::class);
    }

    public function leads() {
        return $this->hasMany(Lead::class, "created_by");
    }

    public function document_type() {
        return $this->belongsTo(DocumentType::class);
    }

    public function role() {
        return $this->belongsTo(Role::class);
    }

    public function scopeActivityOlderThan($query, $interval)
    {
        return $query->where('last_active', '>=', Carbon::now()->subMinutes($interval)->toDateTimeString());
    }

    public function alerts() {
        return $this->hasMany(ExchangeRateAlert::class);
    }

    public function special_exchange_rates_updated() {
        return $this->hasMany(SpecialExchangeRate::class, "updated_by");
    }

    public function operations_analyst_logs() {
        return $this->hasMany(OperationsAnalystLog::class, "created_by");
    }

    public static function get_authenticated_users() {
        return self::activityOlderThan(15)->get();
    }
}
