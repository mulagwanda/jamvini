<?php

namespace Plugins\Promotions\src\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Promotion extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'description', 'promotion_type', 'discount_type',
        'discount_value', 'applies_to', 'recurring_cycles', 'status',
        'stackable', 'priority', 'starts_at', 'ends_at', 'conditions', 'metadata',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'stackable' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'conditions' => 'array',
        'metadata' => 'array',
    ];

    public function coupons()
    {
        return $this->hasMany(Coupon::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()));
    }
}
