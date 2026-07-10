<?php

namespace Plugins\SmsNotifications\src;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SmsController extends Controller
{
    public function index()
    {
        $config = [
            'provider' => \App\Models\Setting::get('sms_provider', 'Africa\'s Talking'),
            'api_key' => \App\Models\Setting::get('sms_api_key', ''),
            'sender_id' => \App\Models\Setting::get('sms_sender_id', 'JamVini'),
            'is_configured' => !empty(\App\Models\Setting::get('sms_api_key')),
        ];

        // Mock SMS log
        $logs = [
            ['to' => '+255712345678', 'message' => 'Invoice paid. Thank you!', 'status' => 'sent', 'date' => now()->subDays(1)],
            ['to' => '+255798765432', 'message' => 'Domain expiring soon!', 'status' => 'failed', 'date' => now()->subDays(3)],
        ];

        return view('plugins.sms-notifications::admin.index', compact('config', 'logs'));
    }

    public function settings(Request $request)
    {
        if ($request->isMethod('post')) {
            $validated = $request->validate([
                'provider' => 'required|string',
                'api_key' => 'required|string',
                'sender_id' => 'required|string|max:11',
            ]);

            \App\Models\Setting::set('sms_provider', $validated['provider']);
            \App\Models\Setting::set('sms_api_key', $validated['api_key']);
            \App\Models\Setting::set('sms_sender_id', $validated['sender_id']);

            return redirect()->route('admin.sms.index')
                ->with('success', 'SMS configuration saved!');
        }

        return view('plugins.sms-notifications::admin.settings');
    }
}