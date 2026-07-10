@extends('themes.default::layouts.admin')

@section('title', $post->title)
@section('breadcrumbs')<a href="{{ route('admin.social.posts.index') }}">Posts</a> <span class="separator">/</span> <span class="current">{{ $post->title }}</span>@endsection

@push('styles')
<style>
.publish-grid { display:grid; gap:12px; margin-top:1rem; }
.publish-card { border:1px solid var(--jv-gray-200); border-radius:12px; padding:12px; background:#fff; }
.publish-head { display:flex; justify-content:space-between; gap:10px; align-items:start; margin-bottom:8px; }
.publish-head h4 { margin:0; font-size:.96rem; color:var(--jv-gray-900); }
.publish-meta { color:var(--jv-gray-500); font-size:.78rem; line-height:1.5; }
.publish-actions { display:grid; gap:8px; margin-top:10px; }
.publish-actions form { display:grid; gap:7px; }
.status-badge { border-radius:999px; padding:4px 8px; font-size:.68rem; font-weight:900; text-transform:uppercase; background:#f1f5f9; color:#475569; }
.status-badge.published { background:#dcfce7; color:#166534; }
.status-badge.pending, .status-badge.queued { background:#dbeafe; color:#1d4ed8; }
.status-badge.manual_required { background:#fef3c7; color:#92400e; }
.status-badge.failed { background:#fee2e2; color:#991b1b; }
.status-badge.skipped { background:#f1f5f9; color:#64748b; }
.workflow-help { border:1px solid #bfdbfe; background:#eff6ff; color:#1e40af; border-radius:10px; padding:12px; line-height:1.5; font-size:.86rem; margin-bottom:12px; }
</style>
@endpush

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">{{ $post->title }}</h1>
        <p class="page-subtitle">{{ $post->campaign?->name ?? 'No campaign' }} · {{ ucfirst($post->status) }}</p>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <a href="{{ route('admin.social.posts.edit', $post) }}" class="btn btn-outline-primary">Edit</a>
        <form action="{{ route('admin.social.posts.duplicate', $post) }}" method="POST">@csrf<button class="btn btn-outline-primary">Duplicate</button></form>
        <form action="{{ route('admin.social.publishing.run-due') }}" method="POST">@csrf<button class="btn btn-outline-primary">Run Queue</button></form>
        <form action="{{ route('admin.social.posts.sync-publications', $post) }}" method="POST">@csrf<button class="btn btn-outline-primary">Sync Publishing</button></form>
        @if($post->status !== 'published')
            <form action="{{ route('admin.social.posts.mark-published', $post) }}" method="POST" data-confirm="Mark this post as published?">@csrf<button class="btn btn-primary">Mark Published</button></form>
        @endif
    </div>
</div>

<div style="display:grid;grid-template-columns:minmax(0,1fr) 330px;gap:1.5rem;align-items:start;">
    <div class="dash-card">
        <div style="white-space:pre-wrap;line-height:1.65;font-size:1rem;">{{ $post->caption }}</div>
        @if($post->hashtags)
            <div style="margin-top:1rem;color:var(--jv-primary);font-weight:700;">{{ implode(' ', $post->hashtags) }}</div>
        @endif
        @if($post->link_url)
            <div style="margin-top:1rem;"><a href="{{ $post->link_url }}" target="_blank" rel="noopener">{{ $post->link_url }}</a></div>
        @endif
        @if($post->media->count())
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:12px;margin-top:1.25rem;">
                @foreach($post->media as $file)
                    <img src="{{ $file->thumbnail_url }}" alt="{{ $file->original_name }}" style="width:100%;height:160px;object-fit:cover;border-radius:12px;border:1px solid var(--jv-gray-200);">
                @endforeach
            </div>
        @endif

        <div class="dash-card" style="margin-top:1rem;">
            <div class="dash-card-head"><h3>Publishing Workflow</h3></div>
            <div class="workflow-help">
                Local V1 uses a hybrid workflow: JamVini tracks each platform separately, while API auto-posting can be enabled later on a live server. For now, publish manually on the social platform, paste the published URL if available, then mark that platform as published.
            </div>
            <div class="publish-grid">
                @forelse($post->publications ?? collect() as $publication)
                    <div class="publish-card">
                        <div class="publish-head">
                            <div>
                                <h4>{{ $platforms[$publication->platform] ?? ucfirst($publication->platform) }}</h4>
                                <div class="publish-meta">
                                    Mode: {{ ucfirst($publication->mode) }}<br>
                                    Account: {{ $publication->account?->name ?? 'No account assigned' }}<br>
                                    Scheduled: {{ $publication->scheduled_at?->format('M d, Y H:i') ?? '-' }}<br>
                                    Published: {{ $publication->published_at?->format('M d, Y H:i') ?? '-' }}
                                </div>
                            </div>
                            <span class="status-badge {{ $publication->status }}">{{ str_replace('_', ' ', $publication->status) }}</span>
                        </div>
                        @if($publication->provider_url)
                            <a href="{{ $publication->provider_url }}" target="_blank" rel="noopener">{{ $publication->provider_url }}</a>
                        @endif
                        @if($publication->last_error)
                            <div class="alert alert-warning" style="margin-top:8px;">{{ $publication->last_error }}</div>
                        @endif
                        <div class="publish-actions">
                            @if($publication->status !== 'published')
                                <form action="{{ route('admin.social.publications.mark-published', $publication) }}" method="POST">
                                    @csrf
                                    <input type="url" name="provider_url" class="form-input" placeholder="Published post URL, optional">
                                    <input type="text" name="notes" class="form-input" placeholder="Publishing notes, optional">
                                    <button class="btn btn-sm btn-primary">Mark {{ $platforms[$publication->platform] ?? $publication->platform }} Published</button>
                                </form>
                                <form action="{{ route('admin.social.publications.mark-failed', $publication) }}" method="POST">
                                    @csrf
                                    <input type="text" name="last_error" class="form-input" placeholder="Reason or API error" required>
                                    <button class="btn btn-sm btn-outline-danger">Mark Failed</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div style="padding:18px;text-align:center;color:var(--jv-gray-500);border:1px dashed var(--jv-gray-300);border-radius:10px;">
                        No publishing records yet. Click <strong>Sync Publishing</strong> to create platform records for this post.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <aside>
        <div class="dash-card" style="margin-bottom:1rem;">
            <div class="dash-card-head"><h3>Post Info</h3></div>
            <p><strong>Status:</strong> {{ ucfirst($post->status) }}</p>
            <p><strong>Platforms:</strong> {{ implode(', ', $post->platforms ?? []) ?: '-' }}</p>
            <p><strong>Scheduled:</strong> {{ $post->scheduled_at?->format('M d, Y H:i') ?? '-' }}</p>
            <p><strong>Published:</strong> {{ $post->published_at?->format('M d, Y H:i') ?? '-' }}</p>
            <p><strong>Created:</strong> {{ $post->created_at->format('M d, Y H:i') }}</p>
        </div>

        <div class="dash-card">
            <div class="dash-card-head"><h3>Activity</h3></div>
            @forelse($post->logs()->latest()->get() as $log)
                <div style="padding:.75rem 0;border-bottom:1px solid var(--jv-gray-100);">
                    <strong>{{ ucfirst($log->status) }}</strong><br>
                    <small style="color:var(--jv-gray-500);">{{ $log->created_at->format('M d, Y H:i') }}</small>
                    <div style="margin-top:.35rem;">{{ $log->message }}</div>
                </div>
            @empty
                <p style="color:var(--jv-gray-500);">No activity yet.</p>
            @endforelse
        </div>
    </aside>
</div>
@endsection
