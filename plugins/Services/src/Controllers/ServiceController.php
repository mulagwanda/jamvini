<?php

namespace Plugins\Services\src\Controllers;

use App\Http\Controllers\Controller;
use App\Core\ActivityLogger;
use Plugins\Services\src\Connectors\ServerConnectorFactory;
use Plugins\Services\src\Models\Service;
use Plugins\Services\src\Models\ServiceGroup;
use Plugins\Services\src\Models\Server;
use Plugins\Services\src\Models\ServerPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ServiceController extends Controller
{
    // ==================== GROUPS ====================
    
    public function index()
    {
        $groups = ServiceGroup::with(['services' => fn ($query) => $query->orderBy('sort_order')->orderBy('name')])
            ->withCount('services')
            ->orderBy('order')
            ->get();
        return view('plugins.Services::admin.index', compact('groups'));
    }

    public function create()
    {
        $groups = ServiceGroup::orderBy('name')->get();
        $servers = Server::active()->with('packages')->get();
        return view('plugins.Services::admin.create', compact('groups', 'servers'));
    }

    public function store(Request $request)
{
    $group = ServiceGroup::find($request->group_id);
    $isDomain = $group && $group->module === 'domains';

    $rules = [
        'group_id' => 'nullable|exists:service_groups,id',
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'badge_label' => 'nullable|string|max:40',
        'features' => 'nullable|string',
        'is_active' => 'nullable|boolean',
        'is_featured' => 'nullable|boolean',
        'upgradable' => 'nullable|boolean',
        'allow_downgrade' => 'nullable|boolean',
        'free_domain_cycles' => 'nullable|array',
        'free_domain_cycles.*' => 'in:monthly,quarterly,semi_annually,annually',
        'server_id' => 'nullable|exists:servers,id',
        'package_name' => 'nullable|string|max:255',
        'options' => 'nullable|array',
        'options.*.name' => 'nullable|string|max:255',
        'options.*.type' => 'nullable|in:number,dropdown,checkbox',
        'options.*.choices' => 'nullable|string|max:2000',
        'options.*.price_monthly' => 'nullable|numeric|min:0',
        'custom_fields' => 'nullable|array',
        'custom_fields.*.label' => 'nullable|string|max:255',
        'custom_fields.*.type' => 'nullable|in:text,textarea,select,checkbox,url,number',
        'custom_fields.*.options' => 'nullable|string|max:2000',
        'custom_fields.*.placeholder' => 'nullable|string|max:255',
        'custom_fields.*.help_text' => 'nullable|string|max:500',
        'custom_fields.*.is_required' => 'nullable|boolean',
        'addons' => 'nullable|array',
        'addons.*.name' => 'nullable|string|max:255',
        'addons.*.description' => 'nullable|string|max:1000',
        'addons.*.price' => 'nullable|numeric|min:0',
        'addons.*.billing_cycle' => 'nullable|in:same_as_parent,one-time,monthly,annually',
        'addons.*.is_required' => 'nullable|boolean',
    ];

    // Only require amount/billing for non-domain services
    if (!$isDomain) {
        $rules['pricing'] = 'required|array|min:1';
        $rules['pricing.monthly'] = 'nullable|numeric|min:0';
        $rules['pricing.quarterly'] = 'nullable|numeric|min:0';
        $rules['pricing.semi_annually'] = 'nullable|numeric|min:0';
        $rules['pricing.annually'] = 'nullable|numeric|min:0';
        $rules['setup_fee'] = 'nullable|numeric|min:0';
        $rules['is_free'] = 'nullable|boolean';
        $rules['billing_type'] = 'nullable|in:recurring,one-time';
        $rules['billing_cycle'] = 'nullable|in:monthly,annually,one-time';
    }

    $validated = $request->validate($rules);
    $validated['is_active'] = $request->boolean('is_active', true);
    $validated['is_featured'] = $request->boolean('is_featured');
    $validated['is_free'] = $request->boolean('is_free');
    $validated['upgradable'] = $request->boolean('upgradable');
    $validated['allow_downgrade'] = $request->boolean('allow_downgrade');
    $validated['free_domain_cycles'] = $request->input('free_domain_cycles', []);
    $serverId = $validated['server_id'] ?? null;
    $packageName = $validated['package_name'] ?? null;
    $options = $validated['options'] ?? [];
    $customFields = $validated['custom_fields'] ?? [];
    $addons = $validated['addons'] ?? [];
    unset($validated['server_id'], $validated['package_name'], $validated['options'], $validated['custom_fields'], $validated['addons']);

    // Set defaults for domain services
    if ($isDomain) {
        $validated['amount'] = 0;
        $validated['billing_cycle'] = 'annually';
        $validated['is_free'] = false;
    } else {
        $validated['amount'] = $validated['pricing']['monthly'] ?? $validated['pricing']['annually'] ?? 0;
        $validated['billing_cycle'] = $validated['billing_cycle'] ?? 'monthly';
    }

    // Handle features
    $validated['features'] = !empty($validated['features']) 
        ? array_map('trim', explode("\n", $validated['features'])) 
        : [];

    // Handle pricing array
    if (!empty($validated['pricing'])) {
        $validated['pricing'] = array_filter($validated['pricing'], fn($v) => $v !== null && $v !== '');
    }

    $service = Service::create($validated);
    $this->syncDefaultServer($service, $serverId, $packageName);
    $this->syncConfigurableOptions($service, $options);
    $this->syncCustomFields($service, $customFields);
    $this->syncAddons($service, $addons);

    // Save TLD configurations if this is a domain service
    if ($isDomain && $request->has('tlds')) {
        foreach ($request->tlds as $tldData) {
            if (empty($tldData['tld'])) continue;
            
            $tld = \Plugins\Domains\src\Models\DomainTld::create([
                'service_id' => $service->id,
                'tld' => $tldData['tld'],
                'registrar_slug' => $tldData['registrar_slug'] ?? null,  // ← ADD THIS
                'dns_management' => !empty($tldData['dns_management']),
                'email_forwarding' => !empty($tldData['email_forwarding']),
                'id_protection' => !empty($tldData['id_protection']),
                'epp_code' => !empty($tldData['epp_code']),
                'auto_register' => !empty($tldData['auto_register']),
            ]);

            // Save pricing per year
            $years = !empty($tldData['years']) ? explode(',', $tldData['years']) : [1];
            foreach ($years as $year) {
                $year = (int) trim($year);
                if ($year < 1 || $year > 10) continue;
                
                \Plugins\Domains\src\Models\DomainPricing::create([
                    'tld_id' => $tld->id,
                    'years' => $year,
                    'register_price' => $tldData['register_price'] ?? 0,
                    'renewal_price' => $tldData['renewal_price'] ?? 0,
                    'transfer_price' => $tldData['transfer_price'] ?? 0,
                ]);
            }

            // Grace/Redemption
            if (!empty($tldData['grace_days'])) {
                \Plugins\Domains\src\Models\DomainPeriodPricing::create([
                    'tld_id' => $tld->id, 'period_type' => 1,
                    'days' => (int) $tldData['grace_days'],
                    'price' => $tldData['grace_price'] ?? 0,
                ]);
            }
            if (!empty($tldData['redemption_days'])) {
                \Plugins\Domains\src\Models\DomainPeriodPricing::create([
                    'tld_id' => $tld->id, 'period_type' => 2,
                    'days' => (int) $tldData['redemption_days'],
                    'price' => $tldData['redemption_price'] ?? 0,
                ]);
            }

            // Addons
            $addons = [
                ['name' => 'DNS Management', 'price' => $tldData['addon_dns'] ?? 0],
                ['name' => 'Email Forwarding', 'price' => $tldData['addon_email'] ?? 0],
                ['name' => 'ID Protection', 'price' => $tldData['addon_id'] ?? 0],
            ];
            foreach ($addons as $addon) {
                if (($addon['price'] ?? 0) > 0) {
                    \Plugins\Domains\src\Models\DomainAddon::create([
                        'tld_id' => $tld->id,
                        'name' => $addon['name'],
                        'price' => $addon['price'],
                    ]);
                }
            }
        }
    }

    return redirect()->route('admin.services.index')
        ->with('success', 'Service "' . $validated['name'] . '" added to catalog!');
}

    public function show(Service $service)
    {
        $service->load(['group', 'servers', 'options', 'customFields', 'addons', 'tlds.pricing', 'tlds.addons']);
        return view('plugins.Services::admin.show', compact('service'));
    }

    public function edit(Service $service)
    {
        $groups = ServiceGroup::orderBy('name')->get();
        $servers = Server::active()->with('packages')->get();
        $service->load(['servers', 'options', 'customFields', 'addons', 'tlds.pricing', 'tlds.addons', 'tlds.periodPricing']);
        
        return view('plugins.Services::admin.edit', compact('service', 'groups', 'servers'));
    }

    public function update(Request $request, Service $service)
    {
        $group = ServiceGroup::find($request->group_id) ?: $service->group;
        $isDomain = $group && $group->module === 'domains';

        $rules = [
            'group_id' => 'nullable|exists:service_groups,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'badge_label' => 'nullable|string|max:40',
            'features' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
            'upgradable' => 'nullable|boolean',
            'allow_downgrade' => 'nullable|boolean',
            'free_domain_cycles' => 'nullable|array',
            'free_domain_cycles.*' => 'in:monthly,quarterly,semi_annually,annually',
            'server_id' => 'nullable|exists:servers,id',
            'package_name' => 'nullable|string|max:255',
            'options' => 'nullable|array',
            'options.*.name' => 'nullable|string|max:255',
            'options.*.type' => 'nullable|in:number,dropdown,checkbox',
            'options.*.choices' => 'nullable|string|max:2000',
            'options.*.price_monthly' => 'nullable|numeric|min:0',
            'custom_fields' => 'nullable|array',
            'custom_fields.*.label' => 'nullable|string|max:255',
            'custom_fields.*.type' => 'nullable|in:text,textarea,select,checkbox,url,number',
            'custom_fields.*.options' => 'nullable|string|max:2000',
            'custom_fields.*.placeholder' => 'nullable|string|max:255',
            'custom_fields.*.help_text' => 'nullable|string|max:500',
            'custom_fields.*.is_required' => 'nullable|boolean',
            'addons' => 'nullable|array',
            'addons.*.name' => 'nullable|string|max:255',
            'addons.*.description' => 'nullable|string|max:1000',
            'addons.*.price' => 'nullable|numeric|min:0',
            'addons.*.billing_cycle' => 'nullable|in:same_as_parent,one-time,monthly,annually',
            'addons.*.is_required' => 'nullable|boolean',
        ];

        if (!$isDomain) {
            $rules['pricing'] = 'required|array|min:1';
            $rules['pricing.monthly'] = 'nullable|numeric|min:0';
            $rules['pricing.quarterly'] = 'nullable|numeric|min:0';
            $rules['pricing.semi_annually'] = 'nullable|numeric|min:0';
            $rules['pricing.annually'] = 'nullable|numeric|min:0';
            $rules['setup_fee'] = 'nullable|numeric|min:0';
            $rules['is_free'] = 'nullable|boolean';
            $rules['billing_type'] = 'nullable|in:recurring,one-time';
            $rules['billing_cycle'] = 'nullable|in:monthly,annually,one-time';
        }

        $validated = $request->validate($rules);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['is_free'] = $request->boolean('is_free');
        $validated['upgradable'] = $request->boolean('upgradable');
        $validated['allow_downgrade'] = $request->boolean('allow_downgrade');
        $validated['free_domain_cycles'] = $request->input('free_domain_cycles', []);
        $serverId = $validated['server_id'] ?? null;
        $packageName = $validated['package_name'] ?? null;
        $options = $validated['options'] ?? [];
        $customFields = $validated['custom_fields'] ?? [];
        $addons = $validated['addons'] ?? [];
        unset($validated['server_id'], $validated['package_name'], $validated['options'], $validated['custom_fields'], $validated['addons']);

        if ($isDomain) {
            $validated['amount'] = 0;
            $validated['billing_cycle'] = 'annually';
            $validated['is_free'] = false;
        } else {
            $validated['amount'] = $validated['pricing']['monthly'] ?? $validated['pricing']['annually'] ?? 0;
            $validated['billing_cycle'] = $validated['billing_cycle'] ?? 'monthly';
        }

        $validated['features'] = !empty($validated['features']) 
            ? array_map('trim', explode("\n", $validated['features'])) 
            : [];

        if (!empty($validated['pricing'])) {
            $validated['pricing'] = array_filter($validated['pricing'], fn($v) => $v !== null && $v !== '');
        }

        $service->update($validated);
        $this->syncDefaultServer($service, $serverId, $packageName);
        $this->syncConfigurableOptions($service, $options);
        $this->syncCustomFields($service, $customFields);
        $this->syncAddons($service, $addons);

        if (!$isDomain && class_exists(\Plugins\Domains\src\Models\DomainTld::class)) {
            \Plugins\Domains\src\Models\DomainTld::where('service_id', $service->id)->delete();
        }

        // Update TLD configurations for domain services
        if ($isDomain && $request->has('tlds')) {
            // Delete existing TLDs (they'll be recreated)
            \Plugins\Domains\src\Models\DomainTld::where('service_id', $service->id)->delete();
            
            foreach ($request->tlds as $tldData) {
                if (empty($tldData['tld'])) continue;
                
                $tld = \Plugins\Domains\src\Models\DomainTld::create([
                    'service_id' => $service->id,
                    'tld' => $tldData['tld'],
                    'registrar_slug' => $tldData['registrar_slug'] ?? null,  // ← ADD THIS
                    'dns_management' => !empty($tldData['dns_management']),
                    'email_forwarding' => !empty($tldData['email_forwarding']),
                    'id_protection' => !empty($tldData['id_protection']),
                    'epp_code' => !empty($tldData['epp_code']),
                    'auto_register' => !empty($tldData['auto_register']),
                ]);

                $years = !empty($tldData['years']) ? explode(',', $tldData['years']) : [1];
                foreach ($years as $year) {
                    $year = (int) trim($year);
                    if ($year < 1) continue;
                    \Plugins\Domains\src\Models\DomainPricing::create([
                        'tld_id' => $tld->id, 'years' => $year,
                        'register_price' => $tldData['register_price'] ?? 0,
                        'renewal_price' => $tldData['renewal_price'] ?? 0,
                        'transfer_price' => $tldData['transfer_price'] ?? 0,
                    ]);
                }

                if (!empty($tldData['grace_days'])) {
                    \Plugins\Domains\src\Models\DomainPeriodPricing::create([
                        'tld_id' => $tld->id, 'period_type' => 1,
                        'days' => (int) $tldData['grace_days'], 'price' => $tldData['grace_price'] ?? 0,
                    ]);
                }
                if (!empty($tldData['redemption_days'])) {
                    \Plugins\Domains\src\Models\DomainPeriodPricing::create([
                        'tld_id' => $tld->id, 'period_type' => 2,
                        'days' => (int) $tldData['redemption_days'], 'price' => $tldData['redemption_price'] ?? 0,
                    ]);
                }

                foreach ([
                    ['name' => 'DNS Management', 'price' => $tldData['addon_dns'] ?? 0],
                    ['name' => 'Email Forwarding', 'price' => $tldData['addon_email'] ?? 0],
                    ['name' => 'ID Protection', 'price' => $tldData['addon_id'] ?? 0],
                ] as $addon) {
                    if (($addon['price'] ?? 0) > 0) {
                        \Plugins\Domains\src\Models\DomainAddon::create([
                            'tld_id' => $tld->id, 'name' => $addon['name'], 'price' => $addon['price'],
                        ]);
                    }
                }
            }
        }

        return redirect()->route('admin.services.index')
            ->with('success', 'Service "' . $service->name . '" updated!');
    }

    public function destroy(Service $service)
    {
        $serviceName = $service->name;
        
        // Soft delete — keeps client_services intact
        $service->delete();

        return redirect()->route('admin.services.index')
            ->with('success', 'Service "' . $serviceName . '" removed from catalog. Existing client orders are preserved.');
    }

    // ==================== SERVICE GROUPS ====================

    public function groups()
    {
        $groups = ServiceGroup::withCount('services')->orderBy('order')->get();
        return view('plugins.Services::admin.groups', compact('groups'));
    }

    public function storeGroup(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:10',
            'description' => 'nullable|string',
            'order' => 'nullable|integer',
            'module' => ['nullable', Rule::in(['hosting', 'domains', 'ssl', 'email', 'custom'])],
            'requires_domain' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['slug'] = $this->uniqueGroupSlug($validated['name']);
        $validated['requires_domain'] = $request->boolean('requires_domain');
        $validated['is_active'] = $request->boolean('is_active', true);

        ServiceGroup::create($validated);

        return redirect()->route('admin.services.groups')
            ->with('success', 'Service group created!');
    }

    public function updateGroup(Request $request, ServiceGroup $group)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:10',
            'description' => 'nullable|string',
            'order' => 'nullable|integer',
            'module' => ['nullable', Rule::in(['hosting', 'domains', 'ssl', 'email', 'custom'])],
            'requires_domain' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['slug'] = $this->uniqueGroupSlug($validated['name'], $group->id);
        $validated['requires_domain'] = $request->boolean('requires_domain');
        $validated['is_active'] = $request->boolean('is_active', true);
        $group->update($validated);

        return redirect()->route('admin.services.groups')
            ->with('success', 'Group updated!');
    }

    public function destroyGroup(ServiceGroup $group)
    {
        if ($group->services()->count() > 0) {
            return back()->with('error', 'Cannot delete group with active services.');
        }

        $group->delete();
        return redirect()->route('admin.services.groups')
            ->with('success', 'Group deleted!');
    }

    public function servers()
    {
        $servers = Server::with(['packages' => fn ($query) => $query->where('is_active', true)->orderBy('name')])
            ->withCount(['services', 'packages'])
            ->orderBy('name')
            ->get();
        return view('plugins.Services::admin.servers', compact('servers'));
    }

    public function storeServer(Request $request)
    {
        $validated = $this->validateServer($request);

        Server::create($validated);

        return redirect()->route('admin.services.servers')->with('success', 'Server added!');
    }

    public function updateServer(Request $request, Server $server)
    {
        $validated = $this->validateServer($request, $server);

        if (($validated['password'] ?? null) === null) {
            unset($validated['password']);
        }

        if (($validated['api_token'] ?? null) === null) {
            unset($validated['api_token']);
        }

        $server->update($validated);

        return redirect()->route('admin.services.servers')->with('success', 'Server updated!');
    }

    public function testServer(Server $server)
    {
        $result = app(ServerConnectorFactory::class)->for($server)->test();
        $server->update(['status' => $result['success'] ? 'active' : 'unreachable']);
        ActivityLogger::log($result['success'] ? 'server.test.ok' : 'server.test.failed', 'Server', $server->id, $result['message'], [
            'server_id' => $server->id,
            'server_type' => $server->type,
            'details' => $result['data'] ?? [],
        ]);

        return redirect()->route('admin.services.servers')
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function syncServerPackages(Server $server)
    {
        $result = app(ServerConnectorFactory::class)->for($server)->packages();

        if (!$result['success']) {
            ActivityLogger::log('server.packages.failed', 'Server', $server->id, $result['message'], [
                'server_id' => $server->id,
                'server_type' => $server->type,
            ]);

            return redirect()->route('admin.services.servers')->with('error', $result['message']);
        }

        $server->packages()->update(['is_active' => false]);

        foreach ($result['packages'] as $package) {
            ServerPackage::updateOrCreate(
                ['server_id' => $server->id, 'name' => $package['name']],
                [
                    'display_name' => $package['display_name'] ?? $package['name'],
                    'limits' => $package['limits'] ?? [],
                    'is_active' => true,
                    'synced_at' => now(),
                ]
            );
        }

        ActivityLogger::log('server.packages.synced', 'Server', $server->id, $result['message'] ?: 'Server packages synced.', [
            'server_id' => $server->id,
            'server_type' => $server->type,
            'packages' => collect($result['packages'])->pluck('name')->values()->all(),
        ]);

        return redirect()->route('admin.services.servers')
            ->with('success', $result['message'] ?: 'Server packages synced.');
    }

    public function serverPackages(Server $server)
    {
        return response()->json($server->packages()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'display_name', 'limits']));
    }

    protected function validateServer(Request $request, ?Server $server = null): array
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => ['required', Rule::in(['cpanel', 'plesk', 'directadmin', 'webuzo', 'cyberpanel', 'ispconfig', 'proxmox', 'vmware', 'irc', 'custom'])],
            'hostname' => ['required', 'string', 'max:255', 'regex:/^(https?:\/\/)?[a-zA-Z0-9.-]+(\/.*)?$/'],
            'ip_address' => 'required|ip',
            'username' => 'nullable|string',
            'password' => 'nullable|string',
            'api_token' => 'nullable|string',
            'port' => 'nullable|integer|min:1|max:65535',
            'use_ssl' => 'nullable|boolean',
            'status' => ['nullable', Rule::in(['active', 'inactive', 'maintenance', 'unreachable'])],
            'max_accounts' => 'nullable|integer|min:0',
            'current_accounts' => 'nullable|integer|min:0',
            'nameserver_list' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        if (array_key_exists('nameserver_list', $validated)) {
            $validated['nameservers'] = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) $validated['nameserver_list']))));
        }

        unset($validated['nameserver_list']);
        $validated['use_ssl'] = $request->boolean('use_ssl');
        $validated['status'] = $validated['status'] ?? 'active';
        $validated['port'] = $validated['port'] ?? $this->defaultServerPort($validated['type'], $validated['use_ssl']);
        $validated['max_accounts'] = $validated['max_accounts'] ?? 0;
        $validated['current_accounts'] = $validated['current_accounts'] ?? 0;

        return $validated;
    }

    public function destroyServer(Server $server)
    {
        if ($server->services()->exists()) {
            return redirect()->route('admin.services.servers')
                ->with('error', 'Cannot delete a server assigned to services.');
        }

        $server->delete();
        return redirect()->route('admin.services.servers')->with('success', 'Server removed!');
    }

    public function editGroup(ServiceGroup $group)
    {
        return response()->json($group);
    }

    public function moduleConfig(string $module)
    {
        $injector = \App\Core\Registries\ModuleRegistry::getConfigInjector($module);
        
        if (!$injector) {
            return response('<p style="color:var(--jv-gray-500);">No configuration available for this module.</p>');
        }

        $service = null;
        if (request()->service_id) {
            $service = Service::find(request()->service_id);
        }

        return response(call_user_func($injector, $service));
    }

    protected function uniqueGroupSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'group';
        $slug = $base;
        $count = 2;

        while (
            ServiceGroup::where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base . '-' . $count++;
        }

        return $slug;
    }

    protected function defaultServerPort(string $type, bool $ssl): int
    {
        return match ($type) {
            'cpanel' => $ssl ? 2087 : 2086,
            'plesk' => 8443,
            'directadmin' => 2222,
            'webuzo' => $ssl ? 2005 : 2004,
            'cyberpanel' => 8090,
            'ispconfig' => 8080,
            'proxmox' => 8006,
            'vmware' => 443,
            'irc' => $ssl ? 6697 : 6667,
            default => $ssl ? 443 : 80,
        };
    }

    protected function syncDefaultServer(Service $service, $serverId, ?string $packageName = null): void
    {
        DB::table('server_service')->where('service_id', $service->id)->delete();

        if (!$serverId) {
            return;
        }

        DB::table('server_service')->insert([
            'server_id' => $serverId,
            'service_id' => $service->id,
            'package_name' => $packageName ?: Str::slug($service->name),
            'limits' => null,
            'is_default' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function syncConfigurableOptions(Service $service, array $options): void
    {
        $service->options()->delete();

        foreach ($options as $index => $option) {
            if (empty($option['name'])) {
                continue;
            }

            $service->options()->create([
                'name' => $option['name'],
                'type' => $option['type'] ?? 'number',
                'options' => $this->optionChoices($option['choices'] ?? ''),
                'prices' => [
                    'monthly' => (float) ($option['price_monthly'] ?? 0),
                ],
                'is_required' => false,
                'sort_order' => $index,
                'is_active' => true,
            ]);
        }
    }

    protected function syncCustomFields(Service $service, array $fields): void
    {
        $service->customFields()->delete();

        foreach ($fields as $index => $field) {
            if (empty($field['label'])) {
                continue;
            }

            $label = trim($field['label']);

            $service->customFields()->create([
                'name' => $this->uniqueChildName($service, 'customFields', $label),
                'label' => $label,
                'type' => $field['type'] ?? 'text',
                'options' => $field['options'] ?? null,
                'placeholder' => $field['placeholder'] ?? null,
                'help_text' => $field['help_text'] ?? null,
                'is_required' => !empty($field['is_required']),
                'is_public' => true,
                'sort_order' => $index,
            ]);
        }
    }

    protected function syncAddons(Service $service, array $addons): void
    {
        $service->addons()->delete();

        foreach ($addons as $index => $addon) {
            if (empty($addon['name'])) {
                continue;
            }

            $service->addons()->create([
                'name' => trim($addon['name']),
                'description' => $addon['description'] ?? null,
                'price' => (float) ($addon['price'] ?? 0),
                'billing_cycle' => $addon['billing_cycle'] ?? 'same_as_parent',
                'is_required' => !empty($addon['is_required']),
                'is_active' => true,
                'sort_order' => $index,
            ]);
        }
    }

    protected function optionChoices(?string $choices): array
    {
        return collect(preg_split('/\r\n|\r|\n/', (string) $choices))
            ->map(fn ($choice) => trim($choice))
            ->filter()
            ->values()
            ->all();
    }

    protected function uniqueChildName(Service $service, string $relation, string $label): string
    {
        $base = Str::slug($label, '_') ?: 'field';
        $name = $base;
        $count = 2;

        while ($service->{$relation}()->where('name', $name)->exists()) {
            $name = $base . '_' . $count++;
        }

        return $name;
    }
}
