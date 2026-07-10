<?php

namespace Plugins\Slider\src\Models;

use Illuminate\Database\Eloquent\Model;

class Slide extends Model
{
    protected $table = 'slides';

    protected $fillable = [
        'slider_id', 'title', 'subtitle', 'description', 'image',
        'button_text', 'button_link', 'button2_text', 'button2_link',
        'alignment', 'overlay_color', 'text_color', 'background_position',
        'content_width', 'animation', 'layers', 'order', 'is_active'
    ];

    protected $casts = ['is_active' => 'boolean', 'order' => 'integer', 'layers' => 'array'];

    public function slider()
    {
        return $this->belongsTo(Slider::class);
    }
}
