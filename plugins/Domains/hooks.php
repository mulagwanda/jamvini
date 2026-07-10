<?php

use App\Core\Registries\ModuleRegistry;
use App\Core\Hooks\Action;
use App\Core\Registries\RegistrarRegistry;

// Register as a service module
ModuleRegistry::register('domains', [
    'name' => 'Domain Registration',
    'icon' => 'globe',
    'config_injector' => function($service = null) {
        $tlds = [];
        if ($service) {
            $tlds = \Plugins\Domains\src\Models\DomainTld::where('service_id', $service->id)
                ->with(['pricing', 'addons', 'periodPricing'])
                ->get();
        }
        return view('plugins.Domains::admin.service-config', compact('service', 'tlds'));
    },
]);

// Listen for order completion to register domains
Action::add('order.completed', function($order) {
    foreach ($order->items as $item) {
        if (!in_array($item->type, ['domain', 'domain_transfer'])) continue;
        if (empty($item->domain)) continue;
        
        // Extract TLD
        $parts = explode('.', strtolower($item->domain));
        $tld = '.' . (count($parts) >= 3 && in_array($parts[count($parts)-2], ['co', 'or', 'go', 'ac'])
            ? $parts[count($parts)-2] . '.' . $parts[count($parts)-1]
            : $parts[count($parts)-1]);
        
        // Find registrar for this TLD
        $registrarSlug = get_registrar_for_tld($tld);
        
        if (!$registrarSlug) {
            \App\Core\ActivityLogger::log('warning', 'Domain', null, 
                "No registrar configured for {$item->domain} — manual registration required");
            continue;
        }
        
        $registrarConfig = RegistrarRegistry::get($registrarSlug);
        if (!$registrarConfig || !$registrarConfig['class']) {
            continue;
        }
        
        try {
            $registrar = app($registrarConfig['class']);
            
            if (!$registrar->isConfigured()) {
                \App\Core\ActivityLogger::log('warning', 'Domain', null,
                    "Registrar {$registrarSlug} not configured — {$item->domain} needs manual registration");
                continue;
            }
            
            // Get nameservers from order item options or defaults
            $nameservers = $item->options['nameservers'] ?? [
                \App\Models\Setting::get('domain_default_ns1', 'ns1.jamvini.co.tz'),
                \App\Models\Setting::get('domain_default_ns2', 'ns2.jamvini.co.tz'),
            ];
            $nameservers = array_filter($nameservers);
            
            $contact = [
                'first_name' => $order->client->first_name,
                'last_name' => $order->client->last_name,
                'email' => $order->client->email,
                'phone' => $order->client->phone,
                'company' => $order->client->company_name,
                'address' => $order->client->address,
                'city' => $order->client->city,
                'country' => $order->client->country ?? 'TZ',
            ];
            
            if ($item->type === 'domain_transfer') {
                $eppCode = $item->options['epp_code'] ?? '';
                $result = $registrar->transfer($item->domain, $eppCode, $item->years ?? 1);
            } else {
                $result = $registrar->register($item->domain, $item->years ?? 1, $nameservers, $contact);
            }
            
            // Update domain record
            $domain = \Plugins\Domains\src\Models\Domain::where('domain_name', $item->domain)->first();
            if ($domain) {
                $domain->update([
                    'registrar' => $registrarSlug,
                    'registration_date' => now(),
                    'expiry_date' => now()->addYears($item->years ?? 1),
                    'status' => 'active',
                ]);
            }
            
            \App\Core\ActivityLogger::log('success', 'Domain', $domain->id ?? null,
                "Domain {$item->domain} registered via {$registrarSlug}");
                
        } catch (\Exception $e) {
            \App\Core\ActivityLogger::log('error', 'Domain', null,
                "Failed to register {$item->domain}: " . $e->getMessage());
        }
    }
});
