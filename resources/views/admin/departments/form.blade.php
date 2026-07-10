@extends('themes.default::layouts.admin')

@php $isEdit = $department->exists; @endphp

@section('title', $isEdit ? 'Edit Department' : 'Add Department')
@section('breadcrumbs')<a href="{{ route('admin.admin-users.index') }}">Admin Users</a> <span class="separator">/</span> <a href="{{ route('admin.departments.index') }}">Departments</a> <span class="separator">/</span> <span class="current">{{ $isEdit ? 'Edit' : 'Add' }}</span>@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">{{ $isEdit ? 'Edit Department' : 'Add Department' }}</h1>
        <p class="page-subtitle">Create staff groups for billing, support, technical work, and management</p>
    </div>
    <a href="{{ route('admin.departments.index') }}" class="btn btn-outline-primary">Back to Departments</a>
</div>

<form action="{{ $isEdit ? route('admin.departments.update', $department) : route('admin.departments.store') }}" method="POST">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div style="display:grid;grid-template-columns:minmax(0,1fr) 320px;gap:1.5rem;align-items:start;">
        <div class="dash-card">
            <div class="dash-card-head"><h3>Department Details</h3></div>
            <div class="form-group">
                <label class="form-label">Name *</label>
                <input type="text" name="name" class="form-input" value="{{ old('name', $department->name) }}" required placeholder="Billing">
            </div>
            <div class="form-group">
                <label class="form-label">Slug</label>
                <input type="text" name="slug" class="form-input" value="{{ old('slug', $department->slug) }}" placeholder="Auto from name">
            </div>
            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-textarea" rows="5" placeholder="What this department handles...">{{ old('description', $department->description) }}</textarea>
            </div>
        </div>

        <aside class="dash-card">
            <div class="dash-card-head"><h3>Status</h3></div>
            <input type="hidden" name="is_active" value="0">
            <label class="toggle-switch">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $department->is_active ?? true) ? 'checked' : '' }}>
                <span class="toggle-slider"></span>
                <span>Active department</span>
            </label>
            <small style="display:block;color:var(--jv-gray-500);margin-top:12px;">Inactive departments stay in history but are hidden from new admin assignments.</small>
        </aside>
    </div>

    <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:1.5rem;">
        <a href="{{ route('admin.departments.index') }}" class="btn btn-outline-danger">Cancel</a>
        <button class="btn btn-primary btn-lg">{{ $isEdit ? 'Update Department' : 'Create Department' }}</button>
    </div>
</form>
@endsection
