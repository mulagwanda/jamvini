<?php

use App\Core\Hooks\Filter;
use Plugins\BannerStudio\src\Models\Banner;

Filter::add('shortcode.banner', function ($output, array $attrs) {
    $slug = $attrs['slug'] ?? null;

    if (!$slug) {
        return '';
    }

    $banner = Banner::where('slug', $slug)->where('is_active', true)->first();

    return $banner ? view('plugins.BannerStudio::public.render', compact('banner'))->render() : '';
});
