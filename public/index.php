<?php
require __DIR__ . '/../vendor/autoload.php';
require '../src/_assets/Includes/autoloader.php';

ini_set('log_errors', 'On');
ini_set('error_log', '/logs/errors.log'); // Sur le serveur, créer un fichier de logs et mettre son emplacement ici. A faire directement sur le serv pas sur git ou quoi
ini_set('display_errors', '0');


$routes = [
    '/'      => \Controllers\HomepageController::class,
    '/login' => \Controllers\LoginController::class,
    '/register' => \Controllers\RegisterController::class,
    '/forgot' => \Controllers\ForgotPasswordController::class,
    '/dashboard' => \Controllers\DashboardController::class,
    '/profile' => \Controllers\ProfileController::class,
    '/surveys' => \Controllers\SurveysController::class,
    '/sitemap' => \Controllers\SitemapController::class,
    '/logout' => \Controllers\LogoutController::class,
    '/MentionsLegales' => \Controllers\MentionsLegalesController::class,
        '/api/check-username' => \Controllers\CheckUsernameController::class
];

$path = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') ?: '/';

if (!isset($routes[$path])) {
    http_response_code(404);
    (new \Views\Error('Erreur 404','Page introuvable', '/'))->show();
    exit;
}
(new $routes[$path]())->execute();
end_page();

function begin_page($title, $style, $navbar = true, $description = "", $link = "", $noindex = false): void {
    ?>
    <!doctype html>
    <html lang="fr">
    <head>
        <!-- Balise Méta -->
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Google -->
        <?php if ($noindex) { ?> <meta name="robots" content="noindex, nofollow">
        <?php } else { ?> <meta name="robots" content="index, follow"> <?php } ?>
        <meta name="description" content="<?=$description?>">
        <meta name="author" content="Lohann BALBAS, Gaël BARTHELEMY, Timéo MORSILLI, Mattéo YANNI">

        <!-- Open Graph -->
        <meta property="og:title" content="<?=$title?>">
        <meta property="og:type" content="website">
        <meta property="og:url" content="<?=$link?>">
        <meta property="og:description" content="<?=$description?>">
        <meta property="og:locale" content="fr_FR">
        <meta property="og:site_name" content="HorseForm">

        <!-- SChema.org JSON-LD -->
        <script type="application/ld+json">
            {
                "@context": "https://schema.org/",
                "@type": "WebApplication",
                "applicationCategory": "BusinessApplication",
                "operatingSystem": "Web",
                "name": "<?=$title?>",
                "inLanguage": "fr",
                "description": "<?=$description?>",
                "url": "<?=$link?>",

                "address": {
                    "@type": "PostalAddress",
                    "addressLocality": "Aix-en-Provence",
                    "addressRegion": "Provence Alpes Cote d'Azur",
                    "postalCode": "13100",
                    "addressCountry": "FR"
                }
            }
        </script>


        <link rel="stylesheet" href="/css/index.css">
        <link rel="stylesheet" href="<?=$style?>">
        <link rel="apple-touch-icon" sizes="180x180" href="/images/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="/images/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="/images/favicon-16x16.png">
        <link rel="manifest" href="/images/site.webmanifest">
        <link rel="canonical" href="<?=$link?>">

        <title><?=$title?></title>
    </head>
    <body>
    <?php if ($navbar) {
        $currentPage = $_SERVER['REQUEST_URI'];
        if (isset($_SESSION['user_id'])) { ?>
            <nav class="navbar">
            <ul class="nav-links">
                <li><a href="/dashboard" class="<?php echo ($currentPage == '/dashboard') ? 'active' : ''; ?>">Dashboard</a></li>
                <li><a href="/profile" class="<?php echo ($currentPage == '/profile') ? 'active' : ''; ?>">Mon profil</a></li>
                <li><a href="/dashboard">Mes sondages</a></li>
                <li><a href="/surveys" class="<?php echo ($currentPage == '/surveys') ? 'active' : ''; ?>">Tous les sondages</a></li>
                <li><a href="/sitemap" class="<?php echo ($currentPage == '/sitemap') ? 'active' : ''; ?>">Plan du site</a></li>
                <li><a href="/logout" class="button-primary">Déconnexion</a></li>
            </ul>
            </nav>
            <?php }
        else {?>
    <nav class="navbar">
        <ul class="nav-links">
            <li><a href="/" class="<?php echo ($currentPage == '/') ? 'active' : ''; ?>">Accueil</a></li>
            <li><a href="/surveys" class="<?php echo ($currentPage == '/surveys') ? 'active' : ''; ?>">Sondages</a></li>
            <li><a href="/sitemap" class="<?php echo ($currentPage == '/sitemap') ? 'active' : ''; ?>">Plan du site</a></li>
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
