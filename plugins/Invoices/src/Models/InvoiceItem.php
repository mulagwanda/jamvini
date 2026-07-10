<?php

namespace Plugins\Invoices\src\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id', 'service_id', 'type', 'description', 'domain',
        'quantity', 'unit_price', 'tax_rate', 'total', 'billing_cycle',
        'period_start', 'period_end', 'metadata'
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'total' => 'decimal:2',
        'period_start' => 'date',
        'period_end' => 'date',
        'metadata' => 'array',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function service()
    {
        return $this->belongsTo(\Plugins\Services\src\Models\Service::class);
    }
}
