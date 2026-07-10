@extends('themes.default::layouts.admin')

@section('title', 'Menus')
@section('breadcrumbs')<span class="current">Menus</span>@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Menus</h1>
        <p class="page-subtitle">Manage public website navigation and footer links.</p>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1.2fr .8fr; gap: 20px;">
    <div class="dash-card" style="padding: 0; overflow: hidden;">
        @if($menus->count())
            <table class="table" style="margin: 0;">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Location</th>
                        <th>Items</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($menus as $menu)
                        <tr>
                            <td><strong>{{ $menu->name }}</strong><br><small style="color: var(--jv-gray-500);">{{ $menu->slug }}</small></td>
                            <td>{{ $locations[$menu->location] ?? ($menu->location ?: 'Unassigned') }}</td>
                            <td>{{ $menu->items_count }}</td>
                            <td><span class="pill pill-{{ $menu->is_active ? 'ok' : 'mute' }}">{{ $menu->is_active ? 'Active' : 'Inactive' }}</span></td>
                            <td class="text-center">
                                <div class="btn-group" style="gap: 4px; justify-content: center;">
                                    <a href="{{ route('admin.menus.edit', $menu) }}" class="btn btn-sm btn-primary">Edit</a>
                                    <form action="{{ route('admin.menus.destroy', $menu) }}" method="POST" onsubmit="return confirm('Delete this menu?')" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty-state" style="padding: 60px;">
                <div class="empty-state-icon">🧭</div>
                <div class="empty-state-title">No menus yet</div>
                <p class="empty-state-desc">Create a menu and assign it to a theme location.</p>
            </div>
        @endif
    </div>

    <form action="{{ route('admin.menus.store') }}" method="POST" class="card">
        @csrf
        <div class="card-header"><h3 class="card-title">New Menu</h3></div>
        <div class="card-body">
            <div class="form-group">
                <label class="form-label" for="name">Menu Name</label>
                <input type="text" id="name" name="name" class="form-input" value="{{ old('name') }}" placeholder="Primary Menu" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="location">Location</label>
                <select id="location" name="location" class="form-select">
                    <option value="">Unassigned</option>
                    @foreach($locations as $key => $label)
                        <option value="{{ $key }}" {{ old('location') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <label class="toggle-switch">
                <input type="checkbox" name="is_active" value="1" checked>
                <span class="toggle-slider"></span>
                <span>Active</span>
            </label>
        </div>
        <div style="display:flex; justify-content:flex-end; padding: 0 24px 24px;">
            <button class="btn btn-primary">Create Menu</button>
        </div>
    </form>
</div>
@endsection
