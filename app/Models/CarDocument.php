<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'car_id',
        'company_id',
        'document_type',
        'file_path',
        'expiration_date',
    ];

    protected $dates = [
        'expiration_date',
    ];

    public function car()
    {
        return $this->belongsTo(Car::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function isExpiringSoon(): bool
    {
        return $this->expiration_date && $this->expiration_date->isBefore(now()->addDays(7));
    }

    public function isExpired(): bool
    {
        return $this->expiration_date && $this->expiration_date->isBefore(now());
    }
}