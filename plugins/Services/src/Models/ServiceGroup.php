<?php

namespace Plugins\Services\src\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceGroup extends Model
{
    protected $table = 'service_groups';

    protected $fillable = ['name', 'slug', 'icon', 'description', 'order', 'module', 'requires_domain', 'is_active'];

    protected $casts = [
        'requires_domain' => 'boolean',
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    public function services()
    {
        return $this->hasMany(Service::class, 'group_id')->orderBy('amount');
    }
}
