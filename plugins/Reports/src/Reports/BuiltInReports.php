<?php

namespace Plugins\Reports\src\Reports;

use App\Core\Registries\ReportRegistry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BuiltInReports
{
    public static function register(): void
    {
        ReportRegistry::register('billing.revenue-summary', [self::class, 'revenueSummary'], [
            'label' => 'Revenue Summary',
            'description' => 'Paid invoices, outstanding invoices, tax, and revenue trend.',
            'category' => 'Billing',
            'icon' => 'dollar-sign',
            'plugin' => 'reports',
            'permission' => 'view_reports',
        ]);

        ReportRegistry::register('clients.client-growth', [self::class, 'clientGrowth'], [
            'label' => 'Client Growth',
            'description' => 'New clients by period and client status totals.',
            'category' => 'Clients',
            'icon' => 'users',
            'plugin' => 'reports',
            'permission' => 'view_reports',
        ]);

        ReportRegistry::register('orders.sales-performance', [self::class, 'salesPerformance'], [
            'label' => 'Sales Performance',
            'description' => 'Orders, accepted orders, completed orders, and order value.',
            'category' => 'Sales',
            'icon' => 'shopping-cart',
            'plugin' => 'reports',
            'permission' => 'view_reports',
        ]);

        ReportRegistry::register('domains.expiring-domains', [self::class, 'expiringDomains'], [
            'label' => 'Expiring Domains',
            'description' => 'Domains expiring soon, grouped by expiry risk.',
            'category' => 'Domains',
            'icon' => 'globe',
            'plugin' => 'reports',
            'permission' => 'view_reports',
            'filters' => ['date_range', 'status'],
        ]);

        ReportRegistry::register('services.service-status', [self::class, 'serviceStatus'], [
            'label' => 'Service Status',
            'description' => 'Active, suspended, pending, and terminated client services.',
            'category' => 'Services',
            'icon' => 'package',
            'plugin' => 'reports',
            'permission' => 'view_reports',
        ]);

        ReportRegistry::register('support.ticket-volume', [self::class, 'ticketVolume'], [
            'label' => 'Ticket Volume',
            'description' => 'Support tickets by status, priority, and created date.',
            'category' => 'Support',
            'icon' => 'message-circle',
            'plugin' => 'reports',
            'permission' => 'view_reports',
        ]);

        ReportRegistry::register('seo.traffic-overview', [self::class, 'trafficOverview'], [
            'label' => 'SEO Traffic Overview',
            'description' => 'Pageviews, visitors, online users, and top pages from JamVini analytics.',
            'category' => 'Marketing',
            'icon' => 'activity',
            'plugin' => 'reports',
            'permission' => 'view_reports',
        ]);
    }

    public static function revenueSummary(array $filters): array
    {
        if (!Schema::hasTable('invoices')) {
            return self::missing('Invoices plugin is not installed.');
        }

        [$from, $to] = self::range($filters);
        $base = DB::table('invoices')->whereBetween('created_at', [$from, $to]);
        $paid = (clone $base)->where('status', 'paid');
        $outstanding = (clone $base)->whereIn('status', ['sent', 'overdue', 'unpaid', 'draft']);
        $rows = (clone $paid)
            ->selectRaw('date(coalesce(paid_at, created_at)) as period, count(*) as invoices, sum(total) as revenue, sum(tax_amount) as tax')
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->map(fn ($row) => [
                'period' => $row->period,
                'invoices' => (int) $row->invoices,
                'revenue' => self::money($row->revenue),
                'tax' => self::money($row->tax),
            ])
            ->all();

        return [
            'summary' => [
                ['label' => 'Paid Revenue', 'value' => self::money((clone $paid)->sum('total')), 'tone' => 'success'],
                ['label' => 'Paid Invoices', 'value' => number_format((clone $paid)->count()), 'tone' => 'info'],
                ['label' => 'Outstanding', 'value' => self::money((clone $outstanding)->sum('total')), 'tone' => 'warning'],
                ['label' => 'Tax', 'value' => self::money((clone $paid)->sum('tax_amount')), 'tone' => 'gray'],
            ],
            'columns' => self::columns(['period' => 'Date', 'invoices' => 'Paid Invoices', 'revenue' => 'Revenue', 'tax' => 'Tax']),
            'rows' => $rows,
            'chart' => self::chartFromRows($rows, 'period', ['revenue' => 'Revenue']),
        ];
    }

    public static function clientGrowth(array $filters): array
    {
        if (!Schema::hasTable('clients')) {
            return self::missing('Clients plugin is not installed.');
        }

        [$from, $to] = self::range($filters);
        $rows = DB::table('clients')
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('date(created_at) as period, count(*) as clients')
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->map(fn ($row) => ['period' => $row->period, 'clients' => (int) $row->clients])
            ->all();

        return [
            'summary' => [
                ['label' => 'Total Clients', 'value' => number_format(DB::table('clients')->count()), 'tone' => 'info'],
                ['label' => 'New In Range', 'value' => number_format(array_sum(array_column($rows, 'clients'))), 'tone' => 'success'],
                ['label' => 'Active', 'value' => number_format(DB::table('clients')->where('status', 'active')->count()), 'tone' => 'success'],
                ['label' => 'Inactive', 'value' => number_format(DB::table('clients')->where('status', '!=', 'active')->count()), 'tone' => 'gray'],
            ],
            'columns' => self::columns(['period' => 'Date', 'clients' => 'New Clients']),
            'rows' => $rows,
            'chart' => self::chartFromRows($rows, 'period', ['clients' => 'New Clients']),
        ];
    }

    public static function salesPerformance(array $filters): array
    {
        if (!Schema::hasTable('orders')) {
            return self::missing('Orders plugin is not installed.');
        }

        [$from, $to] = self::range($filters);
        $base = DB::table('orders')->whereBetween('created_at', [$from, $to]);
        $rows = (clone $base)
            ->selectRaw('date(created_at) as period, count(*) as orders, sum(total) as value')
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->map(fn ($row) => ['period' => $row->period, 'orders' => (int) $row->orders, 'value' => self::money($row->value)])
            ->all();

        return [
            'summary' => [
                ['label' => 'Orders', 'value' => number_format((clone $base)->count()), 'tone' => 'info'],
                ['label' => 'Accepted', 'value' => number_format((clone $base)->where('status', 'accepted')->count()), 'tone' => 'success'],
                ['label' => 'Completed', 'value' => number_format((clone $base)->where('status', 'completed')->count()), 'tone' => 'success'],
                ['label' => 'Order Value', 'value' => self::money((clone $base)->sum('total')), 'tone' => 'warning'],
            ],
            'columns' => self::columns(['period' => 'Date', 'orders' => 'Orders', 'value' => 'Value']),
            'rows' => $rows,
            'chart' => self::chartFromRows($rows, 'period', ['orders' => 'Orders']),
        ];
    }

    public static function expiringDomains(array $filters): array
    {
        if (!Schema::hasTable('domains')) {
            return self::missing('Domains plugin is not installed.');
        }

        $from = !empty($filters['date_from'])
            ? \Carbon\Carbon::parse($filters['date_from'])->startOfDay()
            : now()->startOfDay();
        $to = !empty($filters['date_to'])
            ? \Carbon\Carbon::parse($filters['date_to'])->endOfDay()
            : now()->addDays(90)->endOfDay();

        $rows = DB::table('domains')
            ->whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [$from->toDateString(), $to->toDateString()])
            ->when(!empty($filters['status']), fn ($query) => $query->where('status', $filters['status']))
            ->orderBy('expiry_date')
            ->limit(300)
            ->get()
            ->map(fn ($domain) => [
                'domain_name' => $domain->domain_name,
                'expiry_date' => $domain->expiry_date,
                'status' => $domain->status,
                'auto_renew' => !empty($domain->auto_renew) ? 'Yes' : 'No',
                'risk' => now()->diffInDays(\Carbon\Carbon::parse($domain->expiry_date), false) <= 14 ? 'High' : 'Normal',
            ])
            ->all();

        return [
            'summary' => [
                ['label' => 'Expiring', 'value' => number_format(count($rows)), 'tone' => 'warning'],
                ['label' => 'High Risk', 'value' => number_format(collect($rows)->where('risk', 'High')->count()), 'tone' => 'danger'],
                ['label' => 'Auto Renew', 'value' => number_format(collect($rows)->where('auto_renew', 'Yes')->count()), 'tone' => 'success'],
            ],
            'columns' => self::columns(['domain_name' => 'Domain', 'expiry_date' => 'Expiry', 'status' => 'Status', 'auto_renew' => 'Auto Renew', 'risk' => 'Risk']),
            'rows' => $rows,
        ];
    }

    public static function serviceStatus(array $filters): array
    {
        if (!Schema::hasTable('client_services')) {
            return self::missing('Services plugin is not installed.');
        }

        $rows = DB::table('client_services')
            ->selectRaw('status, count(*) as services, sum(price) as value')
            ->groupBy('status')
            ->orderByDesc('services')
            ->get()
            ->map(fn ($row) => ['status' => str($row->status)->headline()->toString(), 'services' => (int) $row->services, 'value' => self::money($row->value)])
            ->all();

        return [
            'summary' => [
                ['label' => 'Client Services', 'value' => number_format(DB::table('client_services')->count()), 'tone' => 'info'],
                ['label' => 'Active', 'value' => number_format(DB::table('client_services')->where('status', 'active')->count()), 'tone' => 'success'],
                ['label' => 'Suspended', 'value' => number_format(DB::table('client_services')->where('status', 'suspended')->count()), 'tone' => 'warning'],
            ],
            'columns' => self::columns(['status' => 'Status', 'services' => 'Services', 'value' => 'Monthly Value']),
            'rows' => $rows,
            'chart' => self::chartFromRows($rows, 'status', ['services' => 'Services']),
        ];
    }

    public static function ticketVolume(array $filters): array
    {
        if (!Schema::hasTable('support_tickets')) {
            return self::missing('Support plugin is not installed.');
        }

        [$from, $to] = self::range($filters);
        $rows = DB::table('support_tickets')
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('date(created_at) as period, count(*) as tickets')
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->map(fn ($row) => ['period' => $row->period, 'tickets' => (int) $row->tickets])
            ->all();

        return [
            'summary' => [
                ['label' => 'Tickets', 'value' => number_format(array_sum(array_column($rows, 'tickets'))), 'tone' => 'info'],
                ['label' => 'Open', 'value' => number_format(DB::table('support_tickets')->where('status', 'open')->count()), 'tone' => 'warning'],
                ['label' => 'Closed', 'value' => number_format(DB::table('support_tickets')->where('status', 'closed')->count()), 'tone' => 'success'],
            ],
            'columns' => self::columns(['period' => 'Date', 'tickets' => 'Tickets']),
            'rows' => $rows,
            'chart' => self::chartFromRows($rows, 'period', ['tickets' => 'Tickets']),
        ];
    }

    public static function trafficOverview(array $filters): array
    {
        if (!Schema::hasTable('seo_analytics_events')) {
            return self::missing('SEO analytics table is not available.');
        }

        [$from, $to] = self::range($filters);
        $base = DB::table('seo_analytics_events')->whereBetween('occurred_at', [$from, $to]);
        $rows = (clone $base)
            ->where('event_type', 'pageview')
            ->selectRaw('path, count(*) as pageviews, count(distinct visitor_id) as visitors')
            ->groupBy('path')
            ->orderByDesc('pageviews')
            ->limit(50)
            ->get()
            ->map(fn ($row) => ['path' => $row->path ?: '/', 'pageviews' => (int) $row->pageviews, 'visitors' => (int) $row->visitors])
            ->all();

        return [
            'summary' => [
                ['label' => 'Pageviews', 'value' => number_format((clone $base)->where('event_type', 'pageview')->count()), 'tone' => 'info'],
                ['label' => 'Visitors', 'value' => number_format((clone $base)->distinct('visitor_id')->count('visitor_id')), 'tone' => 'success'],
                ['label' => 'Online Now', 'value' => number_format(DB::table('seo_analytics_events')->where('occurred_at', '>=', now()->subMinutes(5))->distinct('visitor_id')->count('visitor_id')), 'tone' => 'warning'],
            ],
            'columns' => self::columns(['path' => 'Page', 'pageviews' => 'Pageviews', 'visitors' => 'Visitors']),
            'rows' => $rows,
            'chart' => self::chartFromRows(array_slice($rows, 0, 10), 'path', ['pageviews' => 'Pageviews']),
        ];
    }

    protected static function range(array $filters, int $defaultDays = 30): array
    {
        $from = !empty($filters['date_from']) ? \Carbon\Carbon::parse($filters['date_from'])->startOfDay() : now()->subDays($defaultDays)->startOfDay();
        $to = !empty($filters['date_to']) ? \Carbon\Carbon::parse($filters['date_to'])->endOfDay() : now()->endOfDay();

        return [$from, $to];
    }

    protected static function columns(array $columns): array
    {
        return collect($columns)->map(fn ($label, $key) => ['key' => $key, 'label' => $label])->values()->all();
    }

    protected static function chartFromRows(array $rows, string $labelKey, array $series): array
    {
        return [
            'type' => 'bar',
            'labels' => array_map(fn ($row) => (string) ($row[$labelKey] ?? ''), $rows),
            'series' => collect($series)->map(fn ($label, $key) => [
                'label' => $label,
                'data' => array_map(fn ($row) => (float) preg_replace('/[^0-9.-]/', '', (string) ($row[$key] ?? 0)), $rows),
            ])->values()->all(),
        ];
    }

    protected static function money(mixed $value): string
    {
        return 'TZS ' . number_format((float) $value, 2);
    }

    protected static function missing(string $message): array
    {
        return [
            'summary' => [['label' => 'Unavailable', 'value' => '0', 'tone' => 'gray']],
            'columns' => self::columns(['message' => 'Message']),
            'rows' => [['message' => $message]],
            'chart' => null,
        ];
    }
}
