<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CarImage extends Model
{
    protected $fillable = [
        'car_id',
        'image_type',
        'path',
    ];

    /**
     * Get the car this image belongs to.
     */
    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }

    /**
     * Get the full URL of the image.
     */
    public function getUrlAttribute(): ?string
    {
        if (!$this->path) return null;

        return Storage::disk('s3')->url($this->path);
    }

    protected static function boot()
    {
        parent::boot();

        // When updating: delete old file if path changes
        static::updating(function ($model) {
            if ($model->isDirty('path')) {
                $oldPath = $model->getOriginal('path');
                if ($oldPath && Storage::disk('s3')->exists($oldPath)) {
                    Storage::disk('s3')->delete($oldPath);
                }
            }
        });

        // When deleting: delete file from S3
        static::deleting(function ($model) {
            if ($model->path && Storage::disk('s3')->exists($model->path)) {
                Storage::disk('s3')->delete($model->path);
            }
        });
    }
}