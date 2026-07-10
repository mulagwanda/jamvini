@extends('themes.default::layouts.admin')

@section('title', 'AI Conversation')
@section('breadcrumbs')<a href="{{ route('admin.ai-assistant.conversations.index') }}">Conversations</a> <span class="separator">/</span> <span class="current">#{{ $conversation->id }}</span>@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Conversation #{{ $conversation->id }}</h1>
        <p class="page-subtitle">{{ $conversation->visitor_name ?: ($conversation->client?->full_name ?? 'Visitor') }} · {{ ucfirst(str_replace('_', ' ', $conversation->status)) }}</p>
    </div>
    <form action="{{ route('admin.ai-assistant.conversations.update', $conversation) }}" method="POST" style="display:flex;gap:8px;align-items:center;">
        @csrf @method('PATCH')
        <select name="status" class="form-select">
            @foreach(['open','human_needed','human_replied','handled','escalated'] as $status)
                <option value="{{ $status }}" {{ $conversation->status === $status ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
            @endforeach
        </select>
        <button class="btn btn-primary">Update</button>
    </form>
</div>

<div style="display:grid;grid-template-columns:minmax(0,1fr) 320px;gap:1.5rem;align-items:start;">
    <div class="dash-card">
        <div class="dash-card-head"><h3>Transcript</h3></div>
        @foreach($conversation->messages as $message)
            @php
                $isVisitor = $message->role === 'user';
                $isStaff = $message->role === 'staff';
                $label = $isVisitor ? 'Visitor' : ($isStaff ? 'Staff' : 'AI Assistant');
                $bg = $isVisitor ? '#eff6ff' : ($isStaff ? '#ecfdf5' : '#f8fafc');
                $border = $isVisitor ? '#bfdbfe' : ($isStaff ? '#bbf7d0' : '#e2e8f0');
            @endphp
            <div style="padding:1rem;border:1px solid {{ $border }};border-radius:12px;margin-bottom:1rem;background:{{ $bg }};">
                <div style="display:flex;justify-content:space-between;gap:12px;">
                    <strong>{{ $label }}</strong>
                    <small style="color:var(--jv-gray-500);">{{ $message->created_at->format('M d, Y H:i') }}</small>
                </div>
                <div style="white-space:pre-wrap;margin-top:.75rem;line-height:1.55;">{{ $message->message }}</div>
                @if(!empty($message->context) && $message->role === 'assistant')
                    <div style="margin-top:.75rem;display:flex;gap:6px;flex-wrap:wrap;">
                        @foreach($message->context as $source)
                            @if(!empty($source['title']))
                                <span class="pill pill-info">{{ $source['title'] }}</span>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach

        <form action="{{ route('admin.ai-assistant.conversations.reply', $conversation) }}" method="POST" style="margin-top:1.25rem;">
            @csrf
            <div class="form-group"><label class="form-label">Staff Reply</label><textarea name="message" class="form-textarea" rows="5" required></textarea></div>
            <div style="display:flex;justify-content:flex-end;gap:8px;">
                <select name="status" class="form-select" style="max-width:180px;"><option value="human_replied">Reply</option><option value="handled">Reply and mark handled</option><option value="open">Reply and keep open</option></select>
                <button class="btn btn-primary">Send Reply</button>
            </div>
        </form>
    </div>

    <aside class="dash-card">
        <div class="dash-card-head"><h3>Visitor</h3></div>
        <p><strong>Name:</strong> {{ $conversation->visitor_name ?: ($conversation->client?->full_name ?? '-') }}</p>
        <p><strong>Email:</strong> {{ $conversation->visitor_email ?: ($conversation->client?->email ?? '-') }}</p>
        <p><strong>Status:</strong> {{ ucfirst(str_replace('_', ' ', $conversation->status)) }}</p>
        <p><strong>Country:</strong> {{ $conversation->country_name ?: ($conversation->country_code ?: 'Unknown') }}</p>
        <p><strong>Started:</strong> {{ $conversation->created_at->format('M d, Y H:i') }}</p>
        <p><strong>Last activity:</strong> {{ $conversation->updated_at->format('M d, Y H:i') }}</p>
        @if($conversation->escalated_at)
            <p><strong>Escalated:</strong> {{ $conversation->escalated_at->format('M d, Y H:i') }}</p>
        @endif
        @if($conversation->last_staff_reply_at)
            <p><strong>Last staff reply:</strong> {{ $conversation->last_staff_reply_at->format('M d, Y H:i') }}</p>
        @endif
        @if($conversation->client)
            <p><strong>Client:</strong> <a href="{{ route('admin.clients.show', $conversation->client) }}">{{ $conversation->client->full_name }}</a></p>
        @endif
        @if($conversation->supportTicket)
            <p><strong>Ticket:</strong> <a href="{{ route('admin.support.tickets.show', $conversation->supportTicket) }}">{{ $conversation->supportTicket->ticket_number }}</a></p>
        @endif
        @if(!empty($conversation->metadata))
            <hr style="border:none;border-top:1px solid var(--jv-gray-200);margin:1rem 0;">
            <p><strong>Page title:</strong><br><small>{{ $conversation->page_title ?: '-' }}</small></p>
            <p><strong>Page URL:</strong><br><small>{{ $conversation->page_url ?: '-' }}</small></p>
            <p><strong>Referrer:</strong><br><small>{{ $conversation->metadata['referrer'] ?? '-' }}</small></p>
            <p><strong>IP:</strong> {{ $conversation->metadata['ip'] ?? '-' }}</p>
            <p><strong>Timezone:</strong> {{ $conversation->metadata['timezone'] ?? '-' }}</p>
            <p><strong>Language:</strong> {{ $conversation->metadata['language'] ?? '-' }}</p>
            <p><strong>User agent:</strong><br><small>{{ $conversation->metadata['user_agent'] ?? '-' }}</small></p>
        @endif
    </aside>
</div>
@endsection
