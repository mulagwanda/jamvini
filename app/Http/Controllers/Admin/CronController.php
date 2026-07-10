<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Core\RegisterCoreTasks;
use App\Core\CronManager;
use App\Models\Setting;
use Illuminate\Support\Str;

class CronController extends Controller
{
    /**
     * Show cron status page.
     */
    public function index()
    {
        $tasks = CronManager::status();
        $cronKey = Setting::get('cron_key', '');
        
        if (empty($cronKey)) {
            $cronKey = Str::random(32);
            Setting::set('cron_key', $cronKey);
        }

        $webUrl = url('/cron?key=' . $cronKey);
        $cronCommand = "* * * * * wget -q -O /dev/null '{$webUrl}' > /dev/null 2>&1";
        $curlCommand = "* * * * * curl -s '{$webUrl}' > /dev/null 2>&1";

        return view('admin.cron', compact('tasks', 'cronKey', 'webUrl', 'cronCommand', 'curlCommand'));
    }

    /**
     * Run cron tasks manually.
     */
    public function runNow()
    {
        RegisterCoreTasks::register();
        $results = CronManager::run();

        $failed = collect($results)->filter(fn ($result) => !($result['success'] ?? false))->count();

        if ($failed > 0) {
            return redirect()->route('admin.cron.index')
                ->with('error', "{$failed} cron task(s) failed. Check the task table and activity log.");
        }

        return redirect()->route('admin.cron.index')
            ->with('success', count($results) . ' cron task(s) executed.');
    }

    /**
     * Toggle a task on/off.
     */
    public function toggle(string $name)
    {
        RegisterCoreTasks::register();
        $tasks = CronManager::all();
        
        if (isset($tasks[$name])) {
            $enabled = !$tasks[$name]['enabled'];
            CronManager::setEnabled($name, $enabled);

            return redirect()->route('admin.cron.index')
                ->with('success', "Task '{$name}' " . ($enabled ? 'enabled' : 'disabled'));
        }

        return redirect()->route('admin.cron.index')
            ->with('error', 'Task not found.');
    }

    /**
     * Generate new cron key.
     */
    public function regenerateKey()
    {
        $newKey = Str::random(32);
        Setting::set('cron_key', $newKey);

        return redirect()->route('admin.cron.index')
            ->with('success', 'New cron key generated.');
    }

    /**
     * Run via web request (for shared hosting without real cron).
     */
    public function runViaWeb()
    {
        RegisterCoreTasks::register();
        $output = CronManager::runViaWeb();
        $status = str_starts_with($output, 'Invalid') ? 403 : 200;

        return response($output, $status)->header('Content-Type', 'text/plain');
    }
}
