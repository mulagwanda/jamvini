@extends('themes.default::layouts.admin')

@section('title', 'Custom Fields')
@section('breadcrumbs')<span class="current">Custom Fields</span>@endsection

@section('content')
<style>
.cf-head { display:flex; justify-content:space-between; gap:16px; align-items:flex-start; margin-bottom:18px; }
.cf-tabs { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:16px; }
.cf-tabs a { display:inline-flex; align-items:center; gap:6px; padding:8px 12px; border:1px solid var(--jv-gray-200); border-radius:8px; background:#fff; color:var(--jv-gray-600); text-decoration:none; font-weight:800; font-size:.85rem; }
.cf-tabs a.active { border-color:var(--jv-primary); color:var(--jv-primary); background:var(--jv-primary-bg); }
.cf-flag { display:inline-flex; align-items:center; padding:3px 8px; border-radius:999px; font-size:.72rem; font-weight:800; background:#f1f5f9; color:#475569; }
</style>

<div class="cf-head">
    <div>
        <h1 class="page-title">Custom Fields</h1>
        <p class="page-subtitle">Collect extra information on registration, client profiles, and future module forms.</p>
    </div>
    <a href="{{ route('admin.custom-fields.create', ['entity_type' => $entity]) }}" class="btn btn-primary">{{ jv_icon('plus', '', 16) }} Add Field</a>
</div>

<div class="cf-tabs">
    @foreach($entities as $key => $label)
        <a href="{{ route('admin.custom-fields.index', ['entity_type' => $key]) }}" class="{{ $entity === $key ? 'active' : '' }}">
            {{ jv_icon($key === 'client' ? 'users' : ($key === 'domain' ? 'globe' : ($key === 'ticket' ? 'headphones' : 'brackets')), '', 15) }} {{ $label }}
        </a>
    @endforeach
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">{{ jv_icon('brackets', '', 18) }} {{ $entities[$entity] ?? 'Fields' }}</h3>
        <span class="badge badge-gray">{{ $fields->total() }} field{{ $fields->total() === 1 ? '' : 's' }}</span>
    </div>
    <div class="card-body" style="padding:0;">
        @if($fields->count())
            <table class="table" style="margin:0;">
                <thead><tr><th>Field</th><th>Type</th><th>Visibility</th><th>Required</th><th>Status</th><th style="text-align:right;">Actions</th></tr></thead>
                <tbody>
                    @foreach($fields as $field)
                        <tr>
                            <td>
                                <strong>{{ $field->label }}</strong><br>
                                <small style="color:var(--jv-gray-500);">{{ $field->name }} · sort {{ $field->sort_order }}</small>
                            </td>
                            <td>{{ $types[$field->type] ?? ucfirst($field->type) }}</td>
                            <td style="display:flex;gap:5px;flex-wrap:wrap;">
                                @if($field->show_on_registration)<span class="cf-flag">Registration</span>@endif
                                @if($field->show_on_admin_profile)<span class="cf-flag">Admin Profile</span>@endif
                                @if($field->is_public)<span class="cf-flag">Public</span>@endif
                            </td>
                            <td>{{ $field->is_required ? 'Yes' : 'No' }}</td>
                            <td><span class="pill pill-{{ $field->is_active ? 'ok' : 'mute' }}">{{ $field->is_active ? 'Active' : 'Inactive' }}</span></td>
                            <td style="text-align:right;">
                                <a href="{{ route('admin.custom-fields.edit', $field) }}" class="btn btn-sm btn-outline-primary">{{ jv_icon('pencil', '', 15) }} Edit</a>
                                <form action="{{ route('admin.custom-fields.destroy', $field) }}" method="POST" style="display:inline;" data-confirm="Delete this custom field and its saved values?" data-danger="true">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">{{ jv_icon('trash-2', '', 15) }}</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty-state">
                <div class="empty-state-icon">{{ jv_icon('brackets', '', 42) }}</div>
                <div class="empty-state-title">No custom fields yet</div>
                <div class="empty-state-desc">Create fields for registration, onboarding, client segmentation, or internal operations.</div>
                <a href="{{ route('admin.custom-fields.create', ['entity_type' => $entity]) }}" class="btn btn-primary">{{ jv_icon('plus', '', 16) }} Add First Field</a>
            </div>
        @endif
    </div>
</div>

{{ $fields->links() }}
@endsection
