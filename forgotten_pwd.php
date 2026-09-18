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
    $mail->Username   = "{$_SERVER['MAIL_ADDRESS']}";
    $mail->Password   =
        "{$_SERVER['MAIL_PASSWORD']}";
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = "{$_SERVER['SMTPS_PORT']}";

    // Destinataires
    $mail->setFrom("{$_SERVER['MAIL_ADDRESS']}", 'ApocalypseHorsemen');
    $mail->addAddress('mattenyni@gmail.com');

    // Contenu de l'e-mail
    $mail->isHTML(true);                                  // Format HTML actif
    $mail->CharSet = 'UTF-8';                             // Gestion des accents
    $mail->Subject = 'Test d\'envoi';
    $mail->Body    = '<h1>Bonjour</h1><p>Ceci est un e-mail envoyé via le SMTP d\'Alwaysdata !</p>';
    $mail->AltBody = 'Ceci est la version texte brut pour les clients e-mail non compatibles HTML';

    $mail->send();
    echo 'Le message a bien été envoyé.';
} catch (Exception $e) {
    echo "Le message n'a pas pu être envoyé. Erreur : {$mail->ErrorInfo}";
}