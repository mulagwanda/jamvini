<?php

namespace Plugins\SocialMediaCentre\src\Models;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SocialPost extends Model
{
    use SoftDeletes;

    protected $table = 'social_posts';

    protected $fillable = [
        'campaign_id', 'title', 'caption', 'link_url', 'hashtags', 'platforms',
        'status', 'scheduled_at', 'published_at', 'created_by', 'notes',
    ];

    protected $casts = [
        'hashtags' => 'array',
        'platforms' => 'array',
        'scheduled_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    public function campaign()
    {
        return $this->belongsTo(SocialCampaign::class, 'campaign_id');
    }

    public function media()
    {
        return $this->belongsToMany(\Plugins\Media\src\Models\Media::class, 'social_post_media', 'post_id', 'media_id')
            ->withPivot('sort_order')
            ->withTimestamps()
            ->orderBy('social_post_media.sort_order');
    }

    public function logs()
    {
        return $this->hasMany(SocialPostLog::class, 'post_id');
    }

    public function publications()
    {
        return $this->hasMany(SocialPostPublication::class, 'post_id');
    }

    public function creator()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }
}
