<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Filament\Notifications\Notification;

class FundType extends Model
{
    // use LogsActivity;

    protected $fillable = [
        'name',
        'icon',
        'balance',
        'company_id',
        'account_number',
        'account_name',
        'qr_code',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
    ];

    public function payments()
    {
        return $this->hasMany(BookingPayments::class, 'fund_type_id');
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    // Get QR Code URL
    public function getQrCodeUrlAttribute()
    {
        if ($this->qr_code && Storage::disk('public')->exists($this->qr_code)) {
            return Storage::url($this->qr_code);
        }
        return null;
    }

    // Get QR Code for S3
    public function getQrCodeTemporaryUrlAttribute()
    {
        if ($this->qr_code && Storage::disk('s3')->exists($this->qr_code)) {
            return Storage::disk('s3')->temporaryUrl($this->qr_code, now()->addMinutes(5));
        }
        return null;
    }

    // Check if fund type has complete bank details
    public function hasBankDetails()
    {
        return $this->account_number && $this->account_name;
    }

    // Boot method for model events
    protected static function boot()
    {
        parent::boot();

        static::updating(function ($model) {
            if ($model->isDirty('qr_code')) {
                $oldPath = $model->getOriginal('qr_code');
                if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }
        });

        static::deleting(function ($model) {
            if ($model->qr_code && Storage::disk('public')->exists($model->qr_code)) {
                Storage::disk('public')->delete($model->qr_code);
            }
        });
    }

    // public function getActivitylogOptions(): LogOptions
    // {
    //     return LogOptions::defaults()
    //     ->logOnly([
    //         'name',
    //         'balance',
    //         'account_number',
    //         'account_name',
    //         'qr_code'
    //     ]);
    // }
}