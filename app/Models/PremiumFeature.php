<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PremiumFeature extends Model
{
    use HasFactory;

    protected $fillable = [
        'feature',
        'active',
        'pricing',
    ];

    protected $casts = [
        'active' => 'boolean',
        'pricing' => 'array', // Automatically cast JSON to array
    ];
}