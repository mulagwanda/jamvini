<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminPermission
{
    public function handle(Request $request, Closure $next, string $module, string $level = 'read'): Response
    {
        $admin = auth('admin')->user();

        if (!$admin || ($admin->status ?? 'active') !== 'active') {
            abort(403, 'Your admin account is not active.');
        }

        $requiredLevel = $level === 'auto' ? $this->levelForRequest($request) : $level;

        if (!$admin->canAccess($module, $requiredLevel)) {
            abort(403, 'You do not have permission to access this area.');
        }

        return $next($request);
    }

    protected function levelForRequest(Request $request): string
    {
        $routeName = (string) $request->route()?->getName();

        if ($request->isMethod('GET') && (str_ends_with($routeName, '.create') || str_ends_with($routeName, '.edit'))) {
            return 'write';
        }

        return match ($request->method()) {
            'GET', 'HEAD', 'OPTIONS' => 'read',
            'DELETE' => 'delete',
            default => 'write',
        };
    }
}
