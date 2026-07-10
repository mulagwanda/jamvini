<?php

namespace Plugins\SocialMediaCentre\src\Models;

use Illuminate\Database\Eloquent\Model;

class SocialPostLog extends Model
{
    protected $table = 'social_post_logs';

    protected $fillable = [
        'post_id', 'platform', 'status', 'message', 'context',
    ];

    protected $casts = [
        'context' => 'array',
    ];
}
