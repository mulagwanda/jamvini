<?php

namespace Plugins\Domains\src\Models;

use Illuminate\Database\Eloquent\Model;

class DomainPricing extends Model
{
    protected $table = 'domain_pricing';

    protected $fillable = [
        'tld_id', 'years', 'register_price', 'renewal_price', 'transfer_price', 'is_active'
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function tld()
    {
        return $this->belongsTo(DomainTld::class, 'tld_id');
    }
}