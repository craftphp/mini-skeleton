<?php

$router = new Craft\Application\Router();

$router->apiGet('/hello/{name}', function ($name) : string {
    return "Hello, " . htmlspecialchars($name);
});

$router->apiGet('/hello/{param}', function ($param) : string {
    return "Hello, " . htmlspecialchars($param);
});