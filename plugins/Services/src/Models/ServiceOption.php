<?php

namespace Plugins\Services\src\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceOption extends Model
{
    protected $table = 'service_options';

    protected $fillable = [
        'service_id', 'name', 'type', 'options', 'prices',
        'is_required', 'sort_order', 'is_active'
    ];

    protected $casts = [
        'options' => 'array',
        'prices' => 'array',
        'is_required' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}