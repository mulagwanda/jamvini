<?php

namespace Plugins\Domains\src\Models;

use Illuminate\Database\Eloquent\Model;

class DomainAddon extends Model
{
    protected $table = 'domain_addons';

    protected $fillable = ['tld_id', 'name', 'type', 'price', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function tld()
    {
        return $this->belongsTo(DomainTld::class, 'tld_id');
    }
}