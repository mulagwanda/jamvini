<?php

namespace Plugins\MaintenanceMode\src\Controllers;

use App\Core\Hooks\Action;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Plugins\MaintenanceMode\src\Models\MaintenanceSetting;

class MaintenanceController extends Controller
{
    public function index()
    {
        $settings = $this->settings();
        $events = Schema::hasTable('maintenance_events')
            ? DB::table('maintenance_events')->latest()->limit(20)->get()
            : collect();

        return view('plugins.MaintenanceMode::admin.index', compact('settings', 'events'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'enabled' => ['nullable', 'boolean'],
            'title' => ['nullable', 'string', 'max:255'],
            'message' => ['nullable', 'string', 'max:2000'],
            'scheduled_start_at' => ['nullable', 'date'],
            'scheduled_end_at' => ['nullable', 'date', 'after_or_equal:scheduled_start_at'],
            'bypass_ips' => ['nullable', 'string', 'max:4000'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'template_style' => ['nullable', 'in:calm,bold,minimal'],
        ]);

        $wasEnabled = MaintenanceSetting::get('enabled', '0') === '1';
        $validated['enabled'] = $request->boolean('enabled') ? '1' : '0';

        foreach ($validated as $key => $value) {
            MaintenanceSetting::set($key, $value ?? '');
        }

        if (Schema::hasTable('maintenance_events')) {
            DB::table('maintenance_events')->insert([
                'type' => $validated['enabled'] === '1' ? 'enabled' : 'disabled',
                'title' => $validated['title'] ?? null,
                'message' => $validated['message'] ?? null,
                'scheduled_start_at' => $validated['scheduled_start_at'] ?? null,
                'scheduled_end_at' => $validated['scheduled_end_at'] ?? null,
                'status' => $validated['enabled'] === '1' ? 'active' : 'completed',
                'metadata' => json_encode(['admin_id' => auth('admin')->id()]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (!$wasEnabled && $validated['enabled'] === '1') {
            Action::do('maintenance.enabled', $validated);
        }
        if ($wasEnabled && $validated['enabled'] !== '1') {
            Action::do('maintenance.disabled', $validated);
        }

        return back()->with('success', 'Maintenance settings saved.');
    }

    public function preview()
    {
        return response()->view('plugins.MaintenanceMode::public.maintenance', ['settings' => $this->settings()], 503);
    }

    protected function settings(): array
    {
        return [
            'enabled' => MaintenanceSetting::get('enabled', '0'),
            'title' => MaintenanceSetting::get('title', 'We are improving your experience'),
            'message' => MaintenanceSetting::get('message', 'JamVini is currently undergoing scheduled maintenance. Please check back soon.'),
            'scheduled_start_at' => MaintenanceSetting::get('scheduled_start_at', ''),
            'scheduled_end_at' => MaintenanceSetting::get('scheduled_end_at', ''),
            'bypass_ips' => MaintenanceSetting::get('bypass_ips', ''),
            'contact_email' => MaintenanceSetting::get('contact_email', ''),
            'template_style' => MaintenanceSetting::get('template_style', 'calm'),
        ];
    }
}
