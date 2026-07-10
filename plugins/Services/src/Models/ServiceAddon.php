<?php

namespace Plugins\Services\src\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceAddon extends Model
{
    protected $table = 'service_addons';

    protected $fillable = [
        'service_id',
        'name',
        'description',
        'price',
        'billing_cycle',
        'is_required',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_required' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
