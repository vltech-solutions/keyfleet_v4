<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InspectionItem extends Model
{
    protected $fillable = ['booking_inspection_id', 'zone_id', 'condition', 'notes', 'photo_path'];
    
    public function inspection()
    {
        return $this->belongsTo(BookingInspection::class);
    }

    public function checklistItem()
    {
        return $this->belongsTo(ChecklistItem::class);
    }
}
