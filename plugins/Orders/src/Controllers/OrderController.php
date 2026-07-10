<?php

namespace Plugins\Orders\src\Controllers;

use App\Http\Controllers\Controller;
use App\Core\ActivityLogger;
use Plugins\Orders\src\Models\Order;
use Plugins\Orders\src\Models\OrderItem;
use Plugins\Clients\src\Models\Client;
use Plugins\Domains\src\Models\Domain;
use Plugins\Services\src\Models\ClientService;
use Plugins\Services\src\Models\Service;
use App\Core\Provisioning\ProvisioningManager;
use Illuminate\Http\Request;
use App\Core\Hooks\Action;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::with(['client', 'invoice'])
            ->when($request->search, fn($q, $s) => $q->where(function ($query) use ($s) {
                $query->where('order_number', 'like', "%{$s}%")
                    ->orWhere('external_id', 'like', "%{$s}%")
                    ->orWhereHas('client', fn($c) => $c->where('first_name', 'like', "%{$s}%")
                        ->orWhere('last_name', 'like', "%{$s}%")
                        ->orWhere('email', 'like', "%{$s}%")
                        ->orWhere('company_name', 'like', "%{$s}%"));
            }))
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->provisioning_status, fn($q, $s) => $q->where('provisioning_status', $s))
            ->latest()
            ->paginate(15);

        $stats = [
            'total' => Order::count(),
            'pending' => Order::pending()->count(),
            'accepted' => Order::accepted()->count(),
            'completed' => Order::completed()->count(),
            'today' => Order::whereDate('created_at', today())->count(),
            'pending_amount' => Order::where('status', 'pending')->sum('total'),
            'month_total' => Order::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->sum('total'),
            'needs_provisioning' => Order::whereIn('provisioning_status', ['not_started', 'pending', 'in_progress'])->whereIn('status', ['accepted', 'processing'])->count(),
        ];

        return view('plugins.Orders::admin.index', compact('orders', 'stats'));
    }

    public function create()
    {
        $clients = Client::orderBy('first_name')->get();
        $services = Service::active()->with('group')->get()->groupBy('group.name');
        
        // Get all TLD configurations with pricing
        $tldConfigs = \Plugins\Domains\src\Models\DomainTld::with(['pricing', 'addons'])->get()->map(function($tld) {
            return [
                'tld' => $tld->tld,
                'service_id' => $tld->service_id,
                'pricing' => $tld->pricing->map(fn($p) => [
                    'years' => $p->years,
                    'register_price' => $p->register_price,
                    'renewal_price' => $p->renewal_price,
                    'transfer_price' => $p->transfer_price,
                ]),
                'dns_management' => $tld->dns_management,
                'email_forwarding' => $tld->email_forwarding,
                'id_protection' => $tld->id_protection,
                'epp_code' => $tld->epp_code,
                'auto_register' => $tld->auto_register,
            ];
        });
        
        return view('plugins.Orders::admin.create', compact('clients', 'services', 'tldConfigs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'status' => 'required|in:pending,accepted',
            'payment_method' => 'nullable|string|max:100',
            'source' => 'nullable|string|max:100',
            'external_id' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'client_notes' => 'nullable|string',
            'admin_notes' => 'nullable|string',
            'generate_invoice' => 'nullable|boolean',
            'admingenerateinvoice' => 'nullable',
            'items' => 'required|array|min:1',
            'items.*.type' => 'required|string',
            'items.*.description' => 'required|string',
            'items.*.domain' => 'nullable|string',
            'items.*.service_id' => 'nullable|exists:services,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.price_override' => 'nullable|numeric|min:0',
            'items.*.billing_cycle' => 'nullable|string',
            'items.*.years' => 'nullable|integer',
            'items.*.reg_type' => 'nullable|string',
            'items.*.epp_code' => 'nullable|string',
            'items.*.dns_management' => 'nullable|boolean',
            'items.*.email_forwarding' => 'nullable|boolean',
            'items.*.id_protection' => 'nullable|boolean',
        ]);

        foreach ($validated['items'] as $index => $item) {
            if (($item['type'] ?? null) === 'hosting' && empty($item['domain'])) {
                return back()
                    ->withErrors(["items.{$index}.domain" => 'A domain name is required for hosting services.'])
                    ->withInput();
            }
        }

        // Calculate totals
        $subtotal = 0; $taxAmount = 0;
        $taxRate = jv_tax_rate();
        foreach ($validated['items'] as $item) {
            $unitPrice = $item['price_override'] ?? $item['unit_price'];
            $itemTotal = $item['quantity'] * $unitPrice;
            $itemTax = $itemTotal * ($taxRate / 100);
            $subtotal += $itemTotal;
            $taxAmount += $itemTax;
        }

        $order = Order::create([
            'client_id' => $validated['client_id'],
            'order_number' => $this->generateOrderNumber(),
            'status' => $validated['status'],
            'currency' => Setting::get('currency', 'TZS'),
            'source' => $validated['source'] ?? 'admin',
            'external_id' => $validated['external_id'] ?? null,
            'payment_method' => $validated['payment_method'] ?? null,
            'provisioning_status' => $validated['status'] === 'accepted' ? 'pending' : 'not_started',
            'ordered_at' => now(),
            'subtotal' => $subtotal,
            'discount' => 0,
            'tax_amount' => $taxAmount,
            'total' => $subtotal + $taxAmount,
            'notes' => $validated['notes'] ?? null,
            'client_notes' => $validated['client_notes'] ?? null,
            'admin_notes' => $validated['admin_notes'] ?? null,
            'ip_address' => $request->ip(),
        ]);

        foreach ($validated['items'] as $item) {
            $unitPrice = $item['price_override'] ?? $item['unit_price'];
            $itemTotal = $item['quantity'] * $unitPrice;
            $itemTax = $itemTotal * ($taxRate / 100);
            OrderItem::create([
                'order_id' => $order->id,
                'service_id' => $item['service_id'] ?? null,
                'type' => $item['type'],
                'description' => $item['description'],
                'domain' => $item['domain'] ?? null,
                'quantity' => $item['quantity'],
                'unit_price' => $unitPrice,
                'tax_rate' => $taxRate,
                'total' => $itemTotal + $itemTax,
                'billing_cycle' => $item['billing_cycle'] ?? null,
                'years' => $item['years'] ?? null,
                'options' => collect([
                    'registration_type' => $item['reg_type'] ?? null,
                    'epp_code' => $item['epp_code'] ?? null,
                    'dns_management' => (bool) ($item['dns_management'] ?? false),
                    'email_forwarding' => (bool) ($item['email_forwarding'] ?? false),
                    'id_protection' => (bool) ($item['id_protection'] ?? false),
                ])->filter(fn ($value) => $value !== null && $value !== false)->all(),
                'status' => $validated['status'] === 'accepted' ? 'active' : 'pending',
            ]);
        }

        $shouldGenerateInvoice = $request->has('generate_invoice')
            ? $request->boolean('generate_invoice')
            : $request->has('admingenerateinvoice');

        if ($order->status === 'accepted' && $shouldGenerateInvoice) {
            $this->generateInvoice($order);
        }

        Action::do('order.created', $order);

        return redirect()->route('admin.orders.show', $order)
            ->with('success', 'Order #' . $order->order_number . ' created!');
    }

    public function show(Order $order)
    {
        $order->load(['client', 'items.service.group', 'items.clientService', 'items.domainRecord', 'invoice.transactions', 'acceptedBy']);
        $provisioningLogs = DB::table('activity_logs')
            ->where('entity_type', 'Order')
            ->where('entity_id', $order->id)
            ->where('action', 'like', 'provisioning.%')
            ->orderBy('created_at')
            ->get();

        return view('plugins.Orders::admin.show', compact('order', 'provisioningLogs'));
    }

    public function edit(Order $order)
    {
        $clients = Client::orderBy('first_name')->get();
        $order->load(['items', 'client']);
        return view('plugins.Orders::admin.edit', compact('order', 'clients'));
    }

    public function update(Request $request, Order $order)
    {
        if ($order->status === 'completed') {
            return back()->with('error', 'Cannot edit completed orders.');
        }

        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'notes' => 'nullable|string',
            'admin_notes' => 'nullable|string',
            'client_notes' => 'nullable|string',
            'payment_method' => 'nullable|string|max:100',
            'source' => 'nullable|string|max:100',
            'external_id' => 'nullable|string|max:100',
            'provisioning_status' => 'nullable|in:not_started,pending,in_progress,completed,failed,cancelled',
        ]);

        $order->update($validated);
        return redirect()->route('admin.orders.show', $order)->with('success', 'Order updated!');
    }

    public function destroy(Order $order)
    {
        if (in_array($order->status, ['completed'], true)) {
            return back()->with('error', 'Completed orders cannot be cancelled.');
        }

        $order->update([
            'status' => 'cancelled',
            'provisioning_status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => request('reason'),
        ]);
        $order->items()->update(['status' => 'cancelled']);

        return redirect()->route('admin.orders.index')->with('success', 'Order cancelled!');
    }

    // Status actions
    public function accept(Order $order)
    {
        if ($order->status !== 'pending') {
            return back()->with('error', 'Only pending orders can be accepted.');
        }

        $order->update([
            'status' => 'accepted',
            'provisioning_status' => 'pending',
            'accepted_by' => auth('admin')->id(),
            'accepted_at' => now(),
        ]);

        // Update items status
        $order->items()->update(['status' => 'active']);

        $this->generateInvoice($order);

        Action::do('order.accepted', $order);

        return redirect()->route('admin.orders.show', $order)
            ->with('success', 'Order accepted and invoice generated!');
    }

    public function generateInvoiceForOrder(Order $order)
    {
        if ($order->invoice_id) {
            return redirect()->route('admin.orders.show', $order)
                ->with('success', 'Order already has an invoice.');
        }

        $this->generateInvoice($order);

        return redirect()->route('admin.orders.show', $order)
            ->with('success', 'Invoice generated for order #' . $order->order_number . '.');
    }

    public function reject(Order $order)
    {
        if ($order->status !== 'pending') {
            return back()->with('error', 'Only pending orders can be rejected.');
        }

        $order->update([
            'status' => 'rejected',
            'provisioning_status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => request('reason'),
        ]);
        $order->items()->update(['status' => 'cancelled']);

        Action::do('order.rejected', $order);

        return redirect()->route('admin.orders.show', $order)
            ->with('success', 'Order rejected.');
    }

    public function complete(Order $order)
    {
        $result = $this->completePaidOrder($order, 'manual');

        if (!$result['success']) {
            return redirect()->route('admin.orders.show', $order)
                ->with('error', $result['message']);
        }

        return redirect()->route('admin.orders.show', $order)
            ->with('success', $result['message']);
    }

    public function completePaidOrder(Order $order, string $trigger = 'manual'): array
    {
        $order->loadMissing('invoice', 'items', 'client');

        if (in_array($order->status, ['completed'], true)) {
            return ['success' => true, 'message' => 'Order already completed.'];
        }

        if (in_array($order->status, ['cancelled', 'rejected'], true)) {
            return ['success' => false, 'message' => 'Cancelled or rejected orders cannot be provisioned.'];
        }

        if ($order->invoice && $order->invoice->status !== 'paid') {
            return ['success' => false, 'message' => 'Invoice must be paid before provisioning starts.'];
        }

        if ($order->status !== 'accepted') {
            $order->update([
                'status' => 'accepted',
                'provisioning_status' => 'pending',
                'accepted_at' => $order->accepted_at ?: now(),
            ]);
            $order->items()->update(['status' => 'active']);
            Action::do('order.accepted', $order->fresh(['client']));
        }

        $order->update(['provisioning_status' => 'in_progress']);
        ActivityLogger::log('provisioning.started', 'Order', $order->id, 'Provisioning started for order #' . $order->order_number, [
            'order_id' => $order->id,
            'trigger' => $trigger,
            'items' => $order->items()->count(),
        ]);

        $failures = $this->createLocalProvisioningRecords($order->fresh(['client', 'items.service.servers']));

        if (!empty($failures)) {
            $order->update(['provisioning_status' => 'failed']);
            ActivityLogger::log('provisioning.failed', 'Order', $order->id, 'Provisioning failed for order #' . $order->order_number, [
                'trigger' => $trigger,
                'failures' => $failures,
            ]);

            return [
                'success' => false,
                'message' => 'Provisioning needs attention: ' . implode(' ', $failures),
            ];
        }

        $order->update([
            'status' => 'completed',
            'provisioning_status' => 'completed',
            'completed_at' => now(),
        ]);

        ActivityLogger::log('provisioning.completed', 'Order', $order->id, 'Provisioning completed for order #' . $order->order_number, [
            'order_id' => $order->id,
            'trigger' => $trigger,
        ]);

        Action::do('order.completed', $order->fresh(['client', 'items']));

        return ['success' => true, 'message' => 'Order completed! Services provisioned.'];
    }

    public function retryProvisioning(Order $order)
    {
        $order->loadMissing('client', 'items.clientService', 'items.service.servers');
        $failures = [];
        $attempted = 0;

        ActivityLogger::log('provisioning.retry.started', 'Order', $order->id, 'Provisioning retry started for order #' . $order->order_number, [
            'order_id' => $order->id,
        ]);

        foreach ($order->items as $item) {
            if ($item->type !== 'hosting' || !$item->service_id) {
                continue;
            }

            $clientService = $item->clientService ?: ClientService::firstOrCreate(
                [
                    'client_id' => $order->client_id,
                    'service_id' => $item->service_id,
                    'domain' => $item->domain,
                ],
                [
                    'price' => $item->unit_price,
                    'billing_cycle' => $item->billing_cycle ?: $item->service?->billing_cycle ?: 'monthly',
                    'registered_date' => now(),
                    'next_due_date' => $this->nextDueDate($item->billing_cycle ?: $item->service?->billing_cycle),
                    'status' => 'active',
                    'notes' => 'Created from provisioning retry for order #' . $order->order_number,
                ]
            );

            $attempted++;
            $result = $this->provisionRemoteHostingAccount($order, $item, $clientService, false);

            $item->update([
                'client_service_id' => $clientService->id,
                'status' => $result['success'] ? 'provisioned' : 'failed',
                'provisioned_at' => $result['success'] ? now() : null,
                'provisioning_notes' => $result['message'],
            ]);

            if (!$result['success']) {
                $failures[] = $item->description . ': ' . $result['message'];
            }
        }

        if ($attempted === 0) {
            return redirect()->route('admin.orders.show', $order)
                ->with('error', 'No hosting items found for provisioning retry.');
        }

        if (!empty($failures)) {
            $order->update(['provisioning_status' => 'failed']);

            return redirect()->route('admin.orders.show', $order)
                ->with('error', 'Provisioning retry failed: ' . implode(' ', $failures));
        }

        $order->update(['provisioning_status' => 'completed']);

        ActivityLogger::log('provisioning.retry.completed', 'Order', $order->id, 'Provisioning retry completed for order #' . $order->order_number, [
            'order_id' => $order->id,
        ]);

        return redirect()->route('admin.orders.show', $order)
            ->with('success', 'Provisioning retry completed.');
    }

    protected function createLocalProvisioningRecords(Order $order): array
    {
        $order->loadMissing('client', 'items.service.servers');
        $failures = [];

        foreach ($order->items as $item) {
            if ($item->provisioned_at) {
                continue;
            }

            if ($item->type === 'domain' && $item->domain) {
                $domain = Domain::firstOrCreate(
                    ['client_id' => $order->client_id, 'domain_name' => strtolower($item->domain)],
                    [
                        'tld' => $this->extractTld($item->domain),
                        'registrar' => 'manual',
                        'registration_date' => now(),
                        'expiry_date' => now()->addYears(max(1, (int) ($item->years ?: 1))),
                        'registration_period' => max(1, (int) ($item->years ?: 1)),
                        'registration_fee' => $item->unit_price,
                        'renewal_fee' => $item->unit_price,
                        'status' => 'active',
                        'auto_renew' => true,
                        'notes' => 'Created from order #' . $order->order_number,
                    ]
                );

                $item->update([
                    'domain_id' => $domain->id,
                    'status' => 'provisioned',
                    'provisioned_at' => now(),
                    'provisioning_notes' => 'Local domain record created.',
                ]);
                ActivityLogger::log('provisioning.local_domain.created', 'Order', $order->id, 'Local domain record created for ' . $item->domain, [
                    'order_item_id' => $item->id,
                    'domain_id' => $domain->id,
                    'domain' => $item->domain,
                ]);

                continue;
            }

            if ($item->service_id) {
                $clientService = ClientService::firstOrCreate(
                    [
                        'client_id' => $order->client_id,
                        'service_id' => $item->service_id,
                        'domain' => $item->domain,
                    ],
                    [
                        'price' => $item->unit_price,
                        'billing_cycle' => $item->billing_cycle ?: $item->service?->billing_cycle ?: 'monthly',
                        'registered_date' => now(),
                        'next_due_date' => $this->nextDueDate($item->billing_cycle ?: $item->service?->billing_cycle),
                        'status' => 'active',
                        'notes' => 'Created from order #' . $order->order_number,
                    ]
                );

                $remoteResult = $this->provisionRemoteHostingAccount($order, $item, $clientService);
                $provisioned = $remoteResult['success'];
                $message = $remoteResult['message'];

                $item->update([
                    'client_service_id' => $clientService->id,
                    'status' => $provisioned ? 'provisioned' : 'failed',
                    'provisioned_at' => $provisioned ? now() : null,
                    'provisioning_notes' => trim('Local client service created. ' . $message),
                ]);

                if (!$provisioned) {
                    $failures[] = $item->description . ': ' . $message;
                }
            }
        }

        return $failures;
    }

    protected function provisionRemoteHostingAccount(Order $order, OrderItem $item, ClientService $clientService, bool $manualIsSuccess = true): array
    {
        if ($item->type !== 'hosting') {
            return ['success' => true, 'message' => 'Remote provisioning not required.', 'data' => []];
        }

        return app(ProvisioningManager::class)
            ->provision($order, $item, $clientService, $manualIsSuccess)
            ->toArray();
    }

    protected function nextDueDate(?string $billingCycle): ?\Carbon\Carbon
    {
        return match ($billingCycle) {
            'monthly' => now()->addMonthNoOverflow(),
            'quarterly' => now()->addMonthsNoOverflow(3),
            'semi_annually', 'semi-annually', 'semiannually' => now()->addMonthsNoOverflow(6),
            'annually', 'annual', 'yearly' => now()->addYear(),
            'biennially' => now()->addYears(2),
            'triennially' => now()->addYears(3),
            'one-time', 'free' => null,
            default => now()->addMonthNoOverflow(),
        };
    }

    protected function extractTld(string $domain): string
    {
        $domain = strtolower(trim($domain));
        $parts = explode('.', $domain);

        if (count($parts) >= 3 && in_array($parts[count($parts) - 2], ['co', 'or', 'go', 'ac', 'ne'], true)) {
            return '.' . $parts[count($parts) - 2] . '.' . $parts[count($parts) - 1];
        }

        return count($parts) > 1 ? '.' . end($parts) : '';
    }

    protected function generateInvoice(Order $order): void
    {
        if ($order->invoice_id) {
            return;
        }

        $prefix = trim(Setting::get('invoice_prefix', 'INV')) ?: 'INV';
        $dueDays = (int) Setting::get('invoice_due_days', '14');

        // Create invoice
        $invoice = \Plugins\Invoices\src\Models\Invoice::create([
            'client_id' => $order->client_id,
            'invoice_number' => $this->generateInvoiceNumber($prefix),
            'currency' => $order->currency,
            'subtotal' => $order->subtotal,
            'discount' => $order->discount ?? 0,
            'tax_amount' => $order->tax_amount,
            'total' => $order->total,
            'status' => 'sent',
            'source' => 'order',
            'external_id' => $order->external_id,
            'due_date' => now()->addDays($dueDays),
            'sent_at' => now(),
            'notes' => "Generated from Order #{$order->order_number}",
            'payment_terms' => Setting::get('invoice_payment_terms', null),
        ]);

        // Create invoice items from order items
        foreach ($order->items as $item) {
            $invoice->items()->create([
                'service_id' => $item->service_id,
                'type' => $item->type,
                'description' => $item->description,
                'domain' => $item->domain,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'tax_rate' => $item->tax_rate,
                'total' => $item->total,
                'billing_cycle' => $item->billing_cycle,
                'metadata' => $item->options,
            ]);
        }

        // Link invoice to order
        $order->update(['invoice_id' => $invoice->id]);

        Action::do('invoice.created', $invoice);
    }

    protected function generateOrderNumber(): string
    {
        $prefix = 'ORD-' . now()->format('Y') . '-';
        $sequence = Order::withTrashed()->where('order_number', 'like', $prefix . '%')->count() + 1;

        do {
            $number = $prefix . str_pad((string) $sequence++, 5, '0', STR_PAD_LEFT);
        } while (Order::withTrashed()->where('order_number', $number)->exists());

        return $number;
    }

    protected function generateInvoiceNumber(string $prefix): string
    {
        $base = $prefix . '-' . now()->format('Y') . '-';
        $sequence = \Plugins\Invoices\src\Models\Invoice::withTrashed()->where('invoice_number', 'like', $base . '%')->count() + 1;

        do {
            $number = $base . str_pad((string) $sequence++, 5, '0', STR_PAD_LEFT);
        } while (\Plugins\Invoices\src\Models\Invoice::withTrashed()->where('invoice_number', $number)->exists());

        return $number;
    }
}
