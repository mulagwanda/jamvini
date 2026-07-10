@extends('themes.default::layouts.admin')

@section('title', $field->exists ? 'Edit Custom Field' : 'Add Custom Field')
@section('breadcrumbs')<a href="{{ route('admin.custom-fields.index') }}">Custom Fields</a> <span class="separator">/</span> <span class="current">{{ $field->exists ? 'Edit' : 'Add' }}</span>@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">{{ $field->exists ? 'Edit Custom Field' : 'Add Custom Field' }}</h1>
        <p class="page-subtitle">Define reusable extra information JamVini should collect and display.</p>
    </div>
    <a href="{{ route('admin.custom-fields.index', ['entity_type' => old('entity_type', $field->entity_type ?? 'client')]) }}" class="btn btn-outline-primary">{{ jv_icon('arrow-left', '', 16) }} Back</a>
</div>

<form method="POST" action="{{ $field->exists ? route('admin.custom-fields.update', $field) : route('admin.custom-fields.store') }}">
    @csrf
    @if($field->exists) @method('PUT') @endif

    <div style="display:grid;grid-template-columns:minmax(0,1fr) 320px;gap:1.5rem;align-items:start;">
        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ jv_icon('brackets', '', 18) }} Field Definition</h3></div>
            <div class="card-body">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                    <div class="form-group">
                        <label class="form-label">Applies To</label>
                        <select name="entity_type" class="form-select" required>
                            @foreach($entities as $value => $label)
                                <option value="{{ $value }}" {{ old('entity_type', $field->entity_type) === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select" required>
                            @foreach($types as $value => $label)
                                <option value="{{ $value }}" {{ old('type', $field->type) === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 220px;gap:16px;">
                    <div class="form-group">
                        <label class="form-label">Label <span class="required">*</span></label>
                        <input name="label" class="form-input" value="{{ old('label', $field->label) }}" required placeholder="Company Registration Number">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Machine Name</label>
                        <input name="name" class="form-input" value="{{ old('name', $field->name) }}" placeholder="Auto from label">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Options</label>
                    <textarea name="options" class="form-textarea" rows="5" placeholder="One option per line for select, radio, or checkbox fields">{{ old('options', $field->options) }}</textarea>
                    <div class="form-hint">Used only by select, radio, and checkbox fields.</div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                    <div class="form-group">
                        <label class="form-label">Placeholder</label>
                        <input name="placeholder" class="form-input" value="{{ old('placeholder', $field->placeholder) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Default Value</label>
                        <input name="default_value" class="form-input" value="{{ old('default_value', $field->default_value) }}">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Help Text</label>
                    <textarea name="help_text" class="form-textarea" rows="3">{{ old('help_text', $field->help_text) }}</textarea>
                </div>
            </div>
        </div>

        <aside style="display:grid;gap:1rem;">
            <div class="card">
                <div class="card-header"><h3 class="card-title">{{ jv_icon('settings', '', 18) }} Behavior</h3></div>
                <div class="card-body" style="display:grid;gap:12px;">
                    <div class="form-group">
                        <label class="form-label">Sort Order</label>
                        <input type="number" name="sort_order" class="form-input" value="{{ old('sort_order', $field->sort_order ?? 0) }}" min="0">
                    </div>
                    @foreach([
                        'is_required' => 'Required',
                        'is_public' => 'Visible to clients',
                        'show_on_registration' => 'Show on registration',
                        'show_on_admin_profile' => 'Show on admin profile',
                        'is_active' => 'Active',
                    ] as $name => $label)
                        <input type="hidden" name="{{ $name }}" value="0">
                        <label class="toggle-switch">
                            <input type="checkbox" name="{{ $name }}" value="1" {{ old($name, $field->{$name} ?? false) ? 'checked' : '' }}>
                            <span class="toggle-slider"></span><span>{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div style="display:flex;gap:10px;justify-content:flex-end;">
                <a href="{{ route('admin.custom-fields.index', ['entity_type' => old('entity_type', $field->entity_type ?? 'client')]) }}" class="btn btn-outline-danger">Cancel</a>
                <button class="btn btn-primary">{{ jv_icon('check-circle', '', 16) }} Save Field</button>
            </div>
        </aside>
    </div>
</form>
@endsection
