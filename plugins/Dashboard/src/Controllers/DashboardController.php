<?php

namespace Plugins\Dashboard\src\Controllers;

use App\Core\ActivityLogger;
use App\Http\Controllers\Controller;
use Plugins\Clients\src\Models\Client;
use Plugins\Domains\src\Models\Domain;
use Plugins\Invoices\src\Models\Invoice;
use Plugins\Orders\src\Models\Order;
use Plugins\Services\src\Models\ClientService;
use Plugins\Services\src\Models\Service;
use App\Core\Registries\DashboardRegistry;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $monthlyRevenue = Invoice::where('status', 'paid')
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('total');

        $lastMonthRevenue = Invoice::where('status', 'paid')
            ->whereMonth('paid_at', now()->subMonthNoOverflow()->month)
            ->whereYear('paid_at', now()->subMonthNoOverflow()->year)
            ->sum('total');

        $activeServices = ClientService::where('status', 'active')->get(['price', 'billing_cycle']);
        $estimatedMrr = $activeServices->sum(function ($service) {
            return match ($service->billing_cycle) {
                'annually', 'annual', 'yearly' => (float) $service->price / 12,
                'quarterly' => (float) $service->price / 3,
                'semi-annually', 'semiannually' => (float) $service->price / 6,
                'one-time', 'free' => 0,
                default => (float) $service->price,
            };
        });

        $pendingStatuses = ['draft', 'sent', 'partial', 'overdue'];

        $stats = [
            'total_clients' => Client::count(),
            'active_clients' => Client::where('status', 'active')->count(),
            'new_clients_month' => Client::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
            'active_services' => $activeServices->count(),
            'suspended_services' => ClientService::where('status', 'suspended')->count(),
            'catalog_services' => Service::where('is_active', true)->count(),
            'active_domains' => Domain::where('status', 'active')->count(),
            'domains_expiring' => Domain::expiringSoon(30)->count(),
            'domains_expiring_7' => Domain::expiringSoon(7)->count(),
            'expired_domains' => Domain::expired()->count(),
            'pending_invoices' => Invoice::whereIn('status', $pendingStatuses)->count(),
            'pending_amount' => Invoice::whereIn('status', $pendingStatuses)->sum('total'),
            'overdue_invoices' => Invoice::where('status', 'overdue')->count(),
            'overdue_amount' => Invoice::where('status', 'overdue')->sum('total'),
            'paid_invoices' => Invoice::where('status', 'paid')->count(),
            'monthly_revenue' => $monthlyRevenue,
            'last_month_revenue' => $lastMonthRevenue,
            'revenue_delta' => $lastMonthRevenue > 0 ? round((($monthlyRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1) : null,
            'estimated_mrr' => $estimatedMrr,
            'total_revenue' => Invoice::where('status', 'paid')->sum('total'),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'pending_orders_amount' => Order::where('status', 'pending')->sum('total'),
        ];

        $this->registerBuiltinWidgets($stats);

        $recentClients = Client::withCount(['services', 'domains'])
            ->withSum(['invoices as outstanding_balance' => fn ($q) => $q->whereIn('status', ['sent', 'partial', 'overdue'])], 'total')
            ->latest()
            ->limit(6)
            ->get();

        $recentInvoices = Invoice::with('client')->latest()->limit(6)->get();
        $overdueInvoices = Invoice::with('client')->where('status', 'overdue')->orderBy('due_date')->limit(5)->get();
        $pendingOrders = Order::with('client')->where('status', 'pending')->latest()->limit(5)->get();
        $expiringDomains = Domain::with('client')->expiringSoon(30)->orderBy('expiry_date')->limit(6)->get();
        $serviceStatus = ClientService::selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');
        $activities = ActivityLogger::recent(8);
        $dashboardWidgets = DashboardRegistry::getWidgets();
        $availableWidgets = DashboardRegistry::allRegistered();
        $gettingStarted = [
            [
                'label' => 'Company settings',
                'done' => filled(\App\Models\Setting::get('company_name')),
                'route' => 'admin.settings.index',
            ],
            [
                'label' => 'Create service catalog',
                'done' => ($stats['catalog_services'] ?? 0) > 0,
                'route' => 'admin.services.create',
            ],
            [
                'label' => 'Add provisioning server',
                'done' => class_exists(\Plugins\Services\src\Models\Server::class) && \Plugins\Services\src\Models\Server::count() > 0,
                'route' => 'admin.services.servers',
            ],
            [
                'label' => 'Enable payment methods',
                'done' => \App\Core\Payments\PaymentGatewayRegistry::enabled() !== [],
                'route' => 'admin.offline-payments.settings',
            ],
            [
                'label' => 'Add first client',
                'done' => ($stats['total_clients'] ?? 0) > 0,
                'route' => 'admin.clients.create',
            ],
        ];

        return view('plugins.Dashboard::admin.index', compact(
            'stats',
            'recentClients',
            'recentInvoices',
            'overdueInvoices',
            'pendingOrders',
            'expiringDomains',
            'serviceStatus',
            'activities',
            'gettingStarted',
            'dashboardWidgets',
            'availableWidgets'
        ));
    }

    public function saveWidgets(Request $request)
    {
        $validated = $request->validate([
            'widgets' => 'nullable|array',
            'widgets.*.enabled' => 'nullable|boolean',
            'widgets.*.position' => 'nullable|integer|min:0',
            'widgets.*.size' => 'nullable|in:small,medium,large,full',
            'widgets.*.color' => 'nullable|in:blue,green,amber,purple,slate,rose',
            'widgets.*.column' => 'nullable|in:main,side,full',
        ]);

        DashboardRegistry::saveSettings($validated['widgets'] ?? []);

        return back()->with('success', 'Dashboard widgets updated.');
    }

    protected function registerBuiltinWidgets(array $stats): void
    {
        DashboardRegistry::registerWidget('monthly-revenue', fn () => view('plugins.Dashboard::admin.widgets.kpi', [
            'label' => 'Monthly Revenue',
            'value' => jv_format_money($stats['monthly_revenue'] ?? 0),
            'meta' => $stats['revenue_delta'] === null ? 'No prior month' : $stats['revenue_delta'] . '% vs last month',
            'icon' => 'trending-up',
        ]), ['title' => 'Monthly Revenue', 'position' => 1, 'size' => 'small', 'color' => 'green', 'plugin' => 'dashboard']);

        DashboardRegistry::registerWidget('estimated-mrr', fn () => view('plugins.Dashboard::admin.widgets.kpi', [
            'label' => 'Estimated MRR',
            'value' => jv_format_money($stats['estimated_mrr'] ?? 0),
            'meta' => ($stats['active_services'] ?? 0) . ' active services',
            'icon' => 'chart-no-axes-column-increasing',
        ]), ['title' => 'Estimated MRR', 'position' => 2, 'size' => 'small', 'color' => 'purple', 'plugin' => 'dashboard']);

        DashboardRegistry::registerWidget('outstanding-balance', fn () => view('plugins.Dashboard::admin.widgets.kpi', [
            'label' => 'Outstanding',
            'value' => jv_format_money($stats['pending_amount'] ?? 0),
            'meta' => ($stats['overdue_invoices'] ?? 0) . ' overdue',
            'icon' => 'file-text',
        ]), ['title' => 'Outstanding', 'position' => 3, 'size' => 'small', 'color' => 'amber', 'plugin' => 'dashboard']);

        DashboardRegistry::registerWidget('pending-orders', fn () => view('plugins.Dashboard::admin.widgets.kpi', [
            'label' => 'Pending Orders',
            'value' => (string) ($stats['pending_orders'] ?? 0),
            'meta' => jv_format_money($stats['pending_orders_amount'] ?? 0),
            'icon' => 'shopping-cart',
        ]), ['title' => 'Pending Orders', 'position' => 4, 'size' => 'small', 'color' => 'blue', 'plugin' => 'dashboard']);
    }
}
