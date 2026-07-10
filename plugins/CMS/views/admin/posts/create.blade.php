@extends('themes.default::layouts.admin')

@section('title', 'New Post')
@section('breadcrumbs')<a href="{{ route('admin.cms.posts.index') }}">Posts</a> <span class="separator">/</span> <span class="current">New</span>@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">New Post</h1>
        <p class="page-subtitle">Write an article with rich formatting, categories, featured image, and publishing controls.</p>
    </div>
</div>

<form action="{{ route('admin.cms.posts.store') }}" method="POST" id="cmsEditorForm">
    @csrf
    @include('plugins.CMS::admin.partials.editor-form', [
        'type' => 'post',
        'item' => null,
        'categories' => $categories,
        'media' => $media,
    ])

    <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 16px;">
        <a href="{{ route('admin.cms.posts.index') }}" class="btn btn-outline-danger">Cancel</a>
        <button type="submit" class="btn btn-primary btn-lg">Create Post</button>
    </div>
</form>
@endsection
