<?php

namespace Plugins\Domains\src\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Domain extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'client_id', 'domain_name', 'tld', 'registrar',
        'registrar_domain_id', 'registrar_statuses', 'registrar_lock',
        'last_synced_at', 'registrar_meta',
        'registration_date', 'expiry_date', 'registration_period',
        'registration_fee', 'renewal_fee', 'nameservers',
        'status', 'auto_renew', 'notes'
    ];

    protected $casts = [
        'registration_date' => 'date',
        'expiry_date' => 'date',
        'last_synced_at' => 'datetime',
        'nameservers' => 'array',
        'registrar_statuses' => 'array',
        'registrar_meta' => 'array',
        'registrar_lock' => 'boolean',
        'auto_renew' => 'boolean',
        'registration_fee' => 'decimal:2',
        'renewal_fee' => 'decimal:2',
    ];

    public function client()
    {
        return $this->belongsTo(\Plugins\Clients\src\Models\Client::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->where('expiry_date', '<=', now()->addDays($days))
                     ->where('expiry_date', '>=', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now())
                     ->where('status', '!=', 'transferred');
    }

    public function getDaysUntilExpiryAttribute()
    {
        if (!$this->expiry_date) return null;
        return now()->diffInDays($this->expiry_date, false);
    }

    public function getIsExpiredAttribute()
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }
}
