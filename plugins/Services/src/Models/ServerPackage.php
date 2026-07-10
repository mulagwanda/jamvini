<?php

namespace Plugins\Services\src\Models;

use Illuminate\Database\Eloquent\Model;

class ServerPackage extends Model
{
    protected $table = 'server_packages';

    protected $fillable = [
        'server_id',
        'name',
        'display_name',
        'limits',
        'is_active',
        'synced_at',
    ];

    protected $casts = [
        'limits' => 'array',
        'is_active' => 'boolean',
        'synced_at' => 'datetime',
    ];

    public function server()
    {
        return $this->belongsTo(Server::class);
    }
}
