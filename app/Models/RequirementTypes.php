<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequirementTypes extends Model
{
    use HasFactory;

    // protected $table = 'requirement_types';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'key',
        'label',
        'helper',
        'required',
        'sample',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'required' => 'boolean',
    ];
}
