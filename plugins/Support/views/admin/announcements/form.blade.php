@extends('themes.default::layouts.admin')

@section('title', $announcement->exists ? 'Edit Announcement' : 'New Announcement')
@section('breadcrumbs')<a href="{{ route('admin.support.announcements.index') }}">Announcements</a> <span class="separator">/</span> <span class="current">{{ $announcement->exists ? 'Edit' : 'New' }}</span>@endsection

@section('content')
<div class="page-header"><h1 class="page-title">{{ $announcement->exists ? 'Edit Announcement' : 'New Announcement' }}</h1></div>

<form action="{{ $announcement->exists ? route('admin.support.announcements.update', $announcement) : route('admin.support.announcements.store') }}" method="POST">
    @csrf
    @if($announcement->exists) @method('PUT') @endif
    <div class="dash-card">
        <div class="form-group"><label class="form-label">Title</label><input type="text" name="title" class="form-input" value="{{ old('title', $announcement->title) }}" required></div>
        <div style="display:grid;grid-template-columns:1fr 220px;gap:16px;">
            <div class="form-group"><label class="form-label">Summary</label><input type="text" name="summary" class="form-input" value="{{ old('summary', $announcement->summary) }}"></div>
            <div class="form-group"><label class="form-label">Type</label><select name="type" class="form-select">@foreach(['news','maintenance','incident','release'] as $type)<option value="{{ $type }}" {{ old('type', $announcement->type ?: 'news') === $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>@endforeach</select></div>
        </div>
        <div class="form-group"><label class="form-label">Content</label><textarea name="content" class="form-textarea" rows="12" required>{{ old('content', $announcement->content) }}</textarea></div>
        <div style="display:flex;gap:18px;align-items:center;flex-wrap:wrap;">
            <label class="toggle-switch"><input type="checkbox" name="is_published" value="1" {{ old('is_published', $announcement->is_published) ? 'checked' : '' }}><span class="toggle-slider"></span><span>Published</span></label>
            <div class="form-group" style="margin:0;"><label class="form-label">Publish Time</label><input type="datetime-local" name="published_at" class="form-input" value="{{ old('published_at', $announcement->published_at?->format('Y-m-d\\TH:i')) }}"></div>
        </div>
    </div>
    <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:1rem;">
        <a href="{{ route('admin.support.announcements.index') }}" class="btn btn-outline-danger">Cancel</a>
        <button class="btn btn-primary">{{ $announcement->exists ? 'Update' : 'Publish' }}</button>
    </div>
</form>
@endsection
