<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
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
        'id',
        'name',
        'last_name',
        'email',
        'password',
        'phone',
        'document_type',
        'document_number',
        'country',
        'city',
        'type',
        'preferences',
        'accepts_publicity',
        'confirmed',
        'car1',
        'car2',
        'car3',
        'image'
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
        'email_verified_at' => 'datetime'
    ];

    public function trips() {
        return $this->hasMany(Trip::class);
    }

    public function media() {
        return $this->hasMany(Media::class);
    }

    public function results() {
        return $this->hasMany(TriviaResult::class);
    }

    public function surveys() {
        return $this->hasMany(Survey::class);
    }

    public function final_surveys() {
        return $this->hasMany(FinalSurvey::class);
    }

    public function music_votes() {
        return $this->hasMany(MusicVote::class);
    }
}
