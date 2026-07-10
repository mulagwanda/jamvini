<?php

namespace Plugins\SocialMediaCentre\src\Models;

use Illuminate\Database\Eloquent\Model;

class SocialAccount extends Model
{
    protected $table = 'social_accounts';

    protected $fillable = [
        'platform', 'name', 'handle', 'status', 'settings',
    ];

    protected $casts = [
        'settings' => 'array',
    ];
}
