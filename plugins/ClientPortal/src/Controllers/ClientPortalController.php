<?php

namespace Plugins\ClientPortal\src\Controllers;

use App\Http\Controllers\Controller;
use Plugins\Clients\src\Models\Client;
use Plugins\Invoices\src\Models\Invoice;
use Plugins\Domains\src\Models\Domain;
use Plugins\Services\src\Models\ClientService;
use Plugins\Services\src\Connectors\ServerConnectorFactory;
use Plugins\Orders\src\Models\Order;
use App\Core\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ClientPortalController extends Controller
{
    public function dashboard()
    {
        $client = Auth::guard('web')->user();

        if (!$client instanceof Client) {
            return redirect()->route('login');
        }

        $stats = [
            'active_services' => ClientService::where('client_id', $client->id)->where('status', 'active')->count(),
            'active_domains' => Domain::where('client_id', $client->id)->where('status', 'active')->count(),
            'pending_invoices' => Invoice::where('client_id', $client->id)->whereIn('status', ['sent', 'overdue', 'partial'])->count(),
            'due_amount' => Invoice::where('client_id', $client->id)->whereIn('status', ['sent', 'overdue', 'partial'])->sum('total'),
            'total_orders' => \Plugins\Orders\src\Models\Order::where('client_id', $client->id)->count(),
            'pending_orders' => \Plugins\Orders\src\Models\Order::where('client_id', $client->id)->where('status', 'pending')->count(),
            'completed_orders' => \Plugins\Orders\src\Models\Order::where('client_id', $client->id)->whereIn('status', ['completed', 'accepted'])->count(),
        ];

        $services = ClientService::with('service.group')->where('client_id', $client->id)->latest()->limit(5)->get();
        $invoices = Invoice::where('client_id', $client->id)->latest()->limit(5)->get();
        $domains = Domain::where('client_id', $client->id)->latest()->limit(5)->get();
        $recentOrders = \Plugins\Orders\src\Models\Order::with('items')
            ->where('client_id', $client->id)
            ->latest()
            ->limit(5)
            ->get();

        return view('plugins.ClientPortal::client.dashboard', compact(
            'client', 'stats', 'services', 'invoices', 'domains', 'recentOrders'
        ));
    }

    public function services()
    {
        $client = Auth::guard('web')->user();
        $services = ClientService::with(['service.group', 'service.servers', 'server'])->where('client_id', $client->id)->latest()->paginate(10);
        return view('plugins.ClientPortal::client.services', compact('client', 'services'));
    }

    public function serviceDetail(ClientService $service)
    {
        $client = Auth::guard('web')->user();
        
        if ($service->client_id !== $client->id) {
            abort(403);
        }
        
        $service->load([
            'service.group',
            'service.options',
            'service.servers',
            'server',
            'properties' => fn ($query) => $query->where('is_public', true)->orderBy('label')->orderBy('key'),
        ]);
        
        return view('plugins.ClientPortal::client.service-detail', compact('client', 'service'));
    }

    public function cpanelLogin(ClientService $service)
    {
        $client = Auth::guard('web')->user();

        if ($service->client_id !== $client->id) {
            abort(403);
        }

        $service->loadMissing(['service.servers', 'server']);
        $server = $service->server ?: ($service->service?->servers?->first(fn ($srv) => (bool) $srv->pivot?->is_default) ?: $service->service?->servers?->first());

        if (!$server || $server->type !== 'cpanel') {
            return back()->with('error', 'cPanel login is not available for this service.');
        }

        $username = $service->remote_username ?: $this->fallbackCpanelUsername($service);

        if (!$username) {
            return back()->with('error', 'cPanel username is missing for this service.');
        }

        $result = app(ServerConnectorFactory::class)->for($server)->createLoginSession([
            'username' => $username,
            'service' => 'cpaneld',
        ]);

        ActivityLogger::log($result['success'] ? 'cpanel.login.created' : 'cpanel.login.failed', 'ClientService', $service->id, $result['message'], [
            'client_id' => $client->id,
            'server_id' => $server->id,
            'username' => $username,
        ]);

        if (!$result['success'] || empty($result['url'])) {
            return back()->with('error', 'Could not create cPanel login session: ' . $result['message']);
        }

        return redirect()->away($result['url']);
    }

    public function domains()
    {
        $client = Auth::guard('web')->user();
        $domains = Domain::where('client_id', $client->id)->latest()->paginate(10);
        return view('plugins.ClientPortal::client.domains', compact('client', 'domains'));
    }

    public function orders()
    {
        $client = Auth::guard('web')->user();
        $orders = Order::with(['items', 'invoice'])
            ->where('client_id', $client->id)
            ->latest()
            ->paginate(10);

        $stats = [
            'total' => Order::where('client_id', $client->id)->count(),
            'completed' => Order::where('client_id', $client->id)->where('status', 'completed')->count(),
            'pending' => Order::where('client_id', $client->id)->where('status', 'pending')->count(),
            'total_spent' => Order::where('client_id', $client->id)->where('status', 'completed')->sum('total'),
        ];

        return view('plugins.ClientPortal::client.orders', compact('client', 'orders', 'stats'));
    }

    public function orderDetail(Order $order)
    {
        $client = Auth::guard('web')->user();
        
        // Ensure client owns this order
        if ($order->client_id !== $client->id) {
            abort(403);
        }
        
        $order->load(['client', 'items', 'invoice.transactions']);
        
        return view('plugins.ClientPortal::client.order-detail', compact('client', 'order'));
    }

    public function invoices()
    {
        $client = Auth::guard('web')->user();
        $invoices = Invoice::with('transactions')
            ->where('client_id', $client->id)
            ->latest()
            ->paginate(10);

        $stats = [
            'total' => Invoice::where('client_id', $client->id)->count(),
            'paid' => Invoice::where('client_id', $client->id)->where('status', 'paid')->count(),
            'unpaid' => Invoice::where('client_id', $client->id)->whereIn('status', ['sent', 'overdue', 'partial'])->count(),
            'overdue' => Invoice::where('client_id', $client->id)->where('status', 'overdue')->count(),
            'due_amount' => Invoice::where('client_id', $client->id)->whereIn('status', ['sent', 'overdue', 'partial'])->sum('total'),
        ];

        return view('plugins.ClientPortal::client.invoices', compact('client', 'invoices', 'stats'));
    }

    public function invoiceDetail(Invoice $invoice)
    {
        $client = Auth::guard('web')->user();
        
        if ($invoice->client_id !== $client->id) {
            abort(403);
        }
        
        $invoice->load(['client', 'items', 'transactions']);
        
        return view('plugins.ClientPortal::client.invoice-detail', compact('client', 'invoice'));
    }

    public function account()
    {
        $client = Auth::guard('web')->user();
        return view('plugins.ClientPortal::client.account', compact('client'));
    }

    public function updateAccount(Request $request)
    {
        $client = Auth::guard('web')->user();
        
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:clients,email,' . $client->id,
            'phone' => 'nullable|string|max:20',
            'company_name' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = bcrypt($request->password);
        } else {
            unset($validated['password']);
        }

        $client->update($validated);

        return back()->with('success', 'Account updated successfully!');
    }

    public function endSupportAccess()
    {
        $clientId = session('support_access_client_id');

        Auth::guard('web')->logout();
        session()->forget(['support_access_admin_id', 'support_access_client_id', 'support_access_started_at']);

        if ($clientId) {
            return redirect()->route('admin.clients.show', $clientId)
                ->with('success', 'Support access ended.');
        }

        return redirect()->route('admin.dashboard');
    }

    protected function fallbackCpanelUsername(ClientService $service): ?string
    {
        $domain = strtolower(trim((string) ($service->remote_domain ?: $service->domain)));

        if (!str_contains($domain, '.')) {
            return null;
        }

        $label = Str::before($domain, '.');
        $username = preg_replace('/[^a-z0-9]/', '', strtolower($label)) ?: 'acct';
        $username = preg_match('/^[a-z]/', $username) ? $username : 'u' . $username;

        return substr(substr($username, 0, 8) . $service->id, 0, 16);
    }
}
