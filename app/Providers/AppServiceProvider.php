<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\File;
use App\Core\RegisterCoreTasks;
use App\Models\Setting;

class AppServiceProvider extends ServiceProvider
{

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (!app()->runningInConsole() && request()->is('install*')) {
            config([
                'cache.default' => 'file',
                'session.driver' => 'file',
            ]);
        }

        // Override auth views to use theme
        View::addLocation(base_path('themes/default'));

        if (!app()->runningInConsole() && File::exists(storage_path('app/installed'))) {
            $timezone = Setting::get('timezone', config('app.timezone'));
            config(['app.timezone' => $timezone]);
            date_default_timezone_set($timezone);

            RegisterCoreTasks::register();
        }
    }
}
