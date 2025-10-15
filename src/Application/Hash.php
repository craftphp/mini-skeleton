<?php
namespace Craft\Application;

/**
 * #### Class Hash extends from password PHP functions
 * 
 * A simple utility class for hashing and verifying strings using default, bcrypt.
 */
#region Hash
class Hash {
    /**
     * Determine if current PHP build supports Argon2i via password_hash.
     * @return bool
     */
    public static function supportsArgon2i(): bool
    {
        if (!defined('PASSWORD_ARGON2I')) {
            return false;
        }
        try {
            $probe = @password_hash('probe', PASSWORD_ARGON2I);
            return $probe !== false;
        } catch (\Throwable $e) {
            return false;
        }
    }
    /**
     * Generate a secure hash of a string using default.
     * @param string $string The input string to hash
     * @return string The hashed string
     */
    public static function default(string $string): string
    {
        $hash = password_hash($string, PASSWORD_DEFAULT);
        if ($hash === false) {
            throw new \RuntimeException('Default hashing failed.');
        }
        return $hash;
    }

    /**
     * Generate a secure hash of a string using bcrypt.
     * @param string $string The input string to hash
     * @param array $options Optional options for bcrypt (e.g., ['cost' => 12])
     * @return string The bcrypt hashed string
     */
    public static function bcrypt(string $string, $options = []): string
    {
        $hash = password_hash($string, PASSWORD_BCRYPT, $options);
        if ($hash === false) {
            throw new \RuntimeException('Bcrypt hashing failed.');
        }
        return $hash;
    }

    /** **PHP 7.1 not supported**
     *
     * Generate a secure hash of a string using Argon2i.
     * 
     * **Note:** Argon2i support requires PHP 7.2+ and that the PHP build has Argon2i enabled.
     * If Argon2i is not supported, it falls back to bcrypt.
     * @param string $string The input string to hash
     * @param array $options Optional options for Argon2i
     * @return string The Argon2i hashed string or bcrypt fallback
     */
    public static function argon2i(string $string, array $options = []): string
    {
        if (!self::supportsArgon2i()) {
            $bcryptOptions = [];
            if (isset($options['cost']) && is_int($options['cost'])) {
                $bcryptOptions['cost'] = $options['cost'];
            }
            return self::bcrypt($string, $bcryptOptions);
        }

        try {
            $hash = password_hash($string, PASSWORD_ARGON2I, $options);
        } catch (\Throwable $e) {
            $hash = false;
        }

        if ($hash === false) {
            return self::bcrypt($string);
        }
        return $hash;
    }

    /**
     * Verify if a given string matches a default or bcrypt hash.
     * @param string $string The input string to verify
     * @param string $hash The hash to compare against
     * @param bool $error_on_failure Whether to throw an exception on failure
     * @return bool True if the string matches the hash, false otherwise
     */
    public static function verify(string $string, string $hash, bool $error_on_failure = false): bool
    {
        $verified = password_verify($string, $hash);

        if (!$verified && $error_on_failure) {
            throw new \RuntimeException('Hash verification failed. Please check your credentials.');
        }

        return (bool)$verified;
    }
}
#endregion