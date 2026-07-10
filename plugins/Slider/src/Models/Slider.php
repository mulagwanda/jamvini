<?php

namespace Plugins\Slider\src\Models;

use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    protected $table = 'sliders';

    protected $fillable = ['title', 'slug', 'type', 'settings', 'is_active'];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    public function slides()
    {
        return $this->hasMany(Slide::class)->orderBy('order');
    }

    public function activeSlides()
    {
        return $this->slides()->where('is_active', true);
    }
}