<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'is_email_verified',
        'is_superuser',
        'is_staff',
        'terms_agreement',
        'avatar_id',
        'access',
        'refresh'
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
        'access',
        'refresh'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_email_verified' => 'boolean',
        'is_superuser' => 'boolean',
        'is_staff' => 'boolean',
        'terms_agreement' => 'boolean',
    ];

    /**
     * Get the identifier that will be stored in the JWT claim.
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Get the custom claims added to the JWT.
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Relation avec l'avatar.
     */

    public function otps(): HasMany
    {
        return $this->hasMany(Otp::class);
    }

    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class, 'auctioneer_id');
    }

    public function bids(): HasMany
    {
        return $this->hasMany(Bid::class);
    }

    public function watchlists(): HasMany
    {
        return $this->hasMany(Watchlist::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'reviewer_id');
    }
    public function avatar()
    {
        return $this->belongsTo(Avatar::class);
    }
}
