<?php

namespace Plugins\SocialMediaCentre\src\Models;

use Illuminate\Database\Eloquent\Model;

class SocialCampaign extends Model
{
    protected $table = 'social_campaigns';

    protected $fillable = [
        'name', 'slug', 'goal', 'starts_at', 'ends_at', 'status', 'notes',
    ];

    protected $casts = [
        'starts_at' => 'date',
        'ends_at' => 'date',
    ];

    public function posts()
    {
        return $this->hasMany(SocialPost::class, 'campaign_id');
    }
}
