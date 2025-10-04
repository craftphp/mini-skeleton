<?php

use App\Controller\HomeController;

$router = new Craft\Application\Router();

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_method'])) {
    $_SERVER['REQUEST_METHOD'] = strtoupper($_POST['_method']);
}

$router->get('/', [HomeController::class, 'index']);

$router->all('/hello/{name}', function ($name) : string {
    return "Hello, " . htmlspecialchars($name);
});
