<?php

namespace App\Models;

use App\Models\Concerns\TracksUserAttribution;

use Illuminate\Database\Eloquent\Model;

class InspectionItem extends Model
{
    use TracksUserAttribution;

    protected $fillable = ['booking_inspection_id', 'zone_id', 'condition', 'notes', 'photo_path'];
    
    public function inspection()
    {
        return $this->belongsTo(BookingInspection::class, 'booking_inspection_id');
    }

    public function checklistItem()
    {
        return $this->belongsTo(ChecklistItem::class);
    }
}
