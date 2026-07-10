@extends('themes.default::layouts.admin')

@section('title', 'Edit Page')
@section('breadcrumbs')<a href="{{ route('admin.cms.pages.index') }}">Pages</a> <span class="separator">/</span> <span class="current">Edit</span>@endsection

@section('content')
@php
    $jvBuilderActive = \App\Models\Plugin::where('slug', 'jv-builder')->where('is_active', true)->exists()
        && \Illuminate\Support\Facades\Route::has('admin.jv-builder.pages.edit');
@endphp
<div class="page-header">
    <div>
        <h1 class="page-title">Edit: {{ $page->title }}</h1>
        <p class="page-subtitle">Update content, permalink, image, and publishing settings.</p>
    </div>
    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
        <a href="{{ route('admin.cms.pages.preview', $page) }}" target="_blank" class="btn btn-outline-primary">Preview</a>
        @if($jvBuilderActive)
            <a href="{{ route('admin.jv-builder.pages.edit', $page) }}" class="btn btn-primary">Open JV Builder</a>
        @endif
    </div>
</div>

<form action="{{ route('admin.cms.pages.update', $page) }}" method="POST" id="cmsEditorForm">
    @csrf @method('PUT')
    @include('plugins.CMS::admin.partials.editor-form', [
        'type' => 'page',
        'item' => $page,
        'media' => $media,
        'previewUrl' => route('admin.cms.pages.preview', $page),
    ])

    <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 16px;">
        <a href="{{ route('admin.cms.pages.index') }}" class="btn btn-outline-danger">Cancel</a>
        <button type="submit" class="btn btn-primary btn-lg">Save Page</button>
    </div>
</form>
@endsection
