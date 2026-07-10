<?php

namespace Plugins\Menus\src\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItem extends Model
{
    protected $table = 'menu_items';

    protected $fillable = [
        'menu_id',
        'parent_id',
        'label',
        'type',
        'url',
        'page_id',
        'route_name',
        'target',
        'visibility',
        'position',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'position' => 'integer',
    ];

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('position')->orderBy('label');
    }

    public function resolvedUrl(): string
    {
        if ($this->type === 'page' && $this->page_id && class_exists(\Plugins\CMS\src\Models\Page::class)) {
            $page = \Plugins\CMS\src\Models\Page::find($this->page_id);

            if ($page) {
                return '/' . ltrim($page->slug, '/');
            }
        }

        if ($this->type === 'route' && $this->route_name && \Illuminate\Support\Facades\Route::has($this->route_name)) {
            return route($this->route_name);
        }

        return $this->url ?: '#';
    }
}
