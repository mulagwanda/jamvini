@extends('themes.default::layouts.admin')

@section('title', 'JV Builder')
@section('breadcrumbs')<span class="current">JV Builder</span>@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">JV Builder</h1>
        <p class="page-subtitle">Choose a CMS page and design it visually.</p>
    </div>
    <a href="{{ route('admin.cms.pages.create') }}" class="btn btn-primary">New Page</a>
</div>

<div class="dash-card" style="padding: 0; overflow: hidden;">
    @if($pages->count() > 0)
        <table class="table" style="margin: 0;">
            <thead>
                <tr>
                    <th>Page</th>
                    <th>Permalink</th>
                    <th>Status</th>
                    <th>Updated</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pages as $page)
                    <tr>
                        <td>
                            <strong>{{ $page->title }}</strong>
                            @if(!empty($page->blocks))
                                <div style="font-size: .8rem; color: var(--jv-gray-500); margin-top: 2px;">Builder layout saved</div>
                            @endif
                        </td>
                        <td><code>/{{ $page->slug }}</code></td>
                        <td><span class="pill pill-{{ $page->status === 'published' ? 'ok' : 'mute' }}">{{ ucfirst($page->status) }}</span></td>
                        <td>{{ optional($page->updated_at)->format('M d, Y') }}</td>
                        <td class="text-center">
                            <div class="btn-group" style="gap:4px;justify-content:center;">
                                <a href="{{ route('admin.jv-builder.pages.edit', $page) }}" class="btn btn-sm btn-primary">Open Builder</a>
                                <a href="{{ route('admin.cms.pages.edit', $page) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                <a href="{{ route('admin.cms.pages.preview', $page) }}" target="_blank" class="btn btn-sm btn-outline-primary">Preview</a>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div style="padding: 16px 24px; display: flex; justify-content: space-between; align-items: center;">
            <small style="color: var(--jv-gray-500);">Showing {{ $pages->firstItem() ?? 0 }}-{{ $pages->lastItem() ?? 0 }} of {{ $pages->total() }}</small>
            {{ $pages->links() }}
        </div>
    @else
        <div class="empty-state" style="padding: 60px;">
            <div class="empty-state-icon">JV</div>
            <div class="empty-state-title">No pages yet</div>
            <p class="empty-state-desc">Create a CMS page first, then open it in JV Builder.</p>
            <a href="{{ route('admin.cms.pages.create') }}" class="btn btn-primary">Create First Page</a>
        </div>
    @endif
</div>
@endsection
