@extends('themes.default::layouts.admin')

@section('title', 'AI Assistant')
@section('breadcrumbs')<span class="current">AI Assistant</span>@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">AI Assistant</h1>
        <p class="page-subtitle">AI chat, knowledge sources, and support escalation.</p>
    </div>
    <div style="display:flex;gap:10px;">
        <a href="{{ route('admin.ai-assistant.conversations.index') }}" class="btn btn-outline-primary">Conversations</a>
        <a href="{{ route('admin.ai-assistant.sources.index') }}" class="btn btn-outline-primary">Sources</a>
        <a href="{{ route('admin.ai-assistant.settings') }}" class="btn btn-primary">Settings</a>
    </div>
</div>

<div class="stats-grid" style="margin-bottom:1.5rem;">
    <div class="stat-card"><div class="stat-label">Sources</div><div class="stat-value">{{ $stats['sources'] }}</div></div>
    <div class="stat-card"><div class="stat-label">Ready</div><div class="stat-value">{{ $stats['ready_sources'] }}</div></div>
    <div class="stat-card"><div class="stat-label">Chats</div><div class="stat-value">{{ $stats['conversations'] }}</div></div>
    <div class="stat-card"><div class="stat-label">Escalated</div><div class="stat-value">{{ $stats['escalated'] }}</div></div>
</div>

<div class="dash-card">
    <div class="dash-card-head"><h3>Recent Conversations</h3></div>
    <table class="data-table">
        <thead><tr><th>Visitor</th><th>Status</th><th>Page</th><th>Country</th><th>Ticket</th><th>Started</th><th></th></tr></thead>
        <tbody>
            @forelse($conversations as $conversation)
                <tr>
                    <td>{{ $conversation->visitor_name ?: $conversation->visitor_email ?: ($conversation->client?->full_name ?? 'Visitor') }}</td>
                    <td><span class="pill pill-{{ $conversation->status === 'escalated' ? 'warn' : 'ok' }}">{{ ucfirst($conversation->status) }}</span></td>
                    <td>{{ $conversation->page_title ?: '-' }}</td>
                    <td>{{ $conversation->country_name ?: ($conversation->country_code ?: 'Unknown') }}</td>
                    <td>{{ $conversation->supportTicket?->ticket_number ?? '-' }}</td>
                    <td>{{ $conversation->created_at->format('M d, Y H:i') }}</td>
                    <td style="text-align:right;"><a href="{{ route('admin.ai-assistant.conversations.show', $conversation) }}" class="btn btn-sm btn-outline-primary">Open</a></td>
                </tr>
            @empty
                <tr><td colspan="7" style="padding:24px;text-align:center;color:var(--jv-gray-500);">No conversations yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
