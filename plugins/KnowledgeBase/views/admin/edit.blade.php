@extends('themes.default::layouts.admin')

@section('title', 'Edit Article')
@section('breadcrumbs')
    <a href="{{ route('admin.kb.index') }}">Knowledge Base</a>
    <span class="separator">/</span>
    <span class="current">Edit</span>
@endsection

@section('content')
<div class="page-header">
    <h1 class="page-title">Edit Article</h1>
</div>

<form action="{{ route('admin.kb.update', $article) }}" method="POST">
    @csrf @method('PUT')
    <div class="card">
        <div class="card-body">
            <div class="form-group">
                <label class="form-label" for="title">Title</label>
                <input type="text" id="title" name="title" class="form-input" value="{{ old('title', $article->title) }}" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="category">Category</label>
                <input type="text" id="category" name="category" class="form-input" value="{{ old('category', $article->category) }}">
            </div>
            <div class="form-group">
                <label class="form-label" for="content">Content</label>
                <textarea id="content" name="content" class="form-textarea" rows="12" required>{{ old('content', $article->content) }}</textarea>
            </div>
            <div class="form-group">
                <label class="toggle-switch">
                    <input type="checkbox" name="is_published" value="1" {{ $article->is_published ? 'checked' : '' }}>
                    <span class="toggle-slider"></span>
                    <span>Published</span>
                </label>
            </div>
        </div>
    </div>
    <div style="display: flex; gap: 12px; justify-content: flex-end;">
        <a href="{{ route('admin.kb.index') }}" class="btn btn-outline-danger">Cancel</a>
        <button type="submit" class="btn btn-primary btn-lg">💾 Update Article</button>
    </div>
</form>
@endsection