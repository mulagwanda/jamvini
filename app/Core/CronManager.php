<?php

namespace App\Core;

use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class CronManager
{
    protected static array $tasks = [];
    protected static array $taskResults = [];

    /**
     * Register a scheduled task.
     */
    public static function register(string $name, string $schedule, callable $callback, array $options = []): void
    {
        $lastRun = Cache::get("cron:{$name}:last_run");

        self::$tasks[$name] = [
            'name' => $name,
            'schedule' => $schedule, // everyMinute, everyFiveMinutes, hourly, daily, weekly
            'callback' => $callback,
            'last_run' => is_string($lastRun) ? $lastRun : null,
            'is_running' => Cache::get("cron:{$name}:running", false),
            'enabled' => !in_array($name, self::disabledTasks(), true) && ($options['enabled'] ?? true),
            'description' => $options['description'] ?? $name,
        ];
    }

    public static function disabledTasks(): array
    {
        $disabled = json_decode(Setting::get('cron_disabled_tasks', '[]'), true);

        return is_array($disabled) ? $disabled : [];
    }

    public static function setEnabled(string $name, bool $enabled): void
    {
        $disabled = self::disabledTasks();

        if ($enabled) {
            $disabled = array_values(array_diff($disabled, [$name]));
        } elseif (!in_array($name, $disabled, true)) {
            $disabled[] = $name;
        }

        Setting::set('cron_disabled_tasks', json_encode($disabled), 'system', 'Disabled Cron Tasks');

        if (isset(self::$tasks[$name])) {
            self::$tasks[$name]['enabled'] = $enabled;
        }
    }

    /**
     * Get all registered tasks.
     */
    public static function all(): array
    {
        return self::$tasks;
    }

    /**
     * Get tasks that are due to run.
     */
    public static function due(): array
    {
        $now = now();
        $due = [];

        foreach (self::$tasks as $name => $task) {
            if (!$task['enabled']) continue;
            if ($task['is_running']) continue;

            $lastRun = null;
            if ($task['last_run']) {
                try {
                    $lastRun = Carbon::parse($task['last_run']);
                } catch (\Exception $e) {
                    $lastRun = null;
                }
            }
            $shouldRun = false;

            switch ($task['schedule']) {
                case 'everyMinute':
                    $shouldRun = !$lastRun || $lastRun->diffInMinutes($now) >= 1;
                    break;
                case 'everyFiveMinutes':
                    $shouldRun = !$lastRun || $lastRun->diffInMinutes($now) >= 5;
                    break;
                case 'everyFifteenMinutes':
                    $shouldRun = !$lastRun || $lastRun->diffInMinutes($now) >= 15;
                    break;
                case 'everyThirtyMinutes':
                    $shouldRun = !$lastRun || $lastRun->diffInMinutes($now) >= 30;
                    break;
                case 'hourly':
                    $shouldRun = !$lastRun || $lastRun->diffInHours($now) >= 1;
                    break;
                case 'daily':
                    $shouldRun = !$lastRun || !$lastRun->isToday();
                    break;
                case 'weekly':
                    $shouldRun = !$lastRun || $lastRun->diffInWeeks($now) >= 1;
                    break;
                case 'monthly':
                    $shouldRun = !$lastRun || $lastRun->diffInMonths($now) >= 1;
                    break;
            }

            if ($shouldRun) {
                $due[$name] = $task;
            }
        }

        return $due;
    }

    /**
     * Run all due tasks.
     */
    public static function run(): array
    {
        $results = [];

        ActivityLogger::log('cron', 'CronManager', null, 'Cron started — ' . count(self::due()) . ' task(s) pending');

        foreach (self::due() as $name => $task) {
            Cache::put("cron:{$name}:running", true, 300);
            Cache::put("cron:{$name}:last_run", now()->toDateTimeString(), now()->addDays(30));

            try {
                $startTime = microtime(true);
                $result = call_user_func($task['callback']);
                $duration = round(microtime(true) - $startTime, 3);

                $results[$name] = [
                    'success' => true,
                    'duration' => $duration,
                    'result' => $result,
                ];

                ActivityLogger::log('cron', 'CronManager', null, "Task '{$name}' completed in {$duration}s");
            } catch (\Exception $e) {
                $results[$name] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                ];

                ActivityLogger::log('cron', 'CronManager', null, "Task '{$name}' failed: " . $e->getMessage());
            }

            Cache::put("cron:{$name}:last_result", $results[$name], now()->addDays(30));
            Cache::forget("cron:{$name}:running");
        }

        ActivityLogger::log('cron', 'CronManager', null, 'Cron finished — ' . count($results) . ' task(s) executed');

        return $results;
    }

    /**
     * Run via web request (for shared hosting without real cron).
     */
    public static function runViaWeb(): string
    {
        $key = request()->get('key');
        $storedKey = \App\Models\Setting::get('cron_key', '');

        if ($key !== $storedKey || empty($storedKey)) {
            return 'Invalid or missing cron key.';
        }

        $results = self::run();

        $output = "Cron executed at " . now()->toDateTimeString() . "\n";
        foreach ($results as $name => $result) {
            $output .= "- {$name}: " . ($result['success'] ? "OK ({$result['duration']}s)" : "FAILED: {$result['error']}") . "\n";
        }

        return $output;
    }

    /**
     * Get the last run time for a task.
     */
    public static function lastRun(string $name): ?string
    {
        $lastRun = Cache::get("cron:{$name}:last_run");
        
        if (is_string($lastRun)) {
            return $lastRun;
        }
        
        return null;
    }

    public static function lastResult(string $name): ?array
    {
        $result = Cache::get("cron:{$name}:last_result");

        return is_array($result) ? $result : null;
    }

    public static function nextRun(string $name): ?string
    {
        $task = self::$tasks[$name] ?? null;

        if (!$task || !$task['enabled']) {
            return null;
        }

        $lastRun = self::lastRun($name);

        if (!$lastRun) {
            return now()->toDateTimeString();
        }

        $lastRun = Carbon::parse($lastRun);

        return match ($task['schedule']) {
            'everyMinute' => $lastRun->addMinute()->toDateTimeString(),
            'everyFiveMinutes' => $lastRun->addMinutes(5)->toDateTimeString(),
            'everyFifteenMinutes' => $lastRun->addMinutes(15)->toDateTimeString(),
            'everyThirtyMinutes' => $lastRun->addMinutes(30)->toDateTimeString(),
            'hourly' => $lastRun->addHour()->toDateTimeString(),
            'daily' => $lastRun->addDay()->toDateTimeString(),
            'weekly' => $lastRun->addWeek()->toDateTimeString(),
            'monthly' => $lastRun->addMonth()->toDateTimeString(),
            default => null,
        };
    }

    /**
     * Check if a task is currently running.
     */
    public static function isRunning(string $name): bool
    {
        return Cache::get("cron:{$name}:running", false);
    }

    /**
     * Get status of all tasks.
     */
    public static function status(): array
    {
        $status = [];
        foreach (self::$tasks as $name => $task) {
            $status[] = [
                'name' => $name,
                'description' => $task['description'],
                'schedule' => $task['schedule'],
                'last_run' => self::lastRun($name),
                'next_run' => self::nextRun($name),
                'last_result' => self::lastResult($name),
                'is_running' => self::isRunning($name),
                'enabled' => $task['enabled'],
            ];
        }
        return $status;
    }
}
