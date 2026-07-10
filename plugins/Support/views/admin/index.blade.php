@extends('themes.default::layouts.admin')

@section('title', 'Support')
@section('breadcrumbs')<span class="current">Support</span>@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Support</h1>
        <p class="page-subtitle">Tickets, announcements, and client help operations</p>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <a href="{{ route('admin.support.tickets.index') }}" class="btn btn-outline-primary">View Tickets</a>
        <a href="{{ route('admin.support.announcements.create') }}" class="btn btn-primary">New Announcement</a>
    </div>
</div>

<div style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:1rem;margin-bottom:1.5rem;">
    @foreach(['open' => 'Open Tickets', 'urgent' => 'Urgent', 'closed' => 'Closed', 'announcements' => 'Announcements'] as $key => $label)
        <div class="dash-card" style="padding:1rem;">
            <div style="color:var(--jv-gray-500);font-size:.75rem;text-transform:uppercase;font-weight:800;">{{ $label }}</div>
            <div style="font-size:1.7rem;font-weight:900;margin-top:4px;">{{ $stats[$key] ?? 0 }}</div>
        </div>
    @endforeach
</div>

<div style="display:grid;grid-template-columns:1.4fr .9fr;gap:1.5rem;align-items:start;">
    <div class="dash-card" style="padding:0;overflow:hidden;">
        <div class="dash-card-head" style="padding:1rem 1.25rem;"><h3>Recent Tickets</h3></div>
        @if($tickets->count())
            <table class="a-table" style="width:100%;border-collapse:collapse;">
                <thead><tr><th style="text-align:left;padding:12px;">Ticket</th><th>Client</th><th>Priority</th><th>Status</th></tr></thead>
                <tbody>
                    @foreach($tickets as $ticket)
                        <tr style="border-top:1px solid #eef2f7;">
                            <td style="padding:12px;"><a href="{{ route('admin.support.tickets.show', $ticket) }}"><strong>{{ $ticket->ticket_number }}</strong></a><br><small>{{ $ticket->subject }}</small></td>
                            <td style="padding:12px;">{{ $ticket->client?->full_name ?? 'Guest' }}</td>
                            <td style="padding:12px;"><span class="pill pill-{{ $ticket->priority === 'urgent' ? 'bad' : ($ticket->priority === 'high' ? 'warn' : 'info') }}">{{ ucfirst($ticket->priority) }}</span></td>
                            <td style="padding:12px;"><span class="pill pill-{{ $ticket->status === 'closed' ? 'mute' : 'ok' }}">{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p style="padding:2rem;text-align:center;color:var(--jv-gray-500);">No tickets yet.</p>
        @endif
    </div>

    <div class="dash-card">
        <div class="dash-card-head"><h3>Announcements</h3></div>
        @forelse($announcements as $announcement)
            <div style="padding:.75rem 0;border-bottom:1px solid #eef2f7;">
                <strong>{{ $announcement->title }}</strong>
                <div style="margin-top:4px;"><span class="pill pill-{{ $announcement->is_published ? 'ok' : 'mute' }}">{{ $announcement->is_published ? 'Published' : 'Draft' }}</span></div>
            </div>
        @empty
            <p style="color:var(--jv-gray-500);">No announcements yet.</p>
        @endforelse
    </div>
</div>
@endsection
