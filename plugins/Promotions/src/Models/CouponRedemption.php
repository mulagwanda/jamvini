<?php

namespace Plugins\Promotions\src\Models;

use Illuminate\Database\Eloquent\Model;

class CouponRedemption extends Model
{
    protected $fillable = [
        'coupon_id', 'promotion_id', 'client_id', 'order_id', 'invoice_id',
        'code', 'discount_amount', 'currency', 'status', 'ip_address',
        'metadata', 'redeemed_at',
    ];

    protected $casts = [
        'discount_amount' => 'decimal:2',
        'metadata' => 'array',
        'redeemed_at' => 'datetime',
    ];
}
