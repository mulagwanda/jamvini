<?php

namespace Plugins\Domains\src\Models;

use Illuminate\Database\Eloquent\Model;

class DomainTld extends Model
{
    protected $table = 'domain_tlds';

    protected $fillable = [
        'service_id', 'tld', 'registrar_slug', 'dns_management', 'email_forwarding',
        'id_protection', 'epp_code', 'auto_register', 'is_active'
    ];

    protected $casts = [
        'dns_management' => 'boolean',
        'email_forwarding' => 'boolean',
        'id_protection' => 'boolean',
        'epp_code' => 'boolean',
        'auto_register' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function service()
    {
        return $this->belongsTo(\Plugins\Services\src\Models\Service::class);
    }

    public function pricing()
    {
        return $this->hasMany(DomainPricing::class, 'tld_id');
    }

    public function addons()
    {
        return $this->hasMany(DomainAddon::class, 'tld_id');
    }

    public function periodPricing()
    {
        return $this->hasMany(DomainPeriodPricing::class, 'tld_id');
    }
}