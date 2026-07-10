<?php

namespace App\Core\Hooks;

class Filter
{
    protected static array $filters = [];

    /**
     * Register a filter hook.
     */
    public static function add(string $hook, callable $callback, int $priority = 10): void
    {
        self::$filters[$hook][$priority][] = $callback;
        if (isset(self::$filters[$hook])) {
            ksort(self::$filters[$hook]);
        }
    }

    /**
     * Apply filters to a value.
     */
    public static function apply(string $hook, mixed $value, ...$args): mixed
    {
        if (empty(self::$filters[$hook])) {
            return $value;
        }

        foreach (self::$filters[$hook] as $callbacks) {
            foreach ($callbacks as $callback) {
                if (is_callable($callback)) {
                    $value = call_user_func($callback, $value, ...$args);
                }
            }
        }

        return $value;
    }

    /**
     * Remove a specific callback from a filter.
     */
    public static function remove(string $hook, callable $callback): void
    {
        if (empty(self::$filters[$hook])) {
            return;
        }

        foreach (self::$filters[$hook] as $priority => $callbacks) {
            self::$filters[$hook][$priority] = array_values(
                array_filter($callbacks, fn($cb) => $cb !== $callback)
            );
        }
    }

    /**
     * Remove all callbacks from a filter.
     */
    public static function removeAll(string $hook): void
    {
        unset(self::$filters[$hook]);
    }

    /**
     * Check if a filter has registered callbacks.
     */
    public static function has(string $hook): bool
    {
        return !empty(self::$filters[$hook]);
    }

    /**
     * Get all registered filters.
     */
    public static function all(): array
    {
        return self::$filters;
    }
}
