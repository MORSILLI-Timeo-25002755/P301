<?php

namespace Controllers;

use _assets\Includes\DatabaseConnection;
use models\UserRepository;
use models\Users;
use PHPMailer\PHPMailer\PHPMailer;
use Views\Error;
use Views\forgotPassword;

class ForgotPasswordController
{
    public function execute(): void
    {
        $userRepository = new UserRepository(new DatabaseConnection());
        $token = filter_input(INPUT_POST, 'token') ?: filter_input(INPUT_GET, 'token');

        if (!$token) {
            $this->requestReset($userRepository);   // étape 1 : demande par email
            return;
        }

        $this->resetPassword($userRepository, (string)$token);   // étape 2 : nouveau mot de passe
    }

    private function requestReset(UserRepository $userRepository): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            (new ForgotPassword())->show();
            return;
        }

        $email = strtolower(trim((string) filter_input(INPUT_POST, 'email')));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            (new ForgotPassword(error: 'Veuillez entrer une adresse email valide.'))->show();
            return;
        }

        $user = $userRepository->findByEmail($email);

        if ($user !== null) {
            $token = bin2hex(random_bytes(32));   // 64 caractères hexadécimaux
            $expiry = date('Y-m-d H:i:s', time() + 900);   // valable 15 minutes

            // On stocke le hash du token, jamais le token lui-même
            $userRepository->setResetToken($user->getId(), hash('sha256', $token), $expiry);
            $this->sendResetEmail($user->getEmail(), $token);
        }

        // Même message que l'email existe ou non
        (new ForgotPassword(message: 'Si cette adresse existe, un email de réinitialisation vient de vous être envoyé.'))->show();
    }

    private function resetPassword(UserRepository $userRepository, string $token): void
    {
        $user = $this->findUserByToken($userRepository, $token);

        if ($user === null) {
            (new Error('Ce lien est invalide ou a expiré.'))->show();
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

        $userRepository->updatePassword($user->getId(), password_hash($password, PASSWORD_DEFAULT));

        (new ForgotPassword(message: 'Mot de passe modifié. Vous pouvez maintenant vous connecter.'))->show();
    }

    private function findUserByToken(UserRepository $userRepository, string $token): ?Users
    {
        // Format attendu : 64 caractères hexadécimaux
        if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
            return null;
        }

        $user = $userRepository->findByResetToken(hash('sha256', $token));

        return ($user !== null && $user->hasValidResetToken()) ? $user : null;
    }

    private function sendResetEmail(string $email, string $token): bool
    {
        require_once __DIR__ . '/../vendor/autoload.php';

        $baseUrl = 'http://localhost:8080';
        $resetLink = $baseUrl . '/forgot?token=' . urlencode($token);

        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = "{$_SERVER['MAIL_HOST']}";
            $mail->SMTPAuth = true;
            $mail->Username = "{$_SERVER['MAIL_ADDRESS']}";
            $mail->Password = "{$_SERVER['MAIL_PASSWORD']}";
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = "{$_SERVER['SMTP_PORT']}";

            // Destinataires
            $mail->setFrom("{$_SERVER['MAIL_ADDRESS']}", 'ApocalypseHorsemen');
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