<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Auth\LoginController as AdminLoginController;
use App\Http\Controllers\Admin\PluginController;
use App\Http\Controllers\Admin\SystemController;
use App\Http\Controllers\Admin\CronController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminDepartmentController;
use App\Http\Controllers\Admin\ThemeController;
use App\Http\Controllers\Admin\JamviniMigrationController;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;

require __DIR__ . '/installer.php';

try {
    $canLoadPlugins = Schema::hasTable('plugins') && Schema::hasTable('settings');
} catch (\Throwable $e) {
    $canLoadPlugins = false;
}

$activePlugins = collect();

if ($canLoadPlugins) {
    app(\App\Core\PluginManager::class)->syncSystemPlugins();
    $activePlugins = \App\Models\Plugin::where('is_active', true)->pluck('slug');
}

Route::get('/', function () use ($activePlugins) {
    if (!File::exists(storage_path('app/installed'))) {
        return redirect('/install');
    }

    $homepage = apply_filters('frontend.homepage', null);
    if ($homepage) {
        return $homepage;
    }
    
    // Fallback to built-in landing page
    return view('welcome');
});

// Client auth
Auth::routes();

// Load active plugin routes (only if plugins table exists)
if ($canLoadPlugins) {
    // ============================================
    // LOAD ALL ACTIVE PLUGIN ROUTES
    // Each plugin's routes.php has its own middleware/prefix wrapper
    // ============================================
    $pluginManager = app(\App\Core\PluginManager::class);
    foreach ($activePlugins as $slug) {
        $routesFile = $pluginManager->pluginPath($slug) . '/routes.php';
        if (file_exists($routesFile)) {
            require $routesFile;
        }
    }


    // ============================================
    // LOAD ACTIVE THEME ROUTES
    // ============================================
    foreach (array_unique([active_theme('public'), active_theme('client'), active_theme('admin')]) as $activeTheme) {
        $themeRoutes = base_path("themes/{$activeTheme}/routes.php");
        if (file_exists($themeRoutes)) {
            require_once $themeRoutes;
        }
    }
}

// ============================================
// SYSTEM ROUTES (not plugins)
// ============================================
Route::prefix('admin')->name('admin.')->group(function () {
    
    Route::get('/login', [AdminLoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminLoginController::class, 'login'])->name('login.submit');
    Route::post('/logout', [AdminLoginController::class, 'logout'])->name('logout');
    
    Route::middleware(['auth:admin'])->group(function () {
        Route::middleware('admin.permission:plugins,read')->group(function () {
            Route::get('/plugins', [PluginController::class, 'index'])->name('plugins.index');
        });
        Route::middleware('admin.permission:plugins,manage')->group(function () {
            Route::post('/plugins/install', [PluginController::class, 'install'])->name('plugins.install');
            Route::post('/plugins/activate', [PluginController::class, 'activate'])->name('plugins.activate');
            Route::post('/plugins/deactivate', [PluginController::class, 'deactivate'])->name('plugins.deactivate');
            Route::post('/plugins/uninstall', [PluginController::class, 'uninstall'])->name('plugins.uninstall');
            Route::post('/plugins/update/{slug}', [PluginController::class, 'update'])->name('plugins.update');
            Route::post('/plugins/upload', [PluginController::class, 'upload'])->name('plugins.upload');
            Route::get('/plugins/{slug}/editor', [PluginController::class, 'editor'])->name('plugins.editor');
            Route::post('/plugins/{slug}/editor', [PluginController::class, 'updateFile'])->name('plugins.editor.update');
        });
        Route::middleware('admin.permission:system,read')->group(function () {
            Route::get('/themes', [ThemeController::class, 'index'])->name('themes.index');
        });
        Route::middleware('admin.permission:system,manage')->group(function () {
            Route::post('/themes/activate', [ThemeController::class, 'activate'])->name('themes.activate');
            Route::post('/themes/upload', [ThemeController::class, 'upload'])->name('themes.upload');
            Route::get('/themes/{slug}/options', [ThemeController::class, 'options'])->name('themes.options');
            Route::post('/themes/{slug}/options', [ThemeController::class, 'updateOptions'])->name('themes.options.update');
            Route::post('/themes/{slug}/demo', [ThemeController::class, 'importDemo'])->name('themes.demo.import');
            Route::get('/themes/{slug}/demo/export', [ThemeController::class, 'exportDemo'])->name('themes.demo.export');
            Route::post('/themes/{slug}/package/import', [ThemeController::class, 'importPackage'])->name('themes.package.import');
        });

        Route::middleware('admin.permission:admins,manage')->group(function () {
            Route::resource('admin-users', AdminUserController::class)->except(['show']);
            Route::resource('departments', AdminDepartmentController::class)->except(['show']);
        });

        Route::middleware('admin.permission:system,read')->group(function () {
            Route::get('/system', [SystemController::class, 'index'])->name('system.index');
            Route::get('/migration', [JamviniMigrationController::class, 'index'])->name('migration.index');
        });
        Route::middleware('admin.permission:system,manage')->group(function () {
            Route::post('/system/clear-cache', [SystemController::class, 'clearCache'])->name('system.clear-cache');
            Route::post('/system/clear-views', [SystemController::class, 'clearViews'])->name('system.clear-views');
            Route::post('/system/clear-routes', [SystemController::class, 'clearRoutes'])->name('system.clear-routes');
            Route::post('/system/storage-link', [SystemController::class, 'createStorageLink'])->name('system.storage-link');
            Route::post('/system/run-migrations', [SystemController::class, 'runMigrations'])->name('system.migrate');
            Route::get('/migration/export', [JamviniMigrationController::class, 'export'])->name('migration.export');
            Route::post('/migration/import', [JamviniMigrationController::class, 'import'])->name('migration.import');
        });
    });
    
});

Route::middleware(['auth:admin', 'admin.permission:system,read'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/cron', [CronController::class, 'index'])->name('cron.index');
});

Route::middleware(['auth:admin', 'admin.permission:system,manage'])->prefix('admin')->name('admin.')->group(function () {
    Route::post('/cron/run', [CronController::class, 'runNow'])->name('cron.run');
    Route::post('/cron/toggle/{name}', [CronController::class, 'toggle'])->name('cron.toggle');
    Route::post('/cron/regenerate', [CronController::class, 'regenerateKey'])->name('cron.regenerate');
});

// Cron endpoint for web-based cron (shared hosting)
Route::get('/cron', [App\Http\Controllers\Admin\CronController::class, 'runViaWeb'])->name('cron.web');

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

if ($canLoadPlugins) {
    $pluginManager = app(\App\Core\PluginManager::class);
    foreach ($activePlugins as $slug) {
        $lateRoutesFile = $pluginManager->pluginPath($slug) . '/routes-late.php';
        if (file_exists($lateRoutesFile)) {
            require $lateRoutesFile;
        }
    }
}
