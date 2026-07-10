<?php

namespace App\Http\Middleware;

use App\Core\PluginManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RunPluginMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $middleware = app(PluginManager::class)->activeMiddlewareFor($this->area($request));

        if (!$middleware) {
            return $next($request);
        }

        $pipeline = array_reduce(
            array_reverse($middleware),
            fn (Closure $carry, string $class) => fn (Request $request) => app($class)->handle($request, $carry),
            $next
        );

        return $pipeline($request);
    }

    protected function area(Request $request): string
    {
        if ($request->is('admin*')) {
            return 'admin';
        }

        if ($request->is('client*')) {
            return 'client';
        }

        if ($request->is('api*')) {
            return 'api';
        }

        return 'public';
    }
}
