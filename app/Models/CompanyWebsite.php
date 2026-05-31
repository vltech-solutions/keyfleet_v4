<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CompanyWebsite extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'header_text',
        'subheader',
        'banner',
        'about_us',
        'about_us_image',
        'business_address',
        'map_url',
        'company_id',
    ];

    /**
     * Get the company that owns the website settings.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

     protected static function boot()
    {
        parent::boot();

        // When updating: delete old file if path changes
        static::updating(function ($model) {
            if ($model->isDirty('banner')) {
                $oldPath = $model->getOriginal('banner');
                if ($oldPath && Storage::disk('s3')->exists($oldPath)) {
                    Storage::disk('s3')->delete($oldPath);
                }
            }

            if ($model->isDirty('about_us_image')) {
                $oldPath = $model->getOriginal('about_us_image');
                if ($oldPath && Storage::disk('s3')->exists($oldPath)) {
                    Storage::disk('s3')->delete($oldPath);
                }
            }
        });

        // When deleting: delete file from S3
        static::deleting(function ($model) {
            if ($model->about_us_image && Storage::disk('s3')->exists($model->about_us_image)) {
                Storage::disk('s3')->delete($model->about_us_image);
            }

            if ($model->banner && Storage::disk('s3')->exists($model->banner)) {
                Storage::disk('s3')->delete($model->banner);
            }
        });
    }
}