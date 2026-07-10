@extends('themes.default::layouts.admin')

@section('title', 'Pages')
@section('breadcrumbs')<span class="current">Pages</span>@endsection

@section('content')
@php
    $jvBuilderActive = \App\Models\Plugin::where('slug', 'jv-builder')->where('is_active', true)->exists()
        && \Illuminate\Support\Facades\Route::has('admin.jv-builder.pages.edit');
@endphp
<div class="page-header">
    <div>
        <h1 class="page-title">Pages</h1>
        <p class="page-subtitle">Manage static pages for your website</p>
    </div>
    <a href="{{ route('admin.cms.pages.create') }}" class="btn btn-primary">➕ New Page</a>
</div>

<div class="dash-card" style="padding: 0; overflow: hidden;">
    @if($pages->count() > 0)
    <table class="table" style="margin: 0;">
        <thead><tr><th>Title</th><th>Slug</th><th>Status</th><th>Created</th><th class="text-center">Actions</th></tr></thead>
        <tbody>
            @foreach($pages as $page)
            <tr>
                <td><strong>{{ $page->title }}</strong></td>
                <td><code>/{{ $page->slug }}</code></td>
                <td><span class="pill pill-{{ $page->status === 'published' ? 'ok' : 'mute' }}">{{ ucfirst($page->status) }}</span></td>
                <td>{{ $page->created_at->format('M d, Y') }}</td>
                <td class="text-center">
                    <div class="btn-group" style="gap:4px;justify-content:center;">
                        <a href="{{ route('admin.cms.pages.preview', $page) }}" target="_blank" class="btn btn-sm btn-outline-primary">Preview</a>
                        @if($jvBuilderActive)
                            <a href="{{ route('admin.jv-builder.pages.edit', $page) }}" class="btn btn-sm btn-primary">JV Builder</a>
                        @endif
                        <a href="{{ route('admin.cms.pages.edit', $page) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                        <form action="{{ route('admin.cms.pages.destroy', $page) }}" method="POST" style="display:inline;" onsubmit="return confirm('Delete this page?')">@csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div style="padding: 16px 24px; display: flex; justify-content: space-between; align-items: center;">
        <small style="color: var(--jv-gray-500);">Showing {{ $pages->firstItem() ?? 0 }}–{{ $pages->lastItem() ?? 0 }} of {{ $pages->total() }}</small>
        {{ $pages->links() }}
    </div>
    @else
    <div class="empty-state" style="padding: 60px;">
        <div class="empty-state-icon">📄</div>
        <div class="empty-state-title">No pages yet</div>
        <p class="empty-state-desc">Create your first page for your website.</p>
        <a href="{{ route('admin.cms.pages.create') }}" class="btn btn-primary">Create First Page</a>
    </div>
    @endif
</div>
@endsection
