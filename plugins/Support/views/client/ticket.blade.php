@extends('themes.default::layouts.frontend')

@section('title', $ticket->ticket_number)

@section('content')
<section class="page-hero"><div class="container"><div class="breadcrumb"><a href="/client/support">Support</a> / {{ $ticket->ticket_number }}</div><h1>{{ $ticket->subject }}</h1><p>{{ $ticket->ticket_number }} · {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</p></div></section>
<main class="container" style="padding:2rem 0;max-width:920px;">
    <div style="background:#fff;border:1px solid var(--gray-200);border-radius:16px;padding:1.25rem;">
        @foreach($ticket->publicReplies as $reply)
            <div style="padding:1rem;border:1px solid var(--gray-200);border-radius:12px;margin-bottom:1rem;background:{{ $reply->author_type === 'admin' ? '#f8fafc' : '#fff' }};">
                <div style="display:flex;justify-content:space-between;gap:12px;"><strong>{{ $reply->author_type === 'admin' ? 'Support Team' : 'You' }}</strong><small>{{ $reply->created_at->format('M d, Y H:i') }}</small></div>
                <div style="white-space:pre-wrap;margin-top:.75rem;line-height:1.55;">{{ $reply->message }}</div>
            </div>
        @endforeach
        @if($ticket->status !== 'closed')
            <form action="{{ route('client.support.tickets.reply', $ticket) }}" method="POST">
                @csrf
                <div class="form-group"><label class="form-label">Reply</label><textarea name="message" class="form-textarea" rows="6" required></textarea></div>
                <div style="text-align:right;"><button class="btn btn-primary">Send Reply</button></div>
            </form>
        @else
            <p style="color:var(--gray-500);">This ticket is closed.</p>
        @endif
    </div>
</main>
@endsection
