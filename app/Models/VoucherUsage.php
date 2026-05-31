<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoucherUsage extends Model
{
    protected $fillable = [
        'voucher_id', 'company_id', 'used_at',
    ];

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }
}
