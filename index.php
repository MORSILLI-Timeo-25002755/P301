<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require '_assets/includes/autoloader.php';

$routes = [
    '/'          => \Controllers\Homepage::class,
    '/login'     => \Controllers\LoginController::class,
    '/register'  => \Controllers\RegisterController::class,
    '/forgot'    => \Controllers\ForgotPasswordController::class,
    '/panel'     => \Controllers\PanelController::class,
    '/dashboard' => \Controllers\PanelController::class,
    '/logout'    => \Controllers\LogoutController::class
];

$path = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') ?: '/';

try {
    if (!isset($routes[$path])) {
        http_response_code(404);
        (new \Views\Error('Erreur 404','Page introuvable'))->show();
        exit;
    }
    (new $routes[$path]())->execute();
    end_page();
} catch (\Exceptions\ControllerException $e) {
    (new \Views\Error('Erreur',$e->getMessage()))->show();
}

function begin_page($title, $style): void {
    ?>
    <!doctype html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <link rel="stylesheet" href="_assets/css/index.css">
        <link rel="stylesheet" href="<?=$style?>">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?=$title?></title>
    </head>
    <body>
    <?php
}

function end_page(): void {
    ?>
    </body>
    </html>
    <?php
}
