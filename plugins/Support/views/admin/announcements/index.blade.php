@extends('themes.default::layouts.admin')

@section('title', 'Announcements')
@section('breadcrumbs')<a href="{{ route('admin.support.index') }}">Support</a> <span class="separator">/</span> <span class="current">Announcements</span>@endsection

@section('content')
<div class="page-header">
    <div><h1 class="page-title">Announcements</h1><p class="page-subtitle">News, maintenance notices, incidents, and releases</p></div>
    <a href="{{ route('admin.support.announcements.create') }}" class="btn btn-primary">New Announcement</a>
</div>

<div class="dash-card" style="padding:0;overflow:hidden;">
    <table class="a-table" style="width:100%;border-collapse:collapse;">
        <thead><tr><th style="text-align:left;padding:14px;">Title</th><th>Type</th><th>Status</th><th>Published</th><th></th></tr></thead>
        <tbody>
            @forelse($announcements as $announcement)
                <tr style="border-top:1px solid #eef2f7;">
                    <td style="padding:14px;"><strong>{{ $announcement->title }}</strong><br><small>{{ $announcement->summary }}</small></td>
                    <td style="padding:14px;"><span class="pill pill-info">{{ ucfirst($announcement->type) }}</span></td>
                    <td style="padding:14px;"><span class="pill pill-{{ $announcement->is_published ? 'ok' : 'mute' }}">{{ $announcement->is_published ? 'Published' : 'Draft' }}</span></td>
                    <td style="padding:14px;">{{ $announcement->published_at?->format('M d, Y H:i') ?? '-' }}</td>
                    <td style="padding:14px;text-align:right;">
                        <a href="{{ route('admin.support.announcements.edit', $announcement) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                        <form action="{{ route('admin.support.announcements.destroy', $announcement) }}" method="POST" style="display:inline;" data-confirm="Delete this announcement?" data-danger="true">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" style="padding:2rem;text-align:center;color:var(--jv-gray-500);">No announcements yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div style="margin-top:1rem;">{{ $announcements->links() }}</div>
@endsection
