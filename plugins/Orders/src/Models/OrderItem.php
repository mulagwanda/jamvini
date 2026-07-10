<?php

namespace Plugins\Orders\src\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $table = 'order_items';

    protected $fillable = [
        'order_id', 'service_id', 'client_service_id', 'domain_id', 'type', 'description', 'domain',
        'quantity', 'unit_price', 'setup_fee', 'discount',
        'tax_rate', 'total', 'billing_cycle', 'years', 'options', 'status',
        'provisioned_at', 'provisioning_notes'
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'setup_fee' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'total' => 'decimal:2',
        'options' => 'array',
        'provisioned_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function service()
    {
        return $this->belongsTo(\Plugins\Services\src\Models\Service::class);
    }

    public function clientService()
    {
        return $this->belongsTo(\Plugins\Services\src\Models\ClientService::class, 'client_service_id');
    }

    public function domainRecord()
    {
        return $this->belongsTo(\Plugins\Domains\src\Models\Domain::class, 'domain_id');
    }
}
