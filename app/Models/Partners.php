<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Partners extends Model
{
    protected $fillable = [
        'name', 'email', 'contact_number', 'address',
        'commission_type', 'commission_value', 'commission_base',
        'access_token', 'token_expires_at'
    ];

    protected $casts = [
        'token_expires_at' => 'datetime',
    ];

    public function cars()
    {
        return $this->hasMany(Car::class, 'partner_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Generate a new access token for the partner
     */
    public function generateAccessToken()
    {
        $this->access_token = Str::random(64);
        $this->token_expires_at = now()->addYear();
        $this->save();
        
        return $this->access_token;
    }

    /**
     * Check if token is valid
     */
    public function hasValidToken()
    {
        return $this->access_token && 
               $this->token_expires_at && 
               $this->token_expires_at->isFuture();
    }

    /**
     * Get the partner report URL
     */
    public function getReportUrlAttribute()
    {
        return route('partner.report', $this->access_token);
    }

    /**
     * Scope for valid tokens
     */
    public function scopeWithValidToken($query)
    {
        return $query->whereNotNull('access_token')
                     ->where('token_expires_at', '>', now());
    }
}