@extends('themes.default::layouts.frontend')

@section('title', 'Support')

@section('content')
<section class="page-hero"><div class="container"><div class="breadcrumb"><a href="/">Home</a> / Client Area / Support</div><h1>Support Center</h1><p>Open tickets, review replies, and read service announcements.</p></div></section>

<main class="container" style="padding:2rem 0;">
    <div style="display:flex;justify-content:space-between;gap:12px;align-items:center;margin-bottom:1rem;">
        <h2 style="margin:0;">My Tickets</h2>
        <a href="{{ route('client.support.tickets.create') }}" class="btn btn-primary">Open Ticket</a>
    </div>
    <div style="display:grid;grid-template-columns:minmax(0,1fr) 320px;gap:1.5rem;align-items:start;">
        <div style="background:#fff;border:1px solid var(--gray-200);border-radius:16px;overflow:hidden;">
            @forelse($tickets as $ticket)
                <a href="{{ route('client.support.tickets.show', $ticket) }}" style="display:block;padding:1rem 1.25rem;border-bottom:1px solid var(--gray-100);color:inherit;">
                    <div style="display:flex;justify-content:space-between;gap:12px;"><strong>{{ $ticket->subject }}</strong><span class="pill pill-{{ $ticket->status === 'closed' ? 'mute' : 'ok' }}">{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</span></div>
                    <small style="color:var(--gray-500);">{{ $ticket->ticket_number }} · {{ ucfirst($ticket->priority) }} · {{ ($ticket->last_reply_at ?: $ticket->updated_at)->diffForHumans() }}</small>
                </a>
            @empty
                <div style="padding:3rem;text-align:center;color:var(--gray-500);">No tickets yet.</div>
            @endforelse
        </div>
        <aside style="background:#fff;border:1px solid var(--gray-200);border-radius:16px;padding:1.25rem;">
            <h3>Announcements</h3>
            @forelse($announcements as $announcement)
                <div style="padding:.7rem 0;border-bottom:1px solid var(--gray-100);"><a href="{{ route('support.announcements.show', $announcement) }}"><strong>{{ $announcement->title }}</strong></a><br><small>{{ $announcement->published_at?->format('M d, Y') }}</small></div>
            @empty
                <p style="color:var(--gray-500);">No announcements.</p>
            @endforelse
        </aside>
    </div>
    <div style="margin-top:1rem;">{{ $tickets->links() }}</div>
</main>
@endsection
