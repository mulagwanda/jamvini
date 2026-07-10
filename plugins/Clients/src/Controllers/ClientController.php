<?php

namespace Plugins\Clients\src\Controllers;

use App\Http\Controllers\Controller;
use App\Core\ActivityLogger;
use Plugins\Clients\src\Models\Client;
use Plugins\Clients\src\Models\ClientGroup;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Core\Hooks\Action;
use Plugins\CustomFields\src\Services\CustomFieldService;

class ClientController extends Controller
{

    public function index(Request $request)
    {
        $clients = \Plugins\Clients\src\Models\Client::withCount('services')
            ->withCount('domains')
            ->withCount(['invoices as open_invoices_count' => fn ($q) => $q->whereIn('status', ['sent', 'overdue', 'partial'])])
            ->withSum(['invoices as outstanding_balance' => fn ($q) => $q->whereIn('status', ['sent', 'overdue', 'partial'])], 'total')
            ->when($request->search, fn($q, $s) => $q->where(function($q) use ($s) {
                $q->where('first_name', 'like', "%{$s}%")
                ->orWhere('last_name', 'like', "%{$s}%")
                ->orWhere('email', 'like', "%{$s}%")
                ->orWhere('company_name', 'like', "%{$s}%")
                ->orWhere('client_number', 'like', "%{$s}%")
                ->orWhere('external_id', 'like', "%{$s}%");
            }))
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->type, fn($q, $s) => $q->where('type', $s))
            ->latest()
            ->paginate(15);

        $stats = [
            'total' => \Plugins\Clients\src\Models\Client::count(),
            'active' => \Plugins\Clients\src\Models\Client::where('status', 'active')->count(),
            'inactive' => \Plugins\Clients\src\Models\Client::where('status', 'inactive')->count(),
            'suspended' => \Plugins\Clients\src\Models\Client::where('status', 'suspended')->count(),
            'companies' => \Plugins\Clients\src\Models\Client::where('type', 'company')->count(),
            'outstanding' => \Plugins\Invoices\src\Models\Invoice::whereIn('status', ['sent', 'overdue', 'partial'])->sum('total'),
            'credits' => \Plugins\Clients\src\Models\Client::sum('credit_balance'),
        ];

