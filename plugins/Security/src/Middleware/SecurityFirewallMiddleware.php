<?php

namespace Plugins\Security\src\Middleware;

use App\Core\Hooks\Action;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Plugins\Security\src\Models\SecuritySetting;
use Symfony\Component\HttpFoundation\Response;

class SecurityFirewallMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$this->enabled() || $request->is('install*')) {
            return $next($request);
        }

        $ipDecision = $this->ipDecision($request->ip());
        if ($ipDecision === 'allow') {
            return $next($request);
        }
        if ($ipDecision === 'block') {
            $this->log('high', 'ip_blocked', $request, 'Blocked IP rule matched.');
            Action::do('security.ip_blocked', $request->ip());
            abort(403, 'Access denied.');
        }

        if ($this->isSuspicious($request)) {
            $this->log('high', 'firewall_pattern', $request, 'Suspicious request pattern blocked.');
            Action::do('security.threat_detected', $request);
            abort(403, 'Request blocked.');
        }

        if ($this->rateLimited($request)) {
            $this->log('medium', 'rate_limited', $request, 'Too many requests from this IP.');
            abort(429, 'Too many requests.');
        }

        return $next($request);
    }

    protected function enabled(): bool
    {
        try {
            return Schema::hasTable('plugins')
                && DB::table('plugins')->where('slug', 'security')->where('is_active', true)->exists()
                && SecuritySetting::get('firewall_enabled', '1') === '1';
        } catch (\Throwable $e) {
            return false;
        }
    }

    protected function ipDecision(?string $ip): ?string
    {
        if (!$ip || !Schema::hasTable('security_ip_rules')) {
            return null;
        }

        $rule = DB::table('security_ip_rules')
            ->where('ip_address', $ip)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->orderByRaw("case when action = 'allow' then 0 else 1 end")
            ->first();

        return $rule?->action;
    }

    protected function isSuspicious(Request $request): bool
    {
        $target = strtolower($request->path() . '?' . $request->getQueryString());
        $patterns = [
            '../', '..%2f', '%2e%2e', '<script', 'union+select', 'union select',
            'wp-admin', 'wp-login.php', '.env', 'composer.json', 'vendor/phpunit',
            'base64_decode', 'eval(', 'cmd=', 'shell_exec',
        ];

        foreach ($patterns as $pattern) {
            if (str_contains($target, $pattern)) {
                return true;
            }
        }

        return false;
    }

    protected function rateLimited(Request $request): bool
    {
        if (SecuritySetting::get('rate_limit_enabled', '1') !== '1') {
            return false;
        }

        $limit = (int) SecuritySetting::get('rate_limit_per_minute', 120);
        $key = 'security:rate:' . sha1((string) $request->ip());
        $count = Cache::increment($key);
        if ($count === 1) {
            Cache::put($key, 1, now()->addMinute());
        }

        return $count > $limit;
    }

    protected function log(string $severity, string $type, Request $request, string $message): void
    {
        if (!Schema::hasTable('security_events')) {
            return;
        }

        DB::table('security_events')->insert([
            'severity' => $severity,
            'type' => $type,
            'ip_address' => $request->ip(),
            'url' => $request->fullUrl(),
            'user_agent' => substr((string) $request->userAgent(), 0, 512),
            'message' => $message,
            'context' => json_encode(['method' => $request->method()]),
            'occurred_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
