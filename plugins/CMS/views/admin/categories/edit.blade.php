@extends('themes.default::layouts.admin')

@section('title', 'Edit Category')
@section('breadcrumbs')<a href="{{ route('admin.cms.categories.index') }}">Categories</a> <span class="separator">/</span> <span class="current">Edit</span>@endsection

@section('content')
<div class="page-header"><h1 class="page-title">Edit: {{ $category->name }}</h1></div>
<form action="{{ route('admin.cms.categories.update', $category) }}" method="POST">
    @csrf @method('PUT')
    <div class="card">
        <div class="card-body">
            <div class="form-group">
                <label class="form-label" for="name">Name</label>
                <input type="text" id="name" name="name" class="form-input" value="{{ old('name', $category->name) }}" required>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label" for="type">Type</label>
                    <select id="type" name="type" class="form-select">
                        <option value="post" {{ $category->type === 'post' ? 'selected' : '' }}>Post</option>
                        <option value="page" {{ $category->type === 'page' ? 'selected' : '' }}>Page</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="parent_id">Parent</label>
                    <select id="parent_id" name="parent_id" class="form-select">
                        <option value="">None</option>
                        @foreach($parents as $p)
                        <option value="{{ $p->id }}" {{ $category->parent_id === $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label" for="description">Description</label>
                <textarea id="description" name="description" class="form-textarea" rows="2">{{ old('description', $category->description) }}</textarea>
            </div>
        </div>
    </div>
    <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 16px;">
        <a href="{{ route('admin.cms.categories.index') }}" class="btn btn-outline-danger">Cancel</a>
        <button type="submit" class="btn btn-primary btn-lg">💾 Update Category</button>
    </div>
</form>
@endsection