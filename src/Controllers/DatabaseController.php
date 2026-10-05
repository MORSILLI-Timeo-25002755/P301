<?php

namespace Controllers;

use \_assets\Includes\DatabaseConnection;
use \Models\UserRepository;
use \Models\Users;

abstract class DatabaseController
{
    protected DatabaseConnection $db;
    public function __construct(){
        $this->db = new DatabaseConnection();
        $this->initSecureSession();
        $this->setSecurityHeaders();
    }

    protected function initSecureSession(): void // (paramètres des cookies trouvés grace à un grand modèle de language)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'lifetime' => 86400, // 1 jour
                'path' => '/',
                // 'domain' => $_SERVER['HTTP_HOST'],
                'secure' => isset($_SERVER['HTTPS']), // True si HTTPS (AlwaysData)
                'httponly' => true, // Bloque les attaques XSS (impossible à lire en JS)
                'samesite' => 'Strict' // Bloque les attaques CSRF
            ]);
            session_start();
        }
    }

    protected function logSecurity(string $type, string $message): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'IP_INCONNUE';
        $date = date('Y-m-d H:i:s');
        $logMessage = "[$date] [$ip] [$type] $message \n";

        error_log($logMessage, 3, __DIR__ . '/../../logs/security.log'); // Créer un autre fichier de logs sur le serveur
    }

    protected function setSecurityHeaders(): void
    {
        header('X-Frame-Options: DENY'); // Anti clickjacking
        header('X-Content-Type-Options: nosniff'); // Sécurité uploads, le navigateur ne peut pas connaitre le type de fichier
    }


    protected function generateCsrfToken(): string // Sécurité anti attaque CRSF
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    protected function verifyCsrfToken(?string $token): bool
    {
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], (string)$token)) {
            $this->logSecurity('ALERTE CSRF', 'Jeton invalide ou manquant.');
            return false;
        }
        return true;
    }
}