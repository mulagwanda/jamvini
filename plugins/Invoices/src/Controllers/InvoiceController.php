<?php

namespace Plugins\Invoices\src\Controllers;

use App\Http\Controllers\Controller;
use Plugins\Invoices\src\Models\Invoice;
use Plugins\Clients\src\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Core\Hooks\Action;
use App\Core\Payments\PaymentManager;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $invoices = Invoice::with(['client', 'transactions'])
            ->when($request->search, function($query, $search) {
                $query->where('invoice_number', 'like', "%{$search}%")
                      ->orWhereHas('client', function($q) use ($search) {
                            $q->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('company_name', 'like', "%{$search}%")
                            ->orWhere('client_number', 'like', "%{$search}%");
                      });
            })
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->filled('overdue'), fn($q) => $q->whereIn('status', ['sent', 'partial', 'overdue'])->whereDate('due_date', '<', today()))
            ->when($request->aging, function ($query, $aging) {
                $query->whereIn('status', ['sent', 'partial', 'overdue'])->whereDate('due_date', '<', today());

                if ($aging === '7') {
                    $query->whereDate('due_date', '>=', today()->subDays(13));
                } elseif ($aging === '14') {
                    $query->whereBetween('due_date', [today()->subDays(29), today()->subDays(14)]);
                } elseif ($aging === '30') {
                    $query->whereDate('due_date', '<=', today()->subDays(30));
                }
            })
            ->latest()->paginate(15);

        $openInvoices = Invoice::with('transactions')->whereIn('status', ['sent', 'partial', 'overdue'])->get();

        $stats = [
            'total' => Invoice::count(),
            'draft' => Invoice::where('status', 'draft')->count(),
            'sent' => Invoice::where('status', 'sent')->count(),
            'paid' => Invoice::where('status', 'paid')->count(),
            'overdue' => Invoice::where('status', 'overdue')->count(),
            'revenue' => Invoice::where('status', 'paid')->sum('total'),
            'outstanding' => $openInvoices->sum(fn ($invoice) => $invoice->remaining_amount),
            'month_revenue' => Invoice::where('status', 'paid')->whereMonth('paid_at', now()->month)->whereYear('paid_at', now()->year)->sum('total'),
            'due_soon' => Invoice::whereIn('status', ['sent', 'partial'])->whereBetween('due_date', [today(), today()->addDays(7)])->count(),
            'overdue_30' => Invoice::whereIn('status', ['sent', 'partial', 'overdue'])->whereDate('due_date', '<=', today()->subDays(30))->count(),
        ];

        return view('plugins.Invoices::admin.index', compact('invoices', 'stats'));
    }

    public function create()
    {
        $clients = Client::orderBy('first_name')->get();
        $invoiceNumber = $this->generateInvoiceNumber();
        $dueDate = now()->addDays((int) \App\Models\Setting::get('invoice_due_days', '14'))->format('Y-m-d');
        $defaultTaxRate = jv_tax_rate();

        return view('plugins.Invoices::admin.create', compact('clients', 'invoiceNumber', 'dueDate', 'defaultTaxRate'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'invoice_number' => 'required|unique:invoices,invoice_number',
            'status' => 'required|in:draft,sent',
            'currency' => 'nullable|string|size:3',
            'source' => 'nullable|string|max:100',
            'external_id' => 'nullable|string|max:100',
            'discount' => 'nullable|numeric|min:0',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'payment_terms' => 'nullable|string',
            'admin_notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.type' => 'nullable|string|max:50',
            'items.*.domain' => 'nullable|string|max:255',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
            'items.*.billing_cycle' => 'nullable|string|max:50',
            'items.*.period_start' => 'nullable|date',
            'items.*.period_end' => 'nullable|date|after_or_equal:items.*.period_start',
        ]);

        [$subtotal, $taxAmount, $total] = $this->calculateTotals($validated['items'], $validated['discount'] ?? 0);

        $invoice = Invoice::create([
            'client_id' => $validated['client_id'],
            'invoice_number' => $validated['invoice_number'],
            'currency' => $validated['currency'] ?? \App\Models\Setting::get('currency', 'TZS'),
            'subtotal' => $subtotal,
            'discount' => $validated['discount'] ?? 0,
            'tax_amount' => $taxAmount,
            'total' => $total,
            'status' => $validated['status'],
            'source' => $validated['source'] ?? 'admin',
            'external_id' => $validated['external_id'] ?? null,
            'due_date' => $validated['due_date'],
            'sent_at' => $validated['status'] === 'sent' ? now() : null,
            'notes' => $validated['notes'] ?? null,
            'payment_terms' => $validated['payment_terms'] ?? null,
            'admin_notes' => $validated['admin_notes'] ?? null,
        ]);

        foreach ($validated['items'] as $item) {
            $itemTotal = $item['quantity'] * $item['unit_price'];
            $itemTax = $itemTotal * (($item['tax_rate'] ?? 0) / 100);
            $invoice->items()->create([
                'description' => $item['description'],
                'type' => $item['type'] ?? 'custom',
                'domain' => $item['domain'] ?? null,
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'tax_rate' => $item['tax_rate'] ?? 0,
                'total' => $itemTotal + $itemTax,
                'billing_cycle' => $item['billing_cycle'] ?? null,
                'period_start' => $item['period_start'] ?? null,
                'period_end' => $item['period_end'] ?? null,
            ]);
        }

        Action::do('invoice.created', $invoice);

        return redirect()->route('admin.invoices.show', $invoice)
            ->with('success', 'Invoice #' . $invoice->invoice_number . ' created!');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['client', 'items', 'transactions.recordedBy']);
        return view('plugins.Invoices::admin.show', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        if (in_array($invoice->status, ['paid'])) {
            return redirect()->route('admin.invoices.show', $invoice)
                ->with('error', 'Cannot edit a paid invoice.');
        }
        $invoice->load(['client', 'items']);
        $clients = Client::orderBy('first_name')->get();
        return view('plugins.Invoices::admin.edit', compact('invoice', 'clients'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        if (in_array($invoice->status, ['paid'])) {
            return redirect()->route('admin.invoices.show', $invoice)->with('error', 'Cannot edit a paid invoice.');
        }

        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'invoice_number' => ['required', Rule::unique('invoices', 'invoice_number')->ignore($invoice->id)],
            'status' => 'required|in:draft,sent,partial,overdue,cancelled',
            'currency' => 'nullable|string|size:3',
            'source' => 'nullable|string|max:100',
            'external_id' => 'nullable|string|max:100',
            'discount' => 'nullable|numeric|min:0',
            'due_date' => 'nullable|date',
            'paid_at' => 'nullable|date',
            'notes' => 'nullable|string',
            'payment_terms' => 'nullable|string',
            'admin_notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.type' => 'nullable|string|max:50',
            'items.*.domain' => 'nullable|string|max:255',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
            'items.*.billing_cycle' => 'nullable|string|max:50',
            'items.*.period_start' => 'nullable|date',
            'items.*.period_end' => 'nullable|date|after_or_equal:items.*.period_start',
        ]);

        [$subtotal, $taxAmount, $total] = $this->calculateTotals($validated['items'], $validated['discount'] ?? 0);

        $invoice->update([
            'client_id' => $validated['client_id'],
            'invoice_number' => $validated['invoice_number'],
            'currency' => $validated['currency'] ?? $invoice->currency,
            'subtotal' => $subtotal,
            'discount' => $validated['discount'] ?? 0,
            'tax_amount' => $taxAmount,
            'total' => $total,
            'status' => $validated['status'],
            'source' => $validated['source'] ?? $invoice->source,
            'external_id' => $validated['external_id'] ?? null,
            'due_date' => $validated['due_date'],
            'sent_at' => $validated['status'] !== 'draft' && !$invoice->sent_at ? now() : $invoice->sent_at,
            'cancelled_at' => $validated['status'] === 'cancelled' ? now() : null,
            'paid_at' => $validated['paid_at'] ?? $invoice->paid_at,
            'notes' => $validated['notes'] ?? null,
            'payment_terms' => $validated['payment_terms'] ?? null,
            'admin_notes' => $validated['admin_notes'] ?? null,
        ]);

        $invoice->items()->delete();
        foreach ($validated['items'] as $item) {
            $itemTotal = $item['quantity'] * $item['unit_price'];
            $itemTax = $itemTotal * (($item['tax_rate'] ?? 0) / 100);
            $invoice->items()->create([
                'description' => $item['description'],
                'type' => $item['type'] ?? 'custom',
                'domain' => $item['domain'] ?? null,
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'tax_rate' => $item['tax_rate'] ?? 0,
                'total' => $itemTotal + $itemTax,
                'billing_cycle' => $item['billing_cycle'] ?? null,
                'period_start' => $item['period_start'] ?? null,
                'period_end' => $item['period_end'] ?? null,
            ]);
        }

        Action::do('invoice.updated', $invoice);

        return redirect()->route('admin.invoices.show', $invoice)
            ->with('success', 'Invoice #' . $invoice->invoice_number . ' updated!');
    }

    public function destroy(Invoice $invoice)
    {
        $invoiceNumber = $invoice->invoice_number;
        $invoice->delete();
        Action::do('invoice.deleted', $invoice);

        return redirect()->route('admin.invoices.index')
            ->with('success', 'Invoice #' . $invoiceNumber . ' deleted!');
    }

    public function markAsPaid(Invoice $invoice)
    {
        if ($invoice->remaining_amount > 0) {
            \Plugins\Invoices\src\Models\Transaction::create([
                'invoice_id' => $invoice->id,
                'amount' => $invoice->remaining_amount,
                'payment_method' => 'manual',
                'transaction_id' => null,
                'status' => 'completed',
                'notes' => 'Marked paid by admin.',
                'recorded_by' => auth('admin')->id(),
            ]);
        }

        $invoice->update(['status' => 'paid', 'paid_at' => now()]);
        Action::do('invoice.paid', $invoice);

        return redirect()->route('admin.invoices.show', $invoice)
            ->with('success', 'Invoice #' . $invoice->invoice_number . ' marked as paid!');
    }

    public function markAsSent(Invoice $invoice)
    {
        $invoice->update(['status' => 'sent', 'sent_at' => now()]);
        Action::do('invoice.sent', $invoice);

        return redirect()->route('admin.invoices.show', $invoice)
            ->with('success', 'Invoice #' . $invoice->invoice_number . ' marked as sent!');
    }

    public function void(Invoice $invoice)
    {
        if ($invoice->status === 'paid' || $invoice->paid_amount > 0) {
            return back()->with('error', 'Paid or partially paid invoices cannot be voided.');
        }

        $invoice->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'admin_notes' => trim(($invoice->admin_notes ? $invoice->admin_notes . "\n" : '') . 'Voided by admin on ' . now()->format('Y-m-d H:i')),
        ]);

        Action::do('invoice.voided', $invoice);

        return redirect()->route('admin.invoices.show', $invoice)
            ->with('success', 'Invoice #' . $invoice->invoice_number . ' voided.');
    }

    public function refreshOverdue()
    {
        $count = Invoice::whereIn('status', ['sent', 'partial'])
            ->whereDate('due_date', '<', today())
            ->update(['status' => 'overdue']);

        return redirect()->route('admin.invoices.index')
            ->with('success', $count . ' invoice(s) marked overdue.');
    }

    public function applyCredit(Request $request, Invoice $invoice)
    {
        $client = $invoice->client;

        if (!$client || ($client->credit_balance ?? 0) <= 0 || $invoice->remaining_amount <= 0) {
            return back()->with('error', 'No available credit can be applied.');
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:1|max:' . min((float) $client->credit_balance, (float) $invoice->remaining_amount),
        ]);

        $amount = (float) $validated['amount'];

        \Plugins\Invoices\src\Models\Transaction::create([
            'invoice_id' => $invoice->id,
            'amount' => $amount,
            'payment_method' => 'client_credit',
            'transaction_id' => 'CREDIT-' . now()->format('YmdHis'),
            'status' => 'completed',
            'notes' => 'Applied from client credit balance.',
            'recorded_by' => auth('admin')->id(),
        ]);

        $client->update(['credit_balance' => max(0, (float) $client->credit_balance - $amount)]);
        $this->syncPaymentStatus($invoice->fresh());

        return redirect()->route('admin.invoices.show', $invoice)
            ->with('success', 'Client credit of ' . jv_format_money($amount) . ' applied.');
    }

    private function generateInvoiceNumber()
    {
        $prefix = trim(\App\Models\Setting::get('invoice_prefix', 'INV')) ?: 'INV';
        $base = $prefix . '-' . now()->format('Y') . '-';
        $sequence = Invoice::withTrashed()->where('invoice_number', 'like', $base . '%')->count() + 1;

        do {
            $number = $base . str_pad((string) $sequence++, 5, '0', STR_PAD_LEFT);
        } while (Invoice::withTrashed()->where('invoice_number', $number)->exists());

        return $number;
    }

    public function downloadPdf(Invoice $invoice)
    {
        $invoice->load(['client', 'items', 'transactions']);
        $company = [
            'name' => \App\Models\Setting::get('company_name', 'JamVini Hosting'),
            'address' => \App\Models\Setting::get('company_address', 'Dar es Salaam, Tanzania'),
            'phone' => \App\Models\Setting::get('company_phone', ''),
            'email' => \App\Models\Setting::get('company_email', ''),
            'tin' => \App\Models\Setting::get('company_tin', ''),
            'vrn' => \App\Models\Setting::get('company_vrn', ''),
            'logo' => \App\Models\Setting::get('company_logo', ''),
        ];

        $pdf = \PDF::loadView('plugins.Invoices::admin.pdf', compact('invoice', 'company'));
        
        return $pdf->download('Invoice-' . $invoice->invoice_number . '.pdf');
    }

    public function recordPayment(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1|max:' . $invoice->remaining_amount,
            'payment_method' => 'required|string',
            'transaction_id' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        app(PaymentManager::class)->record(
            invoice: $invoice,
            amount: (float) $validated['amount'],
            gatewaySlug: $validated['payment_method'],
            transactionId: $validated['transaction_id'] ?? null,
            status: 'completed',
            notes: $validated['notes'] ?? null,
            recordedBy: auth('admin')->id()
        );

        return redirect()->route('admin.invoices.show', $invoice)
            ->with('success', 'Payment of ' . jv_format_money($validated['amount']) . ' recorded!');
    }

    protected function syncPaymentStatus(Invoice $invoice): void
    {
        $totalPaid = $invoice->transactions()->where('status', 'completed')->sum('amount');
        $wasPaid = $invoice->status === 'paid';

        if ($totalPaid >= $invoice->total) {
            $invoice->update(['status' => 'paid', 'paid_at' => $invoice->paid_at ?: now()]);
            if (!$wasPaid) {
                Action::do('invoice.paid', $invoice->fresh(['client']));
            }
        } elseif ($totalPaid > 0) {
            $invoice->update(['status' => 'partial']);
        } elseif ($invoice->due_date && $invoice->due_date->isPast() && in_array($invoice->status, ['sent', 'overdue'], true)) {
            $invoice->update(['status' => 'overdue']);
        }
    }

    protected function calculateTotals(array $items, float|int|string $discount = 0): array
    {
        $subtotal = 0;
        $taxableSubtotal = 0;

        foreach ($items as $item) {
            $itemTotal = $item['quantity'] * $item['unit_price'];
            $subtotal += $itemTotal;
            $taxableSubtotal += $itemTotal * (($item['tax_rate'] ?? 0) / 100);
        }

        $discount = min((float) $discount, $subtotal);
        $taxAmount = max(0, $taxableSubtotal);
        $total = max(0, ($subtotal - $discount) + $taxAmount);

        return [$subtotal, $taxAmount, $total];
    }
}
