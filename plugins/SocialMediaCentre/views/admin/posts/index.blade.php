@extends('themes.default::layouts.admin')

@section('title', 'Social Posts')
@section('breadcrumbs')<a href="{{ route('admin.social.index') }}">Social Centre</a> <span class="separator">/</span> <span class="current">Posts</span>@endsection

@section('content')
<div class="page-header">
    <div><h1 class="page-title">Posts</h1><p class="page-subtitle">Draft, schedule, and track social content.</p></div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
        <a href="{{ route('admin.social.calendar') }}" class="btn btn-outline-primary">Calendar</a>
        <a href="{{ route('admin.social.templates.index') }}" class="btn btn-outline-primary">Templates</a>
        <a href="{{ route('admin.social.settings') }}" class="btn btn-outline-primary">Settings</a>
        <a href="{{ route('admin.social.posts.create') }}" class="btn btn-primary">New Post</a>
    </div>
</div>

<form method="GET" class="dash-card" style="margin-bottom:1rem;padding:14px;display:flex;gap:10px;flex-wrap:wrap;align-items:end;">
    <div class="form-group" style="margin:0;"><label class="form-label">Status</label><select name="status" class="form-select"><option value="">All</option>@foreach(['draft','ready','scheduled','published','failed'] as $status)<option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>@endforeach</select></div>
    <div class="form-group" style="margin:0;"><label class="form-label">Campaign</label><select name="campaign_id" class="form-select"><option value="">All</option>@foreach($campaigns as $campaign)<option value="{{ $campaign->id }}" {{ (string) request('campaign_id') === (string) $campaign->id ? 'selected' : '' }}>{{ $campaign->name }}</option>@endforeach</select></div>
    <button class="btn btn-outline-primary">Filter</button>
</form>

<div class="dash-card">
    <table class="data-table">
        <thead><tr><th>Post</th><th>Campaign</th><th>Platforms</th><th>Status</th><th>Schedule</th><th></th></tr></thead>
        <tbody>
            @forelse($posts as $post)
                <tr>
                    <td><a href="{{ route('admin.social.posts.show', $post) }}"><strong>{{ $post->title }}</strong></a><br><small>{{ str($post->caption)->limit(90) }}</small></td>
                    <td>{{ $post->campaign?->name ?? '-' }}</td>
                    <td>{{ implode(', ', $post->platforms ?? []) ?: '-' }}</td>
                    <td><span class="pill pill-{{ $post->status === 'published' ? 'ok' : ($post->status === 'scheduled' ? 'info' : ($post->status === 'failed' ? 'danger' : 'mute')) }}">{{ ucfirst($post->status) }}</span></td>
                    <td>{{ $post->scheduled_at?->format('M d, Y H:i') ?? '-' }}</td>
                    <td style="text-align:right;"><a href="{{ route('admin.social.posts.edit', $post) }}" class="btn btn-sm btn-outline-primary">Edit</a></td>
                </tr>
            @empty
                <tr><td colspan="6" style="padding:24px;text-align:center;color:var(--jv-gray-500);">No social posts yet.</td></tr>
            @endforelse
        </tbody>
    </table>
    {{ $posts->links() }}
</div>
@endsection
