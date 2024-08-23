<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    /*protected $fillable = [
        'email', 'phone', 'address', 'accountable_email'
    ];*/

    protected $guarded = [];

    protected $connection = 'mysql';

    public function representatives() {
        return $this->hasMany(Representative::class)->where('representatives.representative_type', '=', 'Representante Legal');
    }

    public function business_associates() {
        return $this->hasMany(Representative::class)->where('representatives.representative_type', '=', 'Socio');
    }

    public function economic_activity() {
        return $this->belongsTo(EconomicActivity::class);
    }

    public function document_type() {
        return $this->belongsTo(DocumentType::class);
    }

    public function client_special_exchange_rates() {
        return $this->hasMany(SpecialExchangeRate::class, "client_id");
    }

    public function vendor_special_exchange_rates() {
        return $this->hasMany(SpecialExchangeRate::class, "vendor_id");
    }

    public function executives_comissions() {
        return $this->hasMany(ExecutivesComission::class);
    }

    public function country() {
        return $this->belongsTo(Country::class);
    }

    public function association() {
        return $this->belongsTo(Association::class);
    }

    public function executive() {
        return $this->belongsTo(Executive::class);
    }

    public function invoice() {
        return $this->hasOne(Client::class, "invoice_to");
    }

    public function district() {
        return $this->belongsTo(District::class);
    }

    public function trackings() {
        return $this->hasMany(ClientTracking::class, "client_id");
    }

    public function quotations() {
        return $this->hasMany(Quotation::class);
    }

    public function profession() {
        return $this->belongsTo(Profession::class);
    }

    public function comissions() {
        return $this->hasMany(ClientComission::class, "client_id");
    }

    public function users() {
        return $this->belongsToMany(User::class)->using(ClientUser::class)->withPivot("status");
    }

    public function ibops_client_comissions() {
        return $this->hasMany(IbopsClientComission::class);
    }

    public function bank_accounts() {
        return $this->hasMany(BankAccount::class);
    }

    public function vendor_ranges() {
        return $this->hasMany(VendorRange::class, "vendor_id");
    }

    public function operations() {
        return $this->hasMany(Operation::class);
    }

    public function updater() {
        return $this->belongsTo(User::class, "updated_by");
    }

    public function status() {
        return $this->belongsTo(ClientStatus::class, "client_status_id");
    }

    public function documents() {
        return $this->hasMany(Document::class);
    }

    public function tracking_phase() {
        return $this->belongsTo(TrackingPhase::class);
    }

    public function sector() {
        return $this->belongsTo(Sector::class);
    }

    public function getClientFullNameAttribute()
    {
        if($this->customer_type == 'PJ'){
            return $this->name;
        }
        else{
            return $this->name . ' ' . $this->last_name . ' ' . $this->mothers_name;
        }
    }

    public function sales() {
        return $this->hasMany(Sale::class);
    }

    public function client_invoice_to() {
        return $this->belongsTo(Client::class, "invoice_to");
    }
}
