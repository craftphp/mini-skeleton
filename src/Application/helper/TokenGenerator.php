<?php
/**
 * TokenGenerator class
 * 
 * This class provides methods to generate and verify tokens.
 * The class supports the following token types:
 * - CSRF tokens for preventing Cross-Site Request Forgery attacks.
 * - JWT (JSON Web Tokens) for secure data transmission.
 * 
 */
class TokenGenerator
{
    /**
     * Generates a CSRF token and stores it in the session.
     * 
     * @return string The generated CSRF token.
     */
    public static function csrf_generate(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }
    /**
     * Verifies a given CSRF token against the one stored in the session.
     * 
     * @param string|null $token The CSRF token to verify.
     * @return bool True if the token is valid, false otherwise.
     */
    public static function csrf_verify(?string $token): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        return hash_equals($_SESSION['csrf_token'] ?? '', $token ?? '');
    }
    /**
     * Generates a JWT token.
     * @param array $payload The payload data to include in the token.
     * @param string $secret The secret key used to sign the token.
     * @param int $expiry The token expiry time in seconds. Default is 3600 seconds (1 hour).
     * @return string
     */
    public static function jwt_generate(array $payload, string $secret, int $expiry = 3600): string
    {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload['exp'] = time() + $expiry;
        $payload = json_encode($payload);

        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $secret, true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }
    /**
     * Verifies a given JWT token.
     * 
     * @param string $token The JWT token to verify.
     * @param string $secret The secret key used to sign the token.
     * @return bool True if the token is valid, false otherwise.
     */
    public static function jwt_verify(string $token, string $secret): bool
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }

        [$base64UrlHeader, $base64UrlPayload, $base64UrlSignature] = $parts;

        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $secret, true);
        $expectedSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        if (!hash_equals($expectedSignature, $base64UrlSignature)) {
            return false;
        }

        $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $base64UrlPayload)), true);
        if (isset($payload['exp']) && time() > $payload['exp']) {
            return false;
        }

        return true;
    }
}
