@extends('themes.default::layouts.admin')

@section('title', 'Edit Menu')
@section('breadcrumbs')<a href="{{ route('admin.menus.index') }}">Menus</a> <span class="separator">/</span> <span class="current">{{ $menu->name }}</span>@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">{{ $menu->name }}</h1>
        <p class="page-subtitle">Build links for this theme menu location.</p>
    </div>
    <a href="{{ route('admin.menus.index') }}" class="btn btn-outline-primary">Back to Menus</a>
</div>

<div style="display: grid; grid-template-columns: .9fr 1.1fr; gap: 20px;">
    <div>
        <form action="{{ route('admin.menus.update', $menu) }}" method="POST" class="card">
            @csrf
            @method('PUT')
            <div class="card-header"><h3 class="card-title">Menu Settings</h3></div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label" for="name">Name</label>
                    <input type="text" id="name" name="name" class="form-input" value="{{ old('name', $menu->name) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="location">Location</label>
                    <select id="location" name="location" class="form-select">
                        <option value="">Unassigned</option>
                        @foreach($locations as $key => $label)
                            <option value="{{ $key }}" {{ old('location', $menu->location) === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $menu->is_active) ? 'checked' : '' }}>
                    <span class="toggle-slider"></span>
                    <span>Active</span>
                </label>
            </div>
            <div style="display:flex; justify-content:flex-end; padding: 0 24px 24px;">
                <button class="btn btn-primary">Save Menu</button>
            </div>
        </form>

        <form action="{{ route('admin.menus.items.store', $menu) }}" method="POST" class="card" style="margin-top: 20px;">
            @csrf
            <div class="card-header"><h3 class="card-title">Add Menu Item</h3></div>
            <div class="card-body">
                @include('plugins.menus::admin.partials.item-fields', [
                    'item' => null,
                    'menu' => $menu,
                    'parentItems' => $parentItems,
                    'pages' => $pages,
                    'quickLinks' => $quickLinks,
                ])
            </div>
            <div style="display:flex; justify-content:flex-end; padding: 0 24px 24px;">
                <button class="btn btn-primary">Add Item</button>
            </div>
        </form>
    </div>

    <div class="dash-card" style="padding: 0; overflow: hidden;">
        <div style="padding: 18px 24px; border-bottom: 1px solid var(--jv-gray-200);">
            <h3 style="margin: 0;">Menu Items</h3>
            <p style="margin: 4px 0 0; color: var(--jv-gray-500); font-size: .9rem;">Drag items to reorder. Drop an item onto another item to make it a child.</p>
        </div>

        @php $items = $menu->items()->with('children')->whereNull('parent_id')->orderBy('position')->orderBy('label')->get(); @endphp
        @if($items->count())
            <div class="menu-tree" id="menuTree" data-reorder-url="{{ route('admin.menus.items.reorder', $menu) }}" data-csrf="{{ csrf_token() }}">
                @foreach($items as $item)
                    @include('plugins.menus::admin.partials.item-card', [
                        'item' => $item,
                        'menu' => $menu,
                        'parentItems' => $parentItems,
                        'pages' => $pages,
                        'quickLinks' => $quickLinks,
                        'level' => 0,
                    ])
                @endforeach
            </div>
        @else
            <div class="empty-state" style="padding: 60px;">
                <div class="empty-state-icon">🔗</div>
                <div class="empty-state-title">No links yet</div>
                <p class="empty-state-desc">Add links from the form on the left.</p>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<style>
