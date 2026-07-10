<div class="menu-item-node" data-item-id="{{ $item->id }}">
    <div class="menu-item-card" draggable="true">
        <div class="menu-item-head">
            <div class="menu-item-main">
                <span class="menu-drag-handle" title="Drag to reorder">⋮⋮</span>
                <div class="menu-item-title">
                    <strong>{{ $item->label }}</strong>
                    <span class="menu-item-meta">{{ ucfirst($item->type) }} · {{ $item->resolvedUrl() }} · Position {{ $item->position }}</span>
                </div>
            </div>
            <div class="menu-item-actions">
                <span class="pill pill-{{ $item->is_active ? 'ok' : 'mute' }}">{{ $item->is_active ? 'Active' : 'Hidden' }}</span>
                <button type="button" class="menu-item-toggle" data-menu-toggle>Details</button>
            </div>
        </div>
        <div class="menu-item-details">
        <form action="{{ route('admin.menus.items.update', [$menu, $item]) }}" method="POST">
            @csrf
            @method('PUT')

            @include('plugins.menus::admin.partials.item-fields', [
                'item' => $item,
                'menu' => $menu,
                'parentItems' => $parentItems,
                'pages' => $pages,
                'quickLinks' => $quickLinks,
            ])

            <div style="display: flex; justify-content: flex-end;">
                <button class="btn btn-sm btn-primary">Save Item</button>
            </div>
        </form>

        <form action="{{ route('admin.menus.items.destroy', [$menu, $item]) }}" method="POST" onsubmit="return confirm('Remove this menu item?')" style="display: flex; justify-content: flex-end; margin-top: 8px;">
            @csrf
            @method('DELETE')
            <button class="btn btn-sm btn-outline-danger">Remove</button>
        </form>
        </div>
    </div>

    @if($item->children->count())
        <div class="menu-children">
            @foreach($item->children as $child)
                @include('plugins.menus::admin.partials.item-card', [
                    'item' => $child,
                    'menu' => $menu,
                    'parentItems' => $parentItems,
                    'pages' => $pages,
                    'quickLinks' => $quickLinks,
                    'level' => $level + 1,
                ])
            @endforeach
        </div>
    @else
        <div class="menu-children"></div>
    @endif
</div>
