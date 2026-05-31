<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    protected $fillable = [
        'code', 'amount', 'type', 'valid_until', 'company_ids',
        'usage_limit', 'per_company_limit', 'active',
    ];

    protected $casts = [
        'company_ids' => 'array',
        'valid_until' => 'date',
    ];

    public function usages()
    {
        return $this->hasMany(VoucherUsage::class);
    }
}
