@extends('themes.default::layouts.admin')

@section('title', 'Edit Banner')
@section('breadcrumbs')<a href="{{ route('admin.banner-studio.index') }}">Banner Studio</a> <span class="separator">/</span> <span class="current">{{ $banner->title }}</span>@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Edit Banner</h1>
        <p class="page-subtitle">Shortcode: <code>[banner slug="{{ $banner->slug }}"]</code></p>
    </div>
    <a href="{{ route('admin.banner-studio.studio', $banner) }}" class="btn btn-primary">{{ jv_icon('wand-sparkles', '', 16) }} Open Studio</a>
</div>
<form action="{{ route('admin.banner-studio.update', $banner) }}" method="POST" class="card">
    @csrf @method('PUT')
    <div class="card-body">
        <div class="form-group"><label class="form-label">Banner Name</label><input name="title" class="form-input" value="{{ old('title', $banner->title) }}" required></div>
        <input type="hidden" name="is_active" value="0">
        <label class="toggle-switch"><input type="checkbox" name="is_active" value="1" {{ $banner->is_active ? 'checked' : '' }}><span class="toggle-slider"></span><span>Active</span></label>
    </div>
    <div class="card-footer" style="display:flex;justify-content:flex-end;gap:10px;">
        <a href="{{ route('admin.banner-studio.index') }}" class="btn btn-outline-danger">Cancel</a>
        <button class="btn btn-primary">{{ jv_icon('check-circle', '', 16) }} Save</button>
    </div>
</form>
@endsection