        return view('plugins.Clients::admin.index', compact('clients', 'stats'));
    }

    public function bulk(Request $request)
    {
        $ids = explode(',', $request->ids);
        $action = $request->action;

        if (empty($ids) || !in_array($action, ['activate', 'suspend', 'delete'])) {
            return back()->with('error', 'Invalid action.');
        }

        $statusMap = ['activate' => 'active', 'suspend' => 'suspended'];

        if ($action === 'delete') {
            \Plugins\Clients\src\Models\Client::whereIn('id', $ids)->delete();
            \App\Core\ActivityLogger::log('deleted', 'Client', null, count($ids) . ' clients deleted');
        } else {
            \Plugins\Clients\src\Models\Client::whereIn('id', $ids)->update(['status' => $statusMap[$action]]);
            \App\Core\ActivityLogger::log('updated', 'Client', null, count($ids) . ' clients ' . $action . 'd');
        }

        return back()->with('success', count($ids) . ' client(s) updated!');
    }

    public function export()
    {
        $clients = \Plugins\Clients\src\Models\Client::withCount(['services', 'domains'])
            ->withSum(['invoices as outstanding_balance' => fn ($q) => $q->whereIn('status', ['sent', 'overdue', 'partial'])], 'total')
            ->get();
        
        $csv = "Client Number,Name,Type,Company,Email,Billing Email,Technical Email,Phone,Mobile,City,State,Country,TIN,Status,Services,Domains,Outstanding,Credit,Source,External ID,Created\n";
        foreach ($clients as $c) {
            $csv .= collect([
                $c->client_number,
                $c->full_name,
                $c->type,
                $c->company_name,
                $c->email,
                $c->billing_email,
                $c->technical_email,
                $c->phone,
                $c->mobile,
                $c->city,
                $c->state,
                $c->country,
                $c->tin_number,
                $c->status,
                $c->services_count,
                $c->domains_count,
                $c->outstanding_balance ?? 0,
                $c->credit_balance ?? 0,
                $c->source,
                $c->external_id,
                $c->created_at,
            ])->map(fn ($value) => '"' . str_replace('"', '""', (string) $value) . '"')->implode(',') . "\n";
        }
        
        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="clients.csv"');
    }

    public function create()
    {
        $groups = $this->clientGroups();
        $customFields = $this->customFields()->fields('client', ['admin_profile' => true]);
        $customFieldValues = collect();

        return view('plugins.Clients::admin.create', compact('groups', 'customFields', 'customFieldValues'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules() + $this->customFields()->validationRules('client', ['admin_profile' => true]));

        $validated['client_number'] = $this->generateClientNumber($validated['client_number'] ?? null);
        $validated['vat_exempt'] = $request->boolean('vat_exempt');
        $validated['email_marketing_opt_in'] = $request->boolean('email_marketing_opt_in');

        if (!$request->filled('password')) {
            unset($validated['password']);
        }

        $client = Client::create($validated);
        $this->syncContacts($request, $client);
        $this->customFields()->sync($client, 'client', $request->input('custom_fields', []), ['admin_profile' => true]);

        // Fire hook
        Action::do('client.created', $client);

        return redirect()
            ->route('admin.clients.index')
            ->with('success', 'Client "' . $client->full_name . '" created!');
    }

    public function show(Client $client)
    {
        $client->load([
            'services.service.group',
            'services.service.servers',
            'services.server',
            'invoices.transactions',
            'domains',
            'group',
            'contacts',
            'customFieldValues.field',
        ]);

        $orders = class_exists(\Plugins\Orders\src\Models\Order::class)
            ? \Plugins\Orders\src\Models\Order::with('items')->where('client_id', $client->id)->latest()->limit(10)->get()
            : collect();

        $tickets = class_exists(\Plugins\Support\src\Models\Ticket::class) && Schema::hasTable('support_tickets')
            ? \Plugins\Support\src\Models\Ticket::where('client_id', $client->id)->latest('last_reply_at')->latest()->limit(8)->get()
            : collect();

        $activityLogs = Schema::hasTable('activity_logs')
            ? DB::table('activity_logs')
                ->where(fn ($query) => $query->where('entity_type', 'Client')->where('entity_id', $client->id))
                ->orWhere(fn ($query) => $query->where('description', 'like', '%' . $client->full_name . '%'))
                ->latest()
                ->limit(8)
                ->get()
            : collect();

        $clientSwitcher = Client::query()
            ->select(['id', 'first_name', 'last_name', 'company_name', 'email', 'client_number', 'status'])
            ->orderBy('first_name')
            ->limit(300)
            ->get();

        $metrics = [
            'active_services' => $client->services->where('status', 'active')->count(),
            'suspended_services' => $client->services->where('status', 'suspended')->count(),
            'active_domains' => $client->domains->where('status', 'active')->count(),
            'domains_expiring' => $client->domains->filter(fn ($domain) => $domain->days_until_expiry !== null && $domain->days_until_expiry >= 0 && $domain->days_until_expiry <= 30)->count(),
            'total_invoiced' => $client->invoices->sum('total'),
            'paid_amount' => $client->invoices->sum(fn ($invoice) => $invoice->paid_amount),
            'outstanding' => $client->invoices->whereIn('status', ['sent', 'overdue', 'partial'])->sum('remaining_amount'),
            'overdue_invoices' => $client->invoices->where('status', 'overdue')->count(),
            'orders' => $orders->count(),
        ];

        $customFieldDisplay = $this->customFields()->formattedValues('client', $client->id, ['admin_profile' => true]);

        return view('plugins.Clients::admin.show', compact('client', 'metrics', 'orders', 'tickets', 'activityLogs', 'clientSwitcher', 'customFieldDisplay'));
    }

    public function edit(Client $client)
    {
        $client->load('contacts');
        $groups = $this->clientGroups();
        $customFields = $this->customFields()->fields('client', ['admin_profile' => true]);
        $customFieldValues = $this->customFields()->valuesFor('client', $client->id);

        return view('plugins.Clients::admin.edit', compact('client', 'groups', 'customFields', 'customFieldValues'));
    }

    public function update(Request $request, Client $client)
    {
        $validated = $request->validate($this->rules($client) + $this->customFields()->validationRules('client', ['admin_profile' => true]));

        $validated['client_number'] = $this->generateClientNumber($validated['client_number'] ?? null, $client->id);
        $validated['vat_exempt'] = $request->boolean('vat_exempt');
        $validated['email_marketing_opt_in'] = $request->boolean('email_marketing_opt_in');

        if (!$request->filled('password')) {
            unset($validated['password']);
        }

        $client->update($validated);
        $this->syncContacts($request, $client);
        $this->customFields()->sync($client, 'client', $request->input('custom_fields', []), ['admin_profile' => true]);

        Action::do('client.updated', $client);

        return redirect()
            ->route('admin.clients.index')
            ->with('success', 'Client "' . $client->full_name . '" updated!');
    }

    protected function rules(?Client $client = null): array
    {
        $clientId = $client?->id;

        return [
            'client_number' => ['nullable', 'string', 'max:50', Rule::unique('clients', 'client_number')->ignore($clientId)],
            'type' => 'required|in:individual,company,government,nonprofit',
            'client_group_id' => ['nullable', Rule::exists('client_groups', 'id')],
            'company_name' => 'nullable|string|max:255',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('clients', 'email')->ignore($clientId)],
            'phone' => 'nullable|string|max:30',
            'mobile' => 'nullable|string|max:30',
            'billing_email' => 'nullable|email|max:255',
            'technical_email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:30',
            'country' => 'nullable|string|max:100',
            'tin_number' => 'nullable|string|max:50',
            'vat_exempt' => 'boolean',
            'currency' => 'nullable|in:TZS,USD,KES,UGX,RWF',
            'language' => 'nullable|string|max:10',
            'timezone' => 'nullable|string|max:100',
            'credit_balance' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,inactive,suspended,closed',
            'password' => 'nullable|string|min:8',
            'notes' => 'nullable|string|max:5000',
            'source' => 'nullable|string|max:100',
            'external_id' => 'nullable|string|max:100',
            'email_marketing_opt_in' => 'boolean',
            'contacts' => 'nullable|array',
            'contacts.*.name' => 'nullable|string|max:255',
            'contacts.*.role' => 'nullable|string|max:120',
            'contacts.*.email' => 'nullable|email|max:255',
            'contacts.*.phone' => 'nullable|string|max:30',
            'contacts.*.receives_billing' => 'nullable|boolean',
            'contacts.*.receives_support' => 'nullable|boolean',
        ];
    }

    protected function generateClientNumber(?string $preferred = null, ?int $ignoreId = null): string
    {
        $base = Str::upper(Str::slug($preferred ?: 'JV-' . now()->format('ym') . '-' . str_pad((string) (Client::withTrashed()->count() + 1), 5, '0', STR_PAD_LEFT), '-'));
        $number = $base;
        $count = 2;

        while (
            Client::withTrashed()
                ->where('client_number', $number)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $number = $base . '-' . $count++;
        }

        return $number;
    }

    public function updateNotes(Request $request, Client $client)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string|max:5000',
        ]);

        $client->update($validated);
        ActivityLogger::log('updated', 'Client', $client->id, 'Client notes updated for ' . $client->full_name);

        return back()->with('success', 'Client notes updated.');
    }

    public function supportAccess(Client $client)
    {
        if ($client->status !== 'active') {
            return back()->with('error', 'Support access is only available for active clients.');
        }

        $adminId = Auth::guard('admin')->id();

        Auth::guard('web')->login($client);
        session([
            'support_access_admin_id' => $adminId,
            'support_access_client_id' => $client->id,
            'support_access_started_at' => now()->toDateTimeString(),
        ]);

        ActivityLogger::log('support_access.started', 'Client', $client->id, 'Admin opened client portal support access for ' . $client->full_name, [
            'admin_id' => $adminId,
            'client_id' => $client->id,
        ]);

        return redirect()->route('client.dashboard')
            ->with('success', 'Support access started for ' . $client->full_name . '.');
    }

    public function openTicket(Request $request, Client $client)
    {
        if (!class_exists(\Plugins\Support\src\Models\Ticket::class) || !Schema::hasTable('support_tickets')) {
            return back()->with('error', 'Support plugin is not ready yet.');
        }

        $validated = $request->validate([
            'department' => 'required|string|max:100',
            'priority' => 'required|in:low,normal,high,urgent',
            'related_service_id' => 'nullable|exists:client_services,id',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:10000',
        ]);

        $ticket = \Plugins\Support\src\Models\Ticket::create([
            'ticket_number' => $this->generateTicketNumber(),
            'client_id' => $client->id,
            'department' => $validated['department'],
            'subject' => $validated['subject'],
            'status' => 'open',
            'priority' => $validated['priority'],
            'source' => 'admin_client_profile',
            'related_service_id' => $validated['related_service_id'] ?? null,
            'last_reply_at' => now(),
            'metadata' => ['created_from' => 'client_profile'],
        ]);

        $ticket->replies()->create([
            'admin_id' => Auth::guard('admin')->id(),
            'author_type' => 'admin',
            'message' => $validated['message'],
            'is_private' => false,
        ]);

        ActivityLogger::log('support.ticket.created', 'Ticket', $ticket->id, 'Staff opened ticket ' . $ticket->ticket_number . ' for ' . $client->full_name);
        Action::do('support.ticket_created', $ticket);

        return redirect()->route('admin.support.tickets.show', $ticket)
            ->with('success', 'Ticket opened for ' . $client->full_name . '.');
    }

    public function destroy(Client $client)
    {
        Action::do('client.deleting', $client);

        $clientName = $client->full_name;
        $client->delete();

        Action::do('client.deleted', $client);

        return redirect()
            ->route('admin.clients.index')
            ->with('success', 'Client "' . $clientName . '" deleted!');
    }

    public function search(Request $request)
    {
        $query = $request->get('q');
        
        $clients = Client::where('first_name', 'like', "%{$query}%")
            ->orWhere('last_name', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->orWhere('company_name', 'like', "%{$query}%")
            ->orWhere('client_number', 'like', "%{$query}%")
            ->orWhere('external_id', 'like', "%{$query}%")
            ->limit(10)
            ->get()
            ->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->full_name,
                'email' => $c->email,
                'company' => $c->company_name,
                'client_number' => $c->client_number,
                'external_id' => $c->external_id,
                'status' => $c->status,
            ]);

        return response()->json($clients);
    }

    protected function clientGroups()
    {
        return class_exists(ClientGroup::class) && Schema::hasTable('client_groups')
            ? ClientGroup::orderByDesc('is_default')->orderBy('name')->get()
            : collect();
    }

    protected function syncContacts(Request $request, Client $client): void
    {
        if (!Schema::hasTable('client_contacts')) {
            return;
        }

        $contacts = collect($request->input('contacts', []))
            ->filter(fn ($contact) => filled($contact['name'] ?? null) || filled($contact['email'] ?? null) || filled($contact['phone'] ?? null))
            ->map(fn ($contact) => [
                'name' => $contact['name'] ?? 'Contact',
                'role' => $contact['role'] ?? null,
                'email' => $contact['email'] ?? null,
                'phone' => $contact['phone'] ?? null,
                'receives_billing' => (bool) ($contact['receives_billing'] ?? false),
                'receives_support' => (bool) ($contact['receives_support'] ?? false),
                'is_primary' => false,
            ])
            ->values();

        $client->contacts()->delete();
        $contacts->each(fn ($contact) => $client->contacts()->create($contact));
    }

    protected function generateTicketNumber(): string
    {
        $base = 'TKT-' . now()->format('ymd') . '-';
        $next = 1;

        do {
            $number = $base . str_pad((string) $next++, 4, '0', STR_PAD_LEFT);
        } while (\Plugins\Support\src\Models\Ticket::where('ticket_number', $number)->exists());

        return $number;
    }

    protected function customFields(): CustomFieldService
    {
        return app(CustomFieldService::class);
    }
}
