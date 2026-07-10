@extends('themes.default::layouts.admin')

@section('title', 'New Banner')
@section('breadcrumbs')<a href="{{ route('admin.banner-studio.index') }}">Banner Studio</a> <span class="separator">/</span> <span class="current">New</span>@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">New Banner</h1>
        <p class="page-subtitle">Start with a banner name. The studio will create starter layers for you.</p>
    </div>
</div>
<form action="{{ route('admin.banner-studio.store') }}" method="POST" class="card">
    @csrf
    <div class="card-body">
        <div class="form-group"><label class="form-label">Banner Name</label><input name="title" class="form-input" value="{{ old('title', 'Homepage Hero Banner') }}" required></div>
        <input type="hidden" name="is_active" value="0">
        <label class="toggle-switch"><input type="checkbox" name="is_active" value="1" checked><span class="toggle-slider"></span><span>Active</span></label>
    </div>
    <div class="card-footer" style="display:flex;justify-content:flex-end;gap:10px;">
        <a href="{{ route('admin.banner-studio.index') }}" class="btn btn-outline-danger">Cancel</a>
        <button class="btn btn-primary">{{ jv_icon('wand-sparkles', '', 16) }} Create & Open Studio</button>
    </div>
</form>
@endsection
