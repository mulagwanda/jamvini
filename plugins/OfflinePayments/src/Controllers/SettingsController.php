<?php

namespace Plugins\OfflinePayments\src\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = [
            'bank_enabled' => Setting::get('offline_bank_enabled', '1'),
            'bank_instructions' => Setting::get('offline_bank_instructions', "Bank: Example Bank\nAccount Name: Your Company\nAccount Number: 0000000000\nReference: Invoice number"),
            'cash_enabled' => Setting::get('offline_cash_enabled', '1'),
            'cash_instructions' => Setting::get('offline_cash_instructions', 'Please visit our office to complete payment.'),
        ];

        return view('plugins.OfflinePayments::admin.settings', compact('settings'));
    }

    public function save(Request $request)
    {
        $validated = $request->validate([
            'offline_bank_enabled' => 'nullable|boolean',
            'offline_bank_instructions' => 'nullable|string|max:4000',
            'offline_cash_enabled' => 'nullable|boolean',
            'offline_cash_instructions' => 'nullable|string|max:2000',
        ]);

        foreach (['offline_bank_enabled', 'offline_cash_enabled'] as $key) {
            $validated[$key] = $request->boolean($key) ? '1' : '0';
        }

        foreach ($validated as $key => $value) {
            Setting::set($key, $value, 'payments', ucwords(str_replace('_', ' ', $key)));
        }

        return redirect()->route('admin.offline-payments.settings')->with('success', 'Offline payment settings saved.');
    }
}
