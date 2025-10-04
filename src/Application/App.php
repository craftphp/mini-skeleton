<?php
namespace Craft\Application;

use \Craft\Reports\CraftParse;
use \Craft\Reports\CraftError;
use \Craft\Reports\CraftException;
use \Craft\Reports\CraftRuntime;
use \Datahihi1\TinyEnv\TinyEnv;
use \Exception;

/**
 * #### App Class is the core to boot and run the application.
 *
 * This class initializes and boot the application environment, sets up error handling,
 * loads environment variables, and configures reporting for errors, exceptions,
 * and runtime issues. It also handles reporting if the application is run
 * from the command line.
 */
#region App
class App
{
    /**
     * Version of Craft Framework (Mini edition).
     * @var string
     */
    public const version = '0.1.20251004-mini+dev';

    /**
     * Application environment
     * @var string
     */
    private static $environment = 'production';

    /**
     * Application debug mode (default true for development)
     * @var bool
     */
    private static $debug = true;

    /**
     * Initialize application configuration
     */
    private static function initializeConfig(): void
    {
        self::$environment = env('APP_ENVIRONMENT', 'production');
        self::$debug = env('APP_DEBUG', 'false');

        // Validate environment
        if (!in_array(self::$environment, ['local', 'development', 'staging', 'production'])) {
            self::$environment = 'production';
        }

        // Security: Disable debug in production
        if (self::$environment === 'production') {
            self::$debug = false;
        }
    }

    /**
     * Get current environment
     */
    public static function environment(): string
    {
        return self::$environment;
    }

    /**
     * Check if application is in debug mode
     */
    public static function isDebug(): bool
    {
        return self::$debug;
    }

    /**
     * Check if application is in production mode
     */
    public static function isProduction(): bool
    {
        return self::$environment === 'production';
    }

    /**
     * Set security headers for web requests
     */
    private static function setSecurityHeaders(): void
    {
        if (headers_sent()) {
            return; // Headers already sent
        }

        // Security headers
        header('X-Content-Type-Options: nos niff');
        header('X-Frame-Options: DENY');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');

        // Content Security Policy (basic)
        if (self::isProduction()) {
            header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline';");
        }

        // Remove server information
        header_remove('X-Powered-By');
    }

    /**
     * Set maintenance mode if enabled in environment variables
     */
    private static function setMaintenanceMode(): void
    {
        if (headers_sent()) {
            return;
        }

        // Allow access from localhost
        $serverIps = ['127.0.0.1', '::1'];
        $clientIp = $_SERVER['REMOTE_ADDR'] ?? '';

        if (in_array($clientIp, $serverIps)) {
            return;
        }

        $maintenanceMode = env('MAINTENANCE_MODE', 'false');
        $startTime = env('MAINTENANCE_START_TIME', null);
        $endTime = env('MAINTENANCE_END_TIME', null);
        $currentTime = time();

        if ($endTime && $currentTime > (int) $endTime) {
            return;
        }

        if (filter_var($maintenanceMode, FILTER_VALIDATE_BOOLEAN)) {
            header('HTTP/1.1 503 Service Unavailable');
            header('Retry-After: 3600');
            $startStr = $startTime ? date('H:i:s d/m/Y', (int) $startTime) : null;
            $endStr = $endTime ? date('H:i:s d/m/Y', (int) $endTime) : null;
            $countdown = ($endTime && $currentTime < (int) $endTime) ? ((int) $endTime - $currentTime) : null;

            if (file_exists(INDEX_DIR . 'maintenance.php')) {
                echo str_replace(
                    ['{start}', '{end}', '{countdown}'],
                    [$startStr ?? '', $endStr ?? '', $countdown ?? ''],
                    file_get_contents(INDEX_DIR . 'maintenance.php')
                );
            } else {
                echo '<h1>Maintenance Mode</h1>';
                if ($startStr)
                    echo "<p>Start: $startStr</p>";
                if ($endStr) {
                    echo "<p>End: $endStr</p>";
                    if ($countdown) {
                        $hours = floor($countdown / 3600);
                        $minutes = floor(($countdown % 3600) / 60);
                        $seconds = $countdown % 60;
                        echo "<p>Remaining: {$hours}h {$minutes}m {$seconds}s</p>";
                    }
                }
                echo '<p>The site is currently under maintenance. Please check back later.</p>';
            }
            exit();
        }
    }

    /**
     * Validate session configuration
     *
     * @return void
     * @throws Exception
     */
    private static function validateSessionConfig()
    {
        // Set secure session configuration
        if (self::isProduction()) {
            ini_set('session.cookie_httponly', '1');
            ini_set('session.cookie_secure', '1');
            ini_set('session.use_strict_mode', '1');
            ini_set('session.cookie_same site', 'Strict');
        }

        // Validate session save path
        $sessionPath = ini_get('session.save_path');
        if ($sessionPath && !is_writable($sessionPath)) {
            throw new Exception('Session save path is not writable: ' . $sessionPath);
        }
    }

    /**
     * Validate service configuration
     *
     * @return void
     * @throws Exception
     */
    private static function validateServiceConfig()
    {
        // Validate required environment variables for services
        $requiredVars = ['APP_NAME', 'APP_TIMEZONE'];
        foreach ($requiredVars as $var) {
            if (!env($var)) {
                throw new Exception("Required environment variable missing: {$var}");
            }
        }
    }