.menu-tree { padding: 16px; display: grid; gap: 10px; }
.menu-item-node { display: grid; gap: 8px; }
.menu-item-card { border: 1px solid var(--jv-gray-200); border-radius: 10px; background: #fff; overflow: hidden; }
.menu-item-card.is-dragging { opacity: .55; }
.menu-item-card.drop-before { box-shadow: 0 -3px 0 var(--jv-primary); }
.menu-item-card.drop-after { box-shadow: 0 3px 0 var(--jv-primary); }
.menu-item-card.drop-inside { box-shadow: inset 0 0 0 2px var(--jv-primary); background: #f8f7ff; }
.menu-item-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 14px 16px; cursor: pointer; }
.menu-item-main { display: flex; align-items: center; gap: 10px; min-width: 0; }
.menu-drag-handle { cursor: grab; color: var(--jv-gray-400); font-weight: 700; user-select: none; }
.menu-item-title { display: grid; min-width: 0; }
.menu-item-title strong { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.menu-item-meta { color: var(--jv-gray-500); font-size: .82rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.menu-item-actions { display: flex; align-items: center; gap: 8px; flex: 0 0 auto; }
.menu-item-toggle { border: 1px solid var(--jv-gray-200); background: #fff; border-radius: 8px; padding: 5px 8px; cursor: pointer; }
.menu-item-details { display: none; padding: 0 16px 16px; border-top: 1px solid var(--jv-gray-100); }
.menu-item-card.open .menu-item-details { display: block; }
.menu-children { margin-left: 28px; padding-left: 14px; border-left: 2px solid var(--jv-gray-100); display: grid; gap: 8px; min-height: 6px; }
.menu-reorder-status { margin: 0 16px 16px; color: var(--jv-gray-500); font-size: .82rem; }
@media (max-width: 900px) {
  .menu-children { margin-left: 14px; padding-left: 10px; }
  .menu-item-head { align-items: flex-start; }
}
</style>
<script>
document.querySelectorAll('[data-menu-type]').forEach((select) => {
    const sync = () => {
        const form = select.closest('form');
        form.querySelectorAll('[data-type-fields]').forEach((group) => {
            group.style.display = group.dataset.typeFields === select.value ? '' : 'none';
        });
    };

    select.addEventListener('change', sync);
    sync();
});

const menuTree = document.getElementById('menuTree');
let draggedNode = null;
let currentDrop = null;

if (menuTree) {
    menuTree.addEventListener('click', (event) => {
        const toggle = event.target.closest('[data-menu-toggle]');
        if (!toggle) return;
        event.preventDefault();
        toggle.closest('.menu-item-card').classList.toggle('open');
    });

    menuTree.addEventListener('dragstart', (event) => {
        const node = event.target.closest('.menu-item-node');
        if (!node) return;
        draggedNode = node;
        event.dataTransfer.effectAllowed = 'move';
        event.dataTransfer.setData('text/plain', node.dataset.itemId);
        node.querySelector('.menu-item-card').classList.add('is-dragging');
    });

    menuTree.addEventListener('dragover', (event) => {
        if (!draggedNode) return;
        const card = event.target.closest('.menu-item-card');
        if (!card || card.closest('.menu-item-node') === draggedNode || draggedNode.contains(card)) return;

        event.preventDefault();
        clearDropClasses();

        const rect = card.getBoundingClientRect();
        const xIndent = event.clientX - rect.left;
        const yRatio = (event.clientY - rect.top) / Math.max(rect.height, 1);
        const mode = xIndent > 58 ? 'inside' : yRatio < 0.5 ? 'before' : 'after';

        card.classList.add('drop-' + mode);
        currentDrop = { card, mode };
    });

    menuTree.addEventListener('drop', async (event) => {
        if (!draggedNode || !currentDrop) return;
        event.preventDefault();

        const targetNode = currentDrop.card.closest('.menu-item-node');
        const mode = currentDrop.mode;

        if (mode === 'inside') {
            let children = targetNode.querySelector(':scope > .menu-children');
            if (!children) {
                children = document.createElement('div');
                children.className = 'menu-children';
                targetNode.appendChild(children);
            }
            children.appendChild(draggedNode);
        } else if (mode === 'before') {
            targetNode.parentElement.insertBefore(draggedNode, targetNode);
        } else {
            targetNode.parentElement.insertBefore(draggedNode, targetNode.nextSibling);
        }

        clearDropClasses();
        await saveMenuTree();
    });

    menuTree.addEventListener('dragend', () => {
        if (draggedNode) {
            draggedNode.querySelector('.menu-item-card')?.classList.remove('is-dragging');
        }
        draggedNode = null;
        clearDropClasses();
    });
}

function clearDropClasses() {
    document.querySelectorAll('.drop-before, .drop-after, .drop-inside').forEach((el) => {
        el.classList.remove('drop-before', 'drop-after', 'drop-inside');
    });
    currentDrop = null;
}

async function saveMenuTree() {
    const payload = [];
    collectMenuItems(menuTree, null, payload);
    setMenuStatus('Saving order...');

    try {
        const response = await fetch(menuTree.dataset.reorderUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': menuTree.dataset.csrf,
            },
            body: JSON.stringify({ items: payload }),
        });

        if (!response.ok) throw new Error('Reorder failed');
        setMenuStatus('Order saved.');
    } catch (error) {
        setMenuStatus('Could not save order. Refresh the page and try again.', true);
    }
}

function collectMenuItems(container, parentId, payload) {
    Array.from(container.children)
        .filter((child) => child.classList.contains('menu-item-node'))
        .forEach((node, index) => {
            const id = Number(node.dataset.itemId);
            payload.push({ id, parent_id: parentId, position: index });

            const children = node.querySelector(':scope > .menu-children');
            if (children) collectMenuItems(children, id, payload);
        });
}

function setMenuStatus(message, error = false) {
    let status = document.querySelector('.menu-reorder-status');
    if (!status) {
        status = document.createElement('div');
        status.className = 'menu-reorder-status';
        menuTree.parentElement.appendChild(status);
    }
    status.style.color = error ? '#dc2626' : 'var(--jv-gray-500)';
    status.textContent = message;
}
</script>
@endpush
@endsection
