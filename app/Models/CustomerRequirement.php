<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CustomerRequirement extends Model
{
    use HasFactory;

    protected $table = 'customer_requirements';

    protected $fillable = [
        'customer_id',
        'requirement_type',
        'path',
        'status',
        'date_uploaded',
        'expiration',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function isExpired(): bool
    {
        return $this->expiration !== null && now()->greaterThan($this->expiration);
    }

    public function requirementType()
    {
        return $this->belongsTo(RequirementTypes::class, 'requirement_type', 'id');
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
