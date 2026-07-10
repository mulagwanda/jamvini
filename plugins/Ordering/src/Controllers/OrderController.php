<?php

namespace Plugins\Ordering\src\Controllers;

use App\Http\Controllers\Controller;
use Plugins\Ordering\src\Services\WhoisService;
use Plugins\Ordering\src\Services\CartService;
use Illuminate\Http\Request;
use App\Models\Setting;
use App\Core\Payments\PaymentGatewayRegistry;
use App\Core\Payments\PaymentManager;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    protected WhoisService $whois;
    protected CartService $cart;

    public function __construct(WhoisService $whois, CartService $cart)
    {
        $this->whois = $whois;
        $this->cart = $cart;
    }

    // Domain search page
    public function domains(Request $request)
    {
        $results = [];
        $query = $request->get('domain');
        $type = $request->get('type', 'register');

        if ($query) {
            if (str_contains($query, '.')) {
                $results[] = $this->whois->check($query);
            } else {
                $results = $this->whois->suggestions($query);
            }
        }

        return view('plugins.Ordering::public.domains', compact('results', 'query', 'type'));
    }

    // AJAX domain check
    public function checkDomain(Request $request)
{
    $domain = $request->get('domain');
    $type = $request->get('type', 'register');
    
    if (!$domain) {
        return response()->json(['error' => 'Domain required'], 400);
    }

    if ($type === 'transfer') {
        $result = $this->whois->checkTransferEligibility($domain);
        $suggestions = [];
    } else {
        $result = $this->whois->check($domain);
        
        // Get suggestions excluding the searched TLD.
        $searchedTld = !empty($result['tld']) ? '.' . $result['tld'] : '';
        
        $allSuggestions = $this->whois->suggestions($domain);
        $suggestions = [];
        foreach ($allSuggestions as $s) {
            if ($searchedTld === '' || !str_ends_with($s['domain'], $searchedTld)) {
                $suggestions[] = $s;
            }
        }
        $suggestions = array_slice($suggestions, 0, 4);
    }

    return response()->json([
        'result' => $result,
        'suggestions' => $suggestions ?? [],
        'type' => $type,
    ]);
}

    // Hosting/Services page
    public function services()
    {
        $services = \Plugins\Services\src\Models\Service::where('is_active', true)
            ->with([
                'group',
                'options' => fn ($query) => $query->where('is_active', true),
                'customFields' => fn ($query) => $query->where('is_public', true),
                'addons' => fn ($query) => $query->where('is_active', true),
            ])
            ->whereHas('group', fn($q) => $q->where('is_active', true))
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->latest()
            ->get();
        $groups = $services->groupBy(fn ($service) => $service->group?->name ?: 'Services');

        return view('plugins.Ordering::public.services', compact('services', 'groups'));
    }

    // Add to cart
    public function addToCart(Request $request)
    {
        $request->validate([
            'type' => 'required|string|max:64',
            'name' => 'required|string',
            'price' => 'required|numeric',
            'details' => 'nullable|array',
            'details.domain' => 'nullable|string|max:255',
        ]);

        $details = $request->details ?? [];
        $hostingDomain = $this->cleanDomain($request->input('details.domain'));

        if ($this->serviceRequiresDomain($request->type, $details) && !$hostingDomain) {
            return response()->json([
                'success' => false,
                'message' => 'A domain name is required for this service.',
            ], 422);
        }

        if (!empty($details['service_id'])) {
            $details['domain'] = $hostingDomain;
            $configured = $this->configuredServiceCartItem($details, $request->name);
            $details = $configured['details'];
            $name = $configured['name'];
            $price = $configured['price'];
        } else {
            $name = $request->name;
            $price = (float) $request->price;
        }
        
        $this->cart->addItem([
            'type' => $request->type,
            'name' => $name,
            'price' => $price,
            'details' => $details,
        ]);

        return response()->json([
            'success' => true,
            'count' => $this->cart->count(),
            'message' => 'Added to cart!',
        ]);
    }

    public function updateCart(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|string',
            'billing_cycle' => 'nullable|string|in:monthly,quarterly,semi_annually,annually,one-time,free',
        ]);

        $item = collect($this->cart->getItems())->firstWhere('id', $validated['id']);
        if (!$item) {
            return back()->withErrors(['cart' => 'Cart item not found.']);
        }

        if (empty($item['details']['service_id']) || empty($validated['billing_cycle'])) {
            return back()->withErrors(['cart' => 'This item cannot change billing period.']);
        }

        $details = array_merge($item['details'], ['billing_cycle' => $validated['billing_cycle']]);
        $configured = $this->configuredServiceCartItem($details, $item['name']);
        $service = \Plugins\Services\src\Models\Service::find($details['service_id']);
        $savings = $this->cycleSavingsMessage($service, $validated['billing_cycle']);

        $this->cart->updateItem($validated['id'], [
            'name' => $configured['name'],
            'price' => $configured['price'],
            'details' => $configured['details'],
        ]);

        return back()->with('success', trim('Billing period updated. ' . $savings));
    }

    // View cart
    public function cart()
    {
        $items = $this->cart->getItems();
        $total = $this->cart->total();
        $calculation = $this->cart->calculation(auth()->user());
        $coupons = $this->cart->coupons();
        return view('plugins.Ordering::public.cart', compact('items', 'total', 'calculation', 'coupons'));
    }

    public function applyCoupon(Request $request)
    {
        $validated = $request->validate(['code' => ['required', 'string', 'max:80']]);
        $code = strtoupper(trim($validated['code']));
        $this->cart->applyCoupon($code);
        $calculation = $this->cart->calculation(auth()->user());

        $message = collect($calculation['messages'] ?? [])->firstWhere('code', $code);
        if (($message['type'] ?? null) === 'error') {
            $this->cart->removeCoupon($code);
            return back()->withErrors(['coupon' => $message['message']]);
        }

        return back()->with('success', $message['message'] ?? 'Coupon applied.');
    }

    public function removeCoupon(Request $request)
    {
        $validated = $request->validate(['code' => ['required', 'string', 'max:80']]);
        $this->cart->removeCoupon($validated['code']);

        return back()->with('success', 'Coupon removed.');
    }

    // Remove from cart
    public function removeFromCart(Request $request)
    {
        $this->cart->removeItem($request->id);
        return back()->with('success', 'Item removed!');
    }

    // Checkout page
    public function checkout()
    {
        $items = $this->cart->getItems();
        $total = $this->cart->total();
        $calculation = $this->cart->calculation(auth()->user());
        $paymentGateways = PaymentGatewayRegistry::enabled();

        if (empty($items)) {
            return redirect()->route('order.domains');
        }

        return view('plugins.Ordering::public.checkout', compact('items', 'total', 'calculation', 'paymentGateways'));
    }

    public function placeOrder(Request $request)
{
    $validated = $request->validate([
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'email' => 'required|email',
        'phone' => 'required|string|min:9|max:15',
        'country_code' => 'nullable|string|max:5',
        'company_name' => 'nullable|string|max:255',
        'address' => 'nullable|string|max:500',
        'city' => 'nullable|string|max:100',
        'country' => 'nullable|string|max:100',
        'password' => 'nullable|string|min:8|confirmed',
        'payment_method' => 'nullable|string',
        'notes' => 'nullable|string|max:1000',
    ]);

    // Build full phone number
    $countryCode = $validated['country_code'] ?? '255';
    $phone = ltrim($validated['phone'], '0+');
    $fullPhone = '+' . $countryCode . $phone;

    // Find or create client
    if (auth()->check()) {
        $client = auth()->user();
        // Update contact info
        $client->update([
            'phone' => $client->phone ?? $fullPhone,
            'address' => $client->address ?? ($validated['address'] ?? null),
            'city' => $client->city ?? ($validated['city'] ?? null),
            'country' => $client->country ?? ($validated['country'] ?? 'Tanzania'),
        ]);
    } else {
        $client = \Plugins\Clients\src\Models\Client::firstOrCreate(
            ['email' => $validated['email']],
            [
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'phone' => $fullPhone,
                'company_name' => $validated['company_name'] ?? null,
                'address' => $validated['address'] ?? null,
                'city' => $validated['city'] ?? null,
                'country' => $validated['country'] ?? 'Tanzania',
                'password' => bcrypt($validated['password'] ?? Str::random(12)),
                'status' => 'active',
            ]
        );
    }

    $items = $this->cart->getItems();

    foreach ($items as $item) {
        if ($this->serviceRequiresDomain($item['type'] ?? 'custom', $item['details'] ?? []) && !$this->cleanDomain($item['details']['domain'] ?? null)) {
            return redirect()->route('order.cart')
                ->withErrors(['cart' => 'A domain name is required for every domain-bound service before checkout.']);
        }
    }

    $calculation = $this->cart->calculation($client);
    $couponError = collect($calculation['messages'] ?? [])->firstWhere('type', 'error');
    if ($couponError) {
        return redirect()->route('order.cart')->withErrors(['coupon' => $couponError['message']]);
    }

    $total = $calculation['subtotal'];
    $discountTotal = $calculation['discount_total'];
    $taxRate = $calculation['tax_rate'];
    $taxAmount = $calculation['tax_amount'];
    $grandTotal = $calculation['grand_total'];

    $autoAccept = Setting::get('orders_auto_accept_public', '1') === '1'
        || $request->has('admingenerateinvoice');
    $autoGenerateInvoice = Setting::get('orders_auto_generate_invoice', '1') === '1'
        || $autoAccept;

    // ==================== CREATE ORDER ====================
    $orderNumber = 'ORD-' . date('Y') . '-' . str_pad(\Plugins\Orders\src\Models\Order::count() + 1, 4, '0', STR_PAD_LEFT);
    
    $order = \Plugins\Orders\src\Models\Order::create([
        'client_id' => $client->id,
        'order_number' => $orderNumber,
        'status' => $autoAccept ? 'accepted' : 'pending',
        'subtotal' => $total,
        'discount' => $discountTotal,
        'tax_amount' => $taxAmount,
        'total' => $grandTotal,
        'notes' => $validated['notes'] ?? null,
        'accepted_by' => $autoAccept ? (auth('admin')->id() ?? null) : null,
        'accepted_at' => $autoAccept ? now() : null,
    ]);

    // Create order items
    foreach ($items as $item) {
        $itemType = $item['type'] ?? 'custom';
        $itemTotal = ($item['price'] ?? 0);
        $itemTax = $itemTotal * ($taxRate / 100);

        \Plugins\Orders\src\Models\OrderItem::create([
            'order_id' => $order->id,
            'service_id' => $item['details']['service_id'] ?? null,
            'type' => $itemType,
            'description' => $item['name'] ?? 'Item',
            'domain' => $item['details']['domain'] ?? null,
            'quantity' => 1,
            'unit_price' => $item['price'] ?? 0,
            'discount' => 0,
            'tax_rate' => $taxRate,
            'total' => $itemTotal + $itemTax,
            'billing_cycle' => $item['details']['billing_cycle'] ?? 'annually',
            'years' => $item['details']['years'] ?? null,
            'options' => [
                'nameservers' => $item['details']['nameservers'] ?? [],
                'epp_code' => $item['details']['epp_code'] ?? null,
                'configurable_options' => $item['details']['configurable_options'] ?? [],
                'custom_fields' => $item['details']['custom_fields'] ?? [],
                'addons' => $item['details']['addons'] ?? [],
                'base_price' => $item['details']['base_price'] ?? null,
                'option_total' => $item['details']['option_total'] ?? null,
                'addon_total' => $item['details']['addon_total'] ?? null,
            ],
            'status' => $autoAccept ? 'active' : 'pending',
        ]);
    }

    // ==================== AUTO-GENERATE INVOICE ====================
    if ($autoGenerateInvoice) {
        $invoicePrefix = trim(Setting::get('invoice_prefix', 'INV')) ?: 'INV';
        $invoiceDueDays = (int) Setting::get('invoice_due_days', '14');
        $invoiceNumber = $invoicePrefix . '-' . date('Y') . '-' . str_pad(\Plugins\Invoices\src\Models\Invoice::count() + 1, 4, '0', STR_PAD_LEFT);
        
        $invoice = \Plugins\Invoices\src\Models\Invoice::create([
            'client_id' => $client->id,
            'invoice_number' => $invoiceNumber,
            'currency' => Setting::get('currency', 'TZS'),
            'subtotal' => $total,
            'discount' => $discountTotal,
            'tax_amount' => $taxAmount,
            'total' => $grandTotal,
            'source' => 'order',
            'status' => 'sent',
            'due_date' => now()->addDays($invoiceDueDays),
            'notes' => "Generated from Order #{$orderNumber}",
        ]);

        // Create invoice items
        foreach ($order->items as $orderItem) {
            $invoice->items()->create([
                'service_id' => $orderItem->service_id,
                'type' => $orderItem->type,
                'description' => $orderItem->description,
                'domain' => $orderItem->domain,
                'quantity' => $orderItem->quantity,
                'unit_price' => $orderItem->unit_price,
                'tax_rate' => $orderItem->tax_rate,
                'total' => $orderItem->total,
                'billing_cycle' => $orderItem->billing_cycle,
                'metadata' => $orderItem->options,
            ]);
        }

        // Link invoice to order
        $order->update(['invoice_id' => $invoice->id]);
    }

    if (class_exists(\Plugins\Promotions\src\Services\PromotionCalculator::class)) {
        app(\Plugins\Promotions\src\Services\PromotionCalculator::class)
            ->record($calculation, $order, $invoice ?? null, $client);
    }

    // Fire hooks
    \App\Core\Hooks\Action::do('order.created', $order);
    if ($autoAccept) {
        \App\Core\Hooks\Action::do('order.accepted', $order);
    }

    // Log activity
    \App\Core\ActivityLogger::log('created', 'Order', $order->id, 
        "Order #{$orderNumber} placed by {$client->full_name} — " . jv_format_money($grandTotal));

    if (isset($invoice) && !empty($validated['payment_method'])) {
        $payment = app(PaymentManager::class)->initiate($invoice, $validated['payment_method'], [
            'order_id' => $order->id,
            'client_id' => $client->id,
        ]);

        if ($payment->success && $payment->status === 'pending') {
            app(PaymentManager::class)->record(
                invoice: $invoice,
                amount: (float) $invoice->remaining_amount,
                gatewaySlug: $validated['payment_method'],
                transactionId: $payment->transactionId,
                status: 'pending',
                notes: $payment->message,
                metadata: $payment->payload
            );
        }

        if ($payment->success && $payment->redirectUrl) {
            $this->cart->clear();
            return redirect()->away($payment->redirectUrl);
        }
    }

    // Clear cart
    $this->cart->clear();

    // Redirect to order confirmation
    return redirect()->route('order.confirmation', $order);
}

    /**
     * Show order confirmation page.
     */
    public function confirmation(\Plugins\Orders\src\Models\Order $order)
    {
        $order->load(['client', 'items', 'invoice']);
        return view('plugins.Ordering::public.confirmation', compact('order'));
    }

    private function extractTld(string $domain): ?string
    {
        $parts = explode('.', strtolower($domain));
        if (count($parts) < 2) return null;
        
        if (count($parts) >= 3 && in_array($parts[count($parts)-2], ['co', 'or', 'go', 'ac'])) {
            return $parts[count($parts)-2] . '.' . $parts[count($parts)-1];
        }
        
        return $parts[count($parts)-1];
    }

    private function cleanDomain(?string $domain): ?string
    {
        $domain = strtolower(trim((string) $domain));
        $domain = preg_replace('#^https?://#', '', $domain);
        $domain = trim($domain, "/ \t\n\r\0\x0B");

        return preg_match('/^[a-z0-9][a-z0-9-]*(\.[a-z0-9][a-z0-9-]*)+$/i', $domain) ? $domain : null;
    }

    private function optionPriceForCycle(array $prices, string $billingCycle): float
    {
        if (array_key_exists($billingCycle, $prices)) {
            return (float) $prices[$billingCycle];
        }

        $monthly = (float) ($prices['monthly'] ?? 0);

        return match ($billingCycle) {
            'quarterly' => $monthly * 3,
            'semi_annually' => $monthly * 6,
            'annually' => $monthly * 12,
            default => $monthly,
        };
    }

    private function configuredServiceCartItem(array $details, string $fallbackName): array
    {
        $service = \Plugins\Services\src\Models\Service::with([
            'options' => fn ($query) => $query->where('is_active', true),
            'customFields' => fn ($query) => $query->where('is_public', true),
            'addons' => fn ($query) => $query->where('is_active', true),
        ])->find($details['service_id'] ?? null);

        if (!$service) {
            abort(422, 'Selected service is no longer available.');
        }

        $availableCycles = $service->getAvailableBillingCycles();
        $billingCycle = in_array(($details['billing_cycle'] ?? 'monthly'), ['monthly', 'quarterly', 'semi_annually', 'annually', 'one-time', 'free'], true)
            ? $details['billing_cycle']
            : 'monthly';
        if (!in_array($billingCycle, $availableCycles, true) && !empty($availableCycles)) {
            $billingCycle = $availableCycles[0];
        }

        $basePrice = (float) ($service->pricing[$billingCycle] ?? $service->amount ?? 0);
        $setupFee = (float) ($service->setup_fee ?? 0);
        $selectedOptions = [];
        $optionTotal = 0.0;

        foreach (($details['configurable_options'] ?? []) as $optionId => $value) {
            $option = $service->options->firstWhere('id', (int) $optionId);
            if (!$option) {
                continue;
            }

            if ($option->type === 'dropdown' && !empty($option->options) && !in_array($value, $option->options, true)) {
                continue;
            }

            if ($option->type === 'checkbox') {
                $value = (bool) $value;
                if (!$value) {
                    continue;
                }
            }

            $price = $this->optionPriceForCycle($option->prices ?? [], $billingCycle);
            $optionTotal += $price;
            $selectedOptions[] = [
                'id' => $option->id,
                'name' => $option->name,
                'type' => $option->type,
                'value' => $value,
                'price' => $price,
            ];
        }

        $selectedAddons = [];
        $addonTotal = 0.0;
        $requestedAddonIds = collect($details['addons'] ?? [])->map(fn ($id) => (int) $id)->all();

        foreach ($service->addons as $addon) {
            if (!$addon->is_required && !in_array($addon->id, $requestedAddonIds, true)) {
                continue;
            }

            $price = (float) $addon->price;
            $addonTotal += $price;
            $selectedAddons[] = [
                'id' => $addon->id,
                'name' => $addon->name,
                'price' => $price,
                'billing_cycle' => $addon->billing_cycle,
            ];
        }

        $customFields = [];
        foreach ($service->customFields as $field) {
            $value = $details['custom_fields'][$field->id] ?? null;

            if ($field->is_required && blank($value)) {
                abort(422, $field->label . ' is required.');
            }

            if (!blank($value)) {
                $customFields[] = [
                    'id' => $field->id,
                    'name' => $field->name,
                    'label' => $field->label,
                    'value' => is_array($value) ? implode(', ', $value) : (string) $value,
                ];
            }
        }

        return [
            'name' => $service->name,
            'price' => $basePrice + $setupFee + $optionTotal + $addonTotal,
            'details' => array_merge($details, [
                'service_id' => $service->id,
                'billing_cycle' => $billingCycle,
                'base_price' => $basePrice,
                'setup_fee' => $setupFee,
                'recurring_price' => $basePrice + $optionTotal + $addonTotal,
                'available_cycles' => $availableCycles,
                'renewal_price' => $basePrice + $optionTotal + $addonTotal,
                'cycle_savings' => $this->cycleSavingsMessage($service, $billingCycle),
                'option_total' => $optionTotal,
                'addon_total' => $addonTotal,
                'configurable_options' => $selectedOptions,
                'custom_fields' => $customFields,
                'addons' => $selectedAddons,
            ]),
        ];
    }

    private function serviceRequiresDomain(string $type, array $details): bool
    {
        if ($type === 'hosting') {
            return true;
        }

        if (empty($details['service_id'])) {
            return false;
        }

        $service = \Plugins\Services\src\Models\Service::with('group')->find($details['service_id']);

        return (bool) ($service?->group?->requires_domain);
    }

    private function cycleSavingsMessage($service, string $billingCycle): string
    {
        if (!$service || $billingCycle === 'monthly') {
            return '';
        }

        $pricing = $service->pricing ?? [];
        $monthly = (float) ($pricing['monthly'] ?? 0);
        $current = (float) ($pricing[$billingCycle] ?? 0);
        $months = ['quarterly' => 3, 'semi_annually' => 6, 'annually' => 12][$billingCycle] ?? null;

        if (!$monthly || !$current || !$months) {
            return '';
        }

        $regular = $monthly * $months;
        if ($current >= $regular) {
            return '';
        }

        $percent = round((($regular - $current) / $regular) * 100);

        return "You saved {$percent}% compared with monthly billing.";
    }
}
