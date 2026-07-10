<?php

namespace Plugins\Clients\src\Models;

use Illuminate\Database\Eloquent\Model;

class ClientContact extends Model
{
    protected $fillable = [
        'client_id', 'name', 'role', 'email', 'phone',
        'receives_billing', 'receives_support', 'is_primary',
    ];

    protected $casts = [
        'receives_billing' => 'boolean',
        'receives_support' => 'boolean',
        'is_primary' => 'boolean',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
