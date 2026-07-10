<?php

namespace Plugins\Domains\src\Models;

use Illuminate\Database\Eloquent\Model;

class DomainPeriodPricing extends Model
{
    protected $table = 'domain_period_pricing';

    protected $fillable = ['tld_id', 'period_type', 'days', 'price'];

    public function tld()
    {
        return $this->belongsTo(DomainTld::class, 'tld_id');
    }
}