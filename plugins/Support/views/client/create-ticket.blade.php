@extends('themes.default::layouts.frontend')

@section('title', 'Open Ticket')

@section('content')
<section class="page-hero"><div class="container"><div class="breadcrumb"><a href="/client/support">Support</a> / Open Ticket</div><h1>Open Support Ticket</h1><p>Tell us what you need help with.</p></div></section>
<main class="container" style="padding:2rem 0;max-width:860px;">
    <form action="{{ route('client.support.tickets.store') }}" method="POST" style="background:#fff;border:1px solid var(--gray-200);border-radius:16px;padding:1.5rem;">
        @csrf
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div class="form-group"><label class="form-label">Department</label><select name="department" class="form-select" required><option>Support</option><option>Billing</option><option>Technical</option><option>Domains</option></select></div>
            <div class="form-group"><label class="form-label">Priority</label><select name="priority" class="form-select" required><option value="normal">Normal</option><option value="low">Low</option><option value="high">High</option><option value="urgent">Urgent</option></select></div>
        </div>
        <div class="form-group"><label class="form-label">Related Service</label><select name="related_service_id" class="form-select"><option value="">None</option>@foreach($services as $service)<option value="{{ $service->id }}">{{ $service->service?->name ?? 'Service #' . $service->id }} @if($service->domain) - {{ $service->domain }} @endif</option>@endforeach</select></div>
        <div class="form-group"><label class="form-label">Subject</label><input type="text" name="subject" class="form-input" required></div>
        <div class="form-group"><label class="form-label">Message</label><textarea name="message" class="form-textarea" rows="8" required></textarea></div>
        <div style="display:flex;justify-content:flex-end;gap:10px;"><a href="{{ route('client.support.index') }}" class="btn btn-outline">Cancel</a><button class="btn btn-primary">Submit Ticket</button></div>
    </form>
</main>
@endsection
