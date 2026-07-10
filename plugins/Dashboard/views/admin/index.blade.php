@extends('themes.default::layouts.admin')

@section('title', 'Dashboard')
@section('breadcrumbs')<span class="current">Dashboard</span>@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Dashboard</h1>
        <p class="page-subtitle">Revenue, clients, services, domains, and operational risk</p>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-primary">Review Orders</a>
        <a href="{{ route('admin.clients.create') }}" class="btn btn-primary">Add Client</a>
    </div>
</div>

<style>
.jv-widget-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:16px; margin-bottom:24px; }
.jv-widget-card { background:#fff; border:1px solid var(--jv-gray-200); border-radius:8px; padding:16px; box-shadow:0 1px 3px rgba(15,23,42,.05); border-top:4px solid #3b82f6; }
.jv-widget-card.green { border-top-color:#16a34a; } .jv-widget-card.amber { border-top-color:#d97706; } .jv-widget-card.purple { border-top-color:#7c3aed; } .jv-widget-card.slate { border-top-color:#475569; } .jv-widget-card.rose { border-top-color:#e11d48; }
.jv-widget-card.medium { grid-column:span 2; } .jv-widget-card.large { grid-column:span 3; } .jv-widget-card.full { grid-column:1/-1; }
.jv-widget-kpi { display:flex; justify-content:space-between; gap:16px; align-items:center; }
.jv-widget-kpi .label { color:var(--jv-gray-500); font-size:.78rem; font-weight:800; text-transform:uppercase; }
.jv-widget-kpi .value { font-size:1.45rem; font-weight:900; color:var(--jv-gray-900); }
.jv-widget-kpi .meta { color:var(--jv-gray-500); font-size:.82rem; }
.widget-icon { width:40px; height:40px; border-radius:8px; display:grid; place-items:center; background:#f1f5f9; color:var(--jv-primary); flex-shrink:0; }
.widget-settings-row { display:grid; grid-template-columns:26px minmax(180px,1fr) 120px 120px 120px 90px; gap:10px; align-items:center; padding:10px 0; border-bottom:1px solid var(--jv-gray-100); }
.drag-handle { cursor:grab; color:var(--jv-gray-400); font-weight:900; }
.hidden { display:none !important; }
</style>

<div class="dash-card" style="margin-bottom:1.5rem;">
    <div class="dash-card-head">
        <h3>{{ jv_icon('layout-dashboard', '', 18) }} Dashboard Widgets</h3>
        <button type="button" class="btn btn-sm btn-outline-primary" onclick="document.getElementById('widgetSettings').classList.toggle('hidden')">Customize</button>
    </div>
    <div class="jv-widget-grid" id="dashboardWidgetGrid">
        @foreach($dashboardWidgets as $slug => $widget)
            <div class="jv-widget-card {{ $widget['color'] ?? 'blue' }} {{ $widget['size'] ?? 'small' }}" data-widget="{{ $slug }}">
                {!! call_user_func($widget['callback']) !!}
            </div>
        @endforeach
    </div>
    <form id="widgetSettings" class="hidden" action="{{ route('admin.dashboard.widgets.save') }}" method="POST" style="margin-top:14px;">
        @csrf
        <div id="widgetSettingsRows">
            @foreach($availableWidgets as $slug => $widget)
                <div class="widget-settings-row" draggable="true">
                    <span class="drag-handle">::</span>
                    <input type="hidden" name="widgets[{{ $slug }}][position]" value="{{ $widget['position'] ?? $loop->index }}">
                    <label class="checkbox-group"><input type="checkbox" name="widgets[{{ $slug }}][enabled]" value="1" {{ ($widget['enabled'] ?? true) ? 'checked' : '' }}><span>{{ $widget['title'] ?: $slug }}</span></label>
                    <select name="widgets[{{ $slug }}][size]" class="form-select">
                        @foreach(['small'=>'Small','medium'=>'Medium','large'=>'Large','full'=>'Full'] as $value => $label)<option value="{{ $value }}" {{ ($widget['size'] ?? 'small') === $value ? 'selected' : '' }}>{{ $label }}</option>@endforeach
                    </select>
                    <select name="widgets[{{ $slug }}][color]" class="form-select">
                        @foreach(['blue'=>'Blue','green'=>'Green','amber'=>'Amber','purple'=>'Purple','slate'=>'Slate','rose'=>'Rose'] as $value => $label)<option value="{{ $value }}" {{ ($widget['color'] ?? 'blue') === $value ? 'selected' : '' }}>{{ $label }}</option>@endforeach
                    </select>
                    <select name="widgets[{{ $slug }}][column]" class="form-select">
                        @foreach(['main'=>'Main','side'=>'Side','full'=>'Full'] as $value => $label)<option value="{{ $value }}" {{ ($widget['column'] ?? 'main') === $value ? 'selected' : '' }}>{{ $label }}</option>@endforeach
                    </select>
                    <span class="pill pill-mute">{{ $widget['plugin'] ?: 'core' }}</span>
                </div>
            @endforeach
        </div>
        <div style="display:flex;justify-content:flex-end;margin-top:12px;"><button class="btn btn-primary">Save Widgets</button></div>
    </form>
</div>

@if(collect($gettingStarted ?? [])->contains(fn ($item) => !$item['done']))
<div class="dash-card" style="margin-bottom:1.5rem;">
    <div class="dash-card-head"><h3>{{ jv_icon('list-check', '', 18) }} Getting Started</h3><span class="pill pill-info">{{ collect($gettingStarted)->where('done', true)->count() }}/{{ count($gettingStarted) }} done</span></div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:10px;">
        @foreach($gettingStarted as $item)
            <a href="{{ \Illuminate\Support\Facades\Route::has($item['route']) ? route($item['route']) : '#' }}" style="display:flex;gap:10px;align-items:center;padding:12px;border:1px solid var(--jv-gray-200);border-radius:8px;text-decoration:none;color:inherit;background:{{ $item['done'] ? '#f0fdf4' : '#fff' }};">
                <span>{{ jv_icon($item['done'] ? 'check-circle' : 'circle', '', 16) }}</span>
                <strong style="font-size:.9rem;">{{ $item['label'] }}</strong>
            </a>
        @endforeach
    </div>
</div>
@endif

<div class="grid-2">
    <div class="dash-card">
        <div class="dash-card-head">
            <h3>Business Snapshot</h3>
            <span class="pill pill-ok">Today</span>
        </div>
        <div style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:1rem;text-align:center;padding:10px 0 20px;border-bottom:1px solid var(--jv-gray-100);">
            <div><strong style="font-size:1.4rem;">{{ $stats['total_clients'] ?? 0 }}</strong><br><small>clients</small></div>
            <div><strong style="font-size:1.4rem;">{{ $stats['active_clients'] ?? 0 }}</strong><br><small>active clients</small></div>
            <div><strong style="font-size:1.4rem;">{{ $stats['active_domains'] ?? 0 }}</strong><br><small>active domains</small></div>
            <div><strong style="font-size:1.4rem;">{{ $stats['catalog_services'] ?? 0 }}</strong><br><small>catalog items</small></div>
        </div>
        <div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:1rem;margin-top:18px;">
            <div style="padding:14px;border:1px solid var(--jv-gray-200);border-radius:8px;"><small>All-time revenue</small><br><strong>{{ jv_format_money($stats['total_revenue'] ?? 0) }}</strong></div>
            <div style="padding:14px;border:1px solid var(--jv-gray-200);border-radius:8px;"><small>New clients this month</small><br><strong>{{ $stats['new_clients_month'] ?? 0 }}</strong></div>
            <div style="padding:14px;border:1px solid var(--jv-gray-200);border-radius:8px;"><small>Suspended services</small><br><strong>{{ $stats['suspended_services'] ?? 0 }}</strong></div>
        </div>
    </div>

    <div class="dash-card">
        <div class="dash-card-head"><h3>Quick Actions</h3></div>
        <div class="qa-grid">
            <a href="{{ route('admin.clients.create') }}" class="qa"><span class="qa-ico">{{ jv_icon('user-plus') }}</span>Add Client</a>
            <a href="{{ route('admin.orders.create') }}" class="qa"><span class="qa-ico">{{ jv_icon('shopping-cart') }}</span>Create Order</a>
            <a href="{{ route('admin.invoices.create') }}" class="qa"><span class="qa-ico">{{ jv_icon('file-text') }}</span>Create Invoice</a>
            <a href="{{ route('admin.services.create') }}" class="qa"><span class="qa-ico">{{ jv_icon('package-plus') }}</span>New Service</a>
            <a href="{{ route('admin.domains.index') }}" class="qa"><span class="qa-ico">{{ jv_icon('globe') }}</span>Domains</a>
            <a href="{{ route('admin.settings.index') }}" class="qa"><span class="qa-ico">{{ jv_icon('settings') }}</span>Settings</a>
        </div>
        <div style="margin-top:1rem;display:grid;gap:10px;">
            @if(($stats['domains_expiring_7'] ?? 0) > 0)
                <div style="padding:.85rem;background:#fff7ed;border:1px solid #fed7aa;border-radius:8px;"><strong>{{ $stats['domains_expiring_7'] }} domain(s) expire within 7 days</strong></div>
            @endif
            @if(($stats['expired_domains'] ?? 0) > 0)
                <div style="padding:.85rem;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;"><strong>{{ $stats['expired_domains'] }} expired domain(s) need review</strong></div>
            @endif
            @if(($stats['overdue_invoices'] ?? 0) > 0)
                <div style="padding:.85rem;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;"><strong>{{ $stats['overdue_invoices'] }} overdue invoice(s), {{ jv_format_money($stats['overdue_amount'] ?? 0) }}</strong></div>
            @endif
        </div>
    </div>
</div>

<div class="grid-2">
    <div class="dash-card">
        <div class="dash-card-head"><h3>Overdue Invoices</h3><a href="{{ route('admin.invoices.index') }}" style="font-size:.85rem;color:var(--jv-primary);font-weight:700;text-decoration:none;">View all</a></div>
        @forelse($overdueInvoices as $invoice)
            <div class="feed-item" style="padding:10px 0;border-bottom:1px solid #f1f5f9;">
                <div style="flex:1;"><strong>#{{ $invoice->invoice_number }}</strong><small style="display:block;color:var(--jv-gray-500);">{{ $invoice->client->full_name ?? 'No client' }} · due {{ $invoice->due_date?->format('M d, Y') ?: '-' }}</small></div>
                <strong>{{ jv_format_money($invoice->total) }}</strong>
            </div>
        @empty
            <p style="color:var(--jv-gray-500);text-align:center;padding:20px;">No overdue invoices.</p>
        @endforelse
    </div>

    <div class="dash-card">
        <div class="dash-card-head"><h3>Pending Orders</h3><a href="{{ route('admin.orders.index') }}" style="font-size:.85rem;color:var(--jv-primary);font-weight:700;text-decoration:none;">View all</a></div>
        @forelse($pendingOrders as $order)
            <div class="feed-item" style="padding:10px 0;border-bottom:1px solid #f1f5f9;">
                <div style="flex:1;"><strong>#{{ $order->order_number ?? $order->id }}</strong><small style="display:block;color:var(--jv-gray-500);">{{ $order->client->full_name ?? 'No client' }} · {{ $order->created_at?->diffForHumans() }}</small></div>
                <strong>{{ jv_format_money($order->total ?? 0) }}</strong>
            </div>
        @empty
            <p style="color:var(--jv-gray-500);text-align:center;padding:20px;">No pending orders.</p>
        @endforelse
    </div>
</div>

<div class="grid-2">
    <div class="dash-card">
        <div class="dash-card-head"><h3>Recent Clients</h3><a href="{{ route('admin.clients.index') }}" style="font-size:.85rem;color:var(--jv-primary);font-weight:700;text-decoration:none;">View all</a></div>
        @forelse($recentClients as $client)
            <div class="feed-item" style="padding:10px 0;border-bottom:1px solid #f1f5f9;">
                <div class="avatar" style="width:30px;height:30px;border-radius:50%;background:var(--jv-primary);color:#fff;display:grid;place-items:center;font-size:.75rem;font-weight:700;flex-shrink:0;">{{ strtoupper(substr($client->first_name,0,1).substr($client->last_name,0,1)) }}</div>
                <div style="flex:1;"><strong style="font-size:.88rem">{{ $client->full_name }}</strong><small style="display:block;color:var(--jv-gray-500);">{{ $client->services_count ?? 0 }} services · {{ $client->domains_count ?? 0 }} domains</small></div>
                <strong>{{ jv_format_money($client->outstanding_balance ?? 0) }}</strong>
            </div>
        @empty
            <p style="color:var(--jv-gray-500);text-align:center;padding:20px;">No clients yet.</p>
        @endforelse
    </div>

    <div class="dash-card">
        <div class="dash-card-head"><h3>Domains Expiring Soon</h3><span class="pill pill-warn">{{ $stats['domains_expiring'] ?? 0 }} expiring</span></div>
        @forelse($expiringDomains as $domain)
            <div class="server-row">
                <div class="top"><span>{{ $domain->domain_name }}</span><small>{{ $domain->client->full_name ?? 'N/A' }} · {{ $domain->expiry_date?->format('M d, Y') }}</small></div>
                @php $days = max(0, (int) ($domain->days_until_expiry ?? 0)); @endphp
                <div class="bar {{ $days <= 7 ? 'bad' : 'warn' }}"><span style="width: {{ max(5, min(100, (30 - $days) / 30 * 100)) }}%"></span></div>
            </div>
        @empty
            <p style="color:var(--jv-gray-500);text-align:center;padding:20px;">No domains expiring within 30 days.</p>
        @endforelse
    </div>
</div>

<div class="dash-card">
    <div class="dash-card-head"><h3>Recent Activity</h3></div>
    <div class="activity-feed">
        @forelse($activities as $activity)
            <div class="feed-item">
                <div class="feed-ico" style="background:#ede9fe;color:#6C5CE7">{{ jv_icon('activity') }}</div>
                <div><p>{{ $activity->description }}</p><small>{{ \Carbon\Carbon::parse($activity->created_at)->diffForHumans() }}</small></div>
            </div>
        @empty
            <div class="feed-item"><div class="feed-ico" style="background:#dcfce7;color:#16a34a">{{ jv_icon('check-circle') }}</div><div><p>System is ready. Start adding clients, services, and orders.</p><small>Just now</small></div></div>
        @endforelse
    </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const rows = document.getElementById('widgetSettingsRows');
    if (!rows) return;
    let dragged = null;
    rows.addEventListener('dragstart', event => {
        dragged = event.target.closest('.widget-settings-row');
    });
    rows.addEventListener('dragover', event => {
        event.preventDefault();
        const target = event.target.closest('.widget-settings-row');
        if (!dragged || !target || dragged === target) return;
        const rect = target.getBoundingClientRect();
        rows.insertBefore(dragged, event.clientY > rect.top + rect.height / 2 ? target.nextSibling : target);
        Array.from(rows.querySelectorAll('.widget-settings-row')).forEach((row, index) => {
            const input = row.querySelector('input[type="hidden"]');
            if (input) input.value = index + 1;
        });
    });
})();
</script>
@endpush
