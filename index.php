<?php
require '_assets/includes/autoloader.php';

$routes = [
    '/'      => \Controllers\Homepage::class,
    '/login' => \Controllers\LoginController::class,
];

$path = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') ?: '/';

try {
    if (!isset($routes[$path])) {
        http_response_code(404);
        (new \Views\Error('Page introuvable'))->show();
        exit;
    }

    (new $routes[$path]())->execute();
} catch (\Exceptions\ControllerException $e) {
    (new \Views\Error($e->getMessage()))->show();
}