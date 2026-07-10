<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class SystemController extends Controller
{
    public function index()
    {
        $coreVersion = require app_path('Core/version.php');
        $diskFree = disk_free_space(storage_path());
        $diskTotal = disk_total_space(storage_path());
        $diskUsedPercent = $diskTotal > 0 ? round((($diskTotal - $diskFree) / $diskTotal) * 100, 1) : 0;

        $info = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'core_version' => $coreVersion,
            'database' => DB::connection()->getPdo()->getAttribute(\PDO::ATTR_SERVER_VERSION),
            'database_driver' => DB::connection()->getDriverName(),
            'environment' => app()->environment(),
            'debug' => config('app.debug'),
            'app_url' => config('app.url'),
            'timezone' => config('app.timezone'),
            'cache_driver' => config('cache.default'),
            'session_driver' => config('session.driver'),
            'queue_driver' => config('queue.default'),
            'mail_mailer' => config('mail.default'),
            'disk_free' => $this->formatBytes($diskFree),
            'disk_total' => $this->formatBytes($diskTotal),
            'disk_used_percent' => $diskUsedPercent,
            'plugins_count' => \App\Models\Plugin::count(),
            'active_plugins' => \App\Models\Plugin::where('is_active', true)->count(),
            'installed' => file_exists(storage_path('app/installed')),
            'storage_linked' => File::exists(public_path('storage')),
        ];

        $checks = $this->healthChecks($info);

        return view('admin.system', compact('info', 'checks'));
    }

    public function clearCache()
    {
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        
        return redirect()->route('admin.system.index')
            ->with('success', 'Application cache cleared!');
    }

    public function clearViews()
    {
        Artisan::call('view:clear');
        
        return redirect()->route('admin.system.index')
            ->with('success', 'View cache cleared!');
    }

    public function clearRoutes()
    {
        Artisan::call('route:clear');
        
        return redirect()->route('admin.system.index')
            ->with('success', 'Route cache cleared!');
    }

    public function runMigrations()
    {
        try {
            Artisan::call('migrate', ['--force' => true]);
            $output = trim(Artisan::output());

            $manager = app(\App\Core\PluginManager::class);
            $manager->syncSystemPlugins();

            foreach (\App\Models\Plugin::where('is_active', true)->pluck('slug') as $slug) {
                $manager->runMigrations($slug);
            }
        } catch (\Throwable $e) {
            return redirect()->route('admin.system.index')
                ->with('error', 'Migration failed: ' . $e->getMessage());
        }
        
        return redirect()->route('admin.system.index')
            ->with('success', 'Database migrations completed!' . ($output ? ' ' . $output : ''));
    }

    public function createStorageLink()
    {
        if (File::exists(public_path('storage'))) {
            return redirect()->route('admin.system.index')
                ->with('success', 'Storage link already exists.');
        }

        try {
            Artisan::call('storage:link');
        } catch (\Throwable $e) {
            return redirect()->route('admin.system.index')
                ->with('error', 'Storage link failed: ' . $e->getMessage());
        }

        return redirect()->route('admin.system.index')
            ->with('success', 'Storage link created.');
    }

    protected function healthChecks(array $info): array
    {
        $core = $info['core_version'];

        return [
            [
                'label' => 'Installed',
                'status' => $info['installed'],
                'detail' => $info['installed'] ? 'Installation marker found.' : 'Installation marker is missing.',
            ],
            [
                'label' => 'PHP Version',
                'status' => version_compare(PHP_VERSION, $core['min_php'] ?? '8.2', '>='),
                'detail' => 'Running PHP ' . PHP_VERSION . ', minimum ' . ($core['min_php'] ?? '8.2') . '.',
            ],
            [
                'label' => 'Production Debug',
                'status' => app()->environment('production') ? !$info['debug'] : true,
                'detail' => $info['debug'] ? 'Debug mode is enabled.' : 'Debug mode is disabled.',
            ],
            [
                'label' => 'Storage Writable',
                'status' => is_writable(storage_path()) && is_writable(storage_path('framework')),
                'detail' => 'Storage and framework cache directories must be writable.',
            ],
            [
                'label' => 'Bootstrap Cache Writable',
                'status' => is_writable(base_path('bootstrap/cache')),
                'detail' => 'Required for Laravel cached configuration and routes.',
            ],
            [
                'label' => 'Public Storage Link',
                'status' => $info['storage_linked'],
                'detail' => $info['storage_linked'] ? 'Public storage link exists.' : 'Needed for uploaded logos, media, and documents.',
            ],
            [
                'label' => 'Required PHP Extensions',
                'status' => $this->extensionsAvailable(['ctype', 'curl', 'dom', 'fileinfo', 'filter', 'hash', 'mbstring', 'openssl', 'pdo', 'tokenizer', 'xml']),
                'detail' => 'Required: ctype, curl, dom, fileinfo, filter, hash, mbstring, openssl, pdo, tokenizer, xml.',
            ],
            [
                'label' => 'Disk Space',
                'status' => $info['disk_used_percent'] < 90,
                'detail' => $info['disk_used_percent'] . '% used.',
            ],
        ];
    }

    protected function extensionsAvailable(array $extensions): bool
    {
        foreach ($extensions as $extension) {
            if (!extension_loaded($extension)) {
                return false;
            }
        }

        return true;
    }

    protected function formatBytes(int|float $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $value = max($bytes, 0);
        $unit = 0;

        while ($value >= 1024 && $unit < count($units) - 1) {
            $value /= 1024;
            $unit++;
        }

        return round($value, $unit === 0 ? 0 : 2) . ' ' . $units[$unit];
    }
}
