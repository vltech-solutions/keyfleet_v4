<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChecklistGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'order',
    ];

    // Optional: default cast
    protected $casts = [
        'order' => 'integer',
    ];

    public function items()
    {
        return $this->hasMany(ChecklistItem::class, 'group_id');
    }
}
