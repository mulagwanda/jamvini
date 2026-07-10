@extends('themes.default::layouts.admin')

@section('title', 'Social Media Centre')
@section('breadcrumbs')<span class="current">Social Media Centre</span>@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Social Media Centre</h1>
        <p class="page-subtitle">Plan campaigns, draft posts, schedule content, and track manual publishing.</p>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
        <a href="{{ route('admin.social.posts.create') }}" class="btn btn-primary">New Post</a>
        <a href="{{ route('admin.social.calendar') }}" class="btn btn-outline-primary">Calendar</a>
        <a href="{{ route('admin.social.templates.index') }}" class="btn btn-outline-primary">Templates</a>
        <a href="{{ route('admin.social.campaigns.create') }}" class="btn btn-outline-primary">New Campaign</a>
        <a href="{{ route('admin.social.settings') }}" class="btn btn-outline-primary">Settings</a>
    </div>
</div>

<div class="stats-grid" style="margin-bottom:1.5rem;">
    <div class="stat-card"><div class="stat-label">Drafts</div><div class="stat-value">{{ $stats['drafts'] }}</div></div>
    <div class="stat-card"><div class="stat-label">Scheduled</div><div class="stat-value">{{ $stats['scheduled'] }}</div></div>
    <div class="stat-card"><div class="stat-label">Published</div><div class="stat-value">{{ $stats['published'] }}</div></div>
    <div class="stat-card"><div class="stat-label">Active Campaigns</div><div class="stat-value">{{ $stats['campaigns'] }}</div></div>
    <div class="stat-card"><div class="stat-label">Manual Required</div><div class="stat-value">{{ $stats['manual_required'] }}</div></div>
    <div class="stat-card"><div class="stat-label">Failed Publishing</div><div class="stat-value">{{ $stats['failed_publications'] }}</div></div>
</div>

<div style="display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:1.5rem;align-items:start;">
    <div class="dash-card">
        <div class="dash-card-head"><h3>Upcoming Schedule</h3></div>
        <table class="data-table">
            <thead><tr><th>Post</th><th>Platforms</th><th>Schedule</th></tr></thead>
            <tbody>
                @forelse($upcoming as $post)
                    <tr>
                        <td><a href="{{ route('admin.social.posts.show', $post) }}"><strong>{{ $post->title }}</strong></a><br><small>{{ $post->campaign?->name ?? 'No campaign' }}</small></td>
                        <td>{{ implode(', ', $post->platforms ?? []) ?: '-' }}</td>
                        <td>{{ $post->scheduled_at?->format('M d, Y H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" style="padding:24px;text-align:center;color:var(--jv-gray-500);">No scheduled posts yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="dash-card">
        <div class="dash-card-head"><h3>Recent Posts</h3></div>
        <table class="data-table">
            <thead><tr><th>Post</th><th>Status</th><th>Updated</th></tr></thead>
            <tbody>
                @forelse($recent as $post)
                    <tr>
                        <td><a href="{{ route('admin.social.posts.show', $post) }}"><strong>{{ $post->title }}</strong></a><br><small>{{ $post->campaign?->name ?? 'No campaign' }}</small></td>
                        <td><span class="pill pill-{{ $post->status === 'published' ? 'ok' : ($post->status === 'scheduled' ? 'info' : 'mute') }}">{{ ucfirst($post->status) }}</span></td>
                        <td>{{ $post->updated_at->format('M d, Y H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" style="padding:24px;text-align:center;color:var(--jv-gray-500);">No posts yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
