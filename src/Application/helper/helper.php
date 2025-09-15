<?php

use Craft\Application\Session;

#region Old Input Helper
if (!function_exists('old')) {
    /**
     * Get old input value from the previous request.
     * @param string $key
     * @param mixed $default
     * @return mixed|null
     */
    function old(string $key, $default = null)
    {
        if (isset($GLOBALS['old'][$key])) {
            return $GLOBALS['old'][$key];
        }
        if (isset($GLOBALS['old']) && is_array($GLOBALS['old']) && array_key_exists($key, $GLOBALS['old'])) {
            return $GLOBALS['old'][$key];
        }
        return $default;
    }
}
#endregion

#region Source Helper
if (!function_exists('source')) {
    /**
     * Get the URL for a source file (located in public/source).
     * 
     * @param string $path
     * @return string
     */
    function source(string $path = ''): string
    {
        $baseUrl = getBaseUrl();

        $host = $_SERVER['HTTP_HOST'] ?? '';
        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $path = ltrim(str_replace(['..', '\\'], '', $path), '/');

        if (strpos($baseUrl, '/public/') !== false) {
            return $baseUrl . 'source/' . $path;
        }

        if (
            preg_match('/^([a-zA-Z0-9\-\.]+)(:\d+)?$/', $host) &&
            (strpos($scriptName, '/public/') === false)
        ) {
            return $baseUrl . 'source/' . $path;
        }

        return $baseUrl . 'source/' . $path;
    }
}
#endregion

#region Router Helpers
if (!function_exists('redirect')) {
    /**
     * Redirect helper: 
     * - redirect()->route('name') : Redirect to a named route.
     * - redirect($url) : Redirect to a specific URL.
     * @param string|null $url URL to redirect to.
     * @return object|void
     */
    function redirect(?string $url = null) {
        if ($url !== null) {
            header('Location: ' . $url);
            exit;
        }
        return new class {
            /**
             * Redirect to a named route.
             */
            public function route($name, $params = []) {
                $url = route($name, $params);
                header('Location: ' . $url);
                exit;
            }
            /**
             * Redirect to a specific URL.
             */
            public function to($url) {
                header('Location: ' . $url);
                exit;
            }
        };
    }
}

if (!function_exists('route')) {
    /**
     * Generate a URL for a named route.
     * 
     * @param string $name The route name.
     * @param array $params The route parameters.
     * @return string|null
     */
    function route(string $name, array $params = []): ?string
    {
        return \Craft\Application\Router::route($name, $params);
    }
}
#endregion

#region Session Helpers
if (!function_exists('session')) {
    /**
     * Helper function for session get/set.
     * - session(): get all session variables.
     * - session($key): get session value.
     * - session($key, $value): set session value.
     *
     * @param string|null $key The session key.
     * @param mixed|null $value The session value.
     * @return mixed|null
     */
    function session(?string $key = null, $value = null)
    {

        if (is_null($key) && is_null($value)) {
            return $_SESSION;
        }

        if (!is_null($key) && is_null($value)) {
            return Session::get($key);
        }

        if (!is_null($key) && !is_null($value)) {
            Session::set($key, $value);
            return null;
        }

        return null;
    }
}

if (!function_exists("flash")){
    function flash($key = null, $value = null){
        Session::flash($key, $value);
    }
}

if (!function_exists('getFlash')) {
    /**
     * Get flash data from session.
     * @param string $key
     * @return mixed|null
     */
    function getFlash(string $key)
    {
        return Session::getFlash($key);
    }
}
#endregion

#region Base URL Helper
if (!function_exists('getBaseUrl')) {
    /**
     * Get the base URL of the application.
     * 
     * @return string
     */
    function getBaseUrl(): string
    {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'];
        $scriptName = $_SERVER['SCRIPT_NAME'];
        $basePath = rtrim(str_replace('\\', '/', dirname($scriptName)), '/') . '/';
        return $scheme . "://" . $host . $basePath;
    }
}
#endregion

#region CSRF Token Helper
if (!function_exists('csrf_field')) {
    /**
     * Generate a hidden input field with CSRF token.
     * @return string
     */
    function csrf_field(): string
    {
        $token = \TokenGenerator::csrf_generate();
        return '<input type="hidden" name="_csrf" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }
}
#endregion