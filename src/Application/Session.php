<?php
namespace Craft\Application;

/**
 * #### Session management class
 *
 * This class provides methods to start, get, set, flash and destroy session variables.
 *
 * It is used to manage user sessions in the application.
 */
#region Session
class Session
{
    /**
     * Start the session
     */
    public static function start()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Get a session variable
     *
     * @param string $key
     * @return mixed
     */
    public static function get($key)
    {
        return $_SESSION[$key] ?? null;
    }

    /**
     * Set a session variable
     *
     * @param string $key
     * @param mixed $value
     */
    public static function set($key, $value)
    {
        if (!session($key)) {
            $_SESSION[$key] = $value;
        }
    }

    /**
     * Flash a session variable
     *
     * @param string $key The session flash key.
     * @param mixed $value The session flash value.
     */
    public static function flash(string $key, $value)
    {
        self::start();
        $_SESSION['_flash'][$key] = $value;
    }

    /**
     * Get and remove a flash session variable
     *
     * @param string $key The session flash key.
     * @return mixed
     */
    public static function getFlash(string $key)
    {
        self::start();
        if (isset($_SESSION['_flash'][$key])) {
            $value = $_SESSION['_flash'][$key];
            unset($_SESSION['_flash'][$key]);
            return $value;
        }
        return null;
    }

    /**
     * Set an error message in the session
     *
     * @param string $message The error message.
     * @return $this
     */
    public function withError(string $message)
    {
        self::start();
        $_SESSION['_error'] = $message;
        return $this;
    }

    /**
     * Set a success message in the session
     *
     * @param string $message The success message.
     * @return $this
     */
    public function withSuccess(string $message)
    {
        self::start();
        $_SESSION['_success'] = $message;
        return $this;
    }

    /**
     * Destroy the session
     */
    public static function destroy()
    {
        session_unset();
        session_destroy();
    }
}
#endregion