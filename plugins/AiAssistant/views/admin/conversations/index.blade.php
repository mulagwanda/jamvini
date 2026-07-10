@extends('themes.default::layouts.admin')

@section('title', 'AI Conversations')
@section('breadcrumbs')<a href="{{ route('admin.ai-assistant.index') }}">AI Assistant</a> <span class="separator">/</span> <span class="current">Conversations</span>@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Conversations</h1>
        <p class="page-subtitle">Review AI chats, human handoffs, and transcripts.</p>
    </div>
    <form method="GET" style="display:flex;gap:8px;">
        <select name="status" class="form-select" onchange="this.form.submit()">
            <option value="">All statuses</option>
            @foreach(['open','human_needed','human_replied','handled','escalated'] as $status)
                <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
            @endforeach
        </select>
    </form>
</div>

<div class="dash-card">
    <table class="data-table">
        <thead><tr><th>Visitor</th><th>Status</th><th>Page</th><th>Country</th><th>Messages</th><th>Ticket</th><th>Updated</th><th></th></tr></thead>
        <tbody>
            @forelse($conversations as $conversation)
                <tr>
                    <td>
                        <strong>{{ $conversation->visitor_name ?: ($conversation->client?->full_name ?? 'Visitor') }}</strong><br>
                        <small>{{ $conversation->visitor_email ?: $conversation->client?->email ?: '-' }}</small>
                    </td>
                    <td><span class="pill pill-{{ in_array($conversation->status, ['human_needed','escalated']) ? 'warn' : ($conversation->status === 'handled' ? 'ok' : 'info') }}">{{ ucfirst(str_replace('_', ' ', $conversation->status)) }}</span></td>
                    <td style="max-width:260px;">
                        <strong>{{ $conversation->page_title ?: '-' }}</strong><br>
                        <small style="display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $conversation->page_url ?: '-' }}</small>
                    </td>
                    <td>{{ $conversation->country_name ?: ($conversation->country_code ?: 'Unknown') }}</td>
                    <td>{{ $conversation->messages_count }}</td>
                    <td>
                        @if($conversation->supportTicket)
                            <a href="{{ route('admin.support.tickets.show', $conversation->supportTicket) }}">{{ $conversation->supportTicket->ticket_number }}</a>
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $conversation->updated_at->format('M d, Y H:i') }}</td>
                    <td style="text-align:right;"><a href="{{ route('admin.ai-assistant.conversations.show', $conversation) }}" class="btn btn-sm btn-outline-primary">Open</a></td>
                </tr>
            @empty
                <tr><td colspan="8" style="padding:24px;text-align:center;color:var(--jv-gray-500);">No AI conversations yet.</td></tr>
            @endforelse
        </tbody>
    </table>
    {{ $conversations->links() }}
</div>
@endsection
