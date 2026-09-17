<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';
$dotenv = DotenvVault\DotenvVault::createImmutable(__DIR__);
$dotenv->safeLoad();
require 'vendor/autoload.php';

$mail = new PHPMailer(true);
$mail->SMTPDebug = 2;

try {
    // Configuration du serveur SMTP Alwaysdata
    $mail->isSMTP();
    $mail->Host       = "{$_SERVER['MAIL_HOST']}";
    $mail->SMTPAuth   = true;
    $mail->Username   = "{$_SERVER['MAIL_ADDRESS']}"; // Votre email Alwaysdata
    $mail->Password   =
        "{$_SERVER['MAIL_PASSWORD']}";             // Votre mot de passe email
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;      // Sécurisation SSL
    $mail->Port       = "{$_SERVER['SSL_PORT']}";                              // Port pour SSL

    // Destinataires
    $mail->setFrom("{$_SERVER['MAIL_ADDRESS']}", 'Horsemen blablanbla');
    $mail->addAddress('matteoyni@gmail.com');

    // Contenu de l'e-mail
    $mail->isHTML(true);                                  // Format HTML actif
    $mail->CharSet = 'UTF-8';                             // Gestion des accents
    $mail->Subject = 'Sujet de votre message';
    $mail->Body    = '<h1>Bonjour</h1><p>Ceci est un e-mail envoyé via le SMTP d\'Alwaysdata !</p>';
    $mail->AltBody = 'Ceci est la version texte brut pour les clients e-mail non compatibles HTML';

    $mail->send();
    echo 'Le message a bien été envoyé.';
} catch (Exception $e) {
    echo "Le message n'a pas pu être envoyé. Erreur : {$mail->ErrorInfo}";
}