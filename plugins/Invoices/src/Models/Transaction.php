<?php

namespace Plugins\Invoices\src\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'invoice_id', 'amount', 'currency', 'fee_amount', 'refunded_amount',
        'payment_method', 'gateway_slug', 'gateway_type', 'transaction_id',
        'status', 'paid_at', 'refunded_at', 'notes', 'metadata', 'recorded_by'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee_amount' => 'decimal:2',
        'refunded_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'refunded_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(\App\Models\Admin::class, 'recorded_by');
    }
}
