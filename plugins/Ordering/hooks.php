<?php

use App\Core\Hooks\Filter;
use Plugins\Services\src\Models\Service;

Filter::add('shortcode.pricing', function ($output, array $attrs) {
    if (!class_exists(Service::class)) {
        return '';
    }

    $limit = max(1, min(24, (int) ($attrs['limit'] ?? 0) ?: 0));
    $module = $attrs['module'] ?? 'hosting';

    $services = Service::where('is_active', true)
        ->when($module !== 'all', fn ($query) => $query->whereHas('group', fn ($group) => $group->where('module', $module)))
        ->when($limit > 0, fn ($query) => $query->limit($limit))
        ->get();

    return view('plugins.Ordering::public.services', compact('services'))->render();
}, 10, 2);
