@extends('themes.default::layouts.admin')

@section('title', $ticket->ticket_number)
@section('breadcrumbs')<a href="{{ route('admin.support.tickets.index') }}">Tickets</a> <span class="separator">/</span> <span class="current">{{ $ticket->ticket_number }}</span>@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">{{ $ticket->subject }}</h1>
        <p class="page-subtitle">{{ $ticket->ticket_number }} · {{ $ticket->client?->full_name ?? 'Guest' }}</p>
    </div>
    <form action="{{ route('admin.support.tickets.update', $ticket) }}" method="POST" style="display:flex;gap:8px;flex-wrap:wrap;">
        @csrf @method('PATCH')
        <select name="status" class="form-select">
            @foreach(['open','client_replied','staff_replied','on_hold','closed'] as $status)
                <option value="{{ $status }}" {{ $ticket->status === $status ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
            @endforeach
        </select>
        <select name="priority" class="form-select">
            @foreach(['low','normal','high','urgent'] as $priority)
                <option value="{{ $priority }}" {{ $ticket->priority === $priority ? 'selected' : '' }}>{{ ucfirst($priority) }}</option>
            @endforeach
        </select>
        <input type="text" name="department" class="form-input" value="{{ $ticket->department }}" style="max-width:160px;">
        <button class="btn btn-primary">Update</button>
    </form>
</div>

<div style="display:grid;grid-template-columns:minmax(0,1fr) 320px;gap:1.5rem;align-items:start;">
    <div class="dash-card">
        @foreach($ticket->replies as $reply)
            <div style="padding:1rem;border:1px solid {{ $reply->is_private ? '#fde68a' : '#e2e8f0' }};border-radius:10px;margin-bottom:1rem;background:{{ $reply->is_private ? '#fffbeb' : '#f8fafc' }};">
                <div style="display:flex;justify-content:space-between;gap:12px;">
                    <strong>{{ $reply->author_type === 'admin' ? ($reply->admin?->name ?? 'Staff') : ($reply->client?->full_name ?? 'Client') }}</strong>
                    <small style="color:var(--jv-gray-500);">{{ $reply->created_at->format('M d, Y H:i') }}</small>
                </div>
                @if($reply->is_private)<span class="pill pill-warn" style="margin-top:6px;">Private note</span>@endif
                <div style="white-space:pre-wrap;margin-top:.75rem;line-height:1.55;">{{ $reply->message }}</div>
            </div>
        @endforeach

        <form action="{{ route('admin.support.tickets.reply', $ticket) }}" method="POST">
            @csrf
            <div class="form-group"><label class="form-label">Reply</label><textarea name="message" class="form-textarea" rows="7" required></textarea></div>
            <div style="display:flex;justify-content:space-between;gap:12px;align-items:center;">
                <label class="checkbox-group"><input type="checkbox" name="is_private" value="1"> Private note</label>
                <div style="display:flex;gap:8px;">
                    <select name="status" class="form-select"><option value="staff_replied">Reply and keep open</option><option value="closed">Reply and close</option><option value="on_hold">Reply and hold</option></select>
                    <button class="btn btn-primary">Send Reply</button>
                </div>
            </div>
        </form>
    </div>

    <aside class="dash-card">
        <div class="dash-card-head"><h3>Ticket Info</h3></div>
        <p><strong>Status:</strong> {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</p>
        <p><strong>Priority:</strong> {{ ucfirst($ticket->priority) }}</p>
        <p><strong>Department:</strong> {{ $ticket->department }}</p>
        @if($ticket->client)<p><strong>Client:</strong> <a href="{{ route('admin.clients.show', $ticket->client) }}">{{ $ticket->client->full_name }}</a></p>@endif
        <p><strong>Opened:</strong> {{ $ticket->created_at->format('M d, Y H:i') }}</p>
    </aside>
</div>
@endsection
