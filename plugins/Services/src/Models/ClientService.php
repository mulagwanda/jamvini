<?php

namespace Plugins\Services\src\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientService extends Model
{
    use SoftDeletes;

    protected $table = 'client_services';

    protected $fillable = [
        'client_id', 'service_id', 'server_id', 'control_panel', 'remote_username', 'remote_domain', 'price', 'billing_cycle',
        'next_due_date', 'registered_date', 'status', 'domain', 'notes'
    ];

    protected $casts = [
        'next_due_date' => 'date',
        'registered_date' => 'date',
        'price' => 'decimal:2',
    ];

    public function client()
    {
        return $this->belongsTo(\Plugins\Clients\src\Models\Client::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function server()
    {
        return $this->belongsTo(Server::class);
    }

    public function properties()
    {
        return $this->hasMany(ServiceProperty::class, 'client_service_id');
    }

    public function setProperty(string $key, ?string $value, ?string $label = null, bool $sensitive = false, bool $public = false, array $metadata = []): ServiceProperty
    {
        return $this->properties()->updateOrCreate(
            ['key' => $key],
            [
                'label' => $label ?: str($key)->replace('_', ' ')->title()->toString(),
                'value' => $value,
                'type' => $sensitive ? 'password' : 'text',
                'is_sensitive' => $sensitive,
                'is_public' => $public,
                'metadata' => $metadata,
            ]
        );
    }

    public function getProperty(string $key, mixed $default = null): mixed
    {
        return $this->properties()->where('key', $key)->value('value') ?? $default;
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
