<?php

namespace Plugins\Hosting\src\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class HostingController extends Controller
{
    public function settings()
    {
        $servers = \Plugins\Services\src\Models\Server::active()->get();
        $defaultServer = Setting::get('hosting_default_server');
        
        return view('plugins.Hosting::admin.settings', compact('servers', 'defaultServer'));
    }

    public function saveSettings(Request $request)
    {
        $validated = $request->validate([
            'hosting_default_server' => 'nullable|exists:servers,id',
        ]);

        Setting::set('hosting_default_server', $validated['hosting_default_server'] ?? '');

        return redirect()->route('admin.hosting.settings')
            ->with('success', 'Hosting automation settings saved.');
    }
}
