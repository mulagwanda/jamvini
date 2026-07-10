<?php

namespace Plugins\TzRegistryRegistrar\src\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Plugins\TzRegistryRegistrar\src\Services\TzRegistryRegistrar;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = $this->settings();
        $operations = DB::table('domain_registrar_operations')
            ->where('registrar_slug', TzRegistryRegistrar::SLUG)
            ->latest()
            ->limit(10)
            ->get();

        return view('plugins.TzRegistryRegistrar::admin.settings', compact('settings', 'operations'));
    }

    public function save(Request $request)
    {
        $validated = $request->validate([
            'tznic_enabled' => 'nullable|boolean',
            'tznic_host' => 'required|string|max:255',
            'tznic_port' => 'required|integer|min:1|max:65535',
            'tznic_username' => 'required|string|max:255',
            'tznic_password' => 'nullable|string|max:1000',
            'tznic_certificate_path' => 'required|string|max:1000',
            'tznic_private_key_path' => 'nullable|string|max:1000',
            'tznic_private_key_passphrase' => 'nullable|string|max:1000',
            'tznic_verify_peer' => 'nullable|boolean',
            'tznic_timeout' => 'required|integer|min:5|max:120',
            'tznic_rate_limit_seconds' => 'required|numeric|min:0.1|max:10',
            'tznic_availability_cache_seconds' => 'required|integer|min:0|max:86400',
            'tznic_log_xml' => 'nullable|boolean',
            'tznic_domain_sync_enabled' => 'nullable|boolean',
            'tznic_pricing_sync_enabled' => 'nullable|boolean',
            'tznic_pricing_json' => 'nullable|string',
        ]);

        foreach (['tznic_enabled', 'tznic_verify_peer', 'tznic_log_xml', 'tznic_domain_sync_enabled', 'tznic_pricing_sync_enabled'] as $checkbox) {
            $validated[$checkbox] = $request->boolean($checkbox) ? '1' : '0';
        }

        if (($validated['tznic_password'] ?? '') === '') {
            unset($validated['tznic_password']);
        }

        if (($validated['tznic_private_key_passphrase'] ?? '') === '') {
            unset($validated['tznic_private_key_passphrase']);
        }

        foreach ($validated as $key => $value) {
            Setting::set($key, $value, 'registrar', ucwords(str_replace('_', ' ', $key)));
        }

        return redirect()->route('admin.tznic.settings')->with('success', 'tzNIC registrar settings saved.');
    }

    public function test(TzRegistryRegistrar $registrar)
    {
        if (!$registrar->isConfigured()) {
            return response()->json(['success' => false, 'message' => 'tzNIC registrar is not fully configured.']);
        }

        $domain = 'jamvini-test-' . strtolower(Str::random(8)) . '.co.tz';
        $result = $registrar->check($domain);

        return response()->json([
            'success' => empty($result['error']),
            'message' => $result['message'] ?? 'Connection tested.',
            'data' => $result,
        ]);
    }

    public function syncDomains(TzRegistryRegistrar $registrar)
    {
        $result = $registrar->syncAllDomains(100);

        return redirect()->route('admin.tznic.settings')
            ->with('success', "Domain sync finished: {$result['synced']} synced, {$result['failed']} failed.");
    }

    public function syncPricing(TzRegistryRegistrar $registrar)
    {
        $result = $registrar->syncPricing();

        return redirect()->route('admin.tznic.settings')
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    protected function settings(): array
    {
        return [
            'enabled' => Setting::get('tznic_enabled', '0'),
            'host' => Setting::get('tznic_host', 'epp.tznic.or.tz'),
            'port' => Setting::get('tznic_port', '700'),
            'username' => Setting::get('tznic_username', ''),
            'certificate_path' => Setting::get('tznic_certificate_path', ''),
            'private_key_path' => Setting::get('tznic_private_key_path', ''),
            'verify_peer' => Setting::get('tznic_verify_peer', '1'),
            'timeout' => Setting::get('tznic_timeout', '30'),
            'rate_limit_seconds' => Setting::get('tznic_rate_limit_seconds', '0.5'),
            'availability_cache_seconds' => Setting::get('tznic_availability_cache_seconds', '300'),
            'log_xml' => Setting::get('tznic_log_xml', '0'),
            'domain_sync_enabled' => Setting::get('tznic_domain_sync_enabled', '1'),
            'pricing_sync_enabled' => Setting::get('tznic_pricing_sync_enabled', '0'),
            'pricing_json' => Setting::get('tznic_pricing_json', ''),
        ];
    }
}
