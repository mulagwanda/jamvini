@extends('themes.default::layouts.admin')

@section('title', 'Edit Post')
@section('breadcrumbs')<a href="{{ route('admin.cms.posts.index') }}">Posts</a> <span class="separator">/</span> <span class="current">Edit</span>@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Edit: {{ $post->title }}</h1>
        <p class="page-subtitle">Update article content, publishing settings, categories, and image.</p>
    </div>
    <a href="{{ route('admin.cms.posts.preview', $post) }}" target="_blank" class="btn btn-outline-primary">Preview</a>
</div>

<form action="{{ route('admin.cms.posts.update', $post) }}" method="POST" id="cmsEditorForm">
    @csrf @method('PUT')
    @include('plugins.CMS::admin.partials.editor-form', [
        'type' => 'post',
        'item' => $post,
        'categories' => $categories,
        'media' => $media,
        'previewUrl' => route('admin.cms.posts.preview', $post),
    ])

    <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 16px;">
        <a href="{{ route('admin.cms.posts.index') }}" class="btn btn-outline-danger">Cancel</a>
        <button type="submit" class="btn btn-primary btn-lg">Save Post</button>
    </div>
</form>
@endsection
