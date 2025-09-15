<?php
namespace App\Middleware;

use Craft\Application\Middleware;

/**
 * #### VerifyCsrfToken Middleware
 * 
 * This middleware verifies CSRF tokens for incoming requests to protect against CSRF attacks.
 */
class VerifyCsrfToken extends Middleware
{
    public static function registerSelf(): void
    {
        Middleware::register('csrf', function($context) {
            $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
            if (!in_array($method, ['POST', 'PUT', 'DELETE'])) {
                return null;
            }

            $token = $_POST['_csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
            if (!\TokenGenerator::csrf_verify($token)) {
                http_response_code(419);
                echo "CSRF token mismatch!";
                return false;
            }

            return null;
        });
    }
}
