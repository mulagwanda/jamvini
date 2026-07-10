@extends('themes.default::layouts.admin')

@section('title', $client->full_name)
@section('breadcrumbs')<a href="{{ route('admin.clients.index') }}">Clients</a> <span class="separator">/</span> <span class="current">{{ $client->full_name }}</span>@endsection

@section('content')
<style>
.client-shell { display:grid; grid-template-columns:340px minmax(0,1fr); gap:1.5rem; align-items:start; }
.client-rail, .client-main { display:grid; gap:1rem; }
.client-kpis { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:1rem; margin-bottom:1.5rem; }
.client-kpi { background:#fff; border:1px solid var(--jv-gray-200); border-radius:8px; padding:1rem; }
.client-kpi .label { color:var(--jv-gray-500); font-size:.74rem; text-transform:uppercase; font-weight:800; }
.client-kpi .value { font-size:1.35rem; font-weight:900; margin-top:4px; color:var(--jv-gray-900); }
.client-profile-list { list-style:none; padding:0; margin:1rem 0 0; }
.client-profile-list li { display:flex; justify-content:space-between; gap:12px; padding:8px 0; border-bottom:1px dashed #f1f5f9; }
.client-profile-list span { color:var(--jv-gray-500); }
.client-profile-list strong { text-align:right; word-break:break-word; }
.quick-grid { display:grid; grid-template-columns:1fr 1fr; gap:8px; }
.client-tabs { display:flex; gap:4px; border-bottom:1px solid var(--jv-gray-200); margin-bottom:1rem; overflow:auto; }
.client-tabs .tab { background:none; border:none; padding:10px 14px; cursor:pointer; font-weight:800; color:var(--jv-gray-500); border-bottom:2px solid transparent; white-space:nowrap; }
.client-tabs .tab.active { color:var(--jv-primary); border-bottom-color:var(--jv-primary); }
.client-table { width:100%; border-collapse:collapse; }
.client-table thead { background:#f8fafc; color:#475569; font-size:.76rem; text-transform:uppercase; }
.client-table th, .client-table td { padding:12px 10px; border-bottom:1px solid #eef2f7; vertical-align:top; }
.client-table th { text-align:left; }
.mini-feed { display:grid; gap:10px; }
.mini-feed-item { display:flex; gap:10px; padding:10px 0; border-bottom:1px solid #f1f5f9; }
.mini-feed-ico { width:32px; height:32px; border-radius:8px; display:grid; place-items:center; background:#eef2ff; color:var(--jv-primary); flex-shrink:0; }
.contact-card { border:1px solid var(--jv-gray-200); border-radius:8px; padding:12px; display:grid; gap:6px; }
@media (max-width: 1080px) { .client-shell { grid-template-columns:1fr; } .client-kpis { grid-template-columns:repeat(2,minmax(0,1fr)); } }
@media (max-width: 720px) { .client-kpis, .quick-grid { grid-template-columns:1fr; } .client-table { display:block; overflow-x:auto; white-space:nowrap; } }
</style>

<div class="page-header">
    <div>
        <h1 class="page-title">{{ $client->full_name }}</h1>
        <p class="page-subtitle">
            {{ $client->client_number ?: 'Client #' . $client->id }}
            @if($client->group) · {{ $client->group->name }} @endif
            · {{ ucfirst($client->type ?? 'individual') }} account · {{ ucfirst($client->status) }}
        </p>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
        <select class="form-select" style="min-width:250px;" onchange="if(this.value) window.location.href=this.value">
            <option value="">Switch client...</option>
            @foreach($clientSwitcher as $switchClient)
                <option value="{{ route('admin.clients.show', $switchClient) }}" {{ $switchClient->id === $client->id ? 'selected' : '' }}>
                    {{ $switchClient->full_name }}{{ $switchClient->company_name ? ' - ' . $switchClient->company_name : '' }}
                </option>
            @endforeach
        </select>
        <a href="mailto:{{ $client->email }}" class="btn btn-outline-primary">{{ jv_icon('mail', '', 16) }} Email</a>
        <a href="{{ route('admin.clients.edit', $client) }}" class="btn btn-primary">{{ jv_icon('settings', '', 16) }} Edit Profile</a>
    </div>
</div>

<div class="client-kpis">
    <div class="client-kpi"><div class="label">Outstanding</div><div class="value">{{ jv_format_money($metrics['outstanding'] ?? 0) }}</div><small>{{ $metrics['overdue_invoices'] ?? 0 }} overdue invoice(s)</small></div>
    <div class="client-kpi"><div class="label">Active Services</div><div class="value">{{ $metrics['active_services'] ?? 0 }}</div><small>{{ $metrics['suspended_services'] ?? 0 }} suspended</small></div>
    <div class="client-kpi"><div class="label">Domains</div><div class="value">{{ $metrics['active_domains'] ?? 0 }}</div><small>{{ $metrics['domains_expiring'] ?? 0 }} expiring soon</small></div>
    <div class="client-kpi"><div class="label">Tickets</div><div class="value">{{ $tickets->where('status', '!=', 'closed')->count() }}</div><small>{{ $tickets->where('priority', 'urgent')->count() }} urgent</small></div>
</div>

<div class="client-shell">
    <aside class="client-rail">
        <div class="dash-card">
            <div style="text-align:center;padding:1rem 0;">
                <div style="width:88px;height:88px;border-radius:50%;background:linear-gradient(135deg,#2f6f73,#7a5cff);color:#fff;display:grid;place-items:center;font-size:2rem;font-weight:800;margin:0 auto;">
                    {{ strtoupper(substr($client->first_name, 0, 1) . substr($client->last_name, 0, 1)) }}
                </div>
                <h2 style="margin:12px 0 4px;">{{ $client->full_name }}</h2>
                @if($client->company_name)<small style="color:var(--jv-gray-500);">{{ $client->company_name }}</small>@endif
                <div style="margin-top:10px;display:flex;gap:6px;justify-content:center;flex-wrap:wrap;">
                    <span class="pill pill-{{ $client->status === 'active' ? 'ok' : ($client->status === 'suspended' ? 'bad' : 'warn') }}">{{ ucfirst($client->status) }}</span>
                    @if($client->group)<span class="pill pill-info" style="border-color:{{ $client->group->color }};color:{{ $client->group->color }};">{{ $client->group->name }}</span>@endif
                </div>
            </div>

            <ul class="client-profile-list">
                <li><span>Primary Email</span><strong>{{ $client->email }}</strong></li>
                @if($client->billing_email)<li><span>Billing Email</span><strong>{{ $client->billing_email }}</strong></li>@endif
                @if($client->technical_email)<li><span>Technical Email</span><strong>{{ $client->technical_email }}</strong></li>@endif
                @if($client->phone)<li><span>Phone</span><strong>{{ $client->phone }}</strong></li>@endif
                @if($client->mobile)<li><span>Mobile</span><strong>{{ $client->mobile }}</strong></li>@endif
                <li><span>Location</span><strong>{{ collect([$client->city, $client->state, $client->country ?? 'Tanzania'])->filter()->implode(', ') ?: '-' }}</strong></li>
                @if($client->tin_number)<li><span>TIN / Tax ID</span><strong>{{ $client->tin_number }}</strong></li>@endif
                <li><span>Currency</span><strong>{{ $client->currency ?: 'System default' }}</strong></li>
                <li><span>Client Since</span><strong>{{ $client->created_at->format('M d, Y') }}</strong></li>
                @if($client->source || $client->external_id)<li><span>Source</span><strong>{{ $client->source ?: 'Imported' }}@if($client->external_id)<br><small>{{ $client->external_id }}</small>@endif</strong></li>@endif
            </ul>
        </div>

        <div class="dash-card">
            <div class="dash-card-head"><h3>Quick Actions</h3></div>
            <div class="quick-grid">
                <a href="{{ route('admin.invoices.create', ['client_id' => $client->id]) }}" class="btn btn-sm btn-outline-primary">{{ jv_icon('file-text', '', 16) }} Invoice</a>
                <a href="{{ route('admin.orders.create', ['client_id' => $client->id]) }}" class="btn btn-sm btn-outline-primary">{{ jv_icon('shopping-cart', '', 16) }} Order</a>
                @if($client->status === 'active')
                    <form action="{{ route('admin.clients.support-access', $client) }}" method="POST" data-confirm="Open client portal support access for {{ $client->full_name }}?" style="margin:0;">@csrf<button class="btn btn-sm btn-outline-primary" style="width:100%;">{{ jv_icon('user', '', 16) }} Portal</button></form>
                @endif
                <button class="btn btn-sm btn-outline-primary" type="button" onclick="document.getElementById('openTicketPanel').scrollIntoView({behavior:'smooth'})">{{ jv_icon('headphones', '', 16) }} Ticket</button>
            </div>
        </div>

        <div class="dash-card">
            <div class="dash-card-head"><h3>Billing Snapshot</h3></div>
            <div style="display:grid;gap:10px;">
                <div style="display:flex;justify-content:space-between;"><span>Outstanding</span><strong>{{ jv_format_money($metrics['outstanding'] ?? 0) }}</strong></div>
                <div style="display:flex;justify-content:space-between;"><span>Total Invoiced</span><strong>{{ jv_format_money($metrics['total_invoiced'] ?? 0) }}</strong></div>
                <div style="display:flex;justify-content:space-between;"><span>Paid</span><strong>{{ jv_format_money($metrics['paid_amount'] ?? 0) }}</strong></div>
                <div style="display:flex;justify-content:space-between;"><span>Credit Balance</span><strong>{{ jv_format_money($client->credit_balance ?? 0) }}</strong></div>
                <div style="display:flex;justify-content:space-between;"><span>VAT Exempt</span><strong>{{ $client->vat_exempt ? 'Yes' : 'No' }}</strong></div>
                <div style="display:flex;justify-content:space-between;"><span>Marketing</span><strong>{{ $client->email_marketing_opt_in ? 'Opted in' : 'No' }}</strong></div>
            </div>
        </div>

        @if(($customFieldDisplay ?? collect())->count())
            <div class="dash-card">
                <div class="dash-card-head"><h3>{{ jv_icon('brackets', '', 18) }} Custom Fields</h3></div>
                <ul class="client-profile-list" style="margin-top:0;">
                    @foreach($customFieldDisplay as $item)
                        <li><span>{{ $item['label'] }}</span><strong>{{ $item['value'] ?: '-' }}</strong></li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="dash-card">
            <div class="dash-card-head"><h3>Contacts</h3></div>
            <div style="display:grid;gap:10px;">
                <div class="contact-card">
                    <strong>{{ $client->full_name }}</strong>
                    <small>Primary account owner</small>
                    <span>{{ $client->email }}</span>
                    @if($client->phone || $client->mobile)<span>{{ $client->phone ?: $client->mobile }}</span>@endif
                </div>
                @forelse($client->contacts as $contact)
                    <div class="contact-card">
                        <strong>{{ $contact->name }}</strong>
                        @if($contact->role)<small>{{ $contact->role }}</small>@endif
                        @if($contact->email)<span>{{ $contact->email }}</span>@endif
                        @if($contact->phone)<span>{{ $contact->phone }}</span>@endif
                        <div style="display:flex;gap:6px;flex-wrap:wrap;">
                            @if($contact->receives_billing)<span class="pill pill-info">Billing</span>@endif
                            @if($contact->receives_support)<span class="pill pill-info">Support</span>@endif
                        </div>
                    </div>
                @empty
                    <small style="color:var(--jv-gray-500);">No additional contacts yet.</small>
                @endforelse
            </div>
        </div>
    </aside>

    <main class="client-main">
        <div class="dash-card">
            <div class="client-tabs" id="clientTabs">
                @foreach(['services' => 'Services', 'domains' => 'Domains', 'invoices' => 'Invoices', 'orders' => 'Orders', 'tickets' => 'Tickets', 'activity' => 'Activity', 'notes' => 'Notes'] as $key => $label)
                    <button class="tab {{ $loop->first ? 'active' : '' }}" data-tab="{{ $key }}">{{ $label }}</button>
                @endforeach
            </div>

            <div class="tab-panel active" data-panel="services">
                @if($client->services->count())
                    <table class="client-table">
                        <thead><tr><th>Service</th><th>Domain</th><th>Billing</th><th>Next Due</th><th>Status</th><th>Panel</th></tr></thead>
                        <tbody>
                            @foreach($client->services as $cs)
                                @php $panel = $cs->server ?: ($cs->service?->servers?->first(fn ($srv) => (bool) $srv->pivot?->is_default) ?: $cs->service?->servers?->first()); @endphp
                                <tr>
                                    <td><strong>{{ $cs->service->name ?? 'Service #' . $cs->id }}</strong><br><small>{{ $cs->service->group->name ?? 'Service' }} · #{{ $cs->id }}</small></td>
                                    <td>{{ $cs->domain ?: '-' }}</td>
                                    <td>{{ jv_format_money($cs->price) }} / {{ ucfirst(str_replace('_', ' ', $cs->billing_cycle)) }}</td>
                                    <td>{{ $cs->next_due_date ? $cs->next_due_date->format('M d, Y') : '-' }}</td>
                                    <td><span class="pill pill-{{ $cs->status === 'active' ? 'ok' : ($cs->status === 'suspended' ? 'bad' : 'warn') }}">{{ ucfirst($cs->status) }}</span></td>
                                    <td><span class="pill pill-mute">{{ $panel?->type ? strtoupper($panel->type) : 'Manual' }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p style="text-align:center;color:var(--jv-gray-500);padding:30px;">No services yet.</p>
                @endif
            </div>

            <div class="tab-panel" data-panel="domains" style="display:none;">
                @if($client->domains->count())
                    <table class="client-table">
                        <thead><tr><th>Domain</th><th>Registrar</th><th>Renewal</th><th>Expires</th><th>Status</th></tr></thead>
                        <tbody>
                            @foreach($client->domains as $domain)
                                @php $days = $domain->days_until_expiry; $expired = $days !== null && $days < 0; $near = $days !== null && $days >= 0 && $days <= 30; @endphp
                                <tr>
                                    <td><strong>{{ $domain->domain_name }}</strong><br><small>{{ $domain->tld }}</small></td>
                                    <td>{{ $domain->registrar ?: '-' }}</td>
                                    <td>{{ jv_format_money($domain->renewal_fee ?? 0) }}</td>
                                    <td>{{ $domain->expiry_date ? $domain->expiry_date->format('M d, Y') : '-' }}</td>
                                    <td><span class="pill pill-{{ $expired ? 'bad' : ($near ? 'warn' : 'ok') }}">{{ $expired ? 'Expired' : ($near ? $days . ' days' : ucfirst($domain->status)) }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p style="text-align:center;color:var(--jv-gray-500);padding:30px;">No domains yet.</p>
                @endif
            </div>

            <div class="tab-panel" data-panel="invoices" style="display:none;">
                @if($client->invoices->count())
                    <table class="client-table">
                        <thead><tr><th>Invoice</th><th>Date</th><th>Due</th><th>Total</th><th>Paid</th><th>Status</th></tr></thead>
                        <tbody>
                            @foreach($client->invoices->sortByDesc('created_at') as $invoice)
                                <tr>
                                    <td><a href="{{ route('admin.invoices.show', $invoice) }}"><strong>#{{ $invoice->invoice_number }}</strong></a></td>
                                    <td>{{ $invoice->created_at->format('M d, Y') }}</td>
                                    <td>{{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : '-' }}</td>
                                    <td>{{ jv_format_money($invoice->total) }}</td>
                                    <td>{{ jv_format_money($invoice->paid_amount) }}</td>
                                    <td><span class="pill pill-{{ $invoice->status === 'paid' ? 'ok' : ($invoice->status === 'overdue' ? 'bad' : 'warn') }}">{{ ucfirst($invoice->status) }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p style="text-align:center;color:var(--jv-gray-500);padding:30px;">No invoices yet.</p>
                @endif
            </div>

            <div class="tab-panel" data-panel="orders" style="display:none;">
                @if($orders->count())
                    <table class="client-table">
                        <thead><tr><th>Order</th><th>Total</th><th>Status</th><th>Provisioning</th><th>Created</th></tr></thead>
                        <tbody>
                            @foreach($orders as $order)
                                <tr>
                                    <td><a href="{{ route('admin.orders.show', $order) }}"><strong>#{{ $order->order_number ?? $order->id }}</strong></a><br><small>{{ $order->items?->count() ?? 0 }} item(s)</small></td>
                                    <td>{{ jv_format_money($order->total ?? 0) }}</td>
                                    <td><span class="pill pill-{{ in_array($order->status ?? '', ['active', 'completed', 'accepted'], true) ? 'ok' : 'warn' }}">{{ ucfirst($order->status ?? 'pending') }}</span></td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $order->provisioning_status ?? 'not started')) }}</td>
                                    <td>{{ $order->created_at?->format('M d, Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p style="text-align:center;color:var(--jv-gray-500);padding:30px;">No orders yet.</p>
                @endif
            </div>

            <div class="tab-panel" data-panel="tickets" style="display:none;">
                <div id="openTicketPanel" style="border:1px solid var(--jv-gray-200);border-radius:8px;padding:14px;margin-bottom:16px;">
                    <form action="{{ route('admin.clients.tickets.open', $client) }}" method="POST" style="display:grid;gap:10px;">
                        @csrf
                        <div style="display:grid;grid-template-columns:1fr 140px 160px;gap:10px;">
                            <div class="form-group" style="margin:0;"><label class="form-label">Subject</label><input name="subject" class="form-input" required placeholder="What is the client asking about?"></div>
                            <div class="form-group" style="margin:0;"><label class="form-label">Priority</label><select name="priority" class="form-select"><option value="normal">Normal</option><option value="low">Low</option><option value="high">High</option><option value="urgent">Urgent</option></select></div>
                            <div class="form-group" style="margin:0;"><label class="form-label">Department</label><select name="department" class="form-select"><option>Support</option><option>Billing</option><option>Technical</option><option>Domains</option></select></div>
                        </div>
                        <div class="form-group" style="margin:0;"><label class="form-label">Related Service</label><select name="related_service_id" class="form-select"><option value="">None</option>@foreach($client->services as $service)<option value="{{ $service->id }}">{{ $service->service?->name ?? 'Service #' . $service->id }} @if($service->domain) - {{ $service->domain }} @endif</option>@endforeach</select></div>
                        <textarea name="message" class="form-textarea" rows="4" required placeholder="Initial message or call notes..."></textarea>
                        <div style="text-align:right;"><button class="btn btn-primary btn-sm">{{ jv_icon('headphones', '', 16) }} Open Ticket</button></div>
                    </form>
                </div>
                @if($tickets->count())
                    <table class="client-table">
                        <thead><tr><th>Ticket</th><th>Department</th><th>Priority</th><th>Status</th><th>Last Reply</th></tr></thead>
                        <tbody>
                            @foreach($tickets as $ticket)
                                <tr>
                                    <td>@if(\Illuminate\Support\Facades\Route::has('admin.support.tickets.show'))<a href="{{ route('admin.support.tickets.show', $ticket) }}"><strong>{{ $ticket->ticket_number }}</strong></a>@else<strong>{{ $ticket->ticket_number }}</strong>@endif<br><small>{{ $ticket->subject }}</small></td>
                                    <td>{{ $ticket->department }}</td>
                                    <td><span class="pill pill-{{ $ticket->priority === 'urgent' ? 'bad' : ($ticket->priority === 'high' ? 'warn' : 'mute') }}">{{ ucfirst($ticket->priority) }}</span></td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</td>
                                    <td>{{ $ticket->last_reply_at?->diffForHumans() ?: $ticket->created_at?->diffForHumans() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p style="text-align:center;color:var(--jv-gray-500);padding:20px;">No tickets yet.</p>
                @endif
            </div>

            <div class="tab-panel" data-panel="activity" style="display:none;">
                <div class="mini-feed">
                    @forelse($activityLogs as $activity)
                        <div class="mini-feed-item">
                            <div class="mini-feed-ico">{{ jv_icon('activity', '', 16) }}</div>
                            <div><strong>{{ $activity->description }}</strong><br><small style="color:var(--jv-gray-500);">{{ \Carbon\Carbon::parse($activity->created_at)->diffForHumans() }}</small></div>
                        </div>
                    @empty
                        <p style="text-align:center;color:var(--jv-gray-500);padding:30px;">No activity logs yet.</p>
                    @endforelse
                </div>
            </div>

            <div class="tab-panel" data-panel="notes" style="display:none;">
                @if($client->notes)<div style="background:#fafafa;border:1px dashed var(--jv-gray-200);padding:1rem;border-radius:8px;margin-bottom:12px;white-space:pre-wrap;">{{ $client->notes }}</div>@endif
                <form action="{{ route('admin.clients.notes', $client) }}" method="POST">
                    @csrf @method('PATCH')
                    <textarea name="notes" class="form-textarea" rows="6" placeholder="Private account notes, migration notes, billing preferences...">{{ old('notes', $client->notes) }}</textarea>
                    <div style="text-align:right;margin-top:8px;"><button type="submit" class="btn btn-primary btn-sm">Save Note</button></div>
                </form>
            </div>
        </div>
    </main>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('#clientTabs .tab').forEach(tab => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('#clientTabs .tab').forEach(item => item.classList.remove('active'));
        document.querySelectorAll('.tab-panel').forEach(panel => panel.style.display = 'none');
        tab.classList.add('active');
        const panel = document.querySelector(`[data-panel="${tab.dataset.tab}"]`);
        if (panel) panel.style.display = 'block';
    });
});
</script>
@endpush