    /**
     * Initialize error reporting with validation
     *
     * @param string|null $logDir
     * @return void
     * @throws Exception
     */
    private static function initializeErrorReporting(?string $logDir = null)
    {
        if (is_null($logDir)) {
            CraftParse::sign();
            CraftException::sign();
            CraftError::sign();
            CraftRuntime::sign();
        }
        // Validate log files can be created
        $logFiles = [
            'parse.log',
            'exception.log',
            'error.log',
            'runtime.log'
        ];

        foreach ($logFiles as $logFile) {
            $fullPath = $logDir . $logFile;
            if (!is_writable(dirname($fullPath))) {
                throw new Exception("Cannot write to log file: {$fullPath}");
            }
        }

        CraftParse::sign(true, $logDir . 'parse.log');
        CraftException::sign(true, $logDir . 'exception.log');
        CraftError::sign(true, $logDir . 'error.log');
        CraftRuntime::sign(true, $logDir . 'runtime.log');
    }

    /**
     * Validate application health
     * 
     * @return array
     */
    private static function healthCheck(): array
    {
        $health = [
            'status' => 'healthy',
            'environment' => self::$environment,
            'debug' => self::$debug,
            'version' => self::version,
            'timestamp' => date('Y-m-d H:i:s'),
            'checks' => []
        ];

        // Check required directories
        $requiredDirs = [
            'logs' => INDEX_DIR . 'logs/',
            'vendor' => ROOT_DIR . 'vendor/',
            'app' => ROOT_DIR . 'app/'
        ];

        foreach ($requiredDirs as $name => $path) {
            $health['checks'][$name] = [
                'status' => is_dir($path) && is_readable($path) ? 'ok' : 'error',
                'path' => $path
            ];
        }

        // Check if any checks failed
        foreach ($health['checks'] as $check) {
            if ($check['status'] === 'error') {
                $health['status'] = 'unhealthy';
                break;
            }
        }

        return $health;
    }

    /**
     * Load environment variables from .env file
     * 
     * @return void
     */
    private static function loadEnvironmentVariables()
    {
        if (!class_exists(TinyEnv::class)) {
            throw new Exception('TinyEnv is not installed. Please run "composer require datahihi1/tiny-env"');
        }
        $env = new TinyEnv(ROOT_DIR);
        $env->envfiles(['.env', '.env.test']);
        $env->load();
    }

    /**
     * Configure error reporting based on environment
     * 
     * @return void
     */
    private static function configureErrorReporting()
    {
        if (self::$environment === 'production') {
            error_reporting(0);
            ini_set('display_errors', '0');
            ini_set('display_startup_errors', '0');
            ini_set('log_errors', '1');
            ini_set('error_log', ROOT_DIR . 'public/logs/php_errors.log');
        } else {
            error_reporting(E_ALL);
            ini_set('display_errors', '0');
            ini_set('display_startup_errors', '0');
        }
    }

    /**
     * Configure timezone from environment variable
     * 
     * @return void
     */
    private static function configureTimezone()
    {
        $timezone = env('APP_TIMEZONE', 'UTC');
        if (!in_array($timezone, \DateTimeZone::listIdentifiers())) {
            $timezone = 'UTC';
        }
        date_default_timezone_set($timezone);
    }

    /**
     * Validate database configuration (optional)
     * 
     * @return void
     */
    private static function validateDatabaseConfig()
    {
        if (env('DB_HOST') && env('DB_NAME')) {
            $host = env('DB_HOST');
            $dbname = env('DB_NAME');
            $username = env('DB_USER');
            $password = env('DB_PASS');
            if (!$host || !$dbname || !$username) {
                throw new Exception('Incomplete database configuration');
            }
        }
    }

    /**
     * Initializes the routing configuration.
     * 
     * @return void
     * @throws Exception if the route configuration file is not found or invalid
     */
    private static function initializeRoute()
    {
        // Initialize routing configuration
        $routeConfigPath = ROOT_DIR . 'app/Router/web.php';
        $apiRouteConfigPath = ROOT_DIR . 'app/Router/api.php';
        if (file_exists($routeConfigPath)) {
            require $routeConfigPath;
        } else {
            throw new Exception("Route configuration file not found: " . $routeConfigPath);
        }
        if (file_exists($apiRouteConfigPath)) {
            require $apiRouteConfigPath;
        }
    }

    /**
     * Initializes the web environment.
     *
     * @param string|null $logDir The directory where log files will be stored.
     * 
     * @return self
     */
    public static function initializeWeb(?string $logDir = null)
    {
        try {

            // Load environment variables
            self::loadEnvironmentVariables();

            // Initialize configuration
            self::initializeConfig();

            // Set maintenance mode if enabled
            self::setMaintenanceMode();

            // Configure error reporting
            self::configureErrorReporting();

            // Configure timezone
            self::configureTimezone();

            // Validate log directory
            if ($logDir && !is_dir($logDir)) {
                if (!mkdir($logDir, 0755, true)) {
                    throw new Exception("Failed to create log directory: {$logDir}");
                }
            }

            // Validate log directory is writable
            if ($logDir && !is_writable($logDir)) {
                throw new Exception("Log directory is not writable: {$logDir}");
            }

            // Validate session configuration (moved here)
            self::validateSessionConfig();

            // Start session with security
            Session::start();

            // Initialize error reporting with validation
            if ($logDir) {
                self::initializeErrorReporting($logDir);
            }

            // Validate required environment variables for services
            self::validateServiceConfig();

            // Validate database configuration (optional)
            // self::validateDatabaseConfig();
        } catch (Exception $e) {
            if (self::isDebug()) {
                self::initializeErrorReporting(INDEX_DIR . 'logs/');
                throw $e;
            }
        }
        
        return new self();
    }

    /**
     * Boots the web application.
     * 
     * @return void
     */
    public static function bootWeb()
    {
        // // Set security headers
        // self::setSecurityHeaders();

        // Start run route handler
        self::initializeRoute();

        \Craft\Application\Router::run();
    }
}
#endregion