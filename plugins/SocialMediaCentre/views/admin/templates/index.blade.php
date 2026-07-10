@extends('themes.default::layouts.admin')

@section('title', 'Post Templates')
@section('breadcrumbs')<a href="{{ route('admin.social.index') }}">Social Centre</a> <span class="separator">/</span> <span class="current">Templates</span>@endsection

@push('styles')
<style>
.template-toolbar { display:flex; justify-content:space-between; gap:12px; align-items:end; flex-wrap:wrap; margin-bottom:18px; }
.template-filters { display:flex; gap:10px; flex-wrap:wrap; align-items:end; }
.template-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:14px; }
.template-card { border:1px solid var(--jv-gray-200); background:#fff; border-radius:12px; padding:15px; display:grid; gap:10px; min-height:250px; }
.template-card h3 { margin:0; font-size:1.02rem; color:var(--jv-gray-900); }
.template-desc { color:var(--jv-gray-500); font-size:.84rem; line-height:1.45; min-height:36px; }
.template-caption { border:1px solid var(--jv-gray-100); background:#f8fafc; border-radius:10px; padding:10px; color:var(--jv-gray-700); font-size:.82rem; line-height:1.45; white-space:pre-wrap; max-height:118px; overflow:hidden; }
.template-meta { display:flex; gap:6px; flex-wrap:wrap; }
.template-chip { border:1px solid var(--jv-gray-200); border-radius:999px; padding:4px 8px; color:var(--jv-gray-600); background:#fff; font-size:.7rem; font-weight:800; }
.template-actions { display:flex; gap:8px; flex-wrap:wrap; margin-top:auto; }
@media (max-width:700px) { .template-filters { width:100%; } .template-filters .form-group { width:100%; } }
</style>
@endpush

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Post Templates</h1>
        <p class="page-subtitle">Reusable content recipes for hosting promos, announcements, education, and customer care.</p>
    </div>
    <a href="{{ route('admin.social.templates.create') }}" class="btn btn-primary">New Template</a>
</div>

<div class="template-toolbar">
    <form method="GET" class="template-filters">
        <div class="form-group" style="margin:0;">
            <label class="form-label">Category</label>
            <select name="category" class="form-select">
                <option value="">All categories</option>
                @foreach($categories as $value => $label)
                    <option value="{{ $value }}" {{ request('category') === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group" style="margin:0;">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">All statuses</option>
                @foreach(['active','draft','archived'] as $status)
                    <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
        </div>
        <button class="btn btn-outline-primary">Filter</button>
    </form>
    <a href="{{ route('admin.social.posts.create') }}" class="btn btn-outline-primary">Blank Post</a>
</div>

<div class="template-grid">
    @forelse($templates as $template)
        <div class="template-card">
            <div>
                <div class="template-meta" style="margin-bottom:8px;">
                    <span class="template-chip">{{ $categories[$template->category] ?? ucfirst($template->category) }}</span>
                    <span class="template-chip">{{ ucfirst($template->status) }}</span>
                    @if($template->is_system)<span class="template-chip">Starter</span>@endif
                </div>
                <h3>{{ $template->name }}</h3>
                <div class="template-desc">{{ $template->description ?: 'No description yet.' }}</div>
            </div>

            <div class="template-caption">{{ str($template->caption_template)->limit(280) }}</div>

            <div class="template-meta">
                @forelse($template->platforms ?? [] as $platform)
                    <span class="template-chip">{{ $platforms[$platform] ?? $platform }}</span>
                @empty
                    <span class="template-chip">No platforms</span>
                @endforelse
            </div>

            <div class="template-actions">
                <a href="{{ route('admin.social.templates.use', $template) }}" class="btn btn-sm btn-primary">Use Template</a>
                <a href="{{ route('admin.social.templates.edit', $template) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                <form action="{{ route('admin.social.templates.destroy', $template) }}" method="POST" data-confirm="Delete this template?">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                </form>
            </div>
        </div>
    @empty
        <div class="dash-card" style="grid-column:1/-1;text-align:center;color:var(--jv-gray-500);padding:32px;">No post templates found.</div>
    @endforelse
</div>

<div style="margin-top:16px;">{{ $templates->appends(request()->query())->links() }}</div>
@endsection
