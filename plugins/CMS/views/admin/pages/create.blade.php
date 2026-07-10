@extends('themes.default::layouts.admin')

@section('title', 'New Page')
@section('breadcrumbs')<a href="{{ route('admin.cms.pages.index') }}">Pages</a> <span class="separator">/</span> <span class="current">New</span>@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">New Page</h1>
        <p class="page-subtitle">Create a public page with rich content, permalink, preview metadata, and optional builder support.</p>
    </div>
</div>

<form action="{{ route('admin.cms.pages.store') }}" method="POST" id="cmsEditorForm">
    @csrf
    @include('plugins.CMS::admin.partials.editor-form', [
        'type' => 'page',
        'item' => null,
        'media' => $media,
    ])

    <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 16px;">
        <a href="{{ route('admin.cms.pages.index') }}" class="btn btn-outline-danger">Cancel</a>
        <button type="submit" class="btn btn-primary btn-lg">Create Page</button>
    </div>
</form>
@endsection
