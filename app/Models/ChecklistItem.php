<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChecklistItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'company_id',
        'item',
        'order',
    ];

    // Relations
    public function group()
    {
        return $this->belongsTo(ChecklistGroup::class, 'group_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    
}
