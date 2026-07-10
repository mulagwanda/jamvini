<?php

namespace Plugins\ResellerClubRegistrar\src\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Plugins\ResellerClubRegistrar\src\ResellerClubApi;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = [
            'reseller_id' => Setting::get('resellerclub_reseller_id', ''),
            'api_key' => Setting::get('resellerclub_api_key', ''),
            'customer_id' => Setting::get('resellerclub_customer_id', ''),
            'contact_id' => Setting::get('resellerclub_contact_id', ''),
            'test_mode' => Setting::get('resellerclub_test_mode', '1'),
            'auto_register' => Setting::get('resellerclub_auto_register', '0'),
        ];

        $connectionStatus = $this->testConnection($settings);

        return view('plugins.ResellerClubRegistrar::admin.settings', compact('settings', 'connectionStatus'));
    }

    public function save(Request $request)
    {
        $validated = $request->validate([
            'resellerclub_reseller_id' => 'required|string',
            'resellerclub_api_key' => 'required|string',
            'resellerclub_customer_id' => 'nullable|string',
            'resellerclub_contact_id' => 'nullable|string',
            'resellerclub_test_mode' => 'boolean',
            'resellerclub_auto_register' => 'boolean',
        ]);

        foreach ($validated as $key => $value) {
            Setting::set($key, $value);
        }

        return redirect()->route('admin.resellerclub.settings')
            ->with('success', 'ResellerClub settings saved!');
    }

    public function testApi(Request $request)
    {
        $api = new ResellerClubApi();
        
        if (!$api->isConfigured()) {
            return response()->json(['success' => false, 'message' => 'API not configured']);
        }

        // Test with a known domain
        $result = $api->checkAvailability('google', ['.com']);

        return response()->json([
            'success' => !isset($result['error']),
            'message' => isset($result['error']) ? 'Connection failed' : 'Connection successful',
            'data' => $result,
        ]);
    }

    protected function testConnection(array $settings): ?string
    {
        if (empty($settings['reseller_id']) || empty($settings['api_key'])) {
            return null;
        }

        try {
            $api = new ResellerClubApi();
            $result = $api->checkAvailability('testdomain', ['.com']);
            
            if (isset($result['error'])) {
                return 'failed';
            }
            
            return 'connected';
        } catch (\Exception $e) {
            return 'error: ' . $e->getMessage();
        }
    }
}