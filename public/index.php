<?php
require __DIR__ . '/../vendor/autoload.php';
require '../src/_assets/Includes/autoloader.php';

ini_set('log_errors', 'On');
ini_set('error_log', '/chemin/fichier/logs/errors.log'); // Sur le serveur, créer un fichier de logs et mettre son emplacement ici. A faire directement sur le serv pas sur git ou quoi


$routes = [
    '/'      => \Controllers\Homepage::class,
    '/login' => \Controllers\LoginController::class,
    '/register' => \Controllers\RegisterController::class,
    '/forgot' => \Controllers\ForgotPasswordController::class,
    '/dashboard' => \Controllers\DashboardController::class,
    '/logout' => \Controllers\LogoutController::class,
];

$path = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') ?: '/';

try {
    if (!isset($routes[$path])) {
        http_response_code(404);
        (new \Views\Error('Erreur 404','Page introuvable', '404'))->show();
        exit;
    }
    (new $routes[$path]())->execute();
    end_page();
} catch (\Exceptions\ControllerException $e) {
    (new \Views\Error('Erreur',$e->getMessage(), 'Erreur'))->show();
}

function begin_page($title, $style, $navbar = true): void {
    ?>
    <!doctype html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <link rel="stylesheet" href="/css/index.css">
        <link rel="stylesheet" href="<?=$style?>">
        <link rel="apple-touch-icon" sizes="180x180" href="/images/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="/images/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="/images/favicon-16x16.png">
        <link rel="manifest" href="/images/site.webmanifest">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?=$title?></title>
    </head>
    <body>
    <?php if ($navbar) {
        $currentPage = $_SERVER['REQUEST_URI'];
        if (isset($_SESSION['user_id'])) { ?>
            <nav class="navbar">
            <ul class="nav-links">
                <li><a href="/dashboard" class="<?php echo ($currentPage == '/dashboard') ? 'active' : ''; ?>">Dashboard</a></li>
                <li><a href="/dashboard" class="<?php #echo ($currentPage == '/login') ? 'active' : ''; ?>" >Mes sondages</a></li>
                <li><a href="/dashboard" class="<?php #echo ($currentPage == '/register') ? 'active' : ''; ?>">Recherche</a></li>
                <li><a href="/logout" class="button-primary">Déconnexion</a></li>
            </ul>
            </nav>
            <?php }
        else {?>
    <nav class="navbar">
        <ul class="nav-links">
            <li><a href="/" , class="<?php echo ($currentPage == '/') ? 'active' : ''; ?>">Accueil</a></li>
            <li><a href="/login" class="<?php echo ($currentPage == '/login') ? 'active' : ''; ?>">Connexion</a></li>
            <li><a href="/register" class="<?php echo ($currentPage == '/register') ? 'active' : ''; ?>">Inscription</a></li>
        </ul>
    </nav>
            <?php }
    }
}

function end_page(): void {
    ?>
    </body>
    </html>
    <?php
}
