<?php

namespace Plugins\Services\src\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use SoftDeletes;

    protected $fillable = [
    'group_id', 'name', 'description', 'amount', 'setup_fee',
    'billing_cycle', 'features', 'is_active', 'is_free', 'billing_type',
    'pricing', 'free_domain_cycles', 'configurable_options',
    'upgradable', 'allow_downgrade', 'sort_order', 'badge_label', 'is_featured'
];

    protected $casts = [
    'amount' => 'decimal:2',
    'setup_fee' => 'decimal:2',
    'features' => 'array',
    'is_active' => 'boolean',
    'is_featured' => 'boolean',
    'is_free' => 'boolean',
    'upgradable' => 'boolean',
    'allow_downgrade' => 'boolean',
    'pricing' => 'array',
    'free_domain_cycles' => 'array',
    'configurable_options' => 'array',
];


    public function group()
    {
        return $this->belongsTo(ServiceGroup::class, 'group_id');
    }

    public function clientServices()
    {
        return $this->hasMany(ClientService::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->whereHas('group', fn($q) => $q->where('module', $type));
    }

    public function servers()
    {
        return $this->belongsToMany(Server::class, 'server_service')
            ->withPivot('package_name', 'limits', 'is_default');
    }

    public function options()
    {
        return $this->hasMany(ServiceOption::class)->orderBy('sort_order');
    }

    public function customFields()
    {
        return $this->hasMany(ServiceCustomField::class)->orderBy('sort_order');
    }

    public function addons()
    {
        return $this->hasMany(ServiceAddon::class)->orderBy('sort_order');
    }

    public function getPrice(string $billingCycle): float
    {
        if ($this->is_free) return 0;
        
        $pricing = $this->pricing ?? [];
        return (float) ($pricing[$billingCycle] ?? $this->amount ?? 0);
    }

    public function getAvailableBillingCycles(): array
    {
        if ($this->is_free) return ['free'];
        if ($this->billing_type === 'one-time') return ['one-time'];
        
        $pricing = $this->pricing ?? [];
        return array_keys($pricing);
    }

    public function getFreeDomainCycles(): array
    {
        return $this->free_domain_cycles ?? [];
    }
    public function getTypeAttribute(): string
    {
        return $this->group?->module ?? 'custom';
    }
    public function tlds()
    {
        return $this->hasMany(\Plugins\Domains\src\Models\DomainTld::class, 'service_id');
    }
}
