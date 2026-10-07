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
            $token = bin2hex(random_bytes(32));
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

        $password = (string) filter_input(INPUT_POST, 'pwd');
        $confirm = (string) filter_input(INPUT_POST, 'conf');

        $regex = '/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{12,}$/';

        if ($password !== $confirm || !preg_match($regex, $password)) {
            $token = $this->generateCsrfToken();
            (new \Views\Error('Erreur password', 'Mot de passe pas assez sécurisé', '/forgot?token=' . urlencode($token)))->show();
            exit;
        }

        $this->userRepository->updatePassword($user->getId(), password_hash($password, PASSWORD_DEFAULT));

        (new ForgotPassword(message: 'Mot de passe modifié. Vous pouvez maintenant vous connecter.'))->show();
    }

    private function findUserByToken(string $token): ?Users
    {
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
            $mail->CharSet = 'UTF-8';
            $mail->isSMTP();
            $mail->Host = "{$_ENV['MAIL_HOST']}";
            $mail->SMTPAuth = true;
            $mail->Username = "{$_ENV['MAIL_ADDRESS']}";
            $mail->Password = "{$_ENV['MAIL_PASSWORD']}";
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = "{$_ENV['SMTP_PORT']}";

            $mail->setFrom("{$_ENV['MAIL_ADDRESS']}", 'HorseForm');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Réinitialisation de votre mot de passe';
            $safeResetLink = htmlspecialchars($resetLink, ENT_QUOTES, 'UTF-8');
            $mail->Body = <<<HTML
<!doctype html>
<html lang="fr">
<body style="margin:0; padding:0; background-color:#f8fafc; color:#0f172a; font-family:Arial,Helvetica,sans-serif;">
    <div style="display:none; max-height:0; overflow:hidden; opacity:0;">
        Réinitialisez votre mot de passe HorseForm en quelques clics.
    </div>
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#f8fafc;">
        <tr>
            <td align="center" style="padding:40px 16px;">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:560px;">
                    <tr>
                        <td align="center" style="padding:0 0 20px;">
                            <a href="{$baseUrl}" style="color:#4f46e5; font-size:24px; font-weight:700; text-decoration:none;">
                                HorseForm
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color:#ffffff; border:1px solid #e2e8f0; border-radius:8px; padding:40px 36px;">
                            <p style="margin:0 0 12px; color:#4f46e5; font-size:12px; font-weight:700; letter-spacing:1px; text-transform:uppercase;">
                                Sécurité du compte
                            </p>
                            <h1 style="margin:0 0 16px; color:#0f172a; font-size:28px; line-height:1.25;">
                                Réinitialisez votre mot de passe
                            </h1>
                            <p style="margin:0 0 24px; color:#64748b; font-size:16px; line-height:1.6;">
                                Bonjour,<br><br>
                                Une demande de réinitialisation de mot de passe a été effectuée pour votre compte HorseForm.
                            </p>
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td style="border-radius:6px; background-color:#4f46e5;">
                                        <a href="{$safeResetLink}" style="display:inline-block; padding:14px 22px; color:#ffffff; font-size:15px; font-weight:700; text-decoration:none;">
                                            Choisir un nouveau mot de passe
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            <p style="margin:28px 0 0; padding-top:20px; border-top:1px solid #e2e8f0; color:#64748b; font-size:13px; line-height:1.6;">
                                Ce lien est valable pendant <strong style="color:#0f172a;">15 minutes</strong>.
                                Si vous n'êtes pas à l'origine de cette demande, vous pouvez ignorer cet e-mail.
                            </p>
                            <p style="margin:16px 0 0; color:#94a3b8; font-size:12px; line-height:1.5; word-break:break-all;">
                                Le bouton ne fonctionne pas ? Copiez ce lien dans votre navigateur :<br>
                                <a href="{$safeResetLink}" style="color:#4f46e5;">{$safeResetLink}</a>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding:22px 16px 0; color:#94a3b8; font-size:12px;">
                            © 2026 HorseForm · Créez, partagez et analysez vos sondages.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
            $mail->AltBody = "Bonjour,\n\n"
                . "Une demande de réinitialisation de mot de passe a été effectuée pour votre compte HorseForm.\n\n"
                . "Choisissez un nouveau mot de passe : {$resetLink}\n\n"
                . "Ce lien est valable pendant 15 minutes. Si vous n'êtes pas à l'origine de cette demande, ignorez cet e-mail.";

            $mail->send();
            return true;
        } catch (\PHPMailer\PHPMailer\Exception $e) {
            error_log('Erreur envoi mail : ' . $mail->ErrorInfo);
            return false;
        }
    }
}