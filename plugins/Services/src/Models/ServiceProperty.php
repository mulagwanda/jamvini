<?php

namespace Plugins\Services\src\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceProperty extends Model
{
    protected $table = 'client_service_properties';

    protected $fillable = [
        'client_service_id',
        'key',
        'label',
        'value',
        'type',
        'is_sensitive',
        'is_public',
        'metadata',
    ];

    protected $casts = [
        'value' => 'encrypted',
        'is_sensitive' => 'boolean',
        'is_public' => 'boolean',
        'metadata' => 'array',
    ];

    public function clientService()
    {
        return $this->belongsTo(ClientService::class, 'client_service_id');
    }
}
