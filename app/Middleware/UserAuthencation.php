<?php
namespace App\Middleware;

use Craft\Application\Middleware;

/**
 * #### UserAuthencation Middleware
 * 
 * This middleware handles user authentication for incoming requests.
 */
class UserAuthencation extends Middleware{
    public static function registerSelf(): void
    {
        Middleware::register('auth', function($context) {
            // Implement your authentication logic here
            if (!isset($_SESSION['user'])) {
                http_response_code(401);
                echo "Unauthorized access!";
                return false;
            }
            return null;
        });
    }
}
