<?php

namespace Plugins\Orders\src\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $table = 'orders';

    protected $fillable = [
        'client_id', 'order_number', 'status', 'currency', 'source', 'external_id',
        'payment_method', 'provisioning_status', 'ordered_at', 'subtotal', 'discount',
        'tax_amount', 'total', 'notes', 'client_notes', 'admin_notes', 'ip_address',
        'accepted_by', 'accepted_at', 'completed_at', 'cancelled_at',
        'cancellation_reason', 'invoice_id'
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'ordered_at' => 'datetime',
        'accepted_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(\Plugins\Clients\src\Models\Client::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function invoice()
    {
        return $this->belongsTo(\Plugins\Invoices\src\Models\Invoice::class, 'invoice_id');
    }

    public function acceptedBy()
    {
        return $this->belongsTo(\App\Models\Admin::class, 'accepted_by');
    }

    public function scopePending($q) { return $q->where('status', 'pending'); }
    public function scopeAccepted($q) { return $q->where('status', 'accepted'); }
    public function scopeCompleted($q) { return $q->where('status', 'completed'); }
}
