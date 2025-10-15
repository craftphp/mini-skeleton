<?php
namespace Craft\Application;

/**
 * #### Class Middleware is for handling middleware functionality
 * 
 * This class handles middleware functionality for the application.
 * It provides a simple way to add middleware to routes and groups.
 */
#region Middleware
class Middleware
{
    /**
     * @var array $middlewares Stores registered middlewares
     */
    private static $middlewares = [];

    /**
     * Register a middleware
     * @param string $name Middleware name
     * @param callable $callback Middleware callback
     */
    public static function register(?string $name = null, ?callable $callback = null): void
    {
        if ($name !== null && $callback !== null) {
            self::$middlewares[$name] = $callback;
        }
    }

    /**
     * Get a middleware by name
     * @param string $name Middleware name
     * @return callable|null
     */
    public static function get(string $name): ?callable
    {
        return self::$middlewares[$name] ?? null;
    }

    /**
     * Check if middleware exists
     * @param string $name Middleware name
     * @return bool
     */
    public static function exists(string $name): bool
    {
        return isset(self::$middlewares[$name]);
    }

    /**
     * Run a middleware
     * @param string $name Middleware name
     * @param array $context Context data
     * @return mixed
     */
    public static function run(string $name, array $context = [])
    {
        if (self::exists($name)) {
            return call_user_func(self::$middlewares[$name], $context);
        }
        return null;
    }
}
#endregion