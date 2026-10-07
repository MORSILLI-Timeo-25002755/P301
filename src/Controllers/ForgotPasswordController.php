<?php

namespace Controllers;

use \_assets\Includes\DatabaseConnection;
use \Models\UserRepository;
use \Models\Users;
use \Views\Error;
use \Views\ForgotPassword;
use PHPMailer\PHPMailer\PHPMailer;;

class ForgotPasswordController extends DatabaseController
{
    private UserRepository $userRepository;

    public function __construct() {
        parent::__construct();
        $this->userRepository = new UserRepository($this->db);
    }

    public function execute(): void
    {
        $token = filter_input(INPUT_POST, 'token') ?: filter_input(INPUT_GET, 'token');

        if (!$token) {
            $this->requestReset();   // étape 1 : demande par email
            return;
        }

        $this->resetPassword((string)$token);   // étape 2 : nouveau mot de passe
    }

    private function requestReset(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $token = $this->generateCsrfToken();
            (new ForgotPassword(csrfToken : $token))->show();
            return;
        }

        $email = strtolower(trim((string) filter_input(INPUT_POST, 'email')));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            (new ForgotPassword(error: 'Veuillez entrer une adresse email valide.'))->show();
            return;
        }

        $user = $this->userRepository->findByEmail($email);

        if ($user !== null) {
            $token = bin2hex(random_bytes(32));   // 64 caractères hexadécimaux
            $expiry = date('Y-m-d H:i:s', time() + 900);   // valable 15 minutes

            // On stocke le hash du token, jamais le token lui-même
            $this->userRepository->setResetToken($user->getId(), hash('sha256', $token), $expiry);
            $this->sendResetEmail($user->getEmail(), $token);
        }

        // Même message que l'email existe ou non
        (new ForgotPassword(message: 'Si cette adresse existe, un email de réinitialisation vient de vous être envoyé.'))->show();
    }

    private function resetPassword(string $token): void
    {
        $user = $this->findUserByToken($token);

        if ($user === null) {
            (new Error('Lien invalide !', 'Ce lien est invalide ou a expiré.', '/forgot'))->show();
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            (new ForgotPassword(token: $token))->show();
            return;
        }

        $password = (string) filter_input(INPUT_POST, 'password');
        $confirm = (string) filter_input(INPUT_POST, 'confirm');

        if ($password !== $confirm) {
            (new ForgotPassword(token: $token, error: 'Les mots de passe ne correspondent pas.'))->show();
            return;
        }

        // Adapte les règles à celles de ton inscription
        if (strlen($password) < 8) {
            (new ForgotPassword(token: $token, error: 'Le mot de passe doit contenir au moins 8 caractères.'))->show();
            return;
        }

        $this->userRepository->updatePassword($user->getId(), password_hash($password, PASSWORD_DEFAULT));

        (new ForgotPassword(message: 'Mot de passe modifié. Vous pouvez maintenant vous connecter.'))->show();
    }

    private function findUserByToken(string $token): ?Users
    {
        // Format attendu : 64 caractères hexadécimaux
        if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
            return null;
        }

        $user = $this->userRepository->findByResetToken(hash('sha256', $token));

        return ($user !== null && $user->hasValidResetToken()) ? $user : null;
    }

    private function sendResetEmail(string $email, string $token): bool
    {
        $baseUrl = $_ENV['APP_URL'];
        $resetLink = $baseUrl . '/forgot?token=' . urlencode($token);

        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = "{$_ENV['MAIL_HOST']}";
            $mail->SMTPAuth = true;
            $mail->Username = "{$_ENV['MAIL_ADDRESS']}";
            $mail->Password = "{$_ENV['MAIL_PASSWORD']}";
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = "{$_ENV['SMTP_PORT']}";

            $mail->setFrom("{$_ENV['MAIL_ADDRESS']}", 'ApocalypseHorsemen');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Réinitialisation de votre mot de passe';
            $mail->Body = '<p>Bonjour,</p>'
                . '<p>Pour choisir un nouveau mot de passe, cliquez ici : '
                . '<a href="' . htmlspecialchars($resetLink) . '">Réinitialiser mon mot de passe</a></p>'
                . '<p>Ce lien expire dans 15 minutes. Si vous n\'êtes pas à l\'origine de cette demande, ignorez ce message.</p>';

            $mail->send();
            return true;
        } catch (\PHPMailer\PHPMailer\Exception $e) {
            error_log('Erreur envoi mail : ' . $mail->ErrorInfo);
            return false;
        }
    }
}