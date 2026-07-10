<?php

namespace Plugins\Clients\src\Models;

use Illuminate\Database\Eloquent\Model;

class ClientGroup extends Model
{
    protected $fillable = [
        'name', 'slug', 'color', 'discount_percent', 'is_default', 'description',
    ];

    protected $casts = [
        'discount_percent' => 'decimal:2',
        'is_default' => 'boolean',
    ];

    public function clients()
    {
        return $this->hasMany(Client::class);
    }
}
