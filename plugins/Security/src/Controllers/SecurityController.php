<?php

namespace Plugins\Security\src\Controllers;

use App\Core\Hooks\Action;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Plugins\Security\src\Models\SecuritySetting;

class SecurityController extends Controller
{
    public function index()
    {
        $settings = [
            'firewall_enabled' => SecuritySetting::get('firewall_enabled', '1'),
            'rate_limit_enabled' => SecuritySetting::get('rate_limit_enabled', '1'),
            'rate_limit_per_minute' => SecuritySetting::get('rate_limit_per_minute', '120'),
            'scan_paths' => SecuritySetting::get('scan_paths', 'app,bootstrap,config,routes,plugins,themes'),
        ];

        $events = Schema::hasTable('security_events') ? DB::table('security_events')->latest('occurred_at')->limit(25)->get() : collect();
        $rules = Schema::hasTable('security_ip_rules') ? DB::table('security_ip_rules')->latest()->limit(50)->get() : collect();
        $scanResults = Schema::hasTable('security_file_scan_results') ? DB::table('security_file_scan_results')->latest('scanned_at')->limit(20)->get() : collect();
        $stats = [
            'critical' => $events->where('severity', 'critical')->count(),
            'high' => $events->where('severity', 'high')->count(),
            'blocked' => $events->whereIn('type', ['ip_blocked', 'firewall_pattern'])->count(),
            'rules' => $rules->count(),
        ];

        return view('plugins.Security::admin.index', compact('settings', 'events', 'rules', 'scanResults', 'stats'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'firewall_enabled' => ['nullable', 'boolean'],
            'rate_limit_enabled' => ['nullable', 'boolean'],
            'rate_limit_per_minute' => ['required', 'integer', 'min:30', 'max:5000'],
            'scan_paths' => ['nullable', 'string', 'max:2000'],
        ]);

        $validated['firewall_enabled'] = $request->boolean('firewall_enabled') ? '1' : '0';
        $validated['rate_limit_enabled'] = $request->boolean('rate_limit_enabled') ? '1' : '0';

        foreach ($validated as $key => $value) {
            SecuritySetting::set($key, $value);
        }

        return back()->with('success', 'Security settings saved.');
    }

    public function storeRule(Request $request)
    {
        $validated = $request->validate([
            'ip_address' => ['required', 'ip'],
            'action' => ['required', 'in:allow,block'],
            'reason' => ['nullable', 'string', 'max:255'],
            'expires_at' => ['nullable', 'date'],
        ]);

        DB::table('security_ip_rules')->updateOrInsert(
            ['ip_address' => $validated['ip_address'], 'action' => $validated['action']],
            [
                'reason' => $validated['reason'] ?? null,
                'expires_at' => $validated['expires_at'] ?? null,
                'is_active' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return back()->with('success', 'IP rule saved.');
    }

    public function deleteRule(int $id)
    {
        DB::table('security_ip_rules')->where('id', $id)->delete();

        return back()->with('success', 'IP rule removed.');
    }

    public function scan()
    {
        $paths = preg_split('/[\s,]+/', (string) SecuritySetting::get('scan_paths', 'app,bootstrap,config,routes,plugins,themes'), -1, PREG_SPLIT_NO_EMPTY);
        $dangerPatterns = ['eval(', 'base64_decode(', 'shell_exec(', 'passthru(', 'system(', 'assert('];
        $scanned = 0;
        $flagged = 0;

        foreach ($paths as $path) {
            $root = base_path(trim($path, '/'));
            if (!File::isDirectory($root)) {
                continue;
            }

            foreach (File::allFiles($root) as $file) {
                if (!in_array($file->getExtension(), ['php', 'js', 'blade.php'], true) && !str_ends_with($file->getFilename(), '.blade.php')) {
                    continue;
                }

                $relative = str_replace(base_path() . '/', '', $file->getPathname());
                $content = File::get($file->getPathname());
                $matches = array_values(array_filter($dangerPatterns, fn ($pattern) => str_contains($content, $pattern)));
                $status = $matches ? 'review' : 'ok';
                $message = $matches ? 'Review suspicious functions: ' . implode(', ', $matches) : null;

                DB::table('security_file_scan_results')->updateOrInsert(
                    ['path' => $relative],
                    [
                        'status' => $status,
                        'hash' => hash_file('sha256', $file->getPathname()),
                        'message' => $message,
                        'scanned_at' => now(),
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
                $scanned++;
                if ($matches) {
                    $flagged++;
                }
            }
        }

        $this->logEvent($flagged ? 'medium' : 'info', 'file_scan', "File scan completed. {$scanned} files scanned, {$flagged} flagged.");
        Action::do('security.scan_completed', ['scanned' => $scanned, 'flagged' => $flagged]);

        return back()->with('success', "Security scan completed. {$scanned} files scanned, {$flagged} need review.");
    }

    protected function logEvent(string $severity, string $type, string $message): void
    {
        if (!Schema::hasTable('security_events')) {
            return;
        }

        DB::table('security_events')->insert([
            'severity' => $severity,
            'type' => $type,
            'ip_address' => request()->ip(),
            'url' => request()->fullUrl(),
            'user_agent' => substr((string) request()->userAgent(), 0, 512),
            'message' => $message,
            'context' => json_encode(['admin_id' => auth('admin')->id()]),
            'occurred_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
