<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$basePath = dirname(__DIR__);
$envFile = $basePath . '/.env';
$envExampleFile = $basePath . '/.env.example';

if (!file_exists($envFile) && file_exists($envExampleFile)) {
    copy($envExampleFile, $envFile);
}

if (file_exists($envFile) && is_writable($envFile)) {
    $envContent = file_get_contents($envFile);

    if ($envContent !== false) {
        $hasKey = preg_match('/^APP_KEY=(.+)$/m', $envContent, $matches)
            && trim($matches[1], "\"' ") !== '';

        if (!$hasKey) {
            $key = 'base64:' . base64_encode(random_bytes(32));

            if (preg_match('/^APP_KEY=.*$/m', $envContent)) {
                $envContent = preg_replace('/^APP_KEY=.*$/m', 'APP_KEY=' . $key, $envContent);
            } else {
                $envContent .= PHP_EOL . 'APP_KEY=' . $key . PHP_EOL;
            }

            file_put_contents($envFile, $envContent);
            putenv('APP_KEY=' . $key);
            $_ENV['APP_KEY'] = $key;
            $_SERVER['APP_KEY'] = $key;
        }
    }
}

// Load plugin helpers
require_once __DIR__ . '/../app/Core/helpers.php';

return Application::configure(basePath: $basePath)
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withSingletons([
        \App\Core\PluginManager::class => \App\Core\PluginManager::class,
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append([
            \App\Http\Middleware\RunPluginMiddleware::class,
        ]);

        $middleware->alias([
            'admin.permission' => \App\Http\Middleware\EnsureAdminPermission::class,
            'jamvini.api' => \App\Http\Middleware\EnsureApiToken::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
