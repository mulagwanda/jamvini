@php
    $item = $item ?? null;
    $type = old('type', $item->type ?? 'custom');
@endphp

<div class="form-group">
    <label class="form-label">Label</label>
    <input type="text" name="label" class="form-input" value="{{ old('label', $item->label ?? '') }}" placeholder="About" required>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
    <div class="form-group">
        <label class="form-label">Type</label>
        <select name="type" class="form-select" data-menu-type>
            <option value="custom" {{ $type === 'custom' ? 'selected' : '' }}>Custom URL</option>
            <option value="page" {{ $type === 'page' ? 'selected' : '' }}>CMS Page</option>
            <option value="route" {{ $type === 'route' ? 'selected' : '' }}>Built-in Link</option>
        </select>
    </div>
    <div class="form-group">
        <label class="form-label">Position</label>
        <input type="number" name="position" class="form-input" value="{{ old('position', $item->position ?? 0) }}" min="0" max="9999">
    </div>
</div>

<div class="form-group" data-type-fields="custom">
    <label class="form-label">URL</label>
    <input type="text" name="url" class="form-input" value="{{ old('url', $item->url ?? '') }}" placeholder="/about or https://example.com">
</div>

<div class="form-group" data-type-fields="page">
    <label class="form-label">CMS Page</label>
    <select name="page_id" class="form-select">
        <option value="">Select page</option>
        @foreach($pages as $page)
            <option value="{{ $page->id }}" {{ (string) old('page_id', $item->page_id ?? '') === (string) $page->id ? 'selected' : '' }}>{{ $page->title }} (/{{ $page->slug }})</option>
        @endforeach
    </select>
</div>

<div class="form-group" data-type-fields="route">
    <label class="form-label">Built-in Link</label>
    <select name="url" class="form-select">
        @foreach($quickLinks as $url => $label)
            <option value="{{ $url }}" {{ old('url', $item->url ?? '') === $url ? 'selected' : '' }}>{{ $label }} ({{ $url }})</option>
        @endforeach
    </select>
    <input type="hidden" name="route_name" value="">
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
    <div class="form-group">
        <label class="form-label">Parent</label>
        <select name="parent_id" class="form-select">
            <option value="">Top level</option>
            @foreach($parentItems as $parent)
                @if(!$item || $parent->id !== $item->id)
                    <option value="{{ $parent->id }}" {{ (string) old('parent_id', $item->parent_id ?? '') === (string) $parent->id ? 'selected' : '' }}>{{ $parent->label }}</option>
                @endif
            @endforeach
        </select>
    </div>
    <div class="form-group">
        <label class="form-label">Visibility</label>
        <select name="visibility" class="form-select">
            @foreach(['all' => 'Everyone', 'guest' => 'Guests only', 'auth' => 'Logged-in users'] as $key => $label)
                <option value="{{ $key }}" {{ old('visibility', $item->visibility ?? 'all') === $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
    <div class="form-group">
        <label class="form-label">Target</label>
        <select name="target" class="form-select">
            <option value="_self" {{ old('target', $item->target ?? '_self') === '_self' ? 'selected' : '' }}>Same tab</option>
            <option value="_blank" {{ old('target', $item->target ?? '_self') === '_blank' ? 'selected' : '' }}>New tab</option>
        </select>
    </div>
    <div class="form-group">
        <label class="form-label">Status</label>
        <label class="toggle-switch" style="margin-top: 10px;">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $item->is_active ?? true) ? 'checked' : '' }}>
            <span class="toggle-slider"></span>
            <span>Active</span>
        </label>
    </div>
</div>
