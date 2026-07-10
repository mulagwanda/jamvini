@extends('themes.default::layouts.admin')

@section('title', 'Social Campaigns')
@section('breadcrumbs')<a href="{{ route('admin.social.index') }}">Social Centre</a> <span class="separator">/</span> <span class="current">Campaigns</span>@endsection

@section('content')
<div class="page-header">
    <div><h1 class="page-title">Campaigns</h1><p class="page-subtitle">Group posts around offers, launches, holidays, and promotions.</p></div>
    <a href="{{ route('admin.social.campaigns.create') }}" class="btn btn-primary">New Campaign</a>
</div>

<div class="dash-card">
    <table class="data-table">
        <thead><tr><th>Name</th><th>Goal</th><th>Status</th><th>Dates</th><th>Posts</th><th></th></tr></thead>
        <tbody>
            @forelse($campaigns as $campaign)
                <tr>
                    <td><a href="{{ route('admin.social.campaigns.show', $campaign) }}"><strong>{{ $campaign->name }}</strong></a><br><small>{{ $campaign->slug }}</small></td>
                    <td>{{ $campaign->goal ?: '-' }}</td>
                    <td><span class="pill pill-{{ $campaign->status === 'active' ? 'ok' : 'mute' }}">{{ ucfirst($campaign->status) }}</span></td>
                    <td>{{ $campaign->starts_at?->format('M d') ?? '-' }} - {{ $campaign->ends_at?->format('M d, Y') ?? '-' }}</td>
                    <td>{{ $campaign->posts_count }}</td>
                    <td style="text-align:right;display:flex;gap:6px;justify-content:flex-end;"><a href="{{ route('admin.social.campaigns.show', $campaign) }}" class="btn btn-sm btn-outline-primary">View</a><a href="{{ route('admin.social.campaigns.edit', $campaign) }}" class="btn btn-sm btn-outline-primary">Edit</a></td>
                </tr>
            @empty
                <tr><td colspan="6" style="padding:24px;text-align:center;color:var(--jv-gray-500);">No campaigns yet.</td></tr>
            @endforelse
        </tbody>
    </table>
    {{ $campaigns->links() }}
</div>
@endsection
