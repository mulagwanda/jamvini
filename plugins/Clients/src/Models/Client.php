<?php

namespace Plugins\Clients\src\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Client extends Authenticatable
{
    use SoftDeletes, Notifiable;

    protected $fillable = [
        'client_number', 'company_name', 'type', 'first_name', 'last_name', 'email',
        'phone', 'mobile', 'billing_email', 'technical_email', 'address', 'city', 'state',
        'postal_code', 'country', 'tin_number', 'vat_exempt', 'currency', 'language',
        'timezone', 'credit_balance', 'status', 'password', 'notes', 'source',
        'external_id', 'email_marketing_opt_in', 'client_group_id'
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'vat_exempt' => 'boolean',
        'email_marketing_opt_in' => 'boolean',
        'credit_balance' => 'decimal:2',
        'password' => 'hashed',
    ];

    public function services()
    {
        return $this->hasMany(\Plugins\Services\src\Models\ClientService::class);
    }

    public function invoices()
    {
        return $this->hasMany(\Plugins\Invoices\src\Models\Invoice::class);
    }

    public function domains()
    {
        return $this->hasMany(\Plugins\Domains\src\Models\Domain::class);
    }

    public function group()
    {
        return $this->belongsTo(ClientGroup::class, 'client_group_id');
    }

    public function contacts()
    {
        return $this->hasMany(ClientContact::class);
    }

    public function customFieldValues()
    {
        return $this->hasMany(\Plugins\CustomFields\src\Models\CustomFieldValue::class, 'entity_id')
            ->where('entity_type', 'client');
    }

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
