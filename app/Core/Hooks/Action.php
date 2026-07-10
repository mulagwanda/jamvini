<?php

namespace App\Core\Hooks;

class Action
{
    protected static array $actions = [];

    /**
     * Register an action hook.
     */
    public static function add(string $hook, callable $callback, int $priority = 10): void
    {
        self::$actions[$hook][$priority][] = $callback;
        if (isset(self::$actions[$hook])) {
            ksort(self::$actions[$hook]);
        }
    }

    /**
     * Execute all callbacks registered for a hook.
     */
    public static function do(string $hook, ...$args): void
    {
        if (empty(self::$actions[$hook])) {
            return;
        }

        foreach (self::$actions[$hook] as $callbacks) {
            foreach ($callbacks as $callback) {
                if (is_callable($callback)) {
                    call_user_func($callback, ...$args);
                }
            }
        }
    }

    public static function first(string $hook, ...$args): mixed
    {
        if (empty(self::$actions[$hook])) {
            return null;
        }

        foreach (self::$actions[$hook] as $callbacks) {
            foreach ($callbacks as $callback) {
                if (is_callable($callback)) {
                    $result = call_user_func($callback, ...$args);
                    if ($result !== null) {
                        return $result;
                    }
                }
            }
        }

        return null;
    }

    public static function until(string $hook, ...$args): bool
    {
        if (empty(self::$actions[$hook])) {
            return true;
        }

        foreach (self::$actions[$hook] as $callbacks) {
            foreach ($callbacks as $callback) {
                if (is_callable($callback) && call_user_func($callback, ...$args) === false) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Remove a specific callback from a hook.
     */
    public static function remove(string $hook, callable $callback): void
    {
        if (empty(self::$actions[$hook])) {
            return;
        }

        foreach (self::$actions[$hook] as $priority => $callbacks) {
            self::$actions[$hook][$priority] = array_values(
                array_filter($callbacks, fn($cb) => $cb !== $callback)
            );
        }
    }

    /**
     * Remove all callbacks from a hook.
     */
    public static function removeAll(string $hook): void
    {
        unset(self::$actions[$hook]);
    }

    /**
     * Check if a hook has registered callbacks.
     */
    public static function has(string $hook): bool
    {
        return !empty(self::$actions[$hook]);
    }

    /**
     * Get all registered hooks (for debugging).
     */
    public static function all(): array
    {
        return self::$actions;
    }
}
