<?php

namespace Plugins\BannerStudio\src\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $table = 'banner_studio_banners';

    protected $fillable = [
        'title',
        'slug',
        'settings',
        'layers',
        'is_active',
    ];

    protected $casts = [
        'settings' => 'array',
        'layers' => 'array',
        'is_active' => 'boolean',
    ];
}
