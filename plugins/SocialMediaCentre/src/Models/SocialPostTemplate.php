<?php

namespace Plugins\SocialMediaCentre\src\Models;

use Illuminate\Database\Eloquent\Model;

class SocialPostTemplate extends Model
{
    protected $table = 'social_post_templates';

    protected $fillable = [
        'name', 'slug', 'category', 'description', 'title_template',
        'caption_template', 'hashtags', 'platforms', 'status',
        'is_system', 'sort_order',
    ];

    protected $casts = [
        'hashtags' => 'array',
        'platforms' => 'array',
        'is_system' => 'boolean',
    ];
}
