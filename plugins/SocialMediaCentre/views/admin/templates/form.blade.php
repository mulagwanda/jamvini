@extends('themes.default::layouts.admin')

@section('title', $template->exists ? 'Edit Template' : 'New Template')
@section('breadcrumbs')<a href="{{ route('admin.social.templates.index') }}">Templates</a> <span class="separator">/</span> <span class="current">{{ $template->exists ? 'Edit' : 'New' }}</span>@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">{{ $template->exists ? 'Edit Template' : 'New Template' }}</h1>
        <p class="page-subtitle">Create reusable social copy with placeholders like &#123;&#123;package_name&#125;&#125; or &#123;&#123;price&#125;&#125;.</p>
    </div>
</div>

<form action="{{ $template->exists ? route('admin.social.templates.update', $template) : route('admin.social.templates.store') }}" method="POST">
    @csrf
    @if($template->exists) @method('PUT') @endif

    <div class="dash-card">
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Name</label>
                <input name="name" class="form-input" value="{{ old('name', $template->name) }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">Category</label>
                <select name="category" class="form-select" required>
                    @foreach($categories as $value => $label)
                        <option value="{{ $value }}" {{ old('category', $template->category) === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Description</label>
            <input name="description" class="form-input" value="{{ old('description', $template->description) }}" placeholder="What is this template for?">
        </div>

        <div class="form-group">
            <label class="form-label">Title Template</label>
            <input name="title_template" class="form-input" value="{{ old('title_template', $template->title_template) }}" required>
        </div>

        <div class="form-group">
            <label class="form-label">Caption Template</label>
            <textarea name="caption_template" class="form-textarea" rows="10" required>{{ old('caption_template', $template->caption_template) }}</textarea>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Hashtags</label>
                <input name="hashtags" class="form-input" value="{{ old('hashtags', implode(' ', $template->hashtags ?? [])) }}" placeholder="#hosting #domains">
            </div>
            <div class="form-group">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    @foreach(['active','draft','archived'] as $status)
                        <option value="{{ $status }}" {{ old('status', $template->status ?? 'active') === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Default Platforms</label>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:8px;">
                @foreach($platforms as $value => $label)
                    <label style="border:1px solid var(--jv-gray-200);border-radius:10px;padding:10px;display:flex;gap:8px;align-items:center;">
                        <input type="checkbox" name="platforms[]" value="{{ $value }}" {{ in_array($value, old('platforms', $template->platforms ?? []), true) ? 'checked' : '' }}>
                        <span>{{ $label }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Sort Order</label>
            <input type="number" name="sort_order" class="form-input" value="{{ old('sort_order', $template->sort_order ?? 0) }}" min="0" max="9999">
        </div>
    </div>

    <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:1rem;">
        <a href="{{ route('admin.social.templates.index') }}" class="btn btn-outline-danger">Cancel</a>
        @if($template->exists && $template->status === 'active')
            <a href="{{ route('admin.social.templates.use', $template) }}" class="btn btn-outline-primary">Use Template</a>
        @endif
        <button class="btn btn-primary">{{ $template->exists ? 'Save Template' : 'Create Template' }}</button>
    </div>
</form>
@endsection
