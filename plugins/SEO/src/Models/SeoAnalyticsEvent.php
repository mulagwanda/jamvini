<?php

namespace Plugins\SEO\src\Models;

use Illuminate\Database\Eloquent\Model;

class SeoAnalyticsEvent extends Model
{
    protected $table = 'seo_analytics_events';

    protected $fillable = [
        'visitor_id', 'session_id', 'event_type', 'url', 'path', 'path_hash', 'title', 'referrer',
        'utm_source', 'utm_medium', 'utm_campaign', 'device_type', 'browser', 'country',
        'ip_hash', 'metadata', 'occurred_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'occurred_at' => 'datetime',
    ];
}
