<?php

namespace Plugins\Services\src\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Server extends Model
{
    use SoftDeletes;

    protected $table = 'servers';

    protected $fillable = [
        'name', 'type', 'hostname', 'ip_address', 'username',
        'password', 'api_token', 'port', 'use_ssl', 'status',
        'max_accounts', 'current_accounts', 'nameservers', 'notes'
    ];

    protected $casts = [
        'use_ssl' => 'boolean',
        'nameservers' => 'array',
        'password' => 'encrypted',
        'api_token' => 'encrypted',
    ];

    protected $hidden = ['password', 'api_token'];

    public function services()
    {
        return $this->belongsToMany(Service::class, 'server_service')
            ->withPivot('package_name', 'limits', 'is_default');
    }

    public function packages()
    {
        return $this->hasMany(ServerPackage::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
