@extends('themes.default::layouts.admin')

@section('title', $campaign->name)
@section('breadcrumbs')<a href="{{ route('admin.social.campaigns.index') }}">Campaigns</a> <span class="separator">/</span> <span class="current">{{ $campaign->name }}</span>@endsection

@push('styles')
<style>
.campaign-stats { display:grid; grid-template-columns:repeat(5,minmax(0,1fr)); gap:10px; margin-bottom:18px; }
.campaign-stat { border:1px solid var(--jv-gray-200); background:#fff; border-radius:10px; padding:13px 14px; }
.campaign-stat span { display:block; color:var(--jv-gray-500); font-size:.72rem; font-weight:900; text-transform:uppercase; }
.campaign-stat strong { display:block; color:var(--jv-gray-900); font-size:1.35rem; margin-top:3px; }
.campaign-layout { display:grid; grid-template-columns:minmax(0,1fr) 330px; gap:18px; align-items:start; }
.campaign-post { border:1px solid var(--jv-gray-200); border-radius:12px; padding:12px; display:grid; grid-template-columns:86px minmax(0,1fr) auto; gap:12px; align-items:center; margin-bottom:10px; }
.campaign-thumb { width:86px; height:68px; border-radius:8px; background:#f1f5f9; object-fit:cover; }
.campaign-post h4 { margin:0 0 4px; font-size:.96rem; }
.campaign-post p { margin:0; color:var(--jv-gray-500); font-size:.8rem; line-height:1.45; }
.pub-mini { display:flex; gap:4px; flex-wrap:wrap; margin-top:7px; }
.pub-dot { width:10px; height:10px; border-radius:50%; background:#cbd5e1; }
.pub-dot.published { background:#16a34a; }
.pub-dot.failed { background:#dc2626; }
.pub-dot.manual_required { background:#d97706; }
.pub-dot.pending, .pub-dot.queued { background:#2563eb; }
@media (max-width:980px) { .campaign-layout { grid-template-columns:1fr; } .campaign-stats { grid-template-columns:repeat(2,1fr); } .campaign-post { grid-template-columns:1fr; } .campaign-thumb { width:100%; height:160px; } }
</style>
@endpush

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">{{ $campaign->name }}</h1>
        <p class="page-subtitle">{{ $campaign->goal ?: 'Campaign planning and publishing overview.' }}</p>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
        <a href="{{ route('admin.social.posts.create', ['campaign_id' => $campaign->id]) }}" class="btn btn-primary">New Post</a>
        <a href="{{ route('admin.social.campaigns.edit', $campaign) }}" class="btn btn-outline-primary">Edit Campaign</a>
        <a href="{{ route('admin.social.calendar') }}" class="btn btn-outline-primary">Calendar</a>
    </div>
</div>

<div class="campaign-stats">
    <div class="campaign-stat"><span>Total Posts</span><strong>{{ $stats['posts'] }}</strong></div>
    <div class="campaign-stat"><span>Ready</span><strong>{{ $stats['ready'] }}</strong></div>
    <div class="campaign-stat"><span>Scheduled</span><strong>{{ $stats['scheduled'] }}</strong></div>
    <div class="campaign-stat"><span>Published</span><strong>{{ $stats['published'] }}</strong></div>
    <div class="campaign-stat"><span>Failed</span><strong>{{ $stats['failed'] }}</strong></div>
</div>

<div class="campaign-layout">
    <div class="dash-card">
        <div class="dash-card-head"><h3>Campaign Posts</h3></div>
        @forelse($posts as $post)
            <div class="campaign-post">
                @php $thumb = $post->media->first()?->thumbnail_url; @endphp
                @if($thumb)
                    <img class="campaign-thumb" src="{{ $thumb }}" alt="{{ $post->title }}">
                @else
                    <div class="campaign-thumb" style="display:grid;place-items:center;color:var(--jv-gray-400);font-weight:900;">POST</div>
                @endif
                <div>
                    <h4><a href="{{ route('admin.social.posts.show', $post) }}">{{ $post->title }}</a></h4>
                    <p>{{ str($post->caption)->limit(140) }}</p>
                    <div class="pub-mini" title="Publishing records">
                        @forelse($post->publications as $publication)
                            <span class="pub-dot {{ $publication->status }}" title="{{ $publication->platform }}: {{ $publication->status }}"></span>
                        @empty
                            <span style="color:var(--jv-gray-500);font-size:.75rem;">No publishing records</span>
                        @endforelse
                    </div>
                </div>
                <div style="display:grid;gap:6px;justify-items:end;">
                    <span class="pill pill-{{ $post->status === 'published' ? 'ok' : ($post->status === 'scheduled' ? 'info' : ($post->status === 'failed' ? 'danger' : 'mute')) }}">{{ ucfirst($post->status) }}</span>
                    <small style="color:var(--jv-gray-500);">{{ $post->scheduled_at?->format('M d, Y H:i') ?? $post->updated_at->format('M d, Y') }}</small>
                </div>
            </div>
        @empty
            <div style="padding:28px;text-align:center;color:var(--jv-gray-500);">No posts in this campaign yet.</div>
        @endforelse
    </div>

    <aside>
        <div class="dash-card" style="margin-bottom:1rem;">
            <div class="dash-card-head"><h3>Campaign Info</h3></div>
            <p><strong>Status:</strong> {{ ucfirst($campaign->status) }}</p>
            <p><strong>Start:</strong> {{ $campaign->starts_at?->format('M d, Y') ?? '-' }}</p>
            <p><strong>End:</strong> {{ $campaign->ends_at?->format('M d, Y') ?? '-' }}</p>
            <p><strong>Slug:</strong> {{ $campaign->slug }}</p>
        </div>
        <div class="dash-card">
            <div class="dash-card-head"><h3>Notes</h3></div>
            <div style="white-space:pre-wrap;color:var(--jv-gray-600);line-height:1.6;">{{ $campaign->notes ?: 'No notes yet.' }}</div>
        </div>
    </aside>
</div>
@endsection
