<?php

namespace Plugins\Menus\src\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Plugins\Menus\src\Models\Menu;
use Plugins\Menus\src\Models\MenuItem;

class MenuController extends Controller
{
    protected array $locations = [
        'primary' => 'Primary Header',
        'footer_product' => 'Footer Product',
        'footer_company' => 'Footer Company',
        'footer_support' => 'Footer Support',
    ];

    protected array $quickLinks = [
        '/' => 'Homepage',
        '/hosting' => 'Hosting',
        '/domains' => 'Domain Search',
        '/blog' => 'Blog',
        '/cart' => 'Cart',
        '/login' => 'Login',
        '/client/dashboard' => 'Client Dashboard',
    ];

    public function index()
    {
        if (!$this->tablesReady()) {
            return redirect()->route('admin.system.index')
                ->with('error', 'Menus needs a database update. Please run migrations, then open Menus again.');
        }

        $menus = Menu::withCount('items')->orderBy('location')->orderBy('name')->get();

        return view('plugins.menus::admin.index', [
            'menus' => $menus,
            'locations' => $this->locations,
        ]);
    }

    public function store(Request $request)
    {
        if (!$this->tablesReady()) {
            return redirect()->route('admin.system.index')
                ->with('error', 'Menus needs a database update. Please run migrations, then try again.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ]);

        $slug = Str::slug($validated['name']);
        $baseSlug = $slug;
        $i = 2;

        while (Menu::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $i++;
        }

        Menu::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'location' => $validated['location'] ?: null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.menus.index')->with('success', 'Menu created.');
    }

    public function edit(Menu $menu)
    {
        $menu->load(['items.children']);

        return view('plugins.menus::admin.edit', [
            'menu' => $menu,
            'locations' => $this->locations,
            'quickLinks' => $this->quickLinks,
            'pages' => $this->pages(),
            'parentItems' => $menu->items()->orderBy('position')->get(),
        ]);
    }

    public function update(Request $request, Menu $menu)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ]);

        $menu->update([
            'name' => $validated['name'],
            'location' => $validated['location'] ?: null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.menus.edit', $menu)->with('success', 'Menu updated.');
    }

    public function destroy(Menu $menu)
    {
        $menu->delete();

        return redirect()->route('admin.menus.index')->with('success', 'Menu deleted.');
    }

    public function storeItem(Request $request, Menu $menu)
    {
        $validated = $this->validateItem($request, $menu);
        $menu->items()->create($this->prepareItemData($validated, $request));

        return redirect()->route('admin.menus.edit', $menu)->with('success', 'Menu item added.');
    }

    public function updateItem(Request $request, Menu $menu, MenuItem $item)
    {
        abort_unless($item->menu_id === $menu->id, 404);

        $validated = $this->validateItem($request, $menu, $item);
        $item->update($this->prepareItemData($validated, $request));

        return redirect()->route('admin.menus.edit', $menu)->with('success', 'Menu item updated.');
    }

    public function destroyItem(Menu $menu, MenuItem $item)
    {
        abort_unless($item->menu_id === $menu->id, 404);

        $item->delete();

        return redirect()->route('admin.menus.edit', $menu)->with('success', 'Menu item removed.');
    }

    public function reorderItems(Request $request, Menu $menu)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|integer',
            'items.*.parent_id' => 'nullable|integer',
            'items.*.position' => 'required|integer|min:0|max:9999',
        ]);

        $menuItemIds = $menu->items()->pluck('id')->map(fn ($id) => (int) $id)->all();
        $allowedIds = array_flip($menuItemIds);

        foreach ($validated['items'] as $itemData) {
            $itemId = (int) $itemData['id'];
            $parentId = $itemData['parent_id'] ? (int) $itemData['parent_id'] : null;

            if (!isset($allowedIds[$itemId])) {
                continue;
            }

            if ($parentId && !isset($allowedIds[$parentId])) {
                $parentId = null;
            }

            if ($parentId === $itemId) {
                $parentId = null;
            }

            MenuItem::where('menu_id', $menu->id)->where('id', $itemId)->update([
                'parent_id' => $parentId,
                'position' => (int) $itemData['position'],
            ]);
        }

        return response()->json(['success' => true]);
    }

    protected function validateItem(Request $request, Menu $menu, ?MenuItem $item = null): array
    {
        return $request->validate([
            'label' => 'required|string|max:255',
            'type' => 'required|in:custom,page,route',
            'url' => 'nullable|string|max:255',
            'page_id' => 'nullable|integer',
            'route_name' => 'nullable|string|max:255',
            'parent_id' => [
                'nullable',
                'integer',
                function ($attribute, $value, $fail) use ($menu, $item) {
                    if (!$value) {
                        return;
                    }

                    if ($item && (int) $value === (int) $item->id) {
                        $fail('A menu item cannot be its own parent.');
                        return;
                    }

                    if (!MenuItem::where('menu_id', $menu->id)->where('id', $value)->exists()) {
                        $fail('The selected parent item is invalid.');
                    }
                },
            ],
            'target' => 'required|in:_self,_blank',
            'visibility' => 'required|in:all,guest,auth',
            'position' => 'nullable|integer|min:0|max:9999',
            'is_active' => 'boolean',
        ]);
    }

    protected function prepareItemData(array $validated, Request $request): array
    {
        if ($validated['type'] === 'page') {
            $validated['url'] = null;
        }

        if ($validated['type'] !== 'page') {
            $validated['page_id'] = null;
        }

        if ($validated['type'] !== 'route') {
            $validated['route_name'] = null;
        }

        $validated['parent_id'] = $validated['parent_id'] ?: null;
        $validated['position'] = $validated['position'] ?? 0;
        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }

    protected function pages()
    {
        if (!class_exists(\Plugins\CMS\src\Models\Page::class)) {
            return collect();
        }

        return \Plugins\CMS\src\Models\Page::published()->orderBy('title')->get();
    }

    protected function tablesReady(): bool
    {
        try {
            return Schema::hasTable('menus') && Schema::hasTable('menu_items');
        } catch (\Throwable $e) {
            return false;
        }
    }
}
