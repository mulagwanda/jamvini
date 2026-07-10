<?php

namespace Plugins\MaintenanceMode\src\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Plugins\MaintenanceMode\src\Models\MaintenanceSetting;
use Symfony\Component\HttpFoundation\Response;

class MaintenanceModeMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$this->shouldIntercept($request)) {
            return $next($request);
        }

        $settings = [
            'enabled' => MaintenanceSetting::get('enabled', '0'),
            'title' => MaintenanceSetting::get('title', 'We are improving your experience'),
            'message' => MaintenanceSetting::get('message', 'JamVini is currently undergoing scheduled maintenance. Please check back soon.'),
            'scheduled_start_at' => MaintenanceSetting::get('scheduled_start_at', ''),
            'scheduled_end_at' => MaintenanceSetting::get('scheduled_end_at', ''),
            'contact_email' => MaintenanceSetting::get('contact_email', ''),
            'template_style' => MaintenanceSetting::get('template_style', 'calm'),
        ];

        return response()
            ->view('plugins.MaintenanceMode::public.maintenance', compact('settings'), 503)
            ->header('Retry-After', '300');
    }

    protected function shouldIntercept(Request $request): bool
    {
        try {
            if (!$this->pluginIsActive()) {
                return false;
            }

            if (!Schema::hasTable('maintenance_settings') || MaintenanceSetting::get('enabled', '0') !== '1') {
                return false;
            }
        } catch (\Throwable $e) {
            return false;
        }

        if ($request->is('admin*', 'install*', 'maintenance/preview', 'storage/*', 'build/*', 'vendor/*', 'css/*', 'js/*')) {
            return false;
        }

        if ($request->expectsJson()) {
            return false;
        }

        $bypass = preg_split('/[\s,]+/', (string) MaintenanceSetting::get('bypass_ips', ''), -1, PREG_SPLIT_NO_EMPTY);

        return !in_array($request->ip(), $bypass, true);
    }

    protected function pluginIsActive(): bool
    {
        return Schema::hasTable('plugins')
            && DB::table('plugins')->where('slug', 'maintenance-mode')->where('is_active', true)->exists();
    }
}
