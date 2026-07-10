<?php
use App\Core\Hooks\Filter;

Filter::add('shortcode.slider', function($output, $attrs) {
    $slug = $attrs['slug'] ?? '';
    if (!$slug) return '';
    
    $slider = \Plugins\Slider\src\Models\Slider::where('slug', $slug)->where('is_active', true)->first();
    if (!$slider) return '';
    
    $slider->load('activeSlides');
    return view('plugins.Slider::public.render', compact('slider'))->render();
}, 10, 2);