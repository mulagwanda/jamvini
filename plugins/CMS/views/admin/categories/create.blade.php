@extends('themes.default::layouts.admin')

@section('title', 'New Category')
@section('breadcrumbs')<a href="{{ route('admin.cms.categories.index') }}">Categories</a> <span class="separator">/</span> <span class="current">New</span>@endsection

@section('content')
<div class="page-header"><h1 class="page-title">New Category</h1></div>
<form action="{{ route('admin.cms.categories.store') }}" method="POST">
    @csrf
    <div class="card">
        <div class="card-body">
            <div class="form-group">
                <label class="form-label" for="name">Name</label>
                <input type="text" id="name" name="name" class="form-input" value="{{ old('name') }}" required>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label" for="type">Type</label>
                    <select id="type" name="type" class="form-select">
                        <option value="post">Post</option>
                        <option value="page">Page</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="parent_id">Parent</label>
                    <select id="parent_id" name="parent_id" class="form-select">
                        <option value="">None</option>
                        @foreach($parents as $p)
                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label" for="description">Description</label>
                <textarea id="description" name="description" class="form-textarea" rows="2">{{ old('description') }}</textarea>
            </div>
        </div>
    </div>
    <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 16px;">
        <a href="{{ route('admin.cms.categories.index') }}" class="btn btn-outline-danger">Cancel</a>
        <button type="submit" class="btn btn-primary btn-lg">✅ Create Category</button>
    </div>
</form>
@endsection