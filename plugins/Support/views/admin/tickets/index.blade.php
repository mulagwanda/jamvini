@extends('themes.default::layouts.admin')

@section('title', 'Tickets')
@section('breadcrumbs')<a href="{{ route('admin.support.index') }}">Support</a> <span class="separator">/</span> <span class="current">Tickets</span>@endsection

@section('content')
<div class="page-header">
    <div><h1 class="page-title">Tickets</h1><p class="page-subtitle">Client support queue</p></div>
</div>

<form method="GET" class="dash-card" style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:1rem;">
    <input type="text" name="search" class="form-input" value="{{ request('search') }}" placeholder="Search tickets or clients..." style="max-width:320px;">
    <select name="status" class="form-select" style="max-width:180px;">
        <option value="">All statuses</option>
        @foreach(['open','client_replied','staff_replied','on_hold','closed'] as $status)
            <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
        @endforeach
    </select>
    <select name="priority" class="form-select" style="max-width:180px;">
        <option value="">All priorities</option>
        @foreach(['low','normal','high','urgent'] as $priority)
            <option value="{{ $priority }}" {{ request('priority') === $priority ? 'selected' : '' }}>{{ ucfirst($priority) }}</option>
        @endforeach
    </select>
    <button class="btn btn-primary">Filter</button>
</form>

<div class="dash-card" style="padding:0;overflow:hidden;">
    <table class="a-table" style="width:100%;border-collapse:collapse;">
        <thead><tr><th style="text-align:left;padding:14px;">Ticket</th><th>Client</th><th>Department</th><th>Priority</th><th>Status</th><th>Updated</th></tr></thead>
        <tbody>
            @forelse($tickets as $ticket)
                <tr style="border-top:1px solid #eef2f7;">
                    <td style="padding:14px;"><a href="{{ route('admin.support.tickets.show', $ticket) }}"><strong>{{ $ticket->ticket_number }}</strong></a><br><small>{{ $ticket->subject }}</small></td>
                    <td style="padding:14px;">{{ $ticket->client?->full_name ?? 'Guest' }}</td>
                    <td style="padding:14px;">{{ $ticket->department }}</td>
                    <td style="padding:14px;"><span class="pill pill-{{ $ticket->priority === 'urgent' ? 'bad' : ($ticket->priority === 'high' ? 'warn' : 'info') }}">{{ ucfirst($ticket->priority) }}</span></td>
                    <td style="padding:14px;"><span class="pill pill-{{ $ticket->status === 'closed' ? 'mute' : 'ok' }}">{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</span></td>
                    <td style="padding:14px;">{{ ($ticket->last_reply_at ?: $ticket->updated_at)->diffForHumans() }}</td>
                </tr>
            @empty
                <tr><td colspan="6" style="padding:2rem;text-align:center;color:var(--jv-gray-500);">No tickets found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div style="margin-top:1rem;">{{ $tickets->links() }}</div>
@endsection
