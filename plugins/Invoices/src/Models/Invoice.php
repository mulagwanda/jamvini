<?php

namespace Plugins\Invoices\src\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Plugins\Invoices\src\Models\InvoiceItem;

class Invoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'client_id', 'invoice_number', 'currency', 'subtotal', 'discount',
        'tax_amount', 'total', 'status', 'source', 'external_id', 'due_date',
        'sent_at', 'paid_at', 'cancelled_at', 'notes', 'payment_terms', 'admin_notes'
    ];

    protected $casts = [
        'due_date' => 'date',
        'sent_at' => 'datetime',
        'paid_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function client()
    {
        return $this->belongsTo(\Plugins\Clients\src\Models\Client::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', ['draft', 'sent']);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue');
    }

    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['sent', 'partial', 'overdue']);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class)->latest();
    }

    public function getPaidAmountAttribute()
    {
        if ($this->relationLoaded('transactions')) {
            return $this->transactions->where('status', 'completed')->sum('amount');
        }

        return $this->transactions()->where('status', 'completed')->sum('amount');
    }

    public function getRemainingAmountAttribute()
    {
        return max(0, $this->total - $this->paid_amount);
    }

    public function getPaymentStatusAttribute()
    {
        if ($this->paid_amount >= $this->total) return 'paid';
        if ($this->paid_amount > 0) return 'partial';
        return 'unpaid';
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date && $this->remaining_amount > 0 && $this->due_date->isPast();
    }

    public function getAgeBucketAttribute(): string
    {
        if (!$this->due_date || !$this->is_overdue) {
            return 'current';
        }

        $days = (int) $this->due_date->diffInDays(now());

        return match (true) {
            $days >= 60 => '60+',
            $days >= 30 => '30+',
            $days >= 14 => '14+',
            $days >= 7 => '7+',
            default => '1-6',
        };
    }
}
