@extends('themes.default::layouts.admin')

@section('title', 'Order #' . $order->order_number)
@section('breadcrumbs')<a href="{{ route('admin.orders.index') }}">Orders</a> <span class="separator">/</span> <span class="current">#{{ $order->order_number }}</span>@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">
            Order #{{ $order->order_number }}
            <span class="pill pill-{{ $order->status === 'accepted' || $order->status === 'completed' ? 'ok' : ($order->status === 'rejected' ? 'bad' : 'warn') }}" style="margin-left: 8px;">
                {{ $order->status === 'accepted' ? 'Provisioning' : ucfirst($order->status) }}
            </span>
        </h1>
        <p class="page-subtitle">Placed {{ $order->created_at->format('M d, Y · H:i') }} by {{ $order->client->full_name }}</p>
    </div>
    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
        @if($order->status === 'pending')
            <form action="{{ route('admin.orders.accept', $order) }}" method="POST">@csrf <button class="btn btn-success">✅ Accept & Provision</button></form>
            <form action="{{ route('admin.orders.reject', $order) }}" method="POST" data-confirm="Reject this order?" data-danger="true">@csrf <button class="btn btn-outline-danger">✖ Reject</button></form>
        @elseif($order->status === 'accepted' && $order->invoice && $order->invoice->status === 'paid')
            <form action="{{ route('admin.orders.complete', $order) }}" method="POST">@csrf <button class="btn btn-success">🎉 Complete Order</button></form>
        @endif
        @if(in_array($order->status, ['accepted', 'completed'], true))
            <form action="{{ route('admin.orders.retry-provisioning', $order) }}" method="POST" data-confirm="Retry remote provisioning for this order?">@csrf <button class="btn btn-outline-primary">Retry Provisioning</button></form>
        @endif
        @if(!$order->invoice && !in_array($order->status, ['rejected', 'cancelled'], true))
            <form action="{{ route('admin.orders.generate-invoice', $order) }}" method="POST">@csrf <button class="btn btn-outline-primary">Generate Invoice</button></form>
        @endif
        @if(!in_array($order->status, ['completed', 'cancelled'], true))
            <a href="{{ route('admin.orders.edit', $order) }}" class="btn btn-outline-primary">Edit</a>
        @endif
        @if($order->invoice)
            <a href="{{ route('admin.invoices.show', $order->invoice) }}" class="btn btn-outline-primary">📄 View Invoice</a>
        @endif
    </div>
</div>

