<?php

namespace Controllers;

use \_assets\Includes\DatabaseConnection;
use \Models\UserRepository;
use \Models\Users;

abstract class DatabaseController
{
    public static bool $isTesting = false;
    public static ?string $lastRedirect = null;
    protected DatabaseConnection $db;

    public function __construct(?DatabaseConnection $db = null){
        $this->db = $db ?? new DatabaseConnection();
        $this->initSecureSession();
        $this->setSecurityHeaders();
    }

    public function redirect(string $url): void
    {
        self::$lastRedirect = $url;
        if (!headers_sent()) {
            header("Location: $url");
        }
        $this->terminate();
    }

    public function terminate(): void
    {
        if (self::$isTesting) {
            throw new \RuntimeException('EXIT_CALLED');
        }
        exit;
    }

    protected function initSecureSession(): void // (paramètres des cookies trouvés grace à un grand modèle de language)
    {
        if (session_status() === PHP_SESSION_NONE) {
            if (!headers_sent()) {
                session_set_cookie_params([
                    'lifetime' => 86400, // 1 jour
                    'path' => '/',
                    // 'domain' => $_SERVER['HTTP_HOST'],
                    'secure' => isset($_SERVER['HTTPS']), // True si HTTPS (AlwaysData)
                    'httponly' => true, // Bloque les attaques XSS (impossible à lire en JS)
                    'samesite' => 'Strict' // Bloque les attaques CSRF
                ]);
            }
            session_start();
        }
    }

    protected function logSecurity(string $type, string $message): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'IP_INCONNUE';
        $date = date('Y-m-d H:i:s');
        $logMessage = "[$date] [$ip] [$type] $message \n";

        $logDir = __DIR__ . '/../../logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0777, true);
        }
        @error_log($logMessage, 3, $logDir . '/security.log');
    }

    protected function setSecurityHeaders(): void
    {
        if (!headers_sent()) {
            header('X-Frame-Options: DENY'); // Anti clickjacking
            header('X-Content-Type-Options: nosniff'); // Sécurité uploads, le navigateur ne peut pas connaitre le type de fichier
        }
    }


    protected function getPost(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? filter_input(INPUT_POST, $key) ?? $default;
    }

    protected function getQuery(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? filter_input(INPUT_GET, $key) ?? $default;
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