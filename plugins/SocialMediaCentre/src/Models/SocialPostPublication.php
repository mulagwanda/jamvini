<?php

namespace Plugins\SocialMediaCentre\src\Models;

use Illuminate\Database\Eloquent\Model;

class SocialPostPublication extends Model
{
    protected $table = 'social_post_publications';

    protected $fillable = [
        'post_id', 'account_id', 'platform', 'mode', 'status', 'scheduled_at',
        'queued_at', 'published_at', 'provider_post_id', 'provider_url',
        'attempts', 'notes', 'request_payload', 'response_payload', 'last_error',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'queued_at' => 'datetime',
        'published_at' => 'datetime',
        'request_payload' => 'array',
        'response_payload' => 'array',
    ];

    public function post()
    {
        return $this->belongsTo(SocialPost::class, 'post_id');
    }

    public function account()
    {
        return $this->belongsTo(SocialAccount::class, 'account_id');
    }
}