{{-- Status Steps --}}
<div class="dash-card" style="margin-bottom: 1.5rem;">
    <div class="steps" style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 0; text-align: center;">
        @php
            $steps = [
                ['label' => 'Order Placed', 'date' => $order->created_at->format('M d, H:i'), 'done' => true],
                ['label' => 'Accepted', 'date' => $order->accepted_at?->format('M d, H:i') ?? 'Pending', 'done' => in_array($order->status, ['accepted', 'completed'], true), 'active' => $order->status === 'pending'],
                ['label' => 'Invoice', 'date' => $order->invoice ? ('#' . $order->invoice->invoice_number) : 'Pending', 'done' => (bool) $order->invoice, 'active' => in_array($order->status, ['accepted'], true) && !$order->invoice],
                ['label' => 'Payment', 'date' => $order->invoice && $order->invoice->status === 'paid' ? $order->invoice->paid_at?->format('M d, H:i') : 'Pending', 'done' => $order->invoice && $order->invoice->status === 'paid', 'active' => $order->invoice && !in_array($order->invoice->status, ['paid', 'cancelled'], true)],
                ['label' => 'Provisioned', 'date' => $order->status === 'completed' ? 'Completed' : ucfirst(str_replace('_', ' ', $order->provisioning_status ?? 'pending')), 'done' => $order->status === 'completed', 'active' => $order->provisioning_status === 'in_progress'],
            ];
        @endphp
        @foreach($steps as $i => $step)
        <div class="step {{ $step['done'] ? 'done' : '' }} {{ $step['active'] ?? false ? 'active' : '' }}" style="position: relative; padding: 1rem .5rem .25rem;">
            <div class="dot" style="width: 32px; height: 32px; border-radius: 50%; margin: 0 auto 6px; display: grid; place-items: center; font-weight: 700; font-size: .85rem;
                {{ $step['done'] ? 'background: #16a34a; color: #fff;' : ($step['active'] ?? false ? 'background: var(--jv-primary); color: #fff; box-shadow: 0 0 0 4px #ede9fe;' : 'background: #e5e7eb; color: #64748b;') }}"
            >{{ $step['done'] ? '✓' : $i + 1 }}</div>
            <div class="lbl" style="font-size: .82rem; font-weight: 600;">{{ $step['label'] }}</div>
            <small style="color: #64748b; font-size: .72rem; display: block;">{{ $step['date'] }}</small>
        </div>
        @endforeach
    </div>
    <style>
        .step:not(:last-child)::after {
            content: ""; position: absolute; top: 28px; left: 60%; right: -40%; height: 2px; background: #e5e7eb;
        }
        .step.done:not(:last-child)::after { background: #16a34a; }
        @media (max-width: 900px) { .steps { grid-template-columns: repeat(2, 1fr) !important; gap: .5rem !important; } .step::after { display: none; } }
    </style>
</div>

{{-- Meta Cards --}}
<div class="grid-meta" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 1.5rem;">
    <div class="meta" style="background: #f8fafc; border: 1px solid var(--jv-gray-200); border-radius: 10px; padding: .85rem 1rem;">
        <div class="label" style="color: #64748b; font-size: .7rem; text-transform: uppercase; font-weight: 600;">Order Total</div>
        <div class="value" style="font-weight: 700; font-size: .95rem; margin-top: .2rem;">{{ jv_format_money($order->total) }}</div>
    </div>
    <div class="meta" style="background: #f8fafc; border: 1px solid var(--jv-gray-200); border-radius: 10px; padding: .85rem 1rem;">
        <div class="label" style="color: #64748b; font-size: .7rem; text-transform: uppercase; font-weight: 600;">Payment Status</div>
        <div class="value" style="font-weight: 700; font-size: .95rem; margin-top: .2rem;">
            <span class="pill pill-{{ $order->invoice && $order->invoice->status === 'paid' ? 'ok' : 'warn' }}">{{ $order->invoice ? ucfirst($order->invoice->status) : 'Unpaid' }}</span>
        </div>
    </div>
    <div class="meta" style="background: #f8fafc; border: 1px solid var(--jv-gray-200); border-radius: 10px; padding: .85rem 1rem;">
        <div class="label" style="color: #64748b; font-size: .7rem; text-transform: uppercase; font-weight: 600;">Invoice</div>
        <div class="value" style="font-weight: 700; font-size: .95rem; margin-top: .2rem;">
            @if($order->invoice) #{{ $order->invoice->invoice_number }} @else — @endif
        </div>
    </div>
    <div class="meta" style="background: #f8fafc; border: 1px solid var(--jv-gray-200); border-radius: 10px; padding: .85rem 1rem;">
        <div class="label" style="color: #64748b; font-size: .7rem; text-transform: uppercase; font-weight: 600;">Provisioning</div>
        <div class="value" style="font-weight: 700; font-size: .95rem; margin-top: .2rem;">{{ ucfirst(str_replace('_', ' ', $order->provisioning_status ?? 'not_started')) }}</div>
    </div>
</div>

{{-- Main Content --}}
<div class="grid-2" style="display: grid; grid-template-columns: 1fr 340px; gap: 1.5rem;">
    {{-- Left --}}
    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
        {{-- Line Items --}}
        <div class="dash-card order-items-card" style="padding: 0; overflow: hidden;">
            <div class="dash-card-head" style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--jv-gray-100);">
                <div>
                    <h3 style="margin:0;">Line Items</h3>
                    <small style="color:var(--jv-gray-500);">Services, domains, billing cycles, and provisioning state</small>
                </div>
            </div>
            <table class="a-table order-items-table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="text-align: left; padding: 14px 20px;">Product / Service</th>
                        <th style="text-align: left; padding: 14px 16px;">Type</th>
                        <th style="text-align: left; padding: 14px 16px;">Provisioning</th>
                        <th style="text-align: right; padding: 14px 16px;">Qty</th>
                        <th style="text-align: right; padding: 14px 16px;">Unit</th>
                        <th style="text-align: right; padding: 14px 20px;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                    @php
                        $itemStatusColor = match($item->status) {
                            'provisioned', 'active' => 'ok',
                            'failed' => 'bad',
                            'cancelled' => 'mute',
                            default => 'warn',
                        };
                    @endphp
                    <tr style="border-bottom: 1px solid #eef2f7;">
                        <td style="padding: 18px 20px; vertical-align: top;">
                            <div style="display:flex; gap:12px; align-items:flex-start;">
                                <div style="width:38px;height:38px;border-radius:10px;background:#eef2ff;color:var(--jv-primary);display:grid;place-items:center;font-weight:800;flex:0 0 auto;">
                                    {{ strtoupper(substr($item->type, 0, 1)) }}
                                </div>
                                <div>
                                    <div style="font-weight:800;color:#0f172a;">{{ $item->description }}</div>
                                    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:6px;">
                                        @if($item->domain)<span class="pill pill-info">{{ $item->domain }}</span>@endif
                                        @if($item->billing_cycle)<span class="pill pill-mute">{{ ucfirst(str_replace('_', ' ', $item->billing_cycle)) }}</span>@endif
                                        @if($item->service?->group)<span class="pill pill-mute">{{ $item->service->group->name }}</span>@endif
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td style="padding: 18px 16px; vertical-align: top;">
                            <span class="pill pill-info">{{ ucfirst($item->type) }}</span>
                            <div style="margin-top:6px;"><span class="pill pill-{{ $itemStatusColor }}">{{ ucfirst($item->status) }}</span></div>
                        </td>
                        <td style="padding: 18px 16px; vertical-align: top;">
                            @if($item->clientService)
                                <span class="pill pill-ok">Service #{{ $item->clientService->id }}</span>
                            @elseif($item->domainRecord)
                                <span class="pill pill-ok">Domain #{{ $item->domainRecord->id }}</span>
                            @else
                                <span class="pill pill-mute">Pending</span>
                            @endif
                            @if($item->provisioning_notes)
                                <div style="margin-top:8px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:8px 10px;color:#475569;font-size:.82rem;line-height:1.45;max-width:360px;">{{ $item->provisioning_notes }}</div>
                            @endif
                        </td>
                        <td style="text-align: right; padding: 18px 16px; vertical-align: top;">{{ $item->quantity }}</td>
                        <td style="text-align: right; padding: 18px 16px; vertical-align: top;">{{ jv_format_money($item->unit_price) }}</td>
                        <td style="text-align: right; padding: 18px 20px; vertical-align: top;"><strong>{{ jv_format_money($item->total) }}</strong></td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr><td colspan="5" style="text-align: right; padding: 16px 20px; font-weight: 700;">Subtotal</td><td style="text-align: right; padding: 16px 20px; font-weight: 700;">{{ jv_format_money($order->subtotal) }}</td></tr>
                    <tr><td colspan="5" style="text-align: right; padding: 10px 20px;">{{ jv_tax_label() }} ({{ rtrim(rtrim(number_format(jv_tax_rate(), 2), '0'), '.') }}%)</td><td style="text-align: right; padding: 10px 20px;">{{ jv_format_money($order->tax_amount) }}</td></tr>
                    <tr style="border-top: 2px solid var(--jv-gray-200); background:#f8fafc;"><td colspan="5" style="text-align: right; padding: 18px 20px; font-weight: 800; font-size: 1.05rem; color: var(--jv-primary);">Total</td><td style="text-align: right; padding: 18px 20px; font-weight: 800; font-size: 1.05rem; color: var(--jv-primary);">{{ jv_format_money($order->total) }}</td></tr>
                </tfoot>
            </table>
            <style>
                .order-items-table thead { background: #f8fafc; color: #475569; font-size: .76rem; text-transform: uppercase; letter-spacing: .02em; }
                .order-items-table tbody tr:hover { background: #fbfdff; }
                @media (max-width: 960px) {
                    .order-items-table { display: block; overflow-x: auto; white-space: nowrap; }
                }
            </style>
        </div>

        {{-- Internal Notes --}}
        <div class="dash-card">
            <div class="dash-card-head"><h3>Provisioning Log</h3></div>
            @if(($provisioningLogs ?? collect())->count())
                <div style="display:flex;flex-direction:column;gap:10px;">
                    @foreach($provisioningLogs as $log)
                        @php
                            $metadata = json_decode($log->metadata ?? '{}', true) ?: [];
                            $isError = str_contains($log->action, 'failed') || str_contains($log->action, 'missing');
                        @endphp
                        <div style="border:1px solid var(--jv-gray-200);border-radius:8px;padding:10px 12px;background:{{ $isError ? '#fef2f2' : '#f8fafc' }};">
                            <div style="display:flex;justify-content:space-between;gap:12px;align-items:flex-start;">
                                <div>
                                    <strong>{{ ucwords(str_replace(['provisioning.', '_', '.'], ['', ' ', ' '], $log->action)) }}</strong>
                                    <div style="font-size:.86rem;color:var(--jv-gray-600);margin-top:2px;">{{ $log->description }}</div>
                                </div>
                                <small style="color:var(--jv-gray-500);white-space:nowrap;">{{ \Carbon\Carbon::parse($log->created_at)->format('M d, H:i:s') }}</small>
                            </div>
                            @if(!empty($metadata))
                                <details style="margin-top:8px;">
                                    <summary style="cursor:pointer;color:var(--jv-primary);font-size:.82rem;">View JSON</summary>
                                    <pre style="white-space:pre-wrap;overflow:auto;max-height:320px;background:#0f172a;color:#e2e8f0;border-radius:8px;padding:10px;font-size:.76rem;margin-top:8px;">{{ json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                </details>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <p style="color:var(--jv-gray-500);margin:0;">No provisioning events recorded yet.</p>
            @endif
        </div>

        {{-- Internal Notes --}}
        <div class="dash-card">
            <div class="dash-card-head"><h3>📝 Internal Notes</h3></div>
            @if($order->notes)
            <div style="background: #fff7ed; border: 1px dashed #fdba74; padding: .85rem 1rem; border-radius: 10px; font-size: .88rem; margin-bottom: .75rem;">
                {{ $order->notes }}
            </div>
            @endif
            <form action="{{ route('admin.orders.update', $order) }}" method="POST">
                @csrf @method('PUT')
                <input type="hidden" name="client_id" value="{{ $order->client_id }}">
                <textarea name="notes" placeholder="Order notes..." style="width: 100%; min-height: 70px; padding: .7rem; border: 1px solid var(--jv-gray-200); border-radius: 8px; font-family: inherit; font-size: .9rem;">{{ $order->notes }}</textarea>
                <textarea name="admin_notes" placeholder="Private provisioning notes..." style="width: 100%; min-height: 70px; padding: .7rem; border: 1px solid var(--jv-gray-200); border-radius: 8px; font-family: inherit; font-size: .9rem; margin-top:8px;">{{ $order->admin_notes }}</textarea>
                <select name="provisioning_status" class="form-select" style="margin-top:8px;">
                    @foreach(['not_started' => 'Not Started', 'pending' => 'Pending', 'in_progress' => 'In Progress', 'completed' => 'Completed', 'failed' => 'Failed', 'cancelled' => 'Cancelled'] as $value => $label)
                        <option value="{{ $value }}" {{ ($order->provisioning_status ?? 'not_started') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <div style="text-align: right; margin-top: .5rem;"><button type="submit" class="btn btn-primary btn-sm">💾 Save Note</button></div>
            </form>
        </div>
    </div>

    {{-- Right Sidebar --}}
    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
        {{-- Customer --}}
        <div class="dash-card">
            <div class="dash-card-head"><h3>👤 Customer</h3><a href="{{ route('admin.clients.show', $order->client) }}" class="btn btn-sm btn-outline-primary">View Profile</a></div>
            <div class="client-card" style="display: flex; gap: .8rem; align-items: center; margin-bottom: 1rem;">
                <div class="av" style="width: 48px; height: 48px; border-radius: 50%; background: linear-gradient(135deg, #6C5CE7, #a78bfa); color: #fff; display: grid; place-items: center; font-weight: 700;">
                    {{ strtoupper(substr($order->client->first_name, 0, 1) . substr($order->client->last_name, 0, 1)) }}
                </div>
                <div>
                    <div style="font-weight: 700;">{{ $order->client->full_name }}</div>
                    <small>Client #{{ $order->client->id }}</small>
                </div>
            </div>
            <ul class="info-list" style="list-style: none; padding: 0; font-size: .85rem;">
                <li style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px dashed #f1f5f9;"><span class="lbl" style="color: #64748b;">Email</span><span class="val" style="font-weight: 600;">{{ $order->client->email }}</span></li>
                <li style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px dashed #f1f5f9;"><span class="lbl" style="color: #64748b;">Phone</span><span class="val" style="font-weight: 600;">{{ $order->client->phone ?? '—' }}</span></li>
                <li style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px dashed #f1f5f9;"><span class="lbl" style="color: #64748b;">Company</span><span class="val" style="font-weight: 600;">{{ $order->client->company_name ?? '—' }}</span></li>
                <li style="display: flex; justify-content: space-between; padding: 6px 0;"><span class="lbl" style="color: #64748b;">Status</span><span class="val" style="font-weight: 600;"><span class="pill pill-{{ $order->client->status === 'active' ? 'ok' : 'warn' }}">{{ ucfirst($order->client->status) }}</span></span></li>
            </ul>
        </div>

        {{-- Invoice Info --}}
        @if($order->invoice)
        <div class="dash-card">
            <div class="dash-card-head"><h3>🧾 Invoice</h3></div>
            <ul class="info-list" style="list-style: none; padding: 0; font-size: .85rem;">
                <li style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px dashed #f1f5f9;"><span class="lbl" style="color: #64748b;">Invoice #</span><span class="val" style="font-weight: 600;">#{{ $order->invoice->invoice_number }}</span></li>
                <li style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px dashed #f1f5f9;"><span class="lbl" style="color: #64748b;">Status</span><span class="val" style="font-weight: 600;"><span class="pill pill-{{ $order->invoice->status === 'paid' ? 'ok' : 'warn' }}">{{ ucfirst($order->invoice->status) }}</span></span></li>
                <li style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px dashed #f1f5f9;"><span class="lbl" style="color: #64748b;">Due Date</span><span class="val" style="font-weight: 600;">{{ $order->invoice->due_date?->format('M d, Y') ?? '—' }}</span></li>
                <li style="display: flex; justify-content: space-between; padding: 6px 0;"><span class="lbl" style="color: #64748b;">Paid At</span><span class="val" style="font-weight: 600;">{{ $order->invoice->paid_at?->format('M d, Y H:i') ?? '—' }}</span></li>
            </ul>
        </div>
        @endif

        {{-- Order Meta --}}
        <div class="dash-card">
            <div class="dash-card-head"><h3>📊 Order Info</h3></div>
            <ul class="info-list" style="list-style: none; padding: 0; font-size: .85rem;">
                <li style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px dashed #f1f5f9;"><span class="lbl" style="color: #64748b;">Order #</span><span class="val" style="font-weight: 600;">{{ $order->order_number }}</span></li>
                @if($order->external_id)<li style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px dashed #f1f5f9;"><span class="lbl" style="color: #64748b;">External ID</span><span class="val" style="font-weight: 600;">{{ $order->external_id }}</span></li>@endif
                <li style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px dashed #f1f5f9;"><span class="lbl" style="color: #64748b;">Source</span><span class="val" style="font-weight: 600;">{{ $order->source ?? 'admin' }}</span></li>
                <li style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px dashed #f1f5f9;"><span class="lbl" style="color: #64748b;">Payment</span><span class="val" style="font-weight: 600;">{{ $order->payment_method ? ucfirst($order->payment_method) : '—' }}</span></li>
                <li style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px dashed #f1f5f9;"><span class="lbl" style="color: #64748b;">Status</span><span class="val" style="font-weight: 600;"><span class="pill pill-{{ $order->status === 'accepted' ? 'ok' : 'warn' }}">{{ ucfirst($order->status) }}</span></span></li>
                <li style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px dashed #f1f5f9;"><span class="lbl" style="color: #64748b;">Accepted By</span><span class="val" style="font-weight: 600;">{{ $order->acceptedBy->name ?? '—' }}</span></li>
                <li style="display: flex; justify-content: space-between; padding: 6px 0;"><span class="lbl" style="color: #64748b;">Placed</span><span class="val" style="font-weight: 600;">{{ $order->created_at->format('M d, Y H:i') }}</span></li>
            </ul>
        </div>
    </div>
</div>
@endsection
